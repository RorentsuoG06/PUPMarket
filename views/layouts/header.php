<?php
require_once SRC_PATH . "/Middleware/auth.php";
restoreRememberedSession();

if (!isset($pageTitle)) {
    $pageTitle = "PUPMarket";
}
if (!isset($currentPage)) {
    $currentPage = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= htmlspecialchars($pageTitle) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet"
          href="assets/css/custom.css?v=10">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-pup sticky-top">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shop"></i> PUPMarket
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>"
                       href="index.php">
                        Home
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>"
                       href="products.php">
                        Products
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'my_listings' ? 'active' : '' ?>"
                           href="my_listings.php">
                            My Listings
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id']) && isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'moderation' ? 'active' : '' ?>"
                           href="moderation.php">
                            Moderation
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>"
                       href="about.php">
                        About
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>"
                       href="contact.php">
                        Contact
                    </a>
                </li>

                <?php if (isset($_SESSION['user_id'])): ?>

                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'chats' ? 'active' : '' ?>"
                           href="chats.php">
                            <i class="bi bi-chat-dots"></i>
                            Chats
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'profile' ? 'active' : '' ?>"
                           href="profile.php">
                            <i class="bi bi-person-circle"></i>
                            Profile
                        </a>
                    </li>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning fw-bold"
                           href="logout.php">
                            <i class="bi bi-box-arrow-right"></i>
                            Sign Out
                        </a>
                    </li>

                <?php else: ?>

                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning fw-bold"
                           href="login.php">
                            <i class="bi bi-person"></i>
                            Login
                        </a>
                    </li>

                <?php endif; ?>

            </ul>

        </div>
    </div>
</nav>
