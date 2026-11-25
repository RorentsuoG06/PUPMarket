<?php
require_once __DIR__ . "/../../src/Middleware/auth.php";
require_once __DIR__ . "/../../config/database.php";

requireAuth("../login.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../profile.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$fullName = trim($_POST["full_name"] ?? "");
$username = trim($_POST["username"] ?? "");
$courseYear = trim($_POST["course_year"] ?? "");
$contactInfo = trim($_POST["contact_info"] ?? "");
$bio = trim($_POST["bio"] ?? "");

if ($fullName === "" || $username === "") {
    header("Location: ../profile.php?status=empty");
    exit;
}

if (!preg_match("/^[A-Za-z0-9_]{3,30}$/", $username)) {
    header("Location: ../profile.php?status=username_invalid");
    exit;
}

/* Check username uniqueness excluding the current user. */
$checkStmt = mysqli_prepare(
    $conn,
    "SELECT user_id FROM users WHERE username = ? AND user_id != ? LIMIT 1"
);
if (!$checkStmt) {
    mysqli_close($conn);
    header("Location: ../profile.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($checkStmt, "si", $username, $userId);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
    header("Location: ../profile.php?status=username_taken");
    exit;
}
mysqli_stmt_close($checkStmt);

/* Fetch current avatar_path in case we replace it. */
$currentAvatar = null;
$avatarStmt = mysqli_prepare($conn, "SELECT avatar_path FROM users WHERE user_id = ? LIMIT 1");
if ($avatarStmt) {
    mysqli_stmt_bind_param($avatarStmt, "i", $userId);
    mysqli_stmt_execute($avatarStmt);
    mysqli_stmt_bind_result($avatarStmt, $currentAvatar);
    mysqli_stmt_fetch($avatarStmt);
    mysqli_stmt_close($avatarStmt);
}

$newAvatarPath = $currentAvatar;

if (isset($_FILES["avatar"]) && $_FILES["avatar"]["error"] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES["avatar"];
    if ($file["error"] !== UPLOAD_ERR_OK || $file["size"] > 2 * 1024 * 1024) {
        mysqli_close($conn);
        header("Location: ../profile.php?status=avatar_invalid");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file["tmp_name"]);
    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png" => "png",
        "image/webp" => "webp"
    ];

    if (!isset($allowedTypes[$mimeType])) {
        mysqli_close($conn);
        header("Location: ../profile.php?status=avatar_invalid");
        exit;
    }

    $uploadDir = dirname(__DIR__) . "/uploads/avatars";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = "avatar_" . $userId . "_" . bin2hex(random_bytes(8)) . "." . $allowedTypes[$mimeType];
    $destination = $uploadDir . "/" . $filename;

    if (!move_uploaded_file($file["tmp_name"], $destination)) {
        mysqli_close($conn);
        header("Location: ../profile.php?status=failed");
        exit;
    }

    $newAvatarPath = "uploads/avatars/" . $filename;

    if (!empty($currentAvatar)) {
        $oldFile = dirname(__DIR__) . "/" . $currentAvatar;
        if (is_file($oldFile)) {
            @unlink($oldFile);
        }
    }
}

$courseYearParam = $courseYear !== "" ? mb_substr($courseYear, 0, 100) : null;
$contactInfoParam = $contactInfo !== "" ? mb_substr($contactInfo, 0, 100) : null;
$bioParam = $bio !== "" ? mb_substr($bio, 0, 255) : null;

$updateStmt = mysqli_prepare(
    $conn,
    "UPDATE users
     SET full_name = ?, username = ?, course_year = ?, contact_info = ?, bio = ?, avatar_path = ?
     WHERE user_id = ?"
);

if (!$updateStmt) {
    mysqli_close($conn);
    header("Location: ../profile.php?status=failed");
    exit;
}

mysqli_stmt_bind_param(
    $updateStmt,
    "ssssssi",
    $fullName,
    $username,
    $courseYearParam,
    $contactInfoParam,
    $bioParam,
    $newAvatarPath,
    $userId
);

if (mysqli_stmt_execute($updateStmt)) {
    $_SESSION["full_name"] = $fullName;
    $_SESSION["username"] = $username;
    mysqli_stmt_close($updateStmt);
    mysqli_close($conn);
    header("Location: ../profile.php?status=profile_updated");
    exit;
}

mysqli_stmt_close($updateStmt);
mysqli_close($conn);
header("Location: ../profile.php?status=failed");
exit;
?>
