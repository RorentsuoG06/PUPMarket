<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
require_once __DIR__ . "/../config/database.php";

requireAuth("login.php");

$userId = (int) $_SESSION["user_id"];

$stmt = mysqli_prepare(
    $conn,
    "SELECT full_name, email, username, course_year, contact_info, bio, avatar_path, created_at
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Unable to load profile.");
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result(
    $stmt,
    $fullName,
    $email,
    $username,
    $courseYear,
    $contactInfo,
    $bio,
    $avatarPath,
    $createdAt
);

if (!mysqli_stmt_fetch($stmt)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: logout.php");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);

$message = "";
$messageType = "";
$status = $_GET["status"] ?? "";

$statusMap = [
    "profile_updated" => ["Profile details updated successfully.", "success"],
    "password_updated" => ["Password changed successfully.", "success"],
    "empty" => ["Full name and username are required.", "danger"],
    "username_invalid" => ["Username must be 3–30 characters (letters, numbers, underscores only).", "danger"],
    "username_taken" => ["That username is already taken.", "danger"],
    "avatar_invalid" => ["Profile photo must be a JPG, PNG, or WEBP image under 2MB.", "danger"],
    "pw_empty" => ["Please fill in all password fields.", "danger"],
    "pw_current" => ["Your current password is incorrect.", "danger"],
    "pw_length" => ["New password must be at least 6 characters.", "danger"],
    "pw_match" => ["New passwords do not match.", "danger"],
    "failed" => ["Something went wrong. Please try again.", "danger"]
];

if (isset($statusMap[$status])) {
    [$message, $messageType] = $statusMap[$status];
}

$memberSince = $createdAt ? date("F j, Y", strtotime($createdAt)) : "";
$pageTitle = "My Profile | IskoFinds";
$currentPage = "profile";

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

<section class="py-5 auth-page">
    <div class="container">
        <div class="row justify-content-center g-4">
            <div class="col-lg-4">
                <div class="contact-form-card auth-card text-center">
                    <div class="mb-3">
                        <?php if (!empty($avatarPath)): ?>
                            <img src="<?= htmlspecialchars($avatarPath) ?>"
                                 alt="Profile photo"
                                 class="rounded-circle object-fit-cover border"
                                 style="width: 110px; height: 110px;">
                        <?php else: ?>
                            <div class="rounded-circle bg-light border d-inline-flex align-items-center justify-content-center"
                                 style="width: 110px; height: 110px; font-size: 2.75rem; color: var(--pup-maroon, #800000);">
                                <i class="bi bi-person-fill"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h3 class="fw-bold mb-1"><?= htmlspecialchars($fullName) ?></h3>
                    <p class="text-muted mb-2">@<?= htmlspecialchars($username) ?></p>

                    <span class="badge bg-success-subtle text-success border border-success-subtle mb-3">
                        <i class="bi bi-patch-check-fill"></i>
                        Verified PUP Student
                    </span>

                    <?php if (!empty($courseYear)): ?>
                        <p class="small mb-1">
                            <i class="bi bi-mortarboard-fill"></i>
                            <?= htmlspecialchars($courseYear) ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!empty($bio)): ?>
                        <p class="small text-muted mt-2 mb-2"><?= htmlspecialchars($bio) ?></p>
                    <?php endif; ?>

                    <?php if ($memberSince !== ""): ?>
                        <p class="small text-muted mb-0 mt-3">
                            Member since <?= htmlspecialchars($memberSince) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-lg-7">
                <?php if ($message !== ""): ?>
                    <div class="alert alert-<?= htmlspecialchars($messageType) ?> mb-4">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <div class="contact-form-card auth-card mb-4">
                    <h4 class="fw-bold mb-3">
                        <i class="bi bi-person-lines-fill"></i>
                        Edit Profile
                    </h4>

                    <form method="POST"
                          action="actions/update_profile.php"
                          enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label" for="profileEmail">PUP Webmail</label>
                            <input type="email"
                                   class="form-control"
                                   id="profileEmail"
                                   value="<?= htmlspecialchars($email) ?>"
                                   disabled>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profileFullName">Full Name</label>
                                <input type="text"
                                       class="form-control"
                                       id="profileFullName"
                                       name="full_name"
                                       value="<?= htmlspecialchars($fullName) ?>"
                                       required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profileUsername">Username</label>
                                <input type="text"
                                       class="form-control"
                                       id="profileUsername"
                                       name="username"
                                       value="<?= htmlspecialchars($username) ?>"
                                       pattern="[A-Za-z0-9_]{3,30}"
                                       title="3-30 characters: letters, numbers, and underscores only."
                                       required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profileCourseYear">Course &amp; Year</label>
                                <input type="text"
                                       class="form-control"
                                       id="profileCourseYear"
                                       name="course_year"
                                       placeholder="e.g. BSIT 3-1"
                                       maxlength="100"
                                       value="<?= htmlspecialchars($courseYear ?? "") ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="profileContact">Contact / Meetup Info</label>
                                <input type="text"
                                       class="form-control"
                                       id="profileContact"
                                       name="contact_info"
                                       placeholder="e.g. Messenger link or mobile no."
                                       maxlength="100"
                                       value="<?= htmlspecialchars($contactInfo ?? "") ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="profileBio">Bio</label>
                            <textarea class="form-control"
                                      id="profileBio"
                                      name="bio"
                                      rows="2"
                                      maxlength="255"
                                      placeholder="Tell fellow Iskolar ng Bayan a little about yourself..."><?= htmlspecialchars($bio ?? "") ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="profileAvatar">Profile Photo (optional)</label>
                            <input type="file"
                                   class="form-control"
                                   id="profileAvatar"
                                   name="avatar"
                                   accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, or WEBP up to 2MB.</div>
                        </div>

                        <button type="submit" class="btn btn-pup">
                            <i class="bi bi-check-circle-fill"></i>
                            Save Changes
                        </button>
                    </form>
                </div>

                <div class="contact-form-card auth-card">
                    <h4 class="fw-bold mb-3">
                        <i class="bi bi-shield-lock-fill"></i>
                        Change Password
                    </h4>

                    <form method="POST" action="actions/change_password.php">
                        <div class="mb-3">
                            <label class="form-label" for="currentPassword">Current Password</label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control"
                                       id="currentPassword"
                                       name="current_password"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary password-toggle"
                                        type="button"
                                        data-password-toggle="currentPassword"
                                        aria-label="Show current password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label" for="newPassword">New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="newPassword"
                                           name="new_password"
                                           minlength="6"
                                           placeholder="Minimum 6 characters"
                                           autocomplete="new-password"
                                           required>
                                    <button class="btn btn-outline-secondary password-toggle"
                                            type="button"
                                            data-password-toggle="newPassword"
                                            aria-label="Show new password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="confirmNewPassword">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control"
                                           id="confirmNewPassword"
                                           name="confirm_password"
                                           minlength="6"
                                           placeholder="Re-enter new password"
                                           autocomplete="new-password"
                                           required>
                                    <button class="btn btn-outline-secondary password-toggle"
                                            type="button"
                                            data-password-toggle="confirmNewPassword"
                                            aria-label="Show confirm password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-outline-pup">
                            <i class="bi bi-key-fill"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
