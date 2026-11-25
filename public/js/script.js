(function () {
    const defaultProducts = [
        {
            id: 1,
            name: "Data Structures in Java Book",
            price: 550,
            category: "Books",
            condition: "Good",
            description: "Programming book with minimal highlights.",
            seller: "Valora Mae Lagarnia",
            image: "images/datastructuresinjava.jpg"
        },
        {
            id: 2,
            name: "Modern Database Management Book",
            price: 780,
            category: "Books",
            condition: "Good",
            description: "Database management textbook useful for studying database concepts and systems.",
            seller: "Valora Mae Lagarnia",
            image: "images/mdm.jpg"
        },
        {
            id: 3,
            name: "OOP Reviewer",
            price: 50,
            category: "Books",
            condition: "Like New",
            description: "Printed reviewer for 1st semester midterm exam.",
            seller: "Cadey Torralba",
            image: "images/oopreviewer.jpg"
        },
        {
            id: 4,
            name: "PUPBC Uniform Set - Women",
            price: 800,
            category: "Uniforms",
            condition: "Like New",
            description: "Large Size. Uniform set with pants. Contact me for the other details.",
            seller: "Jessica Mananes",
            image: "images/unifset.jpg"
        },
        {
            id: 5,
            name: "PE Uniform Top only",
            price: 320,
            category: "Uniforms",
            condition: "Good",
            description: "Size medium. All goods pa.",
            seller: "Jeremy Guillermo",
            image: "images/peunif.jpg"
        },
        {
            id: 6,
            name: "CASIO Scientific Calculator",
            price: 700,
            category: "Electronics",
            condition: "Good",
            description: "Scientific calculator in good working condition, suitable for school and mathematical use.",
            seller: "PUPMarket Seller",
            image: "images/calc.jpg"
        },
        {
            id: 7,
            name: "Logitech Wireless Mouse",
            price: 680,
            category: "Electronics",
            condition: "Good",
            description: "Wireless mouse in good working condition. Suitable for laptops, computers, and everyday school use.",
            seller: "PUPMarket Seller",
            image: "images/mouse.jpg"
        },
        {
            id: 8,
            name: "1 Ream A4 Hard Copy Bond paper",
            price: 280,
            category: "School Supplies",
            condition: "New",
            description: "1 ream of A4 bond paper containing 500 sheets. Suitable for printing school requirements and documents.",
            seller: "PUPMarket Seller",
            image: "images/bndpaper.jpg"
        },
        {
            id: 9,
            name: "Mica Paper Set",
            price: 280,
            category: "School Supplies",
            condition: "New",
            description: "Brand new mica paper set suitable for school projects, covers, and other printing needs.",
            seller: "PUPMarket Seller",
            image: "images/mica.jpg"
        },
        {
            id: 10,
            name: "Homemade Cookies",
            price: 10,
            category: "Others",
            condition: "New",
            description: "Freshly made homemade cookies available in Matcha, Choco, and Red Velvet flavors. ₱10 per piece.",
            seller: "Miyuki Espiritu",
            image: "images/cookie.jpg"
        }
    ];

    let products = [];

    function getProducts() {
        const savedProducts = localStorage.getItem("pupmarketProducts");
        if (savedProducts) {
            return JSON.parse(savedProducts);
        }

        localStorage.setItem("pupmarketProducts", JSON.stringify(defaultProducts));
        return defaultProducts;
    }

    function saveProducts() {
        localStorage.setItem("pupmarketProducts", JSON.stringify(products));
    }

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

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function filterProducts() {
        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const sortFilter = document.getElementById("sortFilter");

        if (!searchInput || !categoryFilter) {
            return;
        }

        const searchValue = searchInput.value.toLowerCase().trim();
        const categoryValue = categoryFilter.value;

        const filteredProducts = products.filter(function (product) {
            const matchesSearch = product.name.toLowerCase().includes(searchValue);
            const matchesCategory =
                categoryValue === "all" || product.category === categoryValue;
            return matchesSearch && matchesCategory;
        });

        if (sortFilter?.value === "low") {
            filteredProducts.sort((a, b) => Number(a.price) - Number(b.price));
        } else if (sortFilter?.value === "high") {
            filteredProducts.sort((a, b) => Number(b.price) - Number(a.price));
        } else if (sortFilter?.value === "name") {
            filteredProducts.sort((a, b) => a.name.localeCompare(b.name));
        }

        displayProducts(filteredProducts);
    }

    function setupProductFeatures() {
        const productContainer = document.getElementById("productContainer");
        const featuredContainer = document.getElementById("featuredProducts");

        if (!productContainer && !featuredContainer) {
            return;
        }

        products = getProducts();
        displayProducts(products);
        displayFeaturedProducts();

        const searchInput = document.getElementById("searchInput");
        const categoryFilter = document.getElementById("categoryFilter");
        const sortFilter = document.getElementById("sortFilter");

        searchInput?.addEventListener("input", filterProducts);
        categoryFilter?.addEventListener("change", filterProducts);
        sortFilter?.addEventListener("change", filterProducts);

        const category = new URLSearchParams(window.location.search).get("category");
        if (category && categoryFilter) {
            categoryFilter.value = category;
            filterProducts();
        }

        document.addEventListener("click", function (event) {
            const button = event.target.closest("[data-product-id]");
            if (button) {
                showProductById(button.dataset.productId);
            }
        });

        document.getElementById("contactSellerBtn")?.addEventListener("click", function () {
            alert(
                "Please contact the seller through the information provided.\n\n" +
                "Note: This is a draft project. The messaging feature will be added in the next update."
            );
        });
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

        form.addEventListener("submit", function (event) {
            event.preventDefault();

            const name = document.getElementById("itemName").value.trim();
            const category = document.getElementById("itemCategory").value;
            const price = document.getElementById("itemPrice").value.trim();
            const condition = document.getElementById("itemCondition").value;
            const description = document.getElementById("itemDescription").value.trim();
            const seller = document.getElementById("sellerName").value.trim();
            const file = imageInput?.files[0];

            if (!name || !category || !price || !condition || !description || !seller) {
                alert("Please fill in all required fields.");
                return;
            }

            const numericPrice = Number(price);
            if (!Number.isFinite(numericPrice) || numericPrice <= 0) {
                alert("Price must be greater than 0.");
                return;
            }

            if (!file) {
                alert("Please upload a product photo.");
                return;
            }

            if (!file.type.startsWith("image/")) {
                alert("Please select an image file.");
                return;
            }

            if (file.size > 2 * 1024 * 1024) {
                alert("Please choose an image smaller than 2MB.");
                return;
            }

            const reader = new FileReader();
            reader.onload = function (loadEvent) {
                products.unshift({
                    id: Date.now(),
                    name,
                    price: numericPrice,
                    category,
                    condition,
                    description,
                    seller,
                    image: loadEvent.target.result
                });

                saveProducts();
                displayProducts(products);
                displayFeaturedProducts();
                form.reset();
                previewContainer?.classList.add("d-none");

                const modalElement = document.getElementById("postItemModal");
                if (modalElement) {
                    bootstrap.Modal.getInstance(modalElement)?.hide();
                }

                alert("Your item has been successfully posted!");

                if (window.location.pathname.includes("index.php")) {
                    window.location.href = "products.php";
                }
            };
            reader.readAsDataURL(file);
        });
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

    function setupAuthForms() {
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
                    `${isPassword ? "Hide" : "Show"} ${input.id.toLowerCase().includes("confirm") ? "confirm password" : "password"}`
                );
                button.innerHTML = `<i class="bi ${isPassword ? "bi-eye-slash" : "bi-eye"}"></i>`;
            });
        });

        const registerForm = document.getElementById("registerForm");
        if (registerForm) {
            const otpBox = document.getElementById("otpVerificationBox");
            const otpInput = document.getElementById("otpCode");
            const registerSubmitBtn = document.getElementById("registerSubmitBtn");
            const validateRegMatch = bindPasswordMatch(
                document.getElementById("registerPassword"),
                document.getElementById("confirmPassword")
            );
            const registerOtp = createOtpRequester({
                button: document.getElementById("sendOtpBtn"),
                emailInput: document.getElementById("registerEmail"),
                statusEl: document.getElementById("otpStatus"),
                otpInput,
                endpoint: "api/send_otp.php",
                defaultLabel: "Send OTP",
                resendLabel: "Resend OTP"
            });

            registerForm.addEventListener("submit", function (event) {
                validateRegMatch();

                if (otpBox && otpBox.classList.contains("d-none")) {
                    event.preventDefault();
                    if (!registerForm.reportValidity()) {
                        return;
                    }

                    otpBox.classList.remove("d-none");
                    if (otpInput) {
                        otpInput.required = true;
                    }
                    if (registerSubmitBtn) {
                        registerSubmitBtn.innerHTML = '<i class="bi bi-person-check-fill"></i> Verify &amp; Register Account';
                    }
                    registerOtp.requestOtp();
                }
            });
        }

        const forgotForm = document.getElementById("forgotPasswordForm");
        if (forgotForm) {
            const resetFieldsBox = document.getElementById("resetFieldsBox");
            const resetOtpInput = document.getElementById("resetOtpCode");
            const resetNewPassword = document.getElementById("resetNewPassword");
            const resetConfirmPassword = document.getElementById("resetConfirmPassword");
            const resetSubmitBtn = document.getElementById("resetSubmitBtn");
            const validateResetMatch = bindPasswordMatch(resetNewPassword, resetConfirmPassword);
            const resetOtp = createOtpRequester({
                button: document.getElementById("sendResetOtpBtn"),
                emailInput: document.getElementById("resetEmail"),
                statusEl: document.getElementById("resetOtpStatus"),
                otpInput: resetOtpInput,
                endpoint: "api/send_reset_otp.php",
                defaultLabel: "Send Code",
                resendLabel: "Resend Code"
            });

            forgotForm.addEventListener("submit", async function (event) {
                validateResetMatch();

                if (resetFieldsBox && resetFieldsBox.classList.contains("d-none")) {
                    event.preventDefault();
                    if (!forgotForm.reportValidity()) {
                        return;
                    }

                    resetFieldsBox.classList.remove("d-none");
                    if (resetOtpInput) {
                        resetOtpInput.required = true;
                    }
                    if (resetNewPassword) {
                        resetNewPassword.required = true;
                    }
                    if (resetConfirmPassword) {
                        resetConfirmPassword.required = true;
                    }
                    if (resetSubmitBtn) {
                        resetSubmitBtn.innerHTML = '<i class="bi bi-arrow-repeat"></i> Reset Password';
                    }
                    await resetOtp.requestOtp();
                }
            });
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        setupProductFeatures();
        setupItemListing();
        setupAuthForms();
    });
})();
