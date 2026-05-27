<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle POST updates for Users and Products
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_user'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);

        $sql = "UPDATE users SET username='$username', email='$email', role='$role', location='$location' WHERE id='$user_id'";
        mysqli_query($conn, $sql);
    } elseif (isset($_POST['update_product'])) {
        $product_id  = mysqli_real_escape_string($conn, $_POST['product_id']);
        $name        = mysqli_real_escape_string($conn, $_POST['name']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $price       = mysqli_real_escape_string($conn, $_POST['price']);
        $category    = mysqli_real_escape_string($conn, $_POST['category']);
        $stock       = mysqli_real_escape_string($conn, $_POST['stock']);

        $img_sql = "";
        if (!empty($_FILES['image']['name'])) {
            $file = $_FILES['image'];
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed) && $file['error'] === 0) {
                $check = getimagesize($file['tmp_name']);
                if ($check !== false) {
                    $new_name = bin2hex(random_bytes(8)) . "_" . time() . "." . $ext;
                    if (move_uploaded_file($file['tmp_name'], 'uploads/' . $new_name)) {
                        $img_sql = ", image = '$new_name'";
                    }
                }
            }
        }

        $sql = "UPDATE products SET name='$name', description='$description', price='$price', category='$category', stock='$stock' $img_sql WHERE id='$product_id'";
        mysqli_query($conn, $sql);
    }
    header("Location: admin.php");
    exit();
}

// Handle Deletions
if (isset($_GET['action']) && $_GET['action'] == 'delete_product') {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM products WHERE id = '$product_id'");
    header("Location: admin.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'delete_user') {
    $user_id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "DELETE FROM users WHERE id = '$user_id'");
    header("Location: admin.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] == 'toggle_ban') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    if ($id != $_SESSION['user_id']) {
        mysqli_query($conn, "UPDATE users SET is_banned = 1 - is_banned WHERE id = '$id'");
    }
    header("Location: admin.php");
    exit();
}

//Report modal
if (isset($_GET['action']) && $_GET['action'] == 'resolve_report') {
    $report_id = mysqli_real_escape_string($conn, $_GET['id']);
    mysqli_query($conn, "UPDATE reports SET status = 'resolved' WHERE id = '$report_id'");
    header("Location: admin.php");
    exit();
}

// Fetch data for Edit Modals
$edit_user_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_user') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM users WHERE id='$id'");
    $edit_user_data = mysqli_fetch_assoc($res);
}

$edit_product_data = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit_product') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $res = mysqli_query($conn, "SELECT * FROM products WHERE id='$id'");
    $edit_product_data = mysqli_fetch_assoc($res);
}

// Pagination Logic
$limit = 6;
$user_page = isset($_GET['u_page']) ? (int)$_GET['u_page'] : 1;
$prod_page = isset($_GET['p_page']) ? (int)$_GET['p_page'] : 1;
$u_search = isset($_GET['u_search']) ? mysqli_real_escape_string($conn, $_GET['u_search']) : '';
$p_search = isset($_GET['p_search']) ? mysqli_real_escape_string($conn, $_GET['p_search']) : '';

$u_where = $u_search ? "WHERE username LIKE '%$u_search%' OR email LIKE '%$u_search%'" : "";
$p_where = $p_search ? "WHERE (products.name LIKE '%$p_search%' OR products.description LIKE '%$p_search%' OR users.username LIKE '%$p_search%')" : "";

$u_offset = ($user_page - 1) * $limit;
$p_offset = ($prod_page - 1) * $limit;
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
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="products.php" class="nav-btn">Browse</a>
                <div class="divider"></div>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php else: ?>
                <a href="logout.php" class="nav-btn">Logout</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="store-header">
        <div class="store-info">
            <p class="modal-eyebrow">Control Center</p>
            <h1 class="store-name">Admin Portal</h1>
        </div>
    </div>

    <?php if ($edit_user_data): ?>
        <div id="edit-modal" class="modal" style="display: flex;">
            <div class="form-container">
                <button class="close-btn" type="button" onclick="window.location.href='admin.php'">&times;</button>
                <div style="margin-bottom: 1.5rem;">
                    <p class="modal-eyebrow">Edit User</p>
                </div>
                <form method="POST" action="admin.php">
                    <input type="hidden" name="user_id" value="<?= $edit_user_data['id'] ?>">
                    <div class="form-fields">
                        <div>
                            <label>Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($edit_user_data['username']) ?>" required>
                        </div>
                        <div>
                            <label>Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($edit_user_data['email']) ?>" required>
                        </div>
                        <div>
                            <label>Role</label>
                            <select name="role" required>
                                <option value="user" <?= $edit_user_data['role'] == 'buyer' ? 'selected' : '' ?>>Buyer</option>
                                <option value="seller" <?= $edit_user_data['role'] == 'seller' ? 'selected' : '' ?>>Seller</option>
                                <option value="admin" <?= $edit_user_data['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div>
                            <label>Location</label>
                            <select name="location" required>
                                <option value="Johannesburg" <?= $edit_user_data['location'] == 'Johannesburg' ? 'selected' : '' ?>>Johannesburg</option>
                                <option value="Cape Town" <?= $edit_user_data['location'] == 'Cape Town' ? 'selected' : '' ?>>Cape Town</option>
                                <option value="Pretoria" <?= $edit_user_data['location'] == 'Pretoria' ? 'selected' : '' ?>>Pretoria</option>
                                <option value="Durban" <?= $edit_user_data['location'] == 'Durban' ? 'selected' : '' ?>>Durban</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_user" class="btn-submit">Update User</button>
                        <div class="divider"></div>
                        <button type="button" class="btn-cancel" onclick="window.location.href='admin.php'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($edit_product_data): ?>
        <div id="edit-modal" class="modal" style="display: flex;">
            <div class="form-container">
                <button class="close-btn" type="button" onclick="window.location.href='admin.php'">&times;</button>
                <div style="margin-bottom: 1.5rem;">
                    <p class="modal-eyebrow">Admin Product Edit</p>
                </div>
                <form method="POST" action="admin.php" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" value="<?= $edit_product_data['id'] ?>">
                    <div class="form-fields">
                        <div>
                            <label>Product name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($edit_product_data['name']) ?>" required>
                        </div>
                        <div>
                            <label>Description</label>
                            <textarea name="description" rows="3" required><?= htmlspecialchars($edit_product_data['description']) ?></textarea>
                        </div>
                        <div class="form-grid">
                            <div>
                                <label>Price (R)</label>
                                <input type="number" name="price" step="0.01" min="0" value="<?= $edit_product_data['price'] ?>" required>
                            </div>
                            <div>
                                <label>Stock</label>
                                <input type="number" name="stock" min="0" value="<?= $edit_product_data['stock'] ?>" required>
                            </div>
                        </div>
                        <div>
                            <label>Category</label>
                            <select name="category" required>
                                <option value="electronics" <?= $edit_product_data['category'] == 'electronics' ? 'selected' : '' ?>>Electronics</option>
                                <option value="fashion" <?= $edit_product_data['category'] == 'fashion' ? 'selected' : '' ?>>Fashion</option>
                                <option value="handmade" <?= $edit_product_data['category'] == 'handmade' ? 'selected' : '' ?>>Handmade</option>
                                <option value="home" <?= $edit_product_data['category'] == 'home' ? 'selected' : '' ?>>Home & Garden</option>
                                <option value="books" <?= $edit_product_data['category'] == 'books' ? 'selected' : '' ?>>Books & Media</option>
                                <option value="sports" <?= $edit_product_data['category'] == 'sports' ? 'selected' : '' ?>>Sports</option>
                                <option value="food" <?= $edit_product_data['category'] == 'food' ? 'selected' : '' ?>>Food & Baking</option>
                            </select>
                        </div>
                        <div>
                            <label>Image (Leave blank to keep current)</label>
                            <input type="file" name="image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="update_product" class="btn-submit">Update Product</button>
                        <div class="divider"></div>
                        <button type="button" class="btn-cancel" onclick="window.location.href='admin.php'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <h2 class="section-title">User Reports</h2>
    <section class="product-grid">
        <?php
        $reports_sql = "SELECT r.*, u1.username as reporter, u2.username as reported 
                        FROM reports r 
                        JOIN users u1 ON r.reporter_id = u1.id 
                        JOIN users u2 ON r.reported_id = u2.id 
                        WHERE r.status = 'pending' ORDER BY r.created_at DESC";
        $reports_res = mysqli_query($conn, $reports_sql);
        if (mysqli_num_rows($reports_res) > 0) {
            while ($report = mysqli_fetch_assoc($reports_res)) { ?>
                <div style="border: solid 3px #e05050;">
                    <div class="info-container">
                        <div class="card-meta">
                            <span class="product-category" style="border-color: #e05050; color: #e05050;"><?= $report['reason'] ?></span>
                        </div>
                        <h2>Reported: @<?= htmlspecialchars($report['reported']) ?></h2>
                        <p>By: @<?= htmlspecialchars($report['reporter']) ?></p>
                        <p><?= htmlspecialchars($report['details']) ?></p>
                        <div class="modal-footer" style="margin-top: auto; border: none; padding: 0;">
                            <a href="admin.php?action=resolve_report&id=<?= $report['id'] ?>" class="btn-submit">Mark Resolved</a>
                        </div>
                    </div>
                </div>
        <?php }
        } else {
            echo "<p style='padding: 20px;'>No active reports.</p>";
        } ?>
    </section>

    <div>
        <h2 class="section-title" style="margin: 0;">Manage Users</h2>
        <form method="GET" action="admin.php" class="search-form">
            <input type="text" name="u_search" placeholder="Search users" value="<?= htmlspecialchars($u_search) ?>">
            <input type="hidden" name="p_search" value="<?= htmlspecialchars($p_search) ?>">
            <input type="submit" value="Search">
        </form>
    </div>

    <section class="product-grid">
        <?php
        $total_u_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users $u_where");
        $total_u = mysqli_fetch_assoc($total_u_res)['count'];
        $result = mysqli_query($conn, "SELECT * FROM users $u_where LIMIT $u_offset, $limit");

        while ($row = mysqli_fetch_assoc($result)) {
        ?>
            <div class="product-card">
                <div class="info-container">
                    <div class="card-meta">
                        <span class="product-category"><?= $row["role"] ?></span>
                        <span class="card-location"><?= $row["location"] ?></span>
                    </div>
                    <h2><?= htmlspecialchars($row["username"]) ?></h2>
                    <p><?= htmlspecialchars($row["email"]) ?></p>
                    <div class="modal-footer" style="margin-top: auto; border: none; padding: 0;">
                        <a href="admin.php?action=edit_user&id=<?= $row["id"] ?>" class="btn-submit" style="text-align: center; text-decoration: none;">Edit</a>
                        <a href="admin.php?action=toggle_ban&id=<?= $row["id"] ?>" class="<?= $row['is_banned'] ? 'btn-submit' : 'btn-cancel' ?>" style="text-align: center; text-decoration: none;"><?= $row['is_banned'] ? 'Unban' : 'Ban' ?></a>
                        <a href="admin.php?action=delete_user&id=<?= $row["id"] ?>" class="btn-cancel" style="text-align: center; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                    </div>
                </div>
            </div>
        <?php
        }
        ?>
    </section>
    <div class="pagination">
        <?php for ($i = 1; $i <= ceil($total_u / $limit); $i++): ?>
            <a href="admin.php?u_page=<?= $i ?>&p_page=<?= $prod_page ?>&u_search=<?= urlencode($u_search) ?>&p_search=<?= urlencode($p_search) ?>" class="<?= $i == $user_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>


    <div>
        <h2 class="section-title" style="margin: 0;">Manage Products</h2>
        <form method="GET" action="admin.php" class="search-form">
            <input type="hidden" name="u_search" value="<?= htmlspecialchars($u_search) ?>">
            <input type="text" name="p_search" placeholder="Search products" value="<?= htmlspecialchars($p_search) ?>">
            <input type="submit" value="Search">
        </form>
    </div>

    <section class="product-grid">
        <?php
        $total_p_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM products JOIN users ON products.seller_id = users.id $p_where");
        $total_p = mysqli_fetch_assoc($total_p_res)['count'];

        $sql = "SELECT products.*, users.username 
                FROM products 
                JOIN users ON products.seller_id = users.id 
                $p_where
                LIMIT $p_offset, $limit";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
        ?>
                <div class="product-card">
                    <div class="photo-container">
                        <img src="uploads/<?= $row["image"] ?>" alt="<?= $row["name"] ?>">
                    </div>
                    <div class="info-container">
                        <div class="card-meta">
                            <span class="product-category"><?= $row["category"] ?></span>
                            <span class="product-seller">@<?= $row["username"] ?></span>
                        </div>
                        <h2><?= htmlspecialchars($row["name"]) ?></h2>
                        <p><?= htmlspecialchars($row["description"]) ?></p>
                    </div>
                    <div class="card-price-bar">
                        <span class="price-currency">R</span>
                        <span class="price-amount"><?= $row["price"] ?></span>
                    </div>
                    <div class="modal-footer" style="border: none; padding: 14px; margin: 0;">
                        <a href="admin.php?action=edit_product&id=<?= $row["id"] ?>" class="btn-submit" style="text-align: center; text-decoration: none;">Edit</a>
                        <a href="admin.php?action=delete_product&id=<?= $row["id"] ?>" class="btn-cancel" style="text-align: center; text-decoration: none;" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                    </div>
                </div>
        <?php
            }
        } else {
            echo "<p>You have no listings yet</p>";
        }
        ?>
    </section>
    <div class="pagination">
        <?php for ($i = 1; $i <= ceil($total_p / $limit); $i++): ?>
            <a href="admin.php?p_page=<?= $i ?>&u_page=<?= $user_page ?>&u_search=<?= urlencode($u_search) ?>&p_search=<?= urlencode($p_search) ?>" class="<?= $i == $prod_page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>

    <script>
        document.getElementById('nav-toggle').addEventListener('click', function() {
            document.getElementById('nav-links').classList.toggle('active');
        });
    </script>
</body>

</html>