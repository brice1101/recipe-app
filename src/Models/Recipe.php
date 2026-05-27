<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Recipe
{
    // --------
    // Read
    // --------

    /**
     * Return all recipes with their comma-separated ingredient names attached.
     * Supports optional filtering by cuisine, cooked status, and a search term
     * that matches against the title or any ingredient name.
     *
     * @return array{
     *     cuisine?: string,
     *     cooked?: boolean,
     *     search?: string,
     * } $filters
     *
     * @return array<Recipe>
     */
    public static function all(array $filters = []): array
    {
        $db = Database::getInstance();
        $params = [];

        $sql = "SELECT r.*, GROUP_CONCAT(i.name, ', ') AS ingredients
        FROM recipes r
        LEFT JOIN recipe_ingredients ri ON ri.recipe_id = r.id
        LEFT JOIN ingredients i ON i.id = ri.ingredient_id";

        $where = [];

        if (!empty($filters['cuisine'])) {
            $where[] = "r.cuisine = :cuisine";
            $params[':cuisine'] = $filters['cuisine'];
        }

        if (isset($filters['cooked'])) {
            $where[] = "r.cooked = :cooked";
            $params[':cooked'] = (int) $filters['cooked'] ? 1 : 0;
        }

        if (!empty($filters['search'])) {
            // Match title OR any ingredient name
            $where[] = "r.title LIKE :search OR i.name LIKE :search_i";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search_i'] = '%' . $filters['search'] . '%';
        }

        if ($where) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY r.id ORDER BY r.title ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find a single recipe by id, including its list of ingredients
     *
     * @return array|null
     */

    public static function find(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, GROUP_CONCAT(i.name, ', ') AS ingredients
        FROM recipes r
        LEFT JOIN recipe_ingredients ri ON ri.recipe_id = r.id
        LEFT JOIN ingredients i ON i.id = ri.ingredient_id
        WHERE r.id = :id
        GROUP BY r.id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    /**
     * Return the full ingredient rows for a given recipe.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public static function getIngredients(int $recipeId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT i.id, i.name
FROM ingredients i 
JOIN recipe_ingredients ri ON ri.ingredient_id = i.id
WHERE ri.recipe_id = :recipe_id
ORDER BY i.name");
        $stmt->execute([':recipe_id' => $recipeId]);
        return $stmt->fetchAll();
    }

    // --------
    // Write
    // --------

    /**
     * Insert a new recipe into the database and return its ID.
     *
     * @param array{
     *     title: string,
     *     cuisine: string,
     *     instructions: string,
     *     rating: int|null,
     *     cooked: bool,
     *     image_path: string|null
     * } $data
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO recipes (title, cuisine, instructions, rating, cooked, image_path)
        VALUES (:title, :cuisine, :instructions, :rating, :cooked, :image_path)");
        $stmt->execute([
            ':title' => trim($data['title']),
            ':cuisine' => trim($data['cuisine']),
            ':instructions' => trim($data['instructions']),
            ':rating' => isset($data['rating']) ? (int) $data['rating'] : null,
            ':cooked' => (bool) $data['cooked'] ? 1 : 0,
            ':image_path' => $data['image_path'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    }

    /**
     * Update an existing recipe in the database.
     * Only the keys that are present in the $data array will be updated.
     *
     * @param array{
     *     title?: string,
     *     cuisine?: string,
     *     instructions?: string,
     *     rating?: int|null,
     *     cooked?: bool,
     *     image_path?: string|null,
     * } $data
     */
    public static function update(int $id, array $data): bool
    {
        $allowed = ['title', 'cuisine', 'instructions', 'rating', 'cooked', 'image_path'];
        $sets = [];
        $params = [':id' => $id];

        foreach ($allowed as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            $sets[] = "$field = :$field";
            $params[":$field"] = match ($field) {
                'cooked' => $data[$field] ? 1 : 0,
                'title', 'cuisine', 'instructions' => trim((string) $data[$field]),
                default => $data[$field],
            };
        }

        if (empty($sets)) {
            return false;
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE recipes SET " . implode(', ', $sets) . " WHERE id = :id");
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Toggle the cooked flag on a recipe and return the new value
     *
     */
    public static function toggleCooked(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE recipes SET cooked = 1 - cooked WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // Return the new value of cooked
        $row = $db->prepare("SELECT cooked FROM recipes WHERE id = :id");
        return (bool) $row->fetchColumn();
    }

    /**
     * Delete a recipe by ID
     * recipe_ingredients rows are removed automatically via ON DELETE CASCADE.
     * The caller is responsible for deleting any associated image file.
     */
    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM recipes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    // --------
    // Ingredient linking
    // --------

    /**
     * Replace a recipe's ingredient list with a new set of names
     * Create missing ingredients automatically via Ingredient::firstOrCreate().
     *
     * @param string[] $ingredientNames
    */
    public static function syncIngredients(int $recipeId, array $ingredientNames): void {
        $db = Database::getInstance();

        // Remove existing links
        $db->prepare("DELETE FROM recipe_ingredients WHERE recipe_id = :recipe_id")
            ->execute([':recipe_id' => $recipeId]);

        $insert = $db->prepare("
        INSERT OR IGNORE INTO recipe_ingredients (recipe_id, ingredient_id)
        VALUES (:recipe_id, :ingredient_id)"
            );

        foreach ($ingredientNames as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }
            $ingredientId = Ingredient::firstOrCreate($name);
            $insert->execute([':recipe_id' => $recipeId, ':ingredient_id' => $ingredientId]);
        }
    }
}