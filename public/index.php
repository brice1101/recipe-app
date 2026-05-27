<?php

/**
 * Front controller - all requests go through here
 * Start the dev server from the project root:
 *      php -S localhost:8000 -t public
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Route: GET / or GET /recipes
// More routes will be added as controllers are built
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

echo "Recipe App - routing coming soon. URI: $uri";