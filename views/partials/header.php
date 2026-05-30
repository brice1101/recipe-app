<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Recipe Book') ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>

<nav class="nav">
    <div class="nav__inner">
        <a href="/recipes" class="nav__logo">
            🍳 Recipe <span>Book</span>
        </a>
        <div class="nav__links">
            <a href="/recipes/create" class="btn--nav">+ Add Recipe</a>
        </div>
    </div>
</nav>

<main>

