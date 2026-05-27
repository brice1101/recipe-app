<?php

/**
 * Database setup script
 * Run once from the command line: php setup_db.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Database\Database;

$db = Database::getInstance();

echo "Creating tables...\n";

// recipe table
$db->exec("
CREATE TABLE IF NOT EXISTS recipes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    cuisine TEXT NOT NULL DEFAULT '',
    instructions TEXT NOT NULL DEFAULT '',
    rating INTEGER CHECK(rating IS NULL OR (rating >= 1 AND rating <= 5)),
    cooked INTEGER NOT NULL DEFAULT 0, -- 0 = not cooked, 1 = cooked
    image_path TEXT
);
");

// ingredient table
$db->exec("
CREATE TABLE IF NOT EXISTS ingredients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE
);
");

// recipe_ingredients join table
$db->exec("
CREATE TABLE IF NOT EXISTS recipe_ingredients (
    recipe_id INTEGER NOT NULL REFERENCES recipes(id) ON DELETE CASCADE,
    ingredient_id INTEGER NOT NULL REFERENCES ingredients(id) ON DELETE CASCADE,
    PRIMARY KEY (recipe_id, ingredient_id)  
);
");

echo "Tables created successfully!\n";

// seed some sample data
echo "Seeding sample data...\n";

$recipes = [
    [
        'title' => 'Chicken, Date & Tamarind Curry with Kachumber',
        'cuisine' => 'Indian',
        'instructions' => '1. Caramelise most of the sliced red onions in butter. \n2. Combine 400ml 
        boiled water with dates, tamarind, and creamed coconut \n3. Add ginger, turmeric, curry powder, 
        and black pepper to ref onion \n4. Add the date & tamarind stock to the pan, then add the
        chicken \n5. Cook for 18 minutes \n6. Combine diced tomato, diced cucumber, and remaining
        onion in a bowl with salt and olive oil \n7. Remove the pan from heat and shred cooked
        chicken \n8. Serve curry over rice, with kachumber and coriander to the side.',
        'rating' => 4,
        'cooked' => 0,
        'image_path' => null,
    ],
    [
        'title' => 'Open Steak Sandwich With Balsamic Onions and Chips',
        'cuisine' => 'British',
        'instructions' => '1. Caramelise red onions in butter. \n2. Combine mustard and mayo in a bowl.
        \n3. Add balsamic vinegar and 2 tbsp sugar to the pan, cook 2-3 mins. \n4. etc.',
        'rating' => null,
        'cooked' => 1,
        'image_path' => null,
    ],
];

$ingredientMap = [
    "Chicken, Date & Tamarind Curry with Kachumber" => ["turmeric", "chicken breast", "chicken stock",
        "curry powder", "ginger", "tomato", "rice", "cucumber", "coriander", "dates", "red onion",
        "tamarind paste", "creamed coconut"],
    "Open Steak Sandwich With Balsamic Onions and Chips" => ["rump steak", "mayonnaise", "ciabatta",
        "rosemary", "wholegrain mustard", "rocket", "cherry tomatoes", "balsamic vinegar",
        "white potato"]
];

$insertRecipe = $db->prepare("INSERT INTO recipes (title, cuisine, instructions, rating, cooked, image_path)
    VALUES (:title, :cuisine, :instructions, :rating, :cooked, :image_path)");

$insertIngredient = $db->prepare("INSERT INTO ingredients (name) VALUES (:name)");

$getIngredientId = $db->prepare("SELECT id FROM ingredients WHERE name = :name");

$insertRecipeIngredient = $db->prepare("INSERT INTO recipe_ingredients (recipe_id, ingredient_id)
    VALUES (:recipe_id, :ingredient_id)");

foreach ($recipes as $recipe) {
    $insertRecipe->execute($recipe);
    $recipeId = (int) $db->lastInsertId();

    foreach ($ingredientMap[$recipe['title']] as $ingredientName) {
        $insertIngredient->execute([':name' => $ingredientName]);
        $getIngredientId->execute([':name' => $ingredientName]);
        $ingredientId = (int) $getIngredientId->fetchColumn();

        $insertRecipeIngredient->execute(['recipe_id' => $recipeId, 'ingredient_id' => $ingredientId]);
    }
}

echo "Sample data seeded successfully!\n";
echo "Database setup complete. Saved to config/recipes.sqlite\n";
