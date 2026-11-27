<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
ensureSessionStarted();
restoreRememberedSession();
requireAuth("login.php");

$pageTitle = "My Listings | PUPMarket";
$currentPage = "my_listings";
$csrfToken = csrfToken();

require __DIR__ . "/../src/includes/header.php";
require __DIR__ . "/../src/includes/navbar.php";
?>

<section class="page-header">
    <div class="container">
        <h1 class="fw-bold mb-2">My Listings</h1>
        <p>Update or remove items you have posted.</p>
    </div>
</section>

<main class="py-5">
    <div class="container">
        <p id="myListingsStatus"
           class="alert d-none"
           role="status"
           aria-live="polite"></p>

        <div id="myListingsEmpty" class="text-center py-5 d-none">
            <i class="bi bi-bag fs-1 text-muted" aria-hidden="true"></i>
            <h2 class="h4 mt-3">You have no listings yet</h2>
            <p class="text-muted">Post an item to see it here.</p>
            <a class="btn btn-pup" href="index.php">Post an Item</a>
        </div>

        <div id="myListingsContainer" class="row g-4"></div>

        <nav id="myListingsPagination"
             class="d-none mt-4"
             aria-label="Your listing pages">
            <div class="d-flex align-items-center justify-content-center gap-3">
                <button type="button"
                        class="btn btn-outline-pup"
                        id="myListingsPrevious"
                        aria-label="Previous page">
                    Previous
                </button>
                <span id="myListingsPageStatus"
                      class="small text-muted"
                      aria-live="polite"></span>
                <button type="button"
                        class="btn btn-outline-pup"
                        id="myListingsNext"
                        aria-label="Next page">
                    Next
                </button>
            </div>
        </nav>
    </div>
</main>

<div class="modal fade" id="editListingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5">Edit Listing</h2>
                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <form id="editListingForm"
                  method="POST"
                  enctype="multipart/form-data"
                  data-csrf-token="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="listing_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="editListingName">Item Name</label>
                        <input class="form-control"
                               id="editListingName"
                               name="name"
                               maxlength="120"
                               required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="editListingCategory">Category</label>
                            <select class="form-select" id="editListingCategory" name="category" required>
                                <option value="Books">Books</option>
                                <option value="Uniforms">Uniforms</option>
                                <option value="Electronics">Electronics</option>
                                <option value="School Supplies">School Supplies</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label" for="editListingPrice">Price</label>
                            <input class="form-control"
                                   type="number"
                                   id="editListingPrice"
                                   name="price"
                                   min="0.01"
                                   step="0.01"
                                   required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editListingCondition">Condition</label>
                        <select class="form-select" id="editListingCondition" name="condition" required>
                            <option value="New">New</option>
                            <option value="Like New">Like New</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="editListingDescription">Description</label>
                        <textarea class="form-control"
                                  id="editListingDescription"
                                  name="description"
                                  rows="4"
                                  maxlength="5000"
                                  required></textarea>
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="editListingImage">Replace Photo (optional)</label>
                        <input class="form-control"
                               type="file"
                               id="editListingImage"
                               name="image"
                               accept="image/jpeg,image/png,image/webp">
                        <div class="form-text">Leave blank to keep the current photo. JPG, PNG, or WEBP up to 2MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-pup">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . "/../src/includes/footer.php"; ?>
