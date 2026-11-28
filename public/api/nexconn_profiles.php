<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../src/Middleware/auth.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, private");

function nexconnProfilesJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

ensureSessionStarted();
restoreRememberedSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($conn);
    nexconnProfilesJsonResponse(405, ["success" => false, "message" => "Method not allowed."]);
}

if (!isAuthenticated()) {
    mysqli_close($conn);
    nexconnProfilesJsonResponse(401, ["success" => false, "message" => "Sign in to use chat."]);
}

if (!isValidCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null)) {
    mysqli_close($conn);
    nexconnProfilesJsonResponse(419, ["success" => false, "message" => "Refresh the page and try again."]);
}

$request = json_decode(file_get_contents("php://input") ?: "", true);
$userIds = is_array($request) ? ($request["user_ids"] ?? null) : null;
if (
    !is_array($userIds) ||
    count($userIds) > 100 ||
    array_filter($userIds, static function ($userId): bool {
        return !is_string($userId) || !preg_match('/^[a-f0-9]{32}$/', $userId);
    })
) {
    mysqli_close($conn);
    nexconnProfilesJsonResponse(422, ["success" => false, "message" => "Invalid chat profile request."]);
}

$userIds = array_values(array_unique($userIds));
$stmt = mysqli_prepare(
    $conn,
    "SELECT full_name FROM users WHERE nexconn_user_id = ? LIMIT 1"
);
if (!$stmt) {
    error_log("Unable to prepare Nexconn profile lookup.");
    mysqli_close($conn);
    nexconnProfilesJsonResponse(500, ["success" => false, "message" => "Unable to load chat profiles."]);
}

$requestedUserId = "";
$fullName = "";
mysqli_stmt_bind_param($stmt, "s", $requestedUserId);
mysqli_stmt_bind_result($stmt, $fullName);
$profiles = [];
foreach ($userIds as $requestedUserId) {
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Unable to look up Nexconn chat profile: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        nexconnProfilesJsonResponse(500, ["success" => false, "message" => "Unable to load chat profiles."]);
    }
    if (!mysqli_stmt_store_result($stmt)) {
        error_log("Unable to buffer Nexconn chat profile result: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        nexconnProfilesJsonResponse(500, ["success" => false, "message" => "Unable to load chat profiles."]);
    }
    if (mysqli_stmt_fetch($stmt)) {
        $profiles[] = [
            "userId" => $requestedUserId,
            "name" => nexconnDisplayName($fullName)
        ];
    }
    mysqli_stmt_free_result($stmt);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
nexconnProfilesJsonResponse(200, ["success" => true, "profiles" => $profiles]);
