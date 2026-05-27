<?php
require_once 'db.php';
 session_start(); 

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $plain_password = $_POST['password'];
    $location = $_POST['location'];
    $role = $_POST['role'];

    //Basic validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($plain_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with that email already exists.";
        } else {
            $hashed = password_hash($plain_password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare(
                "INSERT INTO users (username, email, password, location, role) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt2->bind_param("sssss", $username, $email, $hashed, $location, $role);
            if ($stmt2->execute()) {

                $_SESSION['user_id'] = $stmt2->insert_id; // Get the ID of the newly registered user
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role; 
                $_SESSION['location'] = $location;

                $success = "Account created! You are now logged in.";
                header("Location: index.php");
                exit();
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

?>
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
    </nav>

    <div id="loginModal" class="modal">
        <div class="modal-content">
            <div class="auth-header">
                <p class="modal-eyebrow">Join the community</p>
                <h1 class="auth-title">Create Account</h1>
            </div>

            <form method="POST" action="register.php" class="auth-form" id="registerForm">
                <?php if ($error): ?>
                    <p style="color:#ff4444; font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
                <?php if ($success): ?>
                    <p style="color:#00c851; font-size: 14px; margin-bottom: 15px;"><?= $success ?></p>
                <?php endif; ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" placeholder="Choose a display name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" placeholder="your@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Min. 6 characters" required>
                    <span id="pass-msg" style="font-size: 11px; margin-top: 5px; display: block;"></span>
                </div>

                <div class="form-group">
                    <label>I want to...</label>
                    <select name="role" required>
                        <option value="buyer">Buy Products</option>
                        <option value="seller">Sell Products</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Store Location</label>
                    <select name="location" required>
                        <option value="Johannesburg">Johannesburg</option>
                        <option value="Cape Town">Cape Town</option>
                        <option value="Pretoria">Pretoria</option>
                        <option value="Durban">Durban</option>
                    </select>
                </div>

                <button type="submit" class="auth-btn">Register</button>

                <div class="auth-footer">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer">
            <p>© 2026 MOSH</p>
        </div>
    </footer>

<script>
    const registerForm = document.getElementById('registerForm');
    const passwordInput = document.getElementById('password');
    const passMsg = document.getElementById('pass-msg');

    passwordInput.addEventListener('input', () => {
        if (passwordInput.value.length < 6) {
            passMsg.textContent = "Password too short";
            passMsg.style.color = "#ff4444a8";
        } else {
            passMsg.textContent = "Password strength looks good";
            passMsg.style.color = "#00c85095";
        }
    });

    registerForm.addEventListener('submit', (e) => {
        if (passwordInput.value.length < 6) {
            e.preventDefault();
            alert("Please ensure your password is at least 6 characters before registering.");
        }
    });
</script>


</body>

</html>