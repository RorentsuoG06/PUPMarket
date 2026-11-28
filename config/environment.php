<?php

function appEnv(string $name, string $default = ""): string
{
    $value = getenv($name);
    if ($value !== false) {
        return $value;
    }

    static $environment = null;
    if ($environment === null) {
        $environment = [];
        $path = __DIR__ . "/../.env";

        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES);
            if ($lines === false) {
                error_log("Unable to read the application environment file.");
            } else {
                foreach ($lines as $line) {
                    if (!preg_match('/^\s*(?:export\s+)?([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/', $line, $matches)) {
                        continue;
                    }

                    $key = $matches[1];
                    $value = trim($matches[2]);
                    if ($value !== "" && ($value[0] === '"' || $value[0] === "'")) {
                        $quote = $value[0];
                        $end = strrpos($value, $quote);
                        if ($end > 0) {
                            $value = substr($value, 1, $end - 1);
                            if ($quote === '"') {
                                $value = stripcslashes($value);
                            }
                        }
                    } else {
                        $comment = strpos($value, " #");
                        if ($comment !== false) {
                            $value = rtrim(substr($value, 0, $comment));
                        }
                    }
                    $environment[$key] = $value;
                }
            }
        }
    }

    return $environment[$name] ?? $default;
}
