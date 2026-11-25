<?php
require_once __DIR__ . "/../../config/microsoft_oauth.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isMicrosoftOAuthConfigured()) {
    header("Location: ../login.php?status=oauth_unconfigured");
    exit;
}

$state = bin2hex(random_bytes(16));
$_SESSION["microsoft_oauth_state"] = $state;
$_SESSION["remember_login"] = ($_GET["remember"] ?? "") === "1";

$params = http_build_query([
    "client_id" => MICROSOFT_CLIENT_ID,
    "response_type" => "code",
    "redirect_uri" => getMicrosoftRedirectUri(),
    "response_mode" => "query",
    "scope" => "openid profile email User.Read",
    "state" => $state,
    "prompt" => "select_account"
]);

$authUrl = "https://login.microsoftonline.com/" . rawurlencode(MICROSOFT_TENANT) . "/oauth2/v2.0/authorize?" . $params;
header("Location: " . $authUrl);
exit;
?>
