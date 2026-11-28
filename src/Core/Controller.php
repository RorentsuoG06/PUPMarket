<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

abstract class Controller
{
    protected function view(string $view, array $data = [], bool $useLayout = true): void
    {
        $viewFile = VIEWS_PATH . "/" . ltrim($view, "/") . ".php";
        if (!is_file($viewFile)) {
            throw new RuntimeException("View not found: {$view}");
        }

        extract($data, EXTR_SKIP);

        if ($useLayout) {
            require VIEWS_PATH . "/layouts/header.php";
        }

        require $viewFile;

        if ($useLayout) {
            require VIEWS_PATH . "/layouts/footer.php";
        }
    }

    protected function redirect(string $path): void
    {
        $target = preg_match('#^https?://#i', $path) ? $path : ltrim($path, "/");
        header("Location: " . $target);
        exit;
    }

    protected function json(int $statusCode, array $payload): void
    {
        http_response_code($statusCode);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function isPost(): bool
    {
        return ($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST";
    }
}
