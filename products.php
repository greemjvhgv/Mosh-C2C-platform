<?php session_start();
require_once 'db.php';
?>
<!DOCTYPE html>
<html>

<head>
    <title>MOSH</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <nav class="navbar">
        <div class="nav-left">
            <a href="index.php" class="logo"><img src="logo.png" alt="MOSH"><span>ZA</span></a>
        </div>
        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div class="nav-links" id="nav-links">
            <?php if (!isset($_SESSION['username'])) : ?>
                <a href="login.php" class="nav-btn">Login</a>
                <div class="divider"></div>
                <a href="register.php" class="nav-btn primary">Register</a>
            <?php else: ?>
                <a href="cart.php" class="nav-btn">Cart</a>
                <div class="divider"></div>
                <a href="dashboard.php" class="nav-btn">Profile</a>
                <div class="divider"></div>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if (!isset($_SESSION['username'])) : ?>
        <section class="hero-image hero-small">
            <div class="hero-text">
                <h2>Register an account and start selling and buying today!</h2>
                <div class="hero-links">
                    <a href="register.php" class="btn-1">Register Now</a>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="search-section">
        <form action="products.php" method="GET" class="search-form">
            <input type="text" name="my_search" id="live-search" placeholder="Search for products..." value="<?= isset($_GET['my_search']) ? htmlspecialchars($_GET['my_search']) : '' ?>">

            <select name="category">
                <option value="">All categories</option>
                <option value="electronics"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'electronics') ? 'selected' : '' ?>>
                    Electronics
                </option>
                <option value="fashion"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'fashion') ? 'selected' : '' ?>>
                    Fashion
                </option>
                <option value="handmade"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'handmade') ? 'selected' : '' ?>>
                    Handmade
                </option>
                <option value="home"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'home') ? 'selected' : '' ?>>
                    Home & Garden
                </option>
                <option value="books"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'books') ? 'selected' : '' ?>>
                    Books & Media
                </option>
                <option value="sports"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'sports') ? 'selected' : '' ?>>
                    Sports
                </option>
                <option value="food"
                    <?= (isset($_GET['category']) && $_GET['category'] == 'food') ? 'selected' : '' ?>>
                    Food & Baking
                </option>
            </select>

            <select name="location" id="location">

                <option value="">All locations</option>
                <option value="Johannesburg"
                    <?= (isset($_GET['location']) && $_GET['location'] == 'Johannesburg') ? 'selected' : '' ?>>
                    Johannesburg
                </option>
                <option value="Cape Town"
                    <?= (isset($_GET['location']) && $_GET['location'] == 'Cape Town') ? 'selected' : '' ?>>
                    Cape Town
                </option>
                <option value="Pretoria"
                    <?= (isset($_GET['location']) && $_GET['location'] == 'Pretoria') ? 'selected' : '' ?>>
                    Pretoria
                </option>
                <option value="Durban"
                    <?= (isset($_GET['location']) && $_GET['location'] == 'Durban') ? 'selected' : '' ?>>
                    Durban
                </option>
            </select>

            <select name="priceSort" id="priceSort">
                <option value="">Default Pricing</option>
                <option value="asc"
                    <?= (isset($_GET['priceSort']) && $_GET['priceSort'] == 'asc') ? 'selected' : '' ?>>
                    Price: Low to High
                </option>
                <option value="desc"
                    <?= (isset($_GET['priceSort']) && $_GET['priceSort'] == 'desc') ? 'selected' : '' ?>>
                    Price: High to Low
                </option>
            </select>
            <input type="submit" name="search" value="Search">
        </form>
    </section>

    <section class="product-grid">
        <?php
        $conditions = [];

        if (!empty($_GET['my_search'])) {
            $search = mysqli_real_escape_string($conn, $_GET['my_search']);
            $conditions[] = "CONCAT(products.name, products.description) LIKE '%$search%'";
        }

        if (!empty($_GET['category'])) {
            $category = mysqli_real_escape_string($conn, $_GET['category']);
            $conditions[] = "products.category = '$category'";
        }

        if (!empty($_GET['location'])) {
            $location = mysqli_real_escape_string($conn, $_GET['location']);
            $conditions[] = "users.location = '$location'";
        }

        $sort = "";

        if (!empty($_GET['priceSort'])) {
            $priceSort = mysqli_real_escape_string($conn, $_GET['priceSort']);
            if ($priceSort == "asc") {
                $sort = " ORDER BY products.price ASC";
            } elseif ($priceSort == "desc") {
                $sort = " ORDER BY products.price DESC";
            }
        }

        $sql = "SELECT products.*, users.username, users.location
                FROM products
                JOIN users ON products.seller_id = users.id";

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= $sort;
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                echo '
            <a href="product-detail.php?id=' . $row["id"] . '" class="product-card">
                <div class="photo-container">
                    <img src="uploads/' . $row["image"] . '" alt="' . $row["name"] . '">
                </div>
                <div class="info-container">
                <div class="card-meta">
                    <span class="product-category">' . $row["category"] . '</span>
                    <div class="seller-info">
                        <span class="product-seller">@' . $row["username"] . '</span>
                        <span class="card-location">' . $row["location"] . '</span>
                    </div>
                </div>
                <h2>' . $row["name"] . '</h2>
                <p class="card-description">' . $row["description"] . '</p>
                </div>
                <div class="card-price-bar">
                    <span class="price-currency">R</span>
                    <span class="price-amount">' . $row["price"] . '</span>
                </div>
            </a>
            ';
            }
        } else {
            echo "<p>No products found</p>";
        }
        ?>
    </section>

    <button id="backToTop" class="back-to-top" title="Go to top">&uarr;</button>

    <footer>
        <div class="footer">
            <p>© 2026 MOSH</p>
        </div>
    </footer>

</body>

<script>
    document.getElementById('nav-toggle').addEventListener('click', function() {
      document.getElementById('nav-links').classList.toggle('active');
    });

    const searchInput = document.getElementById('live-search');
    const productCards = document.querySelectorAll('.product-card');

    searchInput.addEventListener('input', (e) => {
        const term = e.target.value.toLowerCase();

        productCards.forEach(card => {
            const title = card.querySelector('h2').textContent.toLowerCase();
            const desc = card.querySelector('.card-description').textContent.toLowerCase();
            
            if (title.includes(term) || desc.includes(term)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });

    const backToTopBtn = document.getElementById('backToTop');

    if (backToTopBtn) {
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 400) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
</script>

</html>