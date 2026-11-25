<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../src/Services/ResendService.php";

header("Content-Type: application/json; charset=UTF-8");

function jsonResetResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResetResponse(405, ["success" => false, "message" => "Method not allowed."]);
}

$rawBody = file_get_contents("php://input");
$jsonInput = json_decode($rawBody ?: "", true);
$email = is_array($jsonInput) && isset($jsonInput["email"])
    ? $jsonInput["email"]
    : ($_POST["email"] ?? "");

$email = strtolower(trim((string) $email));

if (
    $email === "" ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !str_ends_with($email, "@iskolarngbayan.pup.edu.ph")
) {
    jsonResetResponse(422, [
        "success" => false,
        "message" => "Please enter a valid @iskolarngbayan.pup.edu.ph email address."
    ]);
}

/* Verify the account exists before sending a reset code. */
$userStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
if (!$userStmt) {
    mysqli_close($conn);
    jsonResetResponse(500, ["success" => false, "message" => "Database error while checking account."]);
}

mysqli_stmt_bind_param($userStmt, "s", $email);
mysqli_stmt_execute($userStmt);
mysqli_stmt_store_result($userStmt);

if (mysqli_stmt_num_rows($userStmt) === 0) {
    mysqli_stmt_close($userStmt);
    mysqli_close($conn);
    jsonResetResponse(404, ["success" => false, "message" => "No account is registered with that PUP Webmail."]);
}
mysqli_stmt_close($userStmt);

/* Enforce a 60-second cooldown between reset code requests. */
$cooldownStmt = mysqli_prepare(
    $conn,
    "SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed
     FROM password_resets
     WHERE email = ?
     ORDER BY reset_id DESC
     LIMIT 1"
);
if ($cooldownStmt) {
    mysqli_stmt_bind_param($cooldownStmt, "s", $email);
    mysqli_stmt_execute($cooldownStmt);
    $elapsed = null;
    mysqli_stmt_bind_result($cooldownStmt, $elapsed);
    if (mysqli_stmt_fetch($cooldownStmt) && $elapsed !== null && (int) $elapsed < 60) {
        $retryAfter = 60 - (int) $elapsed;
        mysqli_stmt_close($cooldownStmt);
        mysqli_close($conn);
        jsonResetResponse(429, [
            "success" => false,
            "message" => "Please wait {$retryAfter}s before requesting a new reset code.",
            "retry_after" => $retryAfter
        ]);
    }
    mysqli_stmt_close($cooldownStmt);
}

$otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);

$deleteStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
if ($deleteStmt) {
    mysqli_stmt_bind_param($deleteStmt, "s", $email);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
}

$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO password_resets (email, otp_hash, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
);
if (!$insertStmt) {
    mysqli_close($conn);
    jsonResetResponse(500, ["success" => false, "message" => "Failed to create reset code."]);
}

mysqli_stmt_bind_param($insertStmt, "ss", $email, $otpHash);
if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_stmt_close($insertStmt);
    mysqli_close($conn);
    jsonResetResponse(500, ["success" => false, "message" => "Failed to store reset code."]);
}
$insertedResetId = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);

$sendResult = sendOtpEmailWithResend($email, $otp, "reset");
if (!$sendResult["success"]) {
    $cleanupStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE reset_id = ?");
    if ($cleanupStmt) {
        mysqli_stmt_bind_param($cleanupStmt, "i", $insertedResetId);
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
    }
    mysqli_close($conn);
    jsonResetResponse(500, ["success" => false, "message" => $sendResult["error"]]);
}

mysqli_close($conn);
jsonResetResponse(200, [
    "success" => true,
    "message" => "Reset code sent to your PUP Webmail. It expires in 10 minutes."
]);
?>
