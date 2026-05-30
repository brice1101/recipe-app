
<?php
$pageTitle = 'All Recipes — Recipe Book';
require __DIR__ . '/../partials/header.php';

/**
 * Variables injected by RecipeController::index():
 *   array $recipes — rows from Recipe::all()
 *   array $cuisines — distinct cuisine strings
 *   array $filters — active filter values
 */

function stars(int|null $rating): string {
    if (!$rating) return '';
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}
?>

<div class="container">

    <div class="page-header">
        <p class="page-header__eyebrow">Your kitchen collection</p>
        <h1 class="page-header__title">Recipe Book</h1>
        <p class="page-header__subtitle">
            <?= count($recipes) ?> recipe<?= count($recipes) !== 1 ? 's' : '' ?>
            <?= !empty($filters) ? '— filtered' : 'in your collection' ?>
        </p>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="/recipes" class="filter-bar">
        <div class="filter-group">
            <label for="search">Search</label>
            <input
                type="text"
                id="search"
                name="search"
                placeholder="Title or ingredient…"
                value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
            >
        </div>

        <div class="filter-group">
            <label for="cuisine">Cuisine</label>
            <select id="cuisine" name="cuisine">
                <option value="">All cuisines</option>
                <?php foreach ($cuisines as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>"
                        <?= ($filters['cuisine'] ?? '') === $c ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="cooked">Status</label>
            <select id="cooked" name="cooked">
                <option value="">All recipes</option>
                <option value="1" <?= isset($filters['cooked']) &&  $filters['cooked'] ? 'selected' : '' ?>>Cooked ✓</option>
                <option value="0" <?= isset($filters['cooked']) && !$filters['cooked'] ? 'selected' : '' ?>>Not yet cooked</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn--primary">Filter</button>
            <?php if (!empty($filters)): ?>
                <a href="/recipes" class="btn btn--ghost">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <!-- Recipe grid -->
    <div class="recipe-grid">
        <?php if (empty($recipes)): ?>
            <div class="empty-state">
                <div class="empty-state__icon">🍽️</div>
                <p class="empty-state__title">No recipes found</p>
                <p class="text-muted mt-1">
                    <?= !empty($filters) ? 'Try adjusting your filters.' : 'Add your first recipe to get started.' ?>
                </p>
                <?php if (empty($filters)): ?>
                    <a href="/recipes/create" class="btn btn--primary mt-2">+ Add Recipe</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <?php foreach ($recipes as $recipe): ?>
                <div class="recipe-card">

                    <?php if (!empty($recipe['image_path'])): ?>
                        <img
                            src="/<?= htmlspecialchars($recipe['image_path']) ?>"
                            alt="<?= htmlspecialchars($recipe['title']) ?>"
                            class="recipe-card__image"
                        >
                    <?php else: ?>
                        <div class="recipe-card__image-placeholder">🍴</div>
                    <?php endif; ?>

                    <div class="recipe-card__body">
                        <div class="recipe-card__meta">
                            <?php if (!empty($recipe['cuisine'])): ?>
                                <span class="recipe-card__cuisine"><?= htmlspecialchars($recipe['cuisine']) ?></span>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>

                            <?php if ($recipe['cooked']): ?>
                                <span class="recipe-card__cooked-badge">Cooked ✓</span>
                            <?php endif; ?>
                        </div>

                        <h2 class="recipe-card__title"><?= htmlspecialchars($recipe['title']) ?></h2>

                        <?php if (!empty($recipe['ingredients'])): ?>
                            <p class="recipe-card__ingredients"><?= htmlspecialchars($recipe['ingredients']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($recipe['rating'])): ?>
                            <div class="recipe-card__rating"><?= stars((int) $recipe['rating']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="recipe-card__footer">
                        <a href="/recipes/<?= $recipe['id'] ?>" class="recipe-card__link">
                            View recipe →
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
