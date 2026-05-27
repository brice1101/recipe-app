<?php

namespace App\Models;

use App\Database\Database;
use PDO;

class Ingredient
{
    // --------
    // Read
    // --------

    /**
     * Return every ingredient, ordered alphabetically by name.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public static function all(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT id, name FROM ingredients ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Return a single ingredient by its ID.
     *
     * @return array{id: int, name: string}|null
     */
    public static function find(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name FROM ingredients WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Find a single ingredient by name
     *
     *@return array{id: int, name: string}|null
     */
    public static function findByName(string $name): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name FROM ingredients WHERE name = :name COLLATE NOCASE");
        $stmt->execute([':name' => trim($name)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // --------
    // Write
    // --------

    /**
     * Insert a new ingredient into the database and return its ID.
     * If an ingredient with the same name already exists, return its ID.
     */
    public static function firstOrCreate(string $name): int {

        $name = trim($name);

        $existing = self::findByName($name);
        if ($existing) {
            return (int) $existing['id'];
        }

        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO ingredients (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        return (int) $db->lastInsertId();
    }

    /**
     * Delete an ingredient by its ID.
     *The recipe_ingredients rows are removed automatically via ON DELETE CASCADE.
     */

    public static function delete(int $id): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("DELETE FROM ingredients WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}