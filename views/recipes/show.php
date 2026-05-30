<?php
$pageTitle = htmlspecialchars($recipe['title']) . ' — Recipe Book';
require __DIR__ . '/../partials/header.php';

/**
 * Variables injected by RecipeController::show():
 *   array $recipe — a single recipe row with 'ingredients' concatenated
 */

function starsShow(int|null $rating): string {
    if (!$rating) return '';
    return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

$ingredientList = !empty($recipe['ingredients'])
    ? array_map('trim', explode(',', $recipe['ingredients']))
    : [];
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 4rem;">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="/recipes">All Recipes</a>
        <span class="breadcrumb__sep">›</span>
        <span><?= htmlspecialchars($recipe['title']) ?></span>
    </nav>

    <div class="recipe-show">

        <!-- Main column -->
        <div>

            <?php if (!empty($recipe['image_path'])): ?>
                <img
                    src="/<?= htmlspecialchars($recipe['image_path']) ?>"
                    alt="<?= htmlspecialchars($recipe['title']) ?>"
                    class="recipe-show__hero"
                >
            <?php else: ?>
                <div class="recipe-show__hero-placeholder">🍴</div>
            <?php endif; ?>

            <?php if (!empty($recipe['cuisine'])): ?>
                <p class="recipe-show__eyebrow"><?= htmlspecialchars($recipe['cuisine']) ?></p>
            <?php endif; ?>

            <h1 class="recipe-show__title"><?= htmlspecialchars($recipe['title']) ?></h1>

            <!-- Action buttons -->
            <div class="recipe-show__actions">

                <button
                    id="cooked-btn"
                    class="btn <?= $recipe['cooked'] ? 'btn--cooked is-cooked' : 'btn--secondary' ?>"
                    data-id="<?= $recipe['id'] ?>"
                    data-cooked="<?= $recipe['cooked'] ? '1' : '0' ?>"
                >
                    <?= $recipe['cooked'] ? '✓ Cooked' : 'Mark as Cooked' ?>
                </button>

                <a href="/recipes/<?= $recipe['id'] ?>/edit" class="btn btn--ghost">
                    ✏️ Edit
                </a>

                <form method="POST" action="/recipes/<?= $recipe['id'] ?>/delete"
                      onsubmit="return confirm('Delete this recipe? This cannot be undone.')">
                    <button type="submit" class="btn btn--danger btn--sm">🗑 Delete</button>
                </form>

            </div>

            <hr class="divider">

            <!-- Instructions -->
            <div class="recipe-show__instructions">
                <h3>Method</h3>
                <?php if (!empty($recipe['instructions'])): ?>
                    <p><?= htmlspecialchars($recipe['instructions']) ?></p>
                <?php else: ?>
                    <p class="text-muted" style="font-style:italic;">No instructions added yet.</p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Sidebar -->
        <aside class="recipe-sidebar">

            <!-- Ingredients -->
            <?php if (!empty($ingredientList)): ?>
                <div class="sidebar-card">
                    <h3 class="sidebar-card__title">Ingredients</h3>
                    <ul class="ingredient-list">
                        <?php foreach ($ingredientList as $ing): ?>
                            <li><?= htmlspecialchars($ing) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Details -->
            <div class="sidebar-card">
                <h3 class="sidebar-card__title">Details</h3>

                <?php if (!empty($recipe['rating'])): ?>
                    <div style="margin-bottom:.75rem;">
                        <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem;">Rating</div>
                        <div class="rating-display">
                            <?= starsShow((int) $recipe['rating']) ?>
                            <span><?= $recipe['rating'] ?>/5</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div>
                    <div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem;">Status</div>
                    <div style="font-size:.95rem;">
            <span
                id="cooked-status"
                style="font-size:.95rem;font-weight:500;color:<?= $recipe['cooked'] ? 'var(--sage)' : 'var(--text-muted)' ?>">
              <?= $recipe['cooked'] ? '✓ Cooked' : 'Not yet cooked' ?>
            </span>
                    </div>
                </div>
            </div>

        </aside>

    </div>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
