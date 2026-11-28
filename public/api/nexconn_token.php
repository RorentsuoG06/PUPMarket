<?php
mysqli_report(MYSQLI_REPORT_OFF);
require_once __DIR__ . "/../../config/environment.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../src/Middleware/auth.php";
require_once __DIR__ . "/../../src/Services/NexconnService.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, private");
header("Pragma: no-cache");

function nexconnJsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

ensureSessionStarted();
restoreRememberedSession();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    mysqli_close($conn);
    nexconnJsonResponse(405, ["success" => false, "message" => "Method not allowed."]);
}

if (!isAuthenticated()) {
    mysqli_close($conn);
    nexconnJsonResponse(401, ["success" => false, "message" => "Sign in to use chat."]);
}

if (!isValidCsrfToken($_SERVER["HTTP_X_CSRF_TOKEN"] ?? null)) {
    mysqli_close($conn);
    nexconnJsonResponse(419, ["success" => false, "message" => "Refresh the page and try again."]);
}

$rawBody = file_get_contents("php://input");
$request = json_decode($rawBody ?: "", true);
if (!is_array($request)) {
    mysqli_close($conn);
    nexconnJsonResponse(400, ["success" => false, "message" => "Invalid chat request."]);
}

$appKey = trim(appEnv("NEXCONN_APP_KEY"));
$appSecret = trim(appEnv("NEXCONN_APP_SECRET"));
$region = strtoupper(trim(appEnv("NEXCONN_REGION", "SG")));
$regions = [
    "SG" => ["https://api.sg-light-api.com", "SG"],
    "NA" => ["https://api.us-light-api.com", "NA"],
    "SA" => ["https://api.sau-light-api.com", "SA"]
];
if ($appKey === "" || $appSecret === "") {
    mysqli_close($conn);
    nexconnJsonResponse(503, [
        "success" => false,
        "message" => "Nexconn is not configured. Add its App Key and App Secret to the server .env file."
    ]);
}
if (!isset($regions[$region])) {
    mysqli_close($conn);
    nexconnJsonResponse(500, [
        "success" => false,
        "message" => "NEXCONN_REGION must be SG, NA, or SA."
    ]);
}

$userId = (int) $_SESSION["user_id"];
$nexconnUserId = nexconnUserId($conn, $userId);
if ($nexconnUserId === null) {
    mysqli_close($conn);
    nexconnJsonResponse(500, [
        "success" => false,
        "message" => "Unable to link your account to Nexconn. Apply the chat database migration and try again."
    ]);
}

$nameStmt = mysqli_prepare($conn, "SELECT full_name FROM users WHERE user_id = ? LIMIT 1");
if (!$nameStmt) {
    error_log("Unable to prepare Nexconn display-name lookup.");
    mysqli_close($conn);
    nexconnJsonResponse(500, ["success" => false, "message" => "Unable to prepare your chat account."]);
}
mysqli_stmt_bind_param($nameStmt, "i", $userId);
if (!mysqli_stmt_execute($nameStmt)) {
    error_log("Unable to look up Nexconn display name: " . mysqli_stmt_error($nameStmt));
    mysqli_stmt_close($nameStmt);
    mysqli_close($conn);
    nexconnJsonResponse(500, ["success" => false, "message" => "Unable to prepare your chat account."]);
}
$displayName = "";
mysqli_stmt_bind_result($nameStmt, $displayName);
$nameFound = mysqli_stmt_fetch($nameStmt);
mysqli_stmt_close($nameStmt);
if (!$nameFound || trim($displayName) === "") {
    mysqli_close($conn);
    nexconnJsonResponse(500, ["success" => false, "message" => "Your account name is unavailable."]);
}

$peer = null;
$listingId = null;
if (array_key_exists("listing_id", $request)) {
    $listingId = filter_var($request["listing_id"], FILTER_VALIDATE_INT);
    if ($listingId === false || $listingId < 1) {
        mysqli_close($conn);
        nexconnJsonResponse(422, ["success" => false, "message" => "That listing cannot be contacted."]);
    }
}

if ($listingId !== null) {
    $peerStmt = mysqli_prepare(
        $conn,
        "SELECT l.seller_user_id, u.full_name
         FROM marketplace_listings l
         INNER JOIN users u ON u.user_id = l.seller_user_id
         WHERE l.listing_id = ?
         LIMIT 1"
    );
    if (!$peerStmt) {
        error_log("Unable to prepare Nexconn listing lookup.");
        mysqli_close($conn);
        nexconnJsonResponse(500, ["success" => false, "message" => "Unable to open this seller chat."]);
    }

    mysqli_stmt_bind_param($peerStmt, "i", $listingId);
    if (!mysqli_stmt_execute($peerStmt)) {
        error_log("Unable to look up Nexconn listing seller: " . mysqli_stmt_error($peerStmt));
        mysqli_stmt_close($peerStmt);
        mysqli_close($conn);
        nexconnJsonResponse(500, ["success" => false, "message" => "Unable to open this seller chat."]);
    }

    $peerUserId = 0;
    $peerName = "";
    mysqli_stmt_bind_result($peerStmt, $peerUserId, $peerName);
    $peerFound = mysqli_stmt_fetch($peerStmt);
    mysqli_stmt_close($peerStmt);
    if (!$peerFound) {
        mysqli_close($conn);
        nexconnJsonResponse(404, ["success" => false, "message" => "This listing is no longer available."]);
    }
    if ((int) $peerUserId === $userId) {
        mysqli_close($conn);
        nexconnJsonResponse(422, ["success" => false, "message" => "You cannot start a chat with yourself."]);
    }

    $peerNexconnId = nexconnUserId($conn, (int) $peerUserId);
    if ($peerNexconnId === null) {
        mysqli_close($conn);
        nexconnJsonResponse(500, ["success" => false, "message" => "Unable to link the seller to Nexconn."]);
    }
    $peer = [
        "userId" => $peerNexconnId,
        "name" => nexconnDisplayName($peerName)
    ];
}

mysqli_close($conn);

$nonce = bin2hex(random_bytes(9));
$timestamp = (string) floor(microtime(true) * 1000);
$signature = sha1($appSecret . $nonce . $timestamp);
$requestId = bin2hex(random_bytes(16));
$payload = json_encode([
    "userId" => $nexconnUserId,
    "name" => nexconnDisplayName($displayName)
]);
if ($payload === false) {
    error_log("Unable to encode Nexconn token request.");
    nexconnJsonResponse(500, ["success" => false, "message" => "Unable to prepare your chat account."]);
}

$curl = curl_init($regions[$region][0] . "/v4/auth/access-token/issue");
if ($curl === false) {
    error_log("Unable to initialize the Nexconn token request.");
    nexconnJsonResponse(500, ["success" => false, "message" => "Unable to connect to the chat service."]);
}

curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        "App-Key: " . $appKey,
        "Nonce: " . $nonce,
        "Timestamp: " . $timestamp,
        "Signature: " . $signature,
        "X-Request-ID: " . $requestId,
        "Content-Type: application/json"
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 4,
    CURLOPT_TIMEOUT => 8,
    CURLOPT_FOLLOWLOCATION => false
]);
$responseBody = curl_exec($curl);
$httpStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
$curlError = curl_error($curl);
curl_close($curl);

$response = is_string($responseBody) ? json_decode($responseBody, true) : null;
$accessToken = is_array($response)
    ? ($response["result"]["accessToken"] ?? null)
    : null;
if (
    $responseBody === false ||
    $httpStatus < 200 ||
    $httpStatus >= 300 ||
    !is_array($response) ||
    (int) ($response["code"] ?? -1) !== 0 ||
    ($response["result"]["userId"] ?? null) !== $nexconnUserId ||
    !is_string($accessToken) ||
    $accessToken === ""
) {
    error_log(
        "Nexconn token request failed (HTTP {$httpStatus}, service code " .
        (is_array($response) ? (string) ($response["code"] ?? "unknown") : "unavailable") .
        ($curlError !== "" ? ", cURL error" : "") . ")."
    );
    nexconnJsonResponse(502, [
        "success" => false,
        "message" => "Nexconn could not create a chat session. Check the server configuration and try again."
    ]);
}

$result = [
    "success" => true,
    "appKey" => $appKey,
    "region" => $regions[$region][1],
    "token" => $accessToken,
    "userId" => $nexconnUserId
];
if ($peer !== null) {
    $result["peer"] = $peer;
}
nexconnJsonResponse(200, $result);
