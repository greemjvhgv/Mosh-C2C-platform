<?php session_start();
require_once 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $email    = trim($_POST['email']);
  $password = $_POST['password'];

  $stmt = $conn->prepare("SELECT id, username, password, role, location, is_banned FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows === 0) {
    $error = "Incorrect Email or Password";
  } else {
    $stmt->bind_result($id, $username, $hashed_password, $role, $location, $is_banned);
    $stmt->fetch();

    if (password_verify($password, $hashed_password)) {
      if ($is_banned) {
        $error = "This account has been suspended.";
      } else {
      $_SESSION['user_id']   = $id;
      $_SESSION['username']  = $username;
      $_SESSION['role']      = $role;
      $_SESSION['email']    = $email;
      $_SESSION['location'] = $location;

      if ($role === 'seller') {
        header("Location: dashboard.php");
      } else {
        header("Location: index.php");
      }
      exit();
      }
    } else {
      $error = "Incorrect Email or Password";
    }
  }
  $stmt->close();
}

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
  </nav>

  <div id="loginModal" class="modal">
    <div class="modal-content">
      <div class="auth-header">
        <p class="modal-eyebrow">Welcome back</p>
        <h1 class="auth-title">Login</h1>
      </div>

      <form method="POST" action="login.php" id="loginForm" class="auth-form">

        <?php if ($error): ?>
          <p style="color:#ff4444; font-size: 14px; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" name="email" placeholder="your@email.com" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" placeholder="Enter your password" required>
        </div>

        <button type="submit" class="auth-btn">Login</button>

        <div class="auth-footer">
          Don't have an account? <a href="register.php">Register here</a>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <div class="footer">
      <p>© 2026 MOSH</p>
    </div>
  </footer>

</body>

</html>