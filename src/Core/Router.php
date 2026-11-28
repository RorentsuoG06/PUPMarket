<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->add("GET", $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->add("POST", $path, $controller, $action);
    }

    public function add(string $method, string $path, string $controller, string $action): void
    {
        $normalized = $this->normalizePath($path);
        $this->routes[strtoupper($method)][$normalized] = [$controller, $action];
    }

    public function dispatch(string $requestUri, string $method = "GET"): void
    {
        $method = strtoupper($method);
        $path = parse_url($requestUri, PHP_URL_PATH) ?: "/";
        $path = $this->stripBasePath($path);
        $normalized = $this->normalizePath($path);

        $handler = $this->routes[$method][$normalized] ?? null;

        if ($handler === null && str_ends_with($normalized, ".php")) {
            $withoutPhp = substr($normalized, 0, -4);
            $withoutPhp = $withoutPhp === "/index" ? "/" : $withoutPhp;
            $handler = $this->routes[$method][$withoutPhp] ?? null;
        }

        if ($handler === null) {
            http_response_code(404);
            $pageTitle = "404 Not Found | PUPMarket";
            $currentPage = "";
            require VIEWS_PATH . "/layouts/header.php";
            require VIEWS_PATH . "/errors/404.php";
            require VIEWS_PATH . "/layouts/footer.php";
            return;
        }

        [$controllerClass, $action] = $handler;
        $controller = new $controllerClass();
        $controller->$action();
    }

    private function normalizePath(string $path): string
    {
        $trimmed = "/" . trim($path, "/");
        return $trimmed === "" ? "/" : $trimmed;
    }

    private function stripBasePath(string $path): string
    {
        $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "");
        $publicDir = rtrim(dirname($scriptName), "/");
        $appRoot = preg_replace("#/public$#", "", $publicDir);

        foreach ([$publicDir, $appRoot] as $prefix) {
            if ($prefix !== "" && $prefix !== "/" && str_starts_with($path, $prefix)) {
                $stripped = substr($path, strlen($prefix));
                return $stripped === "" ? "/" : $stripped;
            }
        }

        return $path;
    }
}
