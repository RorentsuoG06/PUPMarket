<?php
declare(strict_types=1);

require_once dirname(__DIR__) . "/config/app.php";

use App\Core\Database;

header("Content-Type: text/html; charset=UTF-8");

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    $driver = $db->getDriver();
    $version = $pdo->query("SELECT VERSION()")->fetchColumn();
    echo "<h2>Database Connection Successful</h2>";
    echo "<p><strong>Driver:</strong> " . htmlspecialchars($driver) . "</p>";
    echo "<p><strong>Server Version:</strong> " . htmlspecialchars((string) $version) . "</p>";
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h2>Database Connection Failed</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
