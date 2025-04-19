<?php
require_once 'functions.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password']; // Use secure hashing in production

    if (loginUser($username, $password)) {
        // Insert login history record AFTER successful login.
        global $conn;
        // Use the username from the session.
        $stmt = $conn->prepare("INSERT INTO login_history (username, login_time) VALUES (?, NOW())");
        $stmt->bind_param("s", $_SESSION['username']);
        $stmt->execute();

        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Pharmacy System</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="login.php">
      <label for="username">Username:</label>
      <input type="text" name="username" id="username" required>
      
      <label for="password">Password:</label>
      <input type="password" name="password" id="password" required>
      
      <button type="submit">Login</button>
    </form>
  </div>
</body>
</html>
