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
                        <h5 class="modal-title fw-bold" id="postItemModalLabel">
                            Post an Item
                        </h5>
                        <p class="post-item-modal__subtitle mb-0">
                            List school essentials, books, or pre-loved items for PUP Biñan Iskolars.
                        </p>
                    </div>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                </button>

            </div>

            <div class="modal-body post-item-modal__body">

                <?php if (isset($_SESSION["user_id"])): ?>
                    <form id="postItemForm" method="POST" enctype="multipart/form-data"
                        data-csrf-token="<?= htmlspecialchars(csrfToken()) ?>" novalidate>

                        <div class="row g-4">

                            <div class="col-md-5">
                                <label class="form-label fw-semibold" for="itemImage">
                                    Product Photo
                                </label>

                                <label class="post-item-dropzone" id="postItemDropzone" for="itemImage">
                                    <input type="file" class="post-item-dropzone__input" id="itemImage" name="image"
                                        accept="image/jpeg,image/png,image/webp" required>

                                    <div class="post-item-dropzone__placeholder" id="imageDropPlaceholder">
                                        <span class="post-item-dropzone__badge" aria-hidden="true">
                                            <i class="bi bi-cloud-arrow-up"></i>
                                        </span>
                                        <span class="fw-semibold">Click or drop photo here</span>
                                        <span class="small text-muted">JPG, PNG, or WEBP · Max 2MB</span>
                                    </div>

                                    <div class="post-item-dropzone__preview d-none" id="imagePreviewContainer">
                                        <img id="imagePreview" src="" class="img-fluid" alt="Image preview">
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
                                    <input type="text" class="form-control" id="itemName" name="name"
                                        placeholder="e.g. CASIO fx-991ES Plus Calculator" maxlength="120" required>
                                </div>

                                <div class="row g-3 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="itemPrice">
                                            Price
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text fw-semibold">₱</span>
                                            <input type="number" class="form-control" id="itemPrice" name="price"
                                                placeholder="0.00" min="0.01" step="0.01" required>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label fw-semibold" for="itemCategory">
                                            Category
                                        </label>
                                        <select class="form-select" id="itemCategory" name="category" required>
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
                                    <select class="form-select" id="itemCondition" name="condition" required>
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
                                    <textarea class="form-control" id="itemDescription" name="description" rows="4"
                                        placeholder="Include meetup spot (e.g., PUP Biñan lobby), inclusions, or flaws..."
                                        maxlength="5000" required></textarea>
                                </div>
                            </div>

                        </div>

                        <div id="itemSubmitStatus" class="alert d-none mt-3 mb-0" role="status" aria-live="polite"></div>

                        <div class="post-item-modal__footer mt-4 pt-3">
                            <span class="post-item-modal__seller">
                                <i class="bi bi-person-check-fill"></i>
                                Posting as
                                <strong><?= htmlspecialchars($_SESSION["full_name"] ?? "your account") ?></strong>
                            </span>

                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-pup px-4">
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