<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../config/microsoft_oauth.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isMicrosoftOAuthConfigured()) {
    header("Location: ../login.php?status=oauth_unconfigured");
    exit;
}

$state = $_GET["state"] ?? "";
$expectedState = $_SESSION["microsoft_oauth_state"] ?? "";
unset($_SESSION["microsoft_oauth_state"]);

if ($state === "" || $expectedState === "" || !hash_equals($expectedState, $state)) {
    header("Location: ../login.php?status=oauth_failed");
    exit;
}

if (isset($_GET["error"]) || empty($_GET["code"])) {
    header("Location: ../login.php?status=oauth_failed");
    exit;
}

$code = (string) $_GET["code"];

/* Exchange authorization code for an access token. */
$tokenUrl = "https://login.microsoftonline.com/" . rawurlencode(MICROSOFT_TENANT) . "/oauth2/v2.0/token";
$tokenPayload = http_build_query([
    "client_id" => MICROSOFT_CLIENT_ID,
    "client_secret" => MICROSOFT_CLIENT_SECRET,
    "code" => $code,
    "redirect_uri" => getMicrosoftRedirectUri(),
    "grant_type" => "authorization_code",
    "scope" => "openid profile email User.Read"
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => ["Content-Type: application/x-www-form-urlencoded"],
    CURLOPT_POSTFIELDS => $tokenPayload
]);

$tokenResponse = curl_exec($ch);
$tokenHttpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$tokenData = json_decode($tokenResponse ?: "", true);
if ($tokenHttpCode < 200 || $tokenHttpCode >= 300 || empty($tokenData["access_token"])) {
    header("Location: ../login.php?status=oauth_failed");
    exit;
}

/* Fetch the signed-in Microsoft user's profile. */
$graphCh = curl_init("https://graph.microsoft.com/v1.0/me");
curl_setopt_array($graphCh, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $tokenData["access_token"],
        "Accept: application/json"
    ]
]);

$graphResponse = curl_exec($graphCh);
$graphHttpCode = (int) curl_getinfo($graphCh, CURLINFO_HTTP_CODE);
curl_close($graphCh);

$profile = json_decode($graphResponse ?: "", true);
if ($graphHttpCode < 200 || $graphHttpCode >= 300 || !is_array($profile)) {
    header("Location: ../login.php?status=oauth_failed");
    exit;
}

$email = strtolower(trim((string) ($profile["mail"] ?? $profile["userPrincipalName"] ?? "")));
$fullName = trim((string) ($profile["displayName"] ?? ""));

if (
    $email === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !str_ends_with($email, "@iskolarngbayan.pup.edu.ph")
) {
    mysqli_close($conn);
    header("Location: ../login.php?status=email");
    exit;
}

if ($fullName === "") {
    $fullName = strstr($email, "@", true) ?: "PUP Student";
}

/* Look up existing user or create one on first Microsoft sign-in. */
$selectStmt = mysqli_prepare(
    $conn,
    "SELECT user_id, full_name, username FROM users WHERE email = ? LIMIT 1"
);
if (!$selectStmt) {
    mysqli_close($conn);
    header("Location: ../login.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($selectStmt, "s", $email);
mysqli_stmt_execute($selectStmt);

$userId = 0;
$dbFullName = "";
$dbUsername = "";
mysqli_stmt_bind_result($selectStmt, $userId, $dbFullName, $dbUsername);

if (!mysqli_stmt_fetch($selectStmt)) {
    mysqli_stmt_close($selectStmt);

    $dbUsername = "pup_" . bin2hex(random_bytes(12));
    $randomPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

    $insertStmt = mysqli_prepare(
        $conn,
        "INSERT INTO users (full_name, email, username, password) VALUES (?, ?, ?, ?)"
    );
    if (!$insertStmt) {
        mysqli_close($conn);
        header("Location: ../login.php?status=failed");
        exit;
    }

    mysqli_stmt_bind_param($insertStmt, "ssss", $fullName, $email, $dbUsername, $randomPasswordHash);
    if (!mysqli_stmt_execute($insertStmt)) {
        mysqli_stmt_close($insertStmt);
        mysqli_close($conn);
        header("Location: ../login.php?status=failed");
        exit;
    }

    $userId = mysqli_insert_id($conn);
    $dbFullName = $fullName;
    mysqli_stmt_close($insertStmt);
} else {
    mysqli_stmt_close($selectStmt);
}

mysqli_close($conn);

session_regenerate_id(true);
$_SESSION["user_id"] = $userId;
$_SESSION["full_name"] = $dbFullName;
$_SESSION["username"] = $dbUsername;

header("Location: ../index.php");
exit;
?>
