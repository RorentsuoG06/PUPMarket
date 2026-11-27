(function () {
    let products = [];
    let productPage = 1;
    let productLoadSequence = 0;
    let productSearchTimer = null;

    function createProductCard(product) {
        const column = document.createElement("div");
        column.className = "col-6 col-lg-3";

        const card = document.createElement("article");
        card.className = "card product-card h-100";

        const image = document.createElement("img");
        image.className = "product-image";
        image.alt = product.name;
        image.src = product.image || "images/no-image.jpg";
        image.addEventListener("error", function useFallback() {
            image.removeEventListener("error", useFallback);
            image.src = "images/no-image.jpg";
        });

        const body = document.createElement("div");
        body.className = "card-body";

        const category = document.createElement("span");
        category.className = "badge";
        category.textContent = product.category;

        const title = document.createElement("h2");
        title.className = "card-title";
        title.textContent = product.name;

        const price = document.createElement("p");
        price.className = "product-price";
        price.textContent = `₱${Number(product.price).toLocaleString()}`;

        const condition = document.createElement("p");
        condition.className = "small text-muted product-condition";
        condition.textContent = `${product.condition} condition`;

        const button = document.createElement("button");
        button.className = "btn btn-pup w-100";
        button.type = "button";
        button.dataset.productId = product.id;
        button.textContent = "View Details";

        body.append(category, title, price, condition, button);
        card.append(image, body);
        column.append(card);
        return column;
    }

    function displayProducts(productList) {
        const container = document.getElementById("productContainer");
        if (!container) {
            return;
        }

        container.replaceChildren();
        const noResults = document.getElementById("noResults");
        noResults?.classList.toggle("d-none", productList.length > 0);

        productList.forEach(function (product) {
            container.append(createProductCard(product));
        });
    }

    function displayFeaturedProducts() {
        const container = document.getElementById("featuredProducts");
        if (!container) {
            return;
        }

        container.replaceChildren();
        products.slice(0, 4).forEach(function (product) {
            container.append(createProductCard(product));
        });
    }

    function showProductById(id) {
        const product = products.find((item) => String(item.id) === String(id));
        if (!product) {
            return;
        }

        const productName = document.getElementById("productName");
        const productPrice = document.getElementById("productPrice");
        const productCategory = document.getElementById("productCategory");
        const productCondition = document.getElementById("productCondition");
        const productDescription = document.getElementById("productDescription");
        const productSeller = document.getElementById("productSeller");
        const image = document.getElementById("productImage");
        const modalElement = document.getElementById("productModal");
        const contactSellerButton = document.getElementById("contactSellerBtn");
        const reportListingButton = document.getElementById("reportListingBtn");
        const sellerChatStatus = document.getElementById("sellerChatStatus");

        if (!productName || !productPrice || !productCategory || !productCondition ||
            !productDescription || !productSeller || !image || !modalElement) {
            return;
        }

        productName.textContent = product.name;
        productPrice.textContent = `₱${Number(product.price).toLocaleString()}`;
        productCategory.textContent = product.category;
        productCondition.textContent = product.condition;
        productDescription.textContent = product.description;
        productSeller.textContent = product.seller;
        image.src = product.image || "images/no-image.jpg";
        image.onerror = function () {
            image.onerror = null;
            image.src = "images/no-image.jpg";
        };

        if (contactSellerButton) {
            const isAuthenticated = contactSellerButton.dataset.authenticated === "true";
            contactSellerButton.dataset.listingId = product.listingId || "";
            contactSellerButton.dataset.canContact = product.canContactSeller ? "true" : "false";
            contactSellerButton.disabled = !product.listingId || Boolean(product.isOwnListing);

            if (product.isOwnListing) {
                contactSellerButton.innerHTML = '<i class="bi bi-person-check"></i> Your Listing';
                if (sellerChatStatus) {
                    sellerChatStatus.textContent = "You cannot start a chat with yourself.";
                }
            } else if (!product.listingId) {
                contactSellerButton.innerHTML = '<i class="bi bi-chat"></i> Chat Unavailable';
                if (sellerChatStatus) {
                    sellerChatStatus.textContent = "This demo item is not linked to a PUPMarket seller account.";
                }
            } else if (!isAuthenticated) {
                contactSellerButton.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In to Chat';
                contactSellerButton.disabled = false;
                if (sellerChatStatus) {
                    sellerChatStatus.textContent = "Sign in to contact this seller.";
                }
            } else {
                contactSellerButton.innerHTML = '<i class="bi bi-chat"></i> Chat with Seller';
                if (sellerChatStatus) {
                    sellerChatStatus.textContent = "";
                }
            }
        }

        if (reportListingButton) {
            reportListingButton.dataset.listingId = product.listingId || "";
            reportListingButton.disabled = !product.listingId || Boolean(product.isOwnListing);
            reportListingButton.title = product.isOwnListing
                ? "You cannot report your own listing."
                : "";
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    async function loadProducts(page = 1) {
        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const sortFilter = document.getElementById("sortFilter");
        const productContainer = document.getElementById("productContainer");
        const featuredContainer = document.getElementById("featuredProducts");
        const productLoadError = document.getElementById("productLoadError");

        if (!productContainer && !featuredContainer) {
            return;
        }

        const sequence = ++productLoadSequence;
        const params = new URLSearchParams({
            page: String(page),
            per_page: "12"
        });
        const search = searchInput?.value.trim() || "";
        const category = categoryFilter?.value || "all";
        const sort = sortFilter?.value || "default";
        if (search) {
            params.set("q", search);
        }
        if (category !== "all") {
            params.set("category", category);
        }
        if (sort !== "default") {
            params.set("sort", sort);
        }

        try {
            const response = await fetch(`api/products.php?${params.toString()}`, {
                headers: { "Accept": "application/json" }
            });
            const result = await response.json();
            if (
                !response.ok ||
                !result.success ||
                !Array.isArray(result.products) ||
                !result.pagination
            ) {
                throw new Error(result.message || "Unable to load marketplace listings.");
            }
            if (sequence !== productLoadSequence) {
                return;
            }

            products = result.products;
            productPage = Number(result.pagination.page);
            productLoadError?.classList.add("d-none");

            if (productContainer) {
                displayProducts(products);
            }
            if (featuredContainer) {
                displayFeaturedProducts();
            }

            const pagination = document.getElementById("productsPagination");
            const pageStatus = document.getElementById("productsPageStatus");
            const previousButton = document.getElementById("productsPrevious");
            const nextButton = document.getElementById("productsNext");
            const totalPages = Number(result.pagination.totalPages);
            pagination?.classList.toggle("d-none", totalPages <= 1);
            if (pageStatus) {
                pageStatus.textContent =
                    `Page ${productPage} of ${totalPages} · ${Number(result.pagination.total)} items`;
            }
            if (previousButton) {
                previousButton.disabled = productPage <= 1;
            }
            if (nextButton) {
                nextButton.disabled = productPage >= totalPages;
            }
        } catch (error) {
            if (sequence !== productLoadSequence) {
                return;
            }
            console.error("Marketplace listings could not be loaded.", error);
            if (productLoadError) {
                productLoadError.textContent = error instanceof Error
                    ? error.message
                    : "Unable to load marketplace listings.";
                productLoadError.classList.remove("d-none");
            }
        }
    }

    function setupProductFeatures() {
        const productContainer = document.getElementById("productContainer");
        const featuredContainer = document.getElementById("featuredProducts");

        if (!productContainer && !featuredContainer) {
            return;
        }

        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const sortFilter = document.getElementById("sortFilter");

        searchInput?.addEventListener("input", function () {
            window.clearTimeout(productSearchTimer);
            productSearchTimer = window.setTimeout(() => loadProducts(1), 250);
        });
        categoryFilter?.addEventListener("change", () => loadProducts(1));
        sortFilter?.addEventListener("change", () => loadProducts(1));
        document.getElementById("productsPrevious")?.addEventListener("click", function () {
            if (productPage > 1) {
                loadProducts(productPage - 1);
            }
        });
        document.getElementById("productsNext")?.addEventListener("click", function () {
            loadProducts(productPage + 1);
        });

        const category = new URLSearchParams(window.location.search).get("category");
        if (category && categoryFilter) {
            categoryFilter.value = category;
        }

        document.addEventListener("click", function (event) {
            const button = event.target.closest("[data-product-id]");
            if (button) {
                showProductById(button.dataset.productId);
            }
        });

        document.getElementById("contactSellerBtn")?.addEventListener("click", function () {
            const contactButton = document.getElementById("contactSellerBtn");
            const listingId = contactButton?.dataset.listingId;
            if (!listingId || contactButton?.disabled) {
                return;
            }
            if (contactButton.dataset.authenticated !== "true") {
                window.location.href = "login.php";
                return;
            }
            if (contactButton.dataset.canContact !== "true") {
                return;
            }

            window.location.href = `chats.php?product=${encodeURIComponent(listingId)}`;
        });

        loadProducts(1);
    }

    function setupItemListing() {
        const imageInput = document.getElementById("itemImage");
        const preview = document.getElementById("imagePreview");
        const previewContainer = document.getElementById("imagePreviewContainer");

        if (imageInput && preview && previewContainer) {
            imageInput.addEventListener("change", function () {
                const file = imageInput.files[0];
                if (!file) {
                    previewContainer.classList.add("d-none");
                    return;
                }

                if (!file.type.startsWith("image/")) {
                    alert("Please select an image file.");
                    imageInput.value = "";
                    previewContainer.classList.add("d-none");
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (event) {
                    preview.src = event.target.result;
                    previewContainer.classList.remove("d-none");
                };
                reader.readAsDataURL(file);
            });
        }

        const form = document.getElementById("postItemForm");
        if (!form) {
            return;
        }

        form.addEventListener("submit", async function (event) {
            event.preventDefault();

            const name = document.getElementById("itemName").value.trim();
            const category = document.getElementById("itemCategory").value;
            const price = document.getElementById("itemPrice").value.trim();
            const condition = document.getElementById("itemCondition").value;
            const description = document.getElementById("itemDescription").value.trim();
            const file = imageInput?.files[0];
            const status = document.getElementById("itemSubmitStatus");
            const submitButton = form.querySelector('button[type="submit"]');

            if (!name || !category || !price || !condition || !description) {
                if (status) {
                    status.textContent = "Please fill in all required fields.";
                    status.className = "form-text mb-3 text-danger";
                }
                return;
            }

            const numericPrice = Number(price);
            if (!Number.isFinite(numericPrice) || numericPrice <= 0) {
                if (status) {
                    status.textContent = "Price must be greater than 0.";
                    status.className = "form-text mb-3 text-danger";
                }
                return;
            }

            if (!file) {
                if (status) {
                    status.textContent = "Please upload a product photo.";
                    status.className = "form-text mb-3 text-danger";
                }
                return;
            }

            if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
                if (status) {
                    status.textContent = "Use a JPG, PNG, or WEBP image.";
                    status.className = "form-text mb-3 text-danger";
                }
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                if (status) {
                    status.textContent = "Please choose an image smaller than 2MB.";
                    status.className = "form-text mb-3 text-danger";
                }
                return;
            }

            if (status) {
                status.textContent = "Publishing your listing…";
                status.className = "form-text mb-3";
            }
            if (submitButton) {
                submitButton.disabled = true;
            }

            try {
                const response = await fetch("api/products.php", {
                    method: "POST",
                    headers: { "X-CSRF-Token": form.dataset.csrfToken || "" },
                    body: new FormData(form)
                });
                const result = await response.json();
                if (!response.ok || !result.success || !result.product) {
                    throw new Error(result.message || "Your listing could not be published.");
                }

                products.unshift(result.product);
                displayProducts(products);
                displayFeaturedProducts();
                form.reset();
                previewContainer?.classList.add("d-none");

                const modalElement = document.getElementById("postItemModal");
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }

                if (window.location.pathname.includes("index.php")) {
                    window.location.href = "products.php";
                }
            } catch (error) {
                console.error("Marketplace listing could not be saved.", error);
                if (status) {
                    status.textContent = error instanceof Error
                        ? error.message
                        : "Your listing could not be published.";
                    status.className = "form-text mb-3 text-danger";
                }
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                }
            }
        });
    }

<<<<<<< Updated upstream:js/script.js
=======
    function setupMyListings() {
        const container = document.getElementById("myListingsContainer");
        const editForm = document.getElementById("editListingForm");
        if (!container || !editForm) {
            return;
        }

        const status = document.getElementById("myListingsStatus");
        const emptyState = document.getElementById("myListingsEmpty");
        const pagination = document.getElementById("myListingsPagination");
        const pageStatus = document.getElementById("myListingsPageStatus");
        const previousButton = document.getElementById("myListingsPrevious");
        const nextButton = document.getElementById("myListingsNext");
        const modalElement = document.getElementById("editListingModal");
        let listings = [];
        let currentPage = 1;
        let totalPages = 1;

        function setStatus(message, type = "info") {
            if (!status) {
                return;
            }
            status.textContent = message;
            status.className = `alert alert-${type}`;
        }

        function makeManagedCard(product) {
            const column = createProductCard(product);
            const body = column.querySelector(".card-body");
            body.querySelector("button[data-product-id]")?.remove();

            const actions = document.createElement("div");
            actions.className = "listing-management-actions";

            const editButton = document.createElement("button");
            editButton.type = "button";
            editButton.className = "btn btn-outline-pup";
            editButton.dataset.editListing = String(product.listingId);
            editButton.textContent = "Edit";

            const deleteButton = document.createElement("button");
            deleteButton.type = "button";
            deleteButton.className = "btn btn-outline-danger";
            deleteButton.dataset.deleteListing = String(product.listingId);
            deleteButton.textContent = "Delete";

            actions.append(editButton, deleteButton);
            body.append(actions);
            return column;
        }

        async function loadListings(page = currentPage, successMessage = "") {
            setStatus("Loading your listings…", "info");
            try {
                const params = new URLSearchParams({
                    mine: "1",
                    page: String(page),
                    per_page: "12"
                });
                const response = await fetch(`api/products.php?${params.toString()}`, {
                    headers: { "Accept": "application/json" }
                });
                const result = await response.json();
                if (
                    !response.ok ||
                    !result.success ||
                    !Array.isArray(result.products) ||
                    !result.pagination
                ) {
                    throw new Error(result.message || "Unable to load your listings.");
                }

                listings = result.products;
                currentPage = Number(result.pagination.page);
                totalPages = Number(result.pagination.totalPages);
                container.replaceChildren(...listings.map(makeManagedCard));
                const isEmpty = Number(result.pagination.total) === 0;
                emptyState?.classList.toggle("d-none", !isEmpty);
                pagination?.classList.toggle("d-none", totalPages <= 1);
                if (pageStatus) {
                    pageStatus.textContent =
                        `Page ${currentPage} of ${totalPages} · ${Number(result.pagination.total)} listings`;
                }
                if (previousButton) {
                    previousButton.disabled = currentPage <= 1;
                }
                if (nextButton) {
                    nextButton.disabled = currentPage >= totalPages;
                }
                if (successMessage) {
                    setStatus(successMessage, "success");
                } else {
                    status?.classList.add("d-none");
                }
            } catch (error) {
                console.error("Your marketplace listings could not be loaded.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to load your listings.",
                    "danger"
                );
            }
        }

        function sendListingAction(formData) {
            return fetch("api/products.php", {
                method: "POST",
                headers: { "X-CSRF-Token": editForm.dataset.csrfToken || "" },
                body: formData
            }).then(async function (response) {
                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Unable to save the listing.");
                }
                return result;
            });
        }

        container.addEventListener("click", async function (event) {
            const editButton = event.target.closest("[data-edit-listing]");
            if (editButton) {
                const listing = listings.find(
                    (item) => String(item.listingId) === editButton.dataset.editListing
                );
                if (!listing) {
                    return;
                }

                editForm.querySelector('[name="listing_id"]').value = String(listing.listingId);
                editForm.querySelector('[name="name"]').value = listing.name;
                editForm.querySelector('[name="category"]').value = listing.category;
                editForm.querySelector('[name="price"]').value = String(listing.price);
                editForm.querySelector('[name="condition"]').value = listing.condition;
                editForm.querySelector('[name="description"]').value = listing.description;
                editForm.querySelector('[name="image"]').value = "";
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
                return;
            }

            const deleteButton = event.target.closest("[data-delete-listing]");
            if (!deleteButton) {
                return;
            }

            const listingId = deleteButton.dataset.deleteListing;
            const listing = listings.find((item) => String(item.listingId) === listingId);
            if (!listing || !window.confirm(`Delete "${listing.name}"? This cannot be undone.`)) {
                return;
            }

            deleteButton.disabled = true;
            setStatus("Removing listing…", "info");
            const formData = new FormData();
            formData.set("action", "delete");
            formData.set("listing_id", listingId);
            try {
                await sendListingAction(formData);
                await loadListings(currentPage, "Listing removed.");
            } catch (error) {
                console.error("Marketplace listing could not be removed.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to remove the listing.",
                    "danger"
                );
                deleteButton.disabled = false;
            }
        });

        editForm.addEventListener("submit", async function (event) {
            event.preventDefault();
            if (!editForm.reportValidity()) {
                return;
            }

            const submitButton = editForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            setStatus("Saving listing…", "info");
            const formData = new FormData(editForm);
            formData.set("action", "update");
            try {
                await sendListingAction(formData);
                bootstrap.Modal.getInstance(modalElement)?.hide();
                editForm.reset();
                await loadListings(currentPage, "Listing updated.");
            } catch (error) {
                console.error("Marketplace listing could not be updated.", error);
                setStatus(
                    error instanceof Error ? error.message : "Unable to update the listing.",
                    "danger"
                );
            } finally {
                submitButton.disabled = false;
            }
        });

        previousButton?.addEventListener("click", function () {
            if (currentPage > 1) {
                loadListings(currentPage - 1);
            }
        });
        nextButton?.addEventListener("click", function () {
            if (currentPage < totalPages) {
                loadListings(currentPage + 1);
            }
        });

        loadListings(1);
    }

    function createOtpRequester({ button, emailInput, statusEl, otpInput, endpoint, defaultLabel, resendLabel }) {
        let cooldownTimer = null;

        function setStatus(text, isError) {
            if (!statusEl) {
                return;
            }
            statusEl.textContent = text;
            statusEl.className = `form-text mt-2 ${isError ? "text-danger" : "text-success"}`;
        }

        function startCooldown(seconds) {
            if (!button) {
                return;
            }
            if (cooldownTimer) {
                clearInterval(cooldownTimer);
            }
            let remaining = seconds;
            button.disabled = true;
            button.textContent = `Resend (${remaining}s)`;

            cooldownTimer = setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    clearInterval(cooldownTimer);
                    cooldownTimer = null;
                    button.disabled = false;
                    button.textContent = resendLabel;
                } else {
                    button.textContent = `Resend (${remaining}s)`;
                }
            }, 1000);
        }

        async function requestOtp() {
            if (!emailInput) {
                return false;
            }

            const email = emailInput.value.trim().toLowerCase();
            if (!email || !email.endsWith("@iskolarngbayan.pup.edu.ph")) {
                setStatus("Enter a valid @iskolarngbayan.pup.edu.ph email first.", true);
                emailInput.focus();
                return false;
            }

            if (button) {
                button.disabled = true;
                button.textContent = "Sending...";
            }
            setStatus("Sending verification code...", false);

            try {
                const response = await fetch(endpoint, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    setStatus(data.message || "Failed to send verification code.", true);
                    if (data.retry_after && Number(data.retry_after) > 0) {
                        startCooldown(Number(data.retry_after));
                    } else if (button) {
                        button.disabled = false;
                        button.textContent = defaultLabel;
                    }
                    return false;
                }

                setStatus(data.message || "Verification code sent to your PUP Webmail.", false);
                startCooldown(60);
                otpInput?.focus();
                return true;
            } catch (error) {
                setStatus("Network error while sending verification code. Please try again.", true);
                if (button) {
                    button.disabled = false;
                    button.textContent = defaultLabel;
                }
                return false;
            }
        }

        button?.addEventListener("click", requestOtp);
        return { requestOtp, setStatus };
    }

    function bindPasswordMatch(passwordInput, confirmInput) {
        if (!passwordInput || !confirmInput) {
            return function () {};
        }

        function validate() {
            confirmInput.setCustomValidity(
                confirmInput.value && confirmInput.value !== passwordInput.value
                    ? "Passwords do not match."
                    : ""
            );
        }

        passwordInput.addEventListener("input", validate);
        confirmInput.addEventListener("input", validate);
        return validate;
    }

>>>>>>> Stashed changes:public/js/script.js
    function setupAuthForms() {
        document.querySelectorAll("[data-otp-preview]").forEach(function (button) {
            button.addEventListener("click", function () {
                alert("OTP email delivery is not configured yet. No verification code was sent.");
            });
        });

        document.querySelectorAll("[data-password-toggle]").forEach(function (button) {
            button.addEventListener("click", function () {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) {
                    return;
                }

                const isPassword = input.type === "password";
                input.type = isPassword ? "text" : "password";
                button.setAttribute(
                    "aria-label",
                    `${isPassword ? "Hide" : "Show"} ${input.id === "confirmPassword" ? "confirm password" : "password"}`
                );
                button.innerHTML = `<i class="bi ${isPassword ? "bi-eye-slash" : "bi-eye"}"></i>`;
            });
        });

        const form = document.getElementById("registerForm");
        const password = document.getElementById("registerPassword");
        const confirmation = document.getElementById("confirmPassword");

        if (!form || !password || !confirmation) {
            return;
        }

        function validateConfirmation() {
            confirmation.setCustomValidity(
                confirmation.value && confirmation.value !== password.value
                    ? "Passwords do not match."
                    : ""
            );
        }

        password.addEventListener("input", validateConfirmation);
        confirmation.addEventListener("input", validateConfirmation);
        form.addEventListener("submit", validateConfirmation);
    }

    document.addEventListener("DOMContentLoaded", function () {
        setupProductFeatures();
        setupItemListing();
        setupMyListings();
        setupAuthForms();
    });
})();
