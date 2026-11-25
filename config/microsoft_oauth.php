<?php
require_once __DIR__ . "/env.php";

/* Microsoft OAuth 2.0 (Entra ID) Configuration for IskoFinds */
define("MICROSOFT_CLIENT_ID", env("MICROSOFT_CLIENT_ID", ""));
define("MICROSOFT_CLIENT_SECRET", env("MICROSOFT_CLIENT_SECRET", ""));
define("MICROSOFT_TENANT", env("MICROSOFT_TENANT", "common"));

function getMicrosoftRedirectUri(): string
{
    $envUri = env("MICROSOFT_REDIRECT_URI", "");
    if ($envUri !== "") {
        return $envUri;
    }

    $isHttps = !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off";
    $scheme = $isHttps ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    $scriptDir = str_replace("\\", "/", dirname($_SERVER["SCRIPT_NAME"] ?? "/PUPMarket/actions/x.php"));
    $baseDir = preg_replace("#/actions$#", "", $scriptDir);

    return "{$scheme}://{$host}{$baseDir}/actions/microsoft_callback.php";
}

function isMicrosoftOAuthConfigured(): bool
{
    return MICROSOFT_CLIENT_ID !== "" && MICROSOFT_CLIENT_SECRET !== "";
}
?>
