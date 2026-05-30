<?php
$isEdit    = !empty($recipe);
$pageTitle = ($isEdit ? 'Edit ' . $recipe['title'] : 'New Recipe') . ' — Recipe Book';
require __DIR__ . '/../partials/header.php';

/**
 * Variables injected by RecipeController::create() / edit():
 *   array|null $recipe — null for new, existing row for edit
 *   array $errors — validation error messages keyed by field
 */

// Helper: safely echo a field value, preferring POST on re-render after error
function old(string $key, mixed $fallback = ''): string {
    return htmlspecialchars($_POST[$key] ?? $fallback ?? '');
}

$ingredientsValue = old('ingredients', $recipe['ingredients'] ?? '');
?>

<div class="container" style="padding-top: 2rem;">

    <!-- Breadcrumb -->
    <nav class="breadcrumb">
        <a href="/recipes">All Recipes</a>
        <span class="breadcrumb__sep">›</span>
        <?php if ($isEdit): ?>
            <a href="/recipes/<?= $recipe['id'] ?>"><?= htmlspecialchars($recipe['title']) ?></a>
            <span class="breadcrumb__sep">›</span>
            <span>Edit</span>
        <?php else: ?>
            <span>New Recipe</span>
        <?php endif; ?>
    </nav>

    <div class="page-header" style="padding-top:1rem;">
        <p class="page-header__eyebrow"><?= $isEdit ? 'Editing' : 'Adding a new recipe' ?></p>
        <h1 class="page-header__title"><?= $isEdit ? htmlspecialchars($recipe['title']) : 'New Recipe' ?></h1>
    </div>

    <form
        method="POST"
        action="<?= $isEdit ? '/recipes/' . $recipe['id'] . '/edit' : '/recipes' ?>"
        enctype="multipart/form-data"
    >

        <div class="form-card">

            <div class="form-grid">

                <!-- Title -->
                <div class="form-group form-group--full">
                    <label for="title">Title <span class="required">*</span></label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        class="form-control <?= isset($errors['title']) ? 'form-control--error' : '' ?>"
                        value="<?= old('title', $recipe['title'] ?? '') ?>"
                        placeholder="e.g. Spaghetti Bolognese"
                        required
                    >
                    <?php if (isset($errors['title'])): ?>
                        <span class="form-error">⚠ <?= htmlspecialchars($errors['title']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Cuisine -->
                <div class="form-group">
                    <label for="cuisine">Cuisine</label>
                    <input
                        type="text"
                        id="cuisine"
                        name="cuisine"
                        class="form-control"
                        value="<?= old('cuisine', $recipe['cuisine'] ?? '') ?>"
                        placeholder="e.g. Italian, Indian…"
                    >
                </div>

                <!-- Rating -->
                <div class="form-group">
                    <label>Rating</label>
                    <div class="star-input">
                        <?php
                        $currentRating = (int) ($_POST['rating'] ?? $recipe['rating'] ?? 0);
                        for ($i = 5; $i >= 1; $i--): ?>
                            <input
                                type="radio"
                                id="star<?= $i ?>"
                                name="rating"
                                value="<?= $i ?>"
                                <?= $currentRating === $i ? 'checked' : '' ?>
                            >
                            <label for="star<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label>
                        <?php endfor; ?>
                    </div>
                    <?php if (isset($errors['rating'])): ?>
                        <span class="form-error">⚠ <?= htmlspecialchars($errors['rating']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Ingredients -->
                <div class="form-group form-group--full">
                    <label for="ingredients">Ingredients</label>
                    <input
                        type="text"
                        id="ingredients"
                        name="ingredients"
                        class="form-control"
                        value="<?= $ingredientsValue ?>"
                        placeholder="Garlic, Olive oil, Tomatoes…"
                    >
                    <span class="form-hint">Separate ingredients with commas.</span>
                </div>

                <!-- Instructions -->
                <div class="form-group form-group--full">
                    <label for="instructions">Method / Instructions</label>
                    <textarea
                        id="instructions"
                        name="instructions"
                        class="form-control"
                        rows="8"
                        placeholder="Step 1. …&#10;Step 2. …"
                    ><?= old('instructions', $recipe['instructions'] ?? '') ?></textarea>
                </div>

                <!-- Image upload -->
                <div class="form-group form-group--full">
                    <label for="image">Photo</label>

                    <?php if ($isEdit && !empty($recipe['image_path'])): ?>
                        <div class="image-preview-wrap">
                            <img
                                src="/<?= htmlspecialchars($recipe['image_path']) ?>"
                                alt="Current photo"
                                class="image-preview"
                                id="image-preview"
                            >
                        </div>
                        <span class="form-hint mt-1">Upload a new image to replace the current one.</span>
                    <?php else: ?>
                        <img src="" alt="" class="image-preview" id="image-preview" style="display:none;">
                    <?php endif; ?>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        class="form-control mt-1"
                        accept="image/jpeg,image/png,image/gif,image/webp"
                    >
                    <?php if (isset($errors['image'])): ?>
                        <span class="form-error">⚠ <?= htmlspecialchars($errors['image']) ?></span>
                    <?php endif; ?>
                    <span class="form-hint">JPEG, PNG, GIF or WebP — max 5 MB.</span>
                </div>

                <!-- Cooked toggle -->
                <div class="form-group form-group--full">
                    <label class="checkbox-row">
                        <input
                            type="checkbox"
                            name="cooked"
                            value="1"
                            <?= ($_POST['cooked'] ?? $recipe['cooked'] ?? false) ? 'checked' : '' ?>
                        >
                        <span>I've already cooked this recipe</span>
                    </label>
                </div>

            </div><!-- /.form-grid -->

            <div class="form-actions">
                <button type="submit" class="btn btn--primary">
                    <?= $isEdit ? '💾 Save Changes' : '+ Add Recipe' ?>
                </button>
                <a href="<?= $isEdit ? '/recipes/' . $recipe['id'] : '/recipes' ?>" class="btn btn--ghost">
                    Cancel
                </a>

                <?php if ($isEdit): ?>
                    <form method="POST" action="/recipes/<?= $recipe['id'] ?>/delete" style="margin-left:auto;"
                          onsubmit="return confirm('Delete this recipe? This cannot be undone.')">
                        <button type="submit" class="btn btn--danger btn--sm">🗑 Delete Recipe</button>
                    </form>
                <?php endif; ?>
            </div>

        </div><!-- /.form-card -->

    </form>

</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
