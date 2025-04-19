<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pharmacy System</title>
  <!-- Google Fonts for modern typography -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
  <link rel="stylesheet" href="styles.css">
  <script src="script.js" defer></script>
</head>
<body class="<?php echo $isAdmin ? '' : 'no-sidebar'; ?>">
  <div class="container">
    <?php if($isAdmin) { ?>
      <aside class="sidebar" id="adminSidebar">
        <h2><i class="fas fa-user-shield"></i> Admin Panel</h2>
        <nav>
        <ul>
        <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
        <li><a href="sales_summary.php"><i class="fas fa-shopping-cart"></i> Sales Summary</a></li>
        <li><a href="low_stock_alert.php"><i class="fas fa-exclamation-triangle"></i> Low Stock Alerts</a></li>
        <li><a href="settings.php"><i class="fas fa-cogs"></i> Settings</a></li>
        <li><a href="recent_logins.php"><i class="fas fa-history"></i> Recent Logins</a></li>

        <li><a href="activity_log.php"><i class="fas fa-history"></i> Recent Logins</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
      </ul>
        </nav>

       
  
      </aside>
    <?php } ?>
    <main class="content">
      <header>
        <h1>Pharmacy System</h1>
        <?php if($isAdmin) { ?>
          <!-- Toggle button appears only on small screens -->
          <button id="toggleSidebar" title="Toggle Sidebar">☰</button>
        <?php } ?>
      </header>
