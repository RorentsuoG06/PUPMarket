<?php
/* Lightweight .env loader for IskoFinds */

if (!function_exists("loadEnvFile")) {
    function loadEnvFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === "" || str_starts_with($trimmed, "#")) {
                continue;
            }

            $parts = explode("=", $trimmed, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            if ($name !== "" && getenv($name) === false) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
            }
        }
    }

    function env(string $key, ?string $default = null): ?string
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null) {
            return $default;
        }
        return (string) $val;
    }

    loadEnvFile(dirname(__DIR__) . "/.env");
}
?>
