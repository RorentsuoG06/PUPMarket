<?php
if (!isset($currentPage)) {
    $currentPage = "";
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-pup sticky-top">
    <div class="container">

        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-shop"></i> PUPMarket
        </a>

        <button class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav">

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
