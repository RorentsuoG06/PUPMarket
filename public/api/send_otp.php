<?php
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../src/Services/ResendService.php";

header("Content-Type: application/json; charset=UTF-8");

function jsonResponse(int $statusCode, array $data): void
{
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    jsonResponse(405, ["success" => false, "message" => "Method not allowed."]);
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
    jsonResponse(422, [
        "success" => false,
        "message" => "Please enter a valid @iskolarngbayan.pup.edu.ph email address."
    ]);
}

/* Prevent sending OTPs to emails that are already registered. */
$userCheckStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? LIMIT 1");
if (!$userCheckStmt) {
    mysqli_close($conn);
    jsonResponse(500, ["success" => false, "message" => "Database error while checking email."]);
}

mysqli_stmt_bind_param($userCheckStmt, "s", $email);
mysqli_stmt_execute($userCheckStmt);
mysqli_stmt_store_result($userCheckStmt);

if (mysqli_stmt_num_rows($userCheckStmt) > 0) {
    mysqli_stmt_close($userCheckStmt);
    mysqli_close($conn);
    jsonResponse(409, ["success" => false, "message" => "That email is already registered."]);
}
mysqli_stmt_close($userCheckStmt);

/* Enforce a 60-second cooldown between OTP requests for the same email. */
$cooldownStmt = mysqli_prepare(
    $conn,
    "SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed
     FROM email_otps
     WHERE email = ?
     ORDER BY otp_id DESC
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
        jsonResponse(429, [
            "success" => false,
            "message" => "Please wait {$retryAfter}s before requesting a new code.",
            "retry_after" => $retryAfter
        ]);
    }
    mysqli_stmt_close($cooldownStmt);
}

$otp = str_pad((string) random_int(0, 999999), 6, "0", STR_PAD_LEFT);
$otpHash = password_hash($otp, PASSWORD_DEFAULT);

/* Remove previous OTPs for this email before storing the new one. */
$deleteStmt = mysqli_prepare($conn, "DELETE FROM email_otps WHERE email = ?");
if ($deleteStmt) {
    mysqli_stmt_bind_param($deleteStmt, "s", $email);
    mysqli_stmt_execute($deleteStmt);
    mysqli_stmt_close($deleteStmt);
}

$insertStmt = mysqli_prepare(
    $conn,
    "INSERT INTO email_otps (email, otp_hash, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))"
);
if (!$insertStmt) {
    mysqli_close($conn);
    jsonResponse(500, ["success" => false, "message" => "Failed to create verification code."]);
}

mysqli_stmt_bind_param($insertStmt, "ss", $email, $otpHash);
if (!mysqli_stmt_execute($insertStmt)) {
    mysqli_stmt_close($insertStmt);
    mysqli_close($conn);
    jsonResponse(500, ["success" => false, "message" => "Failed to store verification code."]);
}
$insertedOtpId = mysqli_insert_id($conn);
mysqli_stmt_close($insertStmt);

$sendResult = sendOtpEmailWithResend($email, $otp);
if (!$sendResult["success"]) {
    $cleanupStmt = mysqli_prepare($conn, "DELETE FROM email_otps WHERE otp_id = ?");
    if ($cleanupStmt) {
        mysqli_stmt_bind_param($cleanupStmt, "i", $insertedOtpId);
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
    }
    mysqli_close($conn);
    jsonResponse(500, ["success" => false, "message" => $sendResult["error"]]);
}

mysqli_close($conn);
jsonResponse(200, [
    "success" => true,
    "message" => "Verification code sent to your PUP Webmail. It expires in 10 minutes."
]);
?>
