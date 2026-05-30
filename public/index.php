<?php

/**
 * Front controller — all HTTP requests enter here.
 * Start the dev server from the project root:
 *   php -S localhost:8000 -t public
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Router;
use App\Controllers\RecipeController;

$router = new Router();

// ── Recipe routes ────────────────────────────────────────────────────────────
//
//  GET / → redirect to /recipes
//  GET /recipes → list + filter
//  GET /recipes/create → new recipe form
//  POST /recipes → store new recipe
//  GET /recipes/{id} → show single recipe
//  GET /recipes/{id}/edit → edit form
//  POST /recipes/{id}/edit → update recipe
//  POST /recipes/{id}/delete → delete recipe
//  POST /recipes/{id}/toggle-cooked → toggle cooked flag (JSON)
//
// Note: HTML forms only support GET and POST, so PUT/PATCH/DELETE are handled
// as POST to dedicated sub-paths (/edit, /delete, /toggle-cooked).

$router->get('/', function () {
    header('Location: /recipes');
    exit;
});

$router->get('/recipes', function () {
    (new RecipeController())->index();
});

$router->get('/recipes/create', function () {
    (new RecipeController())->create();
});

$router->post('/recipes', function () {
    (new RecipeController())->store();
});

$router->get('/recipes/{id}', function (array $params) {
    (new RecipeController())->show($params);
});

$router->get('/recipes/{id}/edit', function (array $params) {
    (new RecipeController())->edit($params);
});

$router->post('/recipes/{id}/edit', function (array $params) {
    (new RecipeController())->update($params);
});

$router->post('/recipes/{id}/delete', function (array $params) {
    (new RecipeController())->destroy($params);
});

$router->post('/recipes/{id}/toggle-cooked', function (array $params) {
    (new RecipeController())->toggleCooked($params);
});

// ── Dispatch ─────────────────────────────────────────────────────────────────

$router->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);
