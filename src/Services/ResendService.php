<?php
require_once __DIR__ . "/../../config/resend.php";

/**
 * Sends a 6-digit OTP verification email via the Resend REST API.
 *
 * @return array{success: bool, error: string}
 */
function sendOtpEmailWithResend(string $toEmail, string $otp, string $purpose = "register"): array
{
    $apiKey = RESEND_API_KEY;
    if ($apiKey === "") {
        return [
            "success" => false,
            "error" => "Resend API key is not configured. Set RESEND_API_KEY in .env."
        ];
    }

    if (!function_exists("curl_init")) {
        return [
            "success" => false,
            "error" => "PHP cURL extension is required to send verification emails."
        ];
    }

    $isReset = $purpose === "reset";
    $heading = $isReset ? "IskoFinds Password Reset" : "IskoFinds Email Verification";
    $intro = $isReset
        ? "Use the reset code below to set a new password for your IskoFinds student account:"
        : "Use the verification code below to complete your IskoFinds student account registration:";
    $subject = $isReset
        ? "IskoFinds Password Reset Code: {$otp}"
        : "IskoFinds Verification Code: {$otp}";
    $textBody = $isReset
        ? "Your IskoFinds password reset code is: {$otp}. This code expires in 10 minutes."
        : "Your IskoFinds verification code is: {$otp}. This code expires in 10 minutes.";

    $safeOtp = htmlspecialchars($otp, ENT_QUOTES, "UTF-8");
    $htmlBody = <<<HTML
<div style="font-family: Arial, sans-serif; max-width: 480px; margin: 0 auto; padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px;">
    <h2 style="color: #800000; margin-top: 0;">{$heading}</h2>
    <p style="color: #374151; font-size: 15px; line-height: 1.5;">
        {$intro}
    </p>
    <div style="background: #f9fafb; border: 1px dashed #800000; border-radius: 8px; padding: 16px; text-align: center; margin: 20px 0;">
        <span style="font-size: 28px; font-weight: 700; letter-spacing: 6px; color: #800000;">{$safeOtp}</span>
    </div>
    <p style="color: #6b7280; font-size: 13px; line-height: 1.5; margin-bottom: 0;">
        This code expires in 10 minutes. If you did not request this, you can safely ignore this email.
    </p>
</div>
HTML;

    $payload = json_encode([
        "from" => RESEND_FROM_EMAIL,
        "to" => [$toEmail],
        "subject" => $subject,
        "html" => $htmlBody,
        "text" => $textBody
    ]);

    $ch = curl_init("https://api.resend.com/emails");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . $apiKey,
            "Content-Type: application/json"
        ],
        CURLOPT_POSTFIELDS => $payload
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [
            "success" => false,
            "error" => "Failed to reach Resend API: " . $curlError
        ];
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $decoded = json_decode($response, true);
        $apiMessage = is_array($decoded) && isset($decoded["message"])
            ? (string) $decoded["message"]
            : "Resend API returned HTTP {$httpCode}.";
        return [
            "success" => false,
            "error" => $apiMessage
        ];
    }

    return [
        "success" => true,
        "error" => ""
    ];
}
?>
