/* PUPMARKET JAVASCRIPT */


/* SAMPLE PRODUCTS */

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


/* GET PRODUCTS FROM LOCAL STORAGE */

function getProducts() {

    const savedProducts =
        localStorage.getItem("pupmarketProducts");

    if (savedProducts) {
        return JSON.parse(savedProducts);
    }

    localStorage.setItem(
        "pupmarketProducts",
        JSON.stringify(defaultProducts)
    );

    return defaultProducts;
}

let products = getProducts();


/* SAVE PRODUCTS */

function saveProducts() {

    localStorage.setItem(
        "pupmarketProducts",
        JSON.stringify(products)
    );
}


function imageError(imageElement) {
    imageElement.src = "images/no-image.jpg";
}


/* DISPLAY PRODUCTS */

function displayProducts(productList) {

    const container =
        document.getElementById("productContainer");

    if (!container) {
        return;
    }

    container.innerHTML = "";

    const noResults =
        document.getElementById("noResults");

    if (productList.length === 0) {

        if (noResults) {
            noResults.classList.remove("d-none");
        }

        return;
    }

    if (noResults) {
        noResults.classList.add("d-none");
    }

    productList.forEach(function(product) {

        const card =
            document.createElement("div");

        card.className =
            "col-md-6 col-lg-3";

        card.innerHTML = `
            <div class="card product-card h-100">

                <img
                    src="${product.image}"
                    class="product-image"
                    alt="${escapeHTML(product.name)}"
                    onerror="imageError(this)"
                >

                <div class="card-body">

                    <span class="badge bg-secondary">
                        ${escapeHTML(product.category)}
                    </span>

                    <h5 class="card-title mt-2">
                        ${escapeHTML(product.name)}
                    </h5>

                    <h5 class="text-pup">
                        ₱${Number(product.price).toLocaleString()}
                    </h5>

                    <p class="small text-muted">
                        ${escapeHTML(product.condition)}
                        condition
                    </p>

                    <button
                        class="btn btn-pup w-100"
                        onclick="showProductById(${product.id})">

                        View Details

                    </button>

                </div>

            </div>
        `;

        container.appendChild(card);
    });
}


/* 
   DISPLAY FEATURED PRODUCTS ON HOME
    */

function displayFeaturedProducts() {

    const container =
        document.getElementById("featuredProducts");

    if (!container) {
        return;
    }

    container.innerHTML = "";

    const featured =
        products.slice(0, 4);

    featured.forEach(function(product) {

        const card =
            document.createElement("div");

        card.className =
            "col-md-6 col-lg-3";

        card.innerHTML = `
            <div class="card product-card h-100">

                <img
                    src="${product.image}"
                    class="product-image"
                    alt="${escapeHTML(product.name)}"
                    onerror="imageError(this)"
                >

                <div class="card-body">

                    <span class="badge bg-secondary">
                        ${escapeHTML(product.category)}
                    </span>

                    <h5 class="card-title mt-2">
                        ${escapeHTML(product.name)}
                    </h5>

                    <h5 class="text-pup">
                        ₱${Number(product.price).toLocaleString()}
                    </h5>

                    <p class="small text-muted">
                        ${escapeHTML(product.condition)}
                        condition
                    </p>

                    <button
                        class="btn btn-pup w-100"
                        onclick="showProductById(${product.id})">

                        View Details

                    </button>

                </div>

            </div>
        `;

        container.appendChild(card);
    });
}


/* 
   ESCAPE HTML
    */

function escapeHTML(value) {

    const div =
        document.createElement("div");

    div.textContent = value;

    return div.innerHTML;
}


/* SHOW PRODUCT */

function showProductById(id) {

    const product =
        products.find(
            item => item.id === id
        );

    if (!product) {
        return;
    }

    document.getElementById("productName").textContent =
        product.name;

    document.getElementById("productPrice").textContent =
        "₱" +
        Number(product.price).toLocaleString();

    document.getElementById("productCategory").textContent =
        product.category;

    document.getElementById("productCondition").textContent =
        product.condition;

    document.getElementById("productDescription").textContent =
        product.description;

    document.getElementById("productSeller").textContent =
        product.seller;

    const image =
        document.getElementById("productImage");

    if (image) {

        image.src = product.image;

        image.onerror =
            function() {
                imageError(image);
            };
    }

    const modalElement =
        document.getElementById("productModal");

    if (modalElement) {

        const modal =
            new bootstrap.Modal(modalElement);

        modal.show();
    }
}


/* 
   SEARCH + FILTER
    */

function filterProducts() {

    const searchInput =
        document.getElementById("searchInput");

    const categoryFilter =
        document.getElementById("categoryFilter");

    if (!searchInput || !categoryFilter) {
        return;
    }

    const searchValue =
        searchInput.value
            .toLowerCase()
            .trim();

    const categoryValue =
        categoryFilter.value;

    let filteredProducts =
        products.filter(function(product) {

            const matchesSearch =
                product.name
                    .toLowerCase()
                    .includes(searchValue);

            const matchesCategory =
                categoryValue === "all" ||
                product.category === categoryValue;

            return (
                matchesSearch &&
                matchesCategory
            );
        });

    sortProducts(filteredProducts);
}


/* SORT PRODUCTS */

function sortProducts(productList) {

    const sortFilter =
        document.getElementById("sortFilter");

    if (!sortFilter) {

        displayProducts(productList);
        return;
    }

    const sortValue =
        sortFilter.value;

    if (sortValue === "low") {

        productList.sort(
            (a, b) =>
                Number(a.price) -
                Number(b.price)
        );

    } else if (sortValue === "high") {

        productList.sort(
            (a, b) =>
                Number(b.price) -
                Number(a.price)
        );

    } else if (sortValue === "name") {

        productList.sort(
            (a, b) =>
                a.name.localeCompare(
                    b.name
                )
        );
    }

    displayProducts(productList);
}


/* IMAGE PREVIEW */

function setupImagePreview() {

    const imageInput =
        document.getElementById("itemImage");

    const preview =
        document.getElementById("imagePreview");

    const previewContainer =
        document.getElementById(
            "imagePreviewContainer"
        );

    if (!imageInput ||
        !preview ||
        !previewContainer) {

        return;
    }

    imageInput.addEventListener(
        "change",
        function() {

            const file =
                imageInput.files[0];

            if (!file) {

                previewContainer
                    .classList
                    .add("d-none");

                return;
            }

            if (!file.type.startsWith("image/")) {

                alert(
                    "Please select an image file."
                );

                imageInput.value = "";

                return;
            }

            const reader =
                new FileReader();

            reader.onload =
                function(event) {

                    preview.src =
                        event.target.result;

                    previewContainer
                        .classList
                        .remove("d-none");
                };

            reader.readAsDataURL(file);
        }
    );
}


/* POST ITEM */

function setupPostItemForm() {

    const form =
        document.getElementById("postItemForm");

    if (!form) {
        return;
    }

    form.addEventListener(
        "submit",
        function(event) {

            event.preventDefault();

            const name =
                document.getElementById("itemName")
                    .value.trim();

            const category =
                document.getElementById("itemCategory")
                    .value;

            const price =
                document.getElementById("itemPrice")
                    .value.trim();

            const condition =
                document.getElementById("itemCondition")
                    .value;

            const description =
                document.getElementById("itemDescription")
                    .value.trim();

            const seller =
                document.getElementById("sellerName")
                    .value.trim();

            if (
                !name ||
                !category ||
                !price ||
                !condition ||
                !description ||
                !seller
            ) {

                alert(
                    "Please fill in all required fields."
                );

                return;
            }

            const numericPrice =
                Number(price);

            if (
                !Number.isFinite(numericPrice) ||
                numericPrice <= 0
            ) {

                alert(
                    "Price must be greater than 0."
                );

                return;
            }

            const imageInput =
                document.getElementById("itemImage");

            const file =
                imageInput.files[0];

            if (!file) {

                alert(
                    "Please upload a product photo."
                );

                return;
            }

            if (
                file.size >
                2 * 1024 * 1024
            ) {

                alert(
                    "Please choose an image smaller than 2MB."
                );

                return;
            }

            const reader =
                new FileReader();

            reader.onload =
                function(event) {

                    const newProduct = {

                        id: Date.now(),

                        name: name,

                        price: numericPrice,

                        category: category,

                        condition: condition,

                        description: description,

                        seller: seller,

                        image: event.target.result
                    };

                    products.unshift(
                        newProduct
                    );

                    saveProducts();

                    displayProducts(
                        products
                    );

                    displayFeaturedProducts();

                    form.reset();

                    const previewContainer =
                        document.getElementById(
                            "imagePreviewContainer"
                        );

                    if (previewContainer) {

                        previewContainer
                            .classList
                            .add("d-none");
                    }

                    const modalElement =
                        document.getElementById(
                            "postItemModal"
                        );

                    const modal =
                        bootstrap.Modal
                            .getInstance(
                                modalElement
                            );

                    if (modal) {
                        modal.hide();
                    }

                    alert(
                        "Your item has been successfully posted!"
                    );

                    if (
                        window.location.pathname
                            .includes("index.php")
                    ) {

                        setTimeout(
                            function() {

                                window.location.href =
                                    "products.php";

                            },
                            500
                        );
                    }
                };

            reader.readAsDataURL(file);
        }
    );
}


/* CONTACT SELLER BUTTON */

function setupContactSellerButton() {

    const button =
        document.getElementById(
            "contactSellerBtn"
        );

    if (!button) {
        return;
    }

    button.addEventListener(
        "click",
        function() {

            alert(
                "Please contact the seller through the information provided.\n\n" +
                "Note: This is a draft project. The messaging feature will be added in the next update."
            );
        }
    );
}


/* CONTACT FORM CLIENT VALIDATION */

function setupContactForm() {

    const form =
        document.getElementById("contactForm");

    if (!form) {
        return;
    }

    form.addEventListener(
        "submit",
        function(event) {

            const name =
                form.querySelector('[name="name"]')
                    .value.trim();

            const email =
                form.querySelector('[name="email"]')
                    .value.trim();

            const subject =
                form.querySelector('[name="subject"]')
                    .value.trim();

            const message =
                form.querySelector('[name="message"]')
                    .value.trim();

            if (
                !name ||
                !email ||
                !subject ||
                !message
            ) {

                event.preventDefault();

                alert(
                    "Please fill in all required fields."
                );
            }
        }
    );
}


/* CATEGORY FROM URL */

function loadCategoryFromURL() {

    const params =
        new URLSearchParams(
            window.location.search
        );

    const category =
        params.get("category");

    const categoryFilter =
        document.getElementById(
            "categoryFilter"
        );

    if (
        category &&
        categoryFilter
    ) {

        categoryFilter.value =
            category;

        filterProducts();
    }
}


/* PAGE INITIALIZATION */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        if (
            document.getElementById(
                "productContainer"
            )
        ) {

            displayProducts(
                products
            );
        }

        if (
            document.getElementById(
                "featuredProducts"
            )
        ) {

            displayFeaturedProducts();
        }

        const searchInput =
            document.getElementById(
                "searchInput"
            );

        if (searchInput) {

            searchInput.addEventListener(
                "input",
                filterProducts
            );
        }

        const categoryFilter =
            document.getElementById(
                "categoryFilter"
            );

        if (categoryFilter) {

            categoryFilter.addEventListener(
                "change",
                filterProducts
            );
        }

        const sortFilter =
            document.getElementById(
                "sortFilter"
            );

        if (sortFilter) {

            sortFilter.addEventListener(
                "change",
                filterProducts
            );
        }

        setupPostItemForm();

        setupImagePreview();

        setupContactForm();

        setupContactSellerButton();

        loadCategoryFromURL();

    }
);
