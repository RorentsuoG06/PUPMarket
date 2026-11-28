<!-- ═══════════════════════════════════════════════════════════
     PROFILE HERO BANNER
═══════════════════════════════════════════════════════════ -->
<div class="profile-hero">
    <div class="profile-hero__cover"></div>
    <div class="container position-relative">
        <div class="profile-hero__body">
            <!-- Avatar -->
            <div class="profile-hero__avatar-wrap">
                <?php if (!empty($avatarPath)): ?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>"
                         alt="Profile photo"
                         class="profile-hero__avatar">
                <?php else: ?>
                    <div class="profile-hero__avatar profile-hero__avatar--placeholder">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>
            <!-- Identity -->
            <div class="profile-hero__identity">
                <h1 class="profile-hero__name"><?= htmlspecialchars($fullName) ?></h1>
                <p class="profile-hero__handle">@<?= htmlspecialchars($username) ?></p>
                <div class="profile-hero__chips">
                    <span class="profile-chip profile-chip--verified">
                        <i class="bi bi-patch-check-fill"></i>
                        Verified PUP Student
                    </span>
                    <?php if (!empty($courseYear)): ?>
                        <span class="profile-chip">
                            <i class="bi bi-mortarboard-fill"></i>
                            <?= htmlspecialchars($courseYear) ?>
                        </span>
                    <?php endif; ?>
                    <?php if (!empty($contactInfo)): ?>
                        <span class="profile-chip">
                            <i class="bi bi-telephone-fill"></i>
                            <?= htmlspecialchars($contactInfo) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════════════════════ -->
<div class="container profile-content">

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?= htmlspecialchars($messageType) ?> alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-<?= $messageType === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill' ?> me-2"></i>
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ── LEFT: Identity card ─────────────────────────── -->
        <div class="col-lg-4">
            <div class="profile-card profile-identity-card">

                <!-- Bio section -->
                <?php if (!empty($bio)): ?>
                    <div class="profile-identity-card__bio">
                        <i class="bi bi-quote profile-identity-card__quote-icon"></i>
                        <p class="mb-0"><?= htmlspecialchars($bio) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Stats / meta -->
                <ul class="profile-meta-list">
                    <li class="profile-meta-list__item">
                        <span class="profile-meta-list__icon"><i class="bi bi-envelope-fill"></i></span>
                        <span class="profile-meta-list__label">Email</span>
                        <span class="profile-meta-list__value text-truncate"><?= htmlspecialchars($email) ?></span>
                    </li>
                    <?php if (!empty($courseYear)): ?>
                        <li class="profile-meta-list__item">
                            <span class="profile-meta-list__icon"><i class="bi bi-mortarboard-fill"></i></span>
                            <span class="profile-meta-list__label">Course & Year</span>
                            <span class="profile-meta-list__value"><?= htmlspecialchars($courseYear) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if (!empty($contactInfo)): ?>
                        <li class="profile-meta-list__item">
                            <span class="profile-meta-list__icon"><i class="bi bi-telephone-fill"></i></span>
                            <span class="profile-meta-list__label">Contact</span>
                            <span class="profile-meta-list__value"><?= htmlspecialchars($contactInfo) ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($memberSince !== ""): ?>
                        <li class="profile-meta-list__item">
                            <span class="profile-meta-list__icon"><i class="bi bi-calendar3"></i></span>
                            <span class="profile-meta-list__label">Member since</span>
                            <span class="profile-meta-list__value"><?= htmlspecialchars($memberSince) ?></span>
                        </li>
                    <?php endif; ?>
                </ul>

            </div>
        </div>

        <!-- ── RIGHT: Edit forms ──────────────────────────── -->
        <div class="col-lg-8">

            <!-- Edit Profile card -->
            <div class="profile-card mb-4">
                <div class="profile-card__header">
                    <span class="profile-card__header-icon">
                        <i class="bi bi-person-lines-fill"></i>
                    </span>
                    <div>
                        <h2 class="profile-card__title">Edit Profile</h2>
                        <p class="profile-card__subtitle">Update your public information</p>
                    </div>
                </div>

                <div class="profile-card__body">
                    <form method="POST"
                          action="actions/update_profile.php"
                          enctype="multipart/form-data">

                        <!-- Email (read-only) -->
                        <div class="mb-3">
                            <label class="form-label" for="profileEmail">PUP Webmail</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email"
                                       class="form-control"
                                       id="profileEmail"
                                       value="<?= htmlspecialchars($email) ?>"
                                       disabled>
                            </div>
                        </div>

                        <!-- Full Name + Username -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profileFullName">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           id="profileFullName"
                                           name="full_name"
                                           value="<?= htmlspecialchars($fullName) ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profileUsername">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">@</span>
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
                        </div>

                        <!-- Course + Contact -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label" for="profileCourseYear">Course &amp; Year</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-mortarboard"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           id="profileCourseYear"
                                           name="course_year"
                                           placeholder="e.g. BSIT 3-1"
                                           maxlength="100"
                                           value="<?= htmlspecialchars($courseYear ?? "") ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="profileContact">Contact / Meetup Info</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text"
                                           class="form-control"
                                           id="profileContact"
                                           name="contact_info"
                                           placeholder="Messenger link or mobile no."
                                           maxlength="100"
                                           value="<?= htmlspecialchars($contactInfo ?? "") ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Bio -->
                        <div class="mb-3">
                            <label class="form-label" for="profileBio">Bio</label>
                            <textarea class="form-control"
                                      id="profileBio"
                                      name="bio"
                                      rows="3"
                                      maxlength="255"
                                      placeholder="Tell fellow Iskolar ng Bayan a little about yourself..."><?= htmlspecialchars($bio ?? "") ?></textarea>
                            <div class="form-text text-end"><span id="bioCount">0</span>/255</div>
                        </div>

                        <!-- Avatar upload -->
                        <div class="profile-upload-zone mb-4">
                            <i class="bi bi-image profile-upload-zone__icon"></i>
                            <p class="profile-upload-zone__label mb-1">
                                <label for="profileAvatar" class="profile-upload-zone__link">
                                    Choose a photo
                                </label>
                                or drag & drop
                            </p>
                            <p class="profile-upload-zone__hint">JPG, PNG, or WEBP · max 2 MB</p>
                            <input type="file"
                                   class="profile-upload-zone__input"
                                   id="profileAvatar"
                                   name="avatar"
                                   accept="image/jpeg,image/png,image/webp">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-pup px-4">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password card -->
            <div class="profile-card">
                <div class="profile-card__header profile-card__header--danger">
                    <span class="profile-card__header-icon profile-card__header-icon--danger">
                        <i class="bi bi-shield-lock-fill"></i>
                    </span>
                    <div>
                        <h2 class="profile-card__title">Change Password</h2>
                        <p class="profile-card__subtitle">Use a strong, unique password</p>
                    </div>
                </div>

                <div class="profile-card__body">
                    <form method="POST" action="actions/change_password.php">

                        <div class="mb-3">
                            <label class="form-label" for="currentPassword">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password"
                                       class="form-control"
                                       id="currentPassword"
                                       name="current_password"
                                       autocomplete="current-password"
                                       placeholder="Enter current password"
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
                                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                                    <input type="password"
                                           class="form-control"
                                           id="newPassword"
                                           name="new_password"
                                           minlength="6"
                                           placeholder="Min. 6 characters"
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
                                    <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
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

                        <button type="submit" class="btn btn-outline-pup px-4">
                            <i class="bi bi-key-fill me-1"></i>
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

        </div><!-- /col-lg-8 -->
    </div><!-- /row -->
</div><!-- /container -->

<script>
(function () {
    // Bio character counter
    const bio = document.getElementById('profileBio');
    const count = document.getElementById('bioCount');
    if (bio && count) {
        const update = () => { count.textContent = bio.value.length; };
        update();
        bio.addEventListener('input', update);
    }

    // Avatar dropzone
    const zone = document.querySelector('.profile-upload-zone');
    const fileInput = document.getElementById('profileAvatar');
    if (zone && fileInput) {
        zone.addEventListener('click', () => fileInput.click());
        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('is-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('is-over'));
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('is-over');
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                zone.querySelector('.profile-upload-zone__label').textContent = e.dataTransfer.files[0].name;
            }
        });
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length) {
                zone.querySelector('.profile-upload-zone__label').innerHTML = fileInput.files[0].name;
            }
        });
    }
})();
</script>
