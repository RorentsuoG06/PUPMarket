<?php
declare(strict_types=1);

define("ROOT_PATH", dirname(__DIR__));
define("CONFIG_PATH", ROOT_PATH . "/config");
define("SRC_PATH", ROOT_PATH . "/src");
define("VIEWS_PATH", ROOT_PATH . "/views");
define("PUBLIC_PATH", ROOT_PATH . "/public");

require_once CONFIG_PATH . "/env.php";

spl_autoload_register(function (string $class): void {
    $prefix = "App\\";
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = SRC_PATH . "/" . str_replace("\\", "/", $relativeClass) . ".php";
    if (is_file($file)) {
        require_once $file;
    }
});

if (!function_exists("baseUrl")) {
    function baseUrl(string $path = ""): string
    {
        $scriptName = str_replace("\\", "/", $_SERVER["SCRIPT_NAME"] ?? "/index.php");
        $dir = rtrim(dirname($scriptName), "/");
        $base = preg_replace("#/public$#", "", $dir);
        $cleanPath = ltrim($path, "/");

        return $cleanPath === "" ? ($base === "" ? "/" : $base . "/") : $base . "/" . $cleanPath;
    }
}

if (!function_exists("e")) {
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
    }
}

App\Core\Session::start();
