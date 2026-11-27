<?php
$pageTitle = "Products | PUPMarket";
$currentPage = "products";
require "includes/header.php";
require "includes/navbar.php";
?>

<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold">Items for Sale</h1>
        <p>Browse products posted by PUP Biñan students.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">

        <div class="row g-3 mb-5">

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>

                    <input type="text"
                           class="form-control"
                           id="searchInput"
                           maxlength="120"
                           placeholder="Search items...">
                </div>
            </div>

            <div class="col-md-3">

                <select class="form-select"
                        id="categoryFilter">

                    <option value="all">All Categories</option>
                    <option value="Books">Books</option>
                    <option value="Uniforms">Uniforms</option>
                    <option value="Electronics">Electronics</option>
                    <option value="School Supplies">School Supplies</option>
                    <option value="Others">Others</option>

                </select>

            </div>

            <div class="col-md-4">

                <select class="form-select"
                        id="sortFilter">

                    <option value="default">Sort By</option>
                    <option value="low">Price: Low to High</option>
                    <option value="high">Price: High to Low</option>
                    <option value="name">Name: A-Z</option>

                </select>

            </div>

        </div>

        <div class="row g-4"
             id="productContainer">
        </div>
        <div id="productLoadError" class="alert alert-warning mt-4 d-none" role="alert"></div>

        <div id="noResults"
             class="text-center py-5 d-none">

            <i class="bi bi-search fs-1 text-muted"></i>

            <h4 class="mt-3">
                No items found
            </h4>

            <p class="text-muted">
                Try another search or category.
            </p>

        </div>

        <nav id="productsPagination"
             class="d-none mt-4"
             aria-label="Product pages">
            <div class="d-flex align-items-center justify-content-center gap-3">
                <button type="button"
                        class="btn btn-outline-pup"
                        id="productsPrevious"
                        aria-label="Previous page">
                    Previous
                </button>
                <span id="productsPageStatus"
                      class="small text-muted"
                      aria-live="polite"></span>
                <button type="button"
                        class="btn btn-outline-pup"
                        id="productsNext"
                        aria-label="Next page">
                    Next
                </button>
            </div>
        </nav>

    </div>
</section>

<div class="modal fade" id="productModal">

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title"
                    id="productName">
                    Product
                </h5>

                <button class="btn-close"
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

                <button type="button"
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

<<<<<<< Updated upstream:products.php
<?php require "includes/footer.php"; ?>
=======
<?php require __DIR__ . "/../src/includes/report_modal.php"; ?>
<?php require __DIR__ . "/../src/includes/footer.php"; ?>
>>>>>>> Stashed changes:public/products.php
