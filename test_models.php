<?php

/**
 * Quick smoke-test for the model layer
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Recipe;
use App\Models\Ingredient;

// ---- Helpers ----

function pass(string $msg): void { echo " PASS: $msg\n"; }
function fail(string $msg): void { echo " FAIL: $msg\n"; }
function section(string $title): void { echo "\n[$title]\n"; }

// ---- Ingredient tests ----

section('Ingredient::all()');
$ingredients = Ingredient::all();
count($ingredients) > 0 ? pass(count($ingredients) . ' ingredients found') : fail('No ingredients found');

section('Ingredient::firstOrCreate()');
$id1 = Ingredient::firstOrCreate('Olive Oil');
$id2 = Ingredient::firstOrCreate('Olive Oil');
$id1 === $id2 ? pass('Same ingredient ID returned for existing ingredient') : fail('Different ingredient IDs returned for existing ingredient');

section('Ingredient::findByName()');
$found = Ingredient::findByName('olive oil'); // case-insensitive
$found ? pass('Ingredient found') : fail('Ingredient not found');

// ---- Recipe tests ----

section('Recipe::all() - no filters');
$all = Recipe::all();
count($all) > 0 ? pass(count($all) . ' recipes found') : fail('No recipes found');

section('Recipe::all() - filter by cuisine');
$indian = Recipe::all(['cuisine' => 'Indian']);
count($indian) === 1 ? pass('1 Indian recipe found') : fail('expected 1 Indian recipe, found ' . count($indian));

section('Recipe::all() - filter by cooked');
$cooked = Recipe::all(['cooked' => true]);
count($cooked) === 1 ? pass('1 cooked recipe found') : fail('expected 1 cooked recipe, found ' . count($cooked));

section('Recipe::all() - search by title');
$search = Recipe::all(['search' => 'chicken']);
count($search) === 1 ? pass('1 recipe found for "chicken"') : fail('expected 1 recipe for "chicken", found ' . count($search));

section('Recipe::all() - search by ingredient');
$byIngredient = Recipe::all(['search' => 'ginger']);
count($byIngredient) >= 1 ? pass('1 recipe found with "ginger"') : fail('expected 1 recipe with "ginger", found ' . count($byIngredient));

section('Recipe::find()');
$first = $all[0];
$recipe = Recipe::find($first['id']);
$recipe ? pass('Recipe found') : fail('Recipe not found');

// This test is commented out because the function is not yet implemented
// section('Recipe::allCuisines()');
// $cuisines = Recipe::allCuisines();
// count($cuisines) > 0 ? pass(count($cuisines) . ' cuisines found') : fail('No cuisines found');

section('Recipe::create() + syncIngredients()');
$newId = Recipe::create([
    'title' => 'Amafi-Style Risotto with Jumbo King Prawns',
    'cuisine' => 'Italian',
    'instructions' => '1. Cook Veg, 2. Fry Prawns, 3. Add Risotto Rice ...',
    'rating' => 3,
    'cooked' => false,
    'image_path' => null,
]);
$newId > 0 ? pass('Recipe created') : fail('Recipe not created');

Recipe::syncIngredients($newId, ['Flour', 'Water', 'Salt']);
$linked = Recipe::getIngredients($newId);
count($linked) === 3 ? pass('3 ingredients linked to recipe') : fail('expected 3 ingredients, found ' . count($linked));

section('Recipe::update()');
$updated = Recipe::update($newId, ['title' => 'Updated Recipe', 'rating' => 4]);
$updated ? pass('Recipe updated') : fail('Recipe not updated');
$check = Recipe::find($newId);
$check['rating'] === 4 ? pass('Rating updated') : fail('Rating not updated');
$check['title'] === 'Updated Recipe' ? pass('Title updated') : fail('Title not updated');

section('Recipe::toggleCooked()');
$before = (bool) Recipe::find($newId)['cooked'];
$after = Recipe::toggleCooked($newId);
$after !== $before ? pass('Cooked status toggled') : fail('Cooked status not toggled');

section('Recipe::delete()');
$deleted = Recipe::delete($newId);
$deleted ? pass('Recipe deleted') : fail('Recipe not deleted');
$gone = Recipe::find($newId);
$gone === null ? pass('Recipe no longer exists') : fail('Recipe still exists');

echo "\Done.\n";