<!-- ================= HERO ================= -->

<section class="hero-section"
         data-login-success="<?= $showLoginAnimation ? 'true' : 'false' ?>">
    <div class="container">

        <div class="row align-items-center">

            <div class="col-lg-7">

                <span class="badge bg-warning text-dark mb-3">
                    PUP Biñan Student Marketplace
                </span>

                <h1 class="display-4 fw-bold">
                    Where <span class="text-warning">Iskolars</span> Buy & Sell.
                </h1>

                <p class="lead mt-3">
                    A simple marketplace made for PUP Biñan students.
                    Find affordable school essentials, pre-loved items,
                    and more within the campus community.
                </p>

                <div class="mt-4">

                    <a href="products.php"
                       class="btn btn-warning btn-lg me-2">

                        <i class="bi bi-search"></i>
                        Browse Items

                    </a>

                    <?php if (isset($_SESSION["user_id"])): ?>
                        <button class="btn btn-outline-light btn-lg"
                                data-bs-toggle="modal"
                                data-bs-target="#postItemModal">
                            <i class="bi bi-plus-circle"></i>
                            Sell an Item
                        </button>
                    <?php else: ?>
                        <a class="btn btn-outline-light btn-lg" href="login.php">
                            <i class="bi bi-plus-circle"></i>
                            Sign in to Sell
                        </a>
                    <?php endif; ?>

                </div>

            </div>

            <div class="col-lg-5 text-center mt-5 mt-lg-0">

                <div class="hero-icon">
                    <i class="bi bi-shop-window"></i>
                </div>

            </div>

        </div>

    </div>
</section>


<!-- ================= CATEGORIES ================= -->

<section class="py-5">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">
                Browse by Category
            </h2>

            <p class="text-muted">
                Find what you need for school and everyday life.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-6 col-md-3">
                <a href="products.php?category=Books"
                   class="text-decoration-none">

                    <div class="category-card text-center">

                        <i class="bi bi-book"></i>

                        <h5>Books</h5>

                        <p>Textbooks & reviewers</p>

                    </div>

                </a>
            </div>


            <div class="col-6 col-md-3">

                <a href="products.php?category=Uniforms"
                   class="text-decoration-none">

                    <div class="category-card text-center">

                        <i class="bi bi-person-badge"></i>

                        <h5>Uniforms</h5>

                        <p>Pre-loved uniforms</p>

                    </div>

                </a>

            </div>


            <div class="col-6 col-md-3">

                <a href="products.php?category=Electronics"
                   class="text-decoration-none">

                    <div class="category-card text-center">

                        <i class="bi bi-laptop"></i>

                        <h5>Electronics</h5>

                        <p>Gadgets & accessories</p>

                    </div>

                </a>

            </div>


            <div class="col-6 col-md-3">

                <a href="products.php?category=Others"
                   class="text-decoration-none">

                    <div class="category-card text-center">

                        <i class="bi bi-grid"></i>

                        <h5>Others</h5>

                        <p>More student finds</p>

                    </div>

                </a>

            </div>

        </div>

    </div>

</section>


<!-- ================= FEATURED PRODUCTS ================= -->

<section class="py-5 bg-light">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-bold mb-1">
                    Featured Items
                </h2>

                <p class="text-muted mb-0">
                    Some items posted by fellow students.
                </p>

            </div>

            <a href="products.php"
               class="btn btn-outline-pup">

                View All

            </a>

        </div>

        <div class="row g-4"
             id="featuredProducts">
        </div>
        <div id="productLoadError" class="alert alert-warning mt-4 d-none" role="alert"></div>

    </div>

</section>


<!-- ================= HOW IT WORKS ================= -->

<section class="py-5">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">
                How PUPMarket Works
            </h2>

        </div>

        <div class="row g-4 text-center">

            <div class="col-md-4">

                <div class="how-card">

                    <div class="step-icon">
                        <i class="bi bi-search"></i>
                    </div>

                    <h4>1. Find an Item</h4>

                    <p class="text-muted">
                        Browse items posted by fellow
                        PUP Biñan students.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="how-card">

                    <div class="step-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>

                    <h4>2. Contact the Seller</h4>

                    <p class="text-muted">
                        Get in touch with the seller
                        and discuss the details.
                    </p>

                </div>

            </div>


            <div class="col-md-4">

                <div class="how-card">

                    <div class="step-icon">
                        <i class="bi bi-hand-thumbs-up"></i>
                    </div>

                    <h4>3. Meet & Buy</h4>

                    <p class="text-muted">
                        Arrange a safe meetup within
                        the campus community.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- ================= PRODUCT MODAL ================= -->

<div class="modal fade" id="productModal">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="productName">
                    Product
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>

            </div>

            <div class="modal-body product-modal-details">

                <img id="productImage"
                     src=""
                     class="img-fluid product-modal-image"
                     alt="Product image">

                <p>
                    <strong>Price:</strong>
                    <span id="productPrice"></span>
                </p>

                <p>
                    <strong>Category:</strong>
                    <span id="productCategory"></span>
                </p>

                <p>
                    <strong>Condition:</strong>
                    <span id="productCondition"></span>
                </p>

                <p>
                    <strong>Description:</strong>
                    <span id="productDescription"></span>
                </p>

                <p>
                    <strong>Seller:</strong>
                    <span id="productSeller"></span>
                </p>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-pup"
                    id="contactSellerBtn"
                    data-authenticated="<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>">

                    <i class="bi bi-chat"></i>
                    Chat with Seller

                </button>
                <button type="button"
                        class="btn btn-outline-danger"
                        id="reportListingBtn"
                        data-authenticated="<?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>">
                    <i class="bi bi-flag"></i>
                    Report
                </button>
                <p id="sellerChatStatus" class="small text-muted mb-0" aria-live="polite"></p>

            </div>

        </div>

    </div>

</div>

<?php require VIEWS_PATH . "/partials/report_modal.php"; ?>

<?php require __DIR__ . "/../src/includes/post_item_modal.php"; ?>
<?php require VIEWS_PATH . "/partials/post_item_modal.php"; ?>
