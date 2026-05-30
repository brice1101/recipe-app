<?php

namespace App\Controllers;

use App\ImageUploader;
use App\Models\Recipe;
use JetBrains\PhpStorm\NoReturn;
use RuntimeException;

class RecipeController extends BaseController {
    // --------
    // GET /recipes
    // --------

    /**
     * List all recipes, with optional filtering
     * Query params: search, cuisine, cooked (1 | 0 | "")
     */
    public function index(): void
    {
        $filters = [];

        $search = $this->query('search');
        $cuisine = $this->query('cuisine');
        $cooked = $this->query('cooked');

        if ($search !== '') $filters['search'] = $search;
        if ($cuisine !== '') $filters['cuisine'] = $cuisine;
        if ($cooked === '1') {
            $filters['cooked'] = true;
        } elseif ($cooked === '0') {
            $filters['cooked'] = false;
        }
        $recipes = Recipe::all($filters);

        $this->render('recipes/index', compact('recipes', 'filters'));
    }

    // -------------------------------------------------------------------------
    // GET /recipes/{id}
    // -------------------------------------------------------------------------

    /** Show a single recipe. */
    public function show(array $params): void
    {
        $recipe = $this->findOrAbort((int) $params['id']);
        $this->render('recipes/show', compact('recipe'));
    }

    // -------------------------------------------------------------------------
    // GET /recipes/create
    // -------------------------------------------------------------------------

    /** Show the new-recipe form. */
    public function create(): void
    {
        $this->render('recipes/form', [
            'recipe' => null,
            'errors' => [],
        ]);
    }

    // --------
    // POST /recipes
    // --------

    /** Handle new-recipe form submission */
    /** Handle new-recipe form submission. */
    public function store(): void
    {
        [$data, $errors] = $this->parseForm();

        if ($errors) {
            $this->render('recipes/form', ['recipe' => null, 'errors' => $errors]);
            return;
        }

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            try {
                $data['image_path'] = ImageUploader::store($_FILES['image']);
            } catch (RuntimeException $e) {
                $this->render('recipes/form', [
                    'recipe' => null,
                    'errors' => ['image' => $e->getMessage()],
                ]);
                return;
            }
        }

        $id              = Recipe::create($data);
        $ingredientNames = $this->parseIngredients();
        Recipe::syncIngredients($id, $ingredientNames);

        $this->redirect('/recipes/' . $id);
    }

    // -------------------------------------------------------------------------
    // GET /recipes/{id}/edit
    // -------------------------------------------------------------------------

    /** Show the edit form pre-populated with existing data. */
    public function edit(array $params): void
    {
        $recipe = $this->findOrAbort((int) $params['id']);
        $this->render('recipes/form', ['recipe' => $recipe, 'errors' => []]);
    }

    // -------------------------------------------------------------------------
    // POST /recipes/{id}/edit
    // -------------------------------------------------------------------------

    /** Handle edit form submission (HTML forms only support GET/POST). */
    public function update(array $params): void
    {
        $id     = (int) $params['id'];
        $recipe = $this->findOrAbort($id);

        [$data, $errors] = $this->parseForm();

        if ($errors) {
            $this->render('recipes/form', compact('recipe', 'errors'));
            return;
        }

        // Handle image replacement
        if (!empty($_FILES['image']['name'])) {
            try {
                // Delete the old image if one exists
                if (!empty($recipe['image_path'])) {
                    ImageUploader::delete($recipe['image_path']);
                }
                $data['image_path'] = ImageUploader::store($_FILES['image']);
            } catch (RuntimeException $e) {
                $this->render('recipes/form', [
                    'recipe' => $recipe,
                    'errors' => ['image' => $e->getMessage()],
                ]);
                return;
            }
        }

        Recipe::update($id, $data);
        Recipe::syncIngredients($id, $this->parseIngredients());

        $this->redirect('/recipes/' . $id);
    }

    // -------------------------------------------------------------------------
    // POST /recipes/{id}/delete
    // -------------------------------------------------------------------------

    /** Delete a recipe and its image, then redirect to the list. */
    #[NoReturn]
    public function destroy(array $params): void
    {
        $id     = (int) $params['id'];
        $recipe = $this->findOrAbort($id);

        if (!empty($recipe['image_path'])) {
            ImageUploader::delete($recipe['image_path']);
        }

        Recipe::delete($id);
        $this->redirect('/recipes');
    }

    // -------------------------------------------------------------------------
    // POST /recipes/{id}/toggle-cooked
    // -------------------------------------------------------------------------

    /**
     * Toggle the cooked flag.
     * Responds with JSON so the view can update without a full page reload.
     */
    #[NoReturn]
    public function toggleCooked(array $params): void
    {
        $id     = (int) $params['id'];
        $this->findOrAbort($id); // 404 if missing

        $newValue = Recipe::toggleCooked($id);
        $this->json(['cooked' => $newValue]);
    }

    // -------------------------------------------------------------------------
    // Shared helpers
    // -------------------------------------------------------------------------

    /**
     * Validate and return form data and any errors.
     *
     * @return array{0: array, 1: array<string, string>}
     */
    private function parseForm(): array
    {
        $data   = [];
        $errors = [];

        $title = $this->input('title');
        if ($title === '') {
            $errors['title'] = 'Title is required.';
        } else {
            $data['title'] = $title;
        }

        $data['cuisine']      = $this->input('cuisine');
        $data['instructions'] = $this->input('instructions');
        $data['cooked']       = $this->input('cooked') === '1';

        $rating = $this->input('rating');
        if ($rating !== '' && (!ctype_digit($rating) || $rating < 1 || $rating > 5)) {
            $errors['rating'] = 'Rating must be a number between 1 and 5.';
        } else {
            $data['rating'] = $rating !== '' ? (int) $rating : null;
        }

        return [$data, $errors];
    }

    /**
     * Parse the comma-separated ingredients field from POST.
     *
     * @return string[]
     */
    private function parseIngredients(): array
    {
        $raw  = $this->input('ingredients');
        $list = array_map('trim', explode(',', $raw));
        return array_values(array_filter($list));
    }

    /**
     * Find a recipe by id or respond with 404.
     */
    private function findOrAbort(int $id): array
    {
        $recipe = Recipe::find($id);
        if (!$recipe) {
            $this->abort(404, "Recipe #$id not found.");
        }
        return $recipe;
    }
}

