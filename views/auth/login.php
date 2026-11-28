<section class="py-5 auth-page">

    <div class="container">

        <div class="row justify-content-center">

            <div class="col-lg-6 col-xl-5">

                <div class="contact-form-card auth-card">

                    <div class="auth-switch" aria-label="Account options">
                        <span class="active" aria-current="page">
                            <i class="bi bi-box-arrow-in-right"></i>
                            Log In
                        </span>
                        <a href="register.php">
                            <i class="bi bi-person-plus-fill"></i>
                            Create Account
                        </a>
                    </div>

                    <div class="auth-heading">
                        <h2 class="fw-bold">Welcome Back</h2>
                        <p class="text-muted mb-0">Log in with your PUP student credentials.</p>
                    </div>

                    <?php if ($message !== ""): ?>

                        <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                            <?= htmlspecialchars($message) ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST"
                          action="actions/process_login.php">

                        <div class="mb-3">

                            <label class="form-label" for="loginEmail">
                                PUP Webmail
                            </label>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-envelope-fill"></i>
                                </span>
                                <input type="email"
                                       class="form-control"
                                       id="loginEmail"
                                       name="email"
                                       placeholder="name@iskolarngbayan.pup.edu.ph"
                                       pattern=".+@iskolarngbayan\.pup\.edu\.ph"
                                       title="Use your @iskolarngbayan.pup.edu.ph email address."
                                       autocomplete="email"
                                       required>
                            </div>

                        </div>

                        <div class="mb-4">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0" for="loginPassword">
                                    Password
                                </label>
                                <a href="forgot_password.php" class="small text-decoration-none">
                                    Forgot password?
                                </a>
                            </div>

                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password"
                                       class="form-control"
                                       id="loginPassword"
                                       name="password"
                                       placeholder="Enter your password"
                                       autocomplete="current-password"
                                       required>
                                <button class="btn btn-outline-secondary password-toggle"
                                        type="button"
                                        data-password-toggle="loginPassword"
                                        aria-label="Show password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="rememberMe"
                                   name="remember_me"
                                   value="1">
                            <label class="form-check-label" for="rememberMe">
                                Remember me
                                <span class="text-muted small">(stay signed in for 30 days)</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="btn btn-pup auth-submit w-100">

                            <i class="bi bi-box-arrow-in-right"></i>
                            Log In

                        </button>

                    </form>

                    <div class="auth-divider my-3">
                        <span>or</span>
                    </div>

                    <a href="actions/microsoft_login.php"
                       class="btn btn-microsoft w-100"
                       data-remember-me-link>
                        <svg width="18" height="18" viewBox="0 0 21 21" aria-hidden="true">
                            <rect x="1" y="1" width="9" height="9" fill="#f25022"/>
                            <rect x="11" y="1" width="9" height="9" fill="#7fba00"/>
                            <rect x="1" y="11" width="9" height="9" fill="#00a4ef"/>
                            <rect x="11" y="11" width="9" height="9" fill="#ffb900"/>
                        </svg>
                        Continue with Microsoft
                    </a>


                    <p class="text-center mt-4 mb-0">

                        Don't have an account?
                        <a href="register.php">Create an account</a>

                    </p>

                </div>

            </div>

        </div>

    </div>

</section>

