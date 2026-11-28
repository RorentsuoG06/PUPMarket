<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$showLoginAnimation = !empty($_SESSION["login_success"]);
unset($_SESSION["login_success"]);

$pageTitle = "PUPMarket | PUP Biñan Student Marketplace";
$currentPage = "home";
require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

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

<?php require __DIR__ . "/../src/includes/report_modal.php"; ?>

<!-- ================= POST ITEM MODAL ================= -->

<div class="modal fade" id="postItemModal" tabindex="-1" aria-labelledby="postItemModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content post-item-modal">

            <div class="modal-header post-item-modal__header">

                <div class="d-flex align-items-center gap-3">
                    <span class="post-item-modal__icon" aria-hidden="true">
                        <i class="bi bi-bag-plus-fill"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="postItemModalLabel">
                            Post an Item
                        </h5>
                        <p class="post-item-modal__subtitle mb-0">
                            List school essentials, books, or pre-loved items for PUP Biñan Iskolars.
                        </p>
                    </div>
                </div>

                <button type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>

            <div class="modal-body post-item-modal__body">

                <?php if (isset($_SESSION["user_id"])): ?>
                <form id="postItemForm"
                      method="POST"
                      enctype="multipart/form-data"
                      data-csrf-token="<?= htmlspecialchars(csrfToken()) ?>"
                      novalidate>

                    <div class="row g-4">

                        <div class="col-md-5">
                            <label class="form-label fw-semibold" for="itemImage">
                                Product Photo
                            </label>

                            <label class="post-item-dropzone" id="postItemDropzone" for="itemImage">
                                <input type="file"
                                       class="post-item-dropzone__input"
                                       id="itemImage"
                                       name="image"
                                       accept="image/jpeg,image/png,image/webp"
                                       required>

                                <div class="post-item-dropzone__placeholder" id="imageDropPlaceholder">
                                    <span class="post-item-dropzone__badge" aria-hidden="true">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </span>
                                    <span class="fw-semibold">Click or drop photo here</span>
                                    <span class="small text-muted">JPG, PNG, or WEBP · Max 2MB</span>
                                </div>

                                <div class="post-item-dropzone__preview d-none" id="imagePreviewContainer">
                                    <img id="imagePreview"
                                         src=""
                                         class="img-fluid"
                                         alt="Image preview">
                                    <span class="post-item-dropzone__change">
                                        <i class="bi bi-arrow-repeat"></i> Change photo
                                    </span>
                                </div>
                            </label>
                        </div>

                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="itemName">
                                    Item Name
                                </label>
                                <input type="text"
                                       class="form-control"
                                       id="itemName"
                                       name="name"
                                       placeholder="e.g. CASIO fx-991ES Plus Calculator"
                                       maxlength="120"
                                       required>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="itemPrice">
                                        Price
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text fw-semibold">₱</span>
                                        <input type="number"
                                               class="form-control"
                                               id="itemPrice"
                                               name="price"
                                               placeholder="0.00"
                                               min="0.01"
                                               step="0.01"
                                               required>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-semibold" for="itemCategory">
                                        Category
                                    </label>
                                    <select class="form-select"
                                            id="itemCategory"
                                            name="category"
                                            required>
                                        <option value="">Select category</option>
                                        <option value="Books">Books</option>
                                        <option value="Uniforms">Uniforms</option>
                                        <option value="Electronics">Electronics</option>
                                        <option value="School Supplies">School Supplies</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold" for="itemCondition">
                                    Condition
                                </label>
                                <select class="form-select"
                                        id="itemCondition"
                                        name="condition"
                                        required>
                                    <option value="">Select condition</option>
                                    <option value="New">New — Brand new, unused</option>
                                    <option value="Like New">Like New — Barely used, no flaws</option>
                                    <option value="Good">Good — Lightly used, works great</option>
                                    <option value="Fair">Fair — Visible wear, still functional</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-semibold mb-0" for="itemDescription">
                                        Description
                                    </label>
                                    <span class="small text-muted" id="itemDescriptionCount">0 / 5000</span>
                                </div>
                                <textarea class="form-control"
                                          id="itemDescription"
                                          name="description"
                                          rows="4"
                                          placeholder="Include meetup spot (e.g., PUP Biñan lobby), inclusions, or flaws..."
                                          maxlength="5000"
                                          required></textarea>
                            </div>
                        </div>

                    </div>

                    <div id="itemSubmitStatus" class="alert d-none mt-3 mb-0" role="status" aria-live="polite"></div>

                    <div class="post-item-modal__footer mt-4 pt-3">
                        <span class="post-item-modal__seller">
                            <i class="bi bi-person-check-fill"></i>
                            Posting as <strong><?= htmlspecialchars($_SESSION["full_name"] ?? "your account") ?></strong>
                        </span>

                        <div class="d-flex gap-2">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="btn btn-pup px-4">
                                <i class="bi bi-upload"></i>
                                Publish Listing
                            </button>
                        </div>
                    </div>

                </form>
                <?php else: ?>
                    <div class="text-center py-4">
                        <i class="bi bi-lock fs-1 text-muted" aria-hidden="true"></i>
                        <h6 class="fw-bold mt-3">Sign in to post an item</h6>
                        <p class="text-muted mb-3">Listings are linked to your verified PUP Biñan student account.</p>
                        <a class="btn btn-pup px-4" href="login.php">Sign In</a>
                    </div>
                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
