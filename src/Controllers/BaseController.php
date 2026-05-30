<?php

namespace App\Controllers;

use JetBrains\PhpStorm\NoReturn;
use RuntimeException;

abstract class BaseController {
    // --------
    // View rendering
    // --------

    /*
     * Render a view template, injecting data into it.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): void {
        // Make every key available in the view
        extract($data, EXTR_SKIP);

        $path = __DIR__ . '/../../views/' . $view . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException("View file not found: $view");
        }

        require $path;
    }

    // --------
    // Redirects
    // --------

    #[NoReturn]
    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    // --------
    // Request helpers
    // --------

    // Return a trimmed POST value, or $default if not set
    protected function input(string $key, string $default = ''): string {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }

    protected function query(string $key, string $default = ''): string {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }

    // --------
    // Response helpers
    // --------

    // Send a JSON response and exit
    #[NoReturn]
    protected function json(mixed $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // Abort with an HTTP error
    #[NoReturn]
    protected function abort(int $status, string $message = ''): void {
        http_response_code($status);
        echo "<h1>$status</h1><p>" . htmlspecialchars($message) . "</p>";
        exit;
    }
}