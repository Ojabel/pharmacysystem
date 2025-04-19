<?php
// low_stock_alert.php
require_once 'functions.php';
checkLogin();
if($_SESSION['role'] != 'admin'){
  echo "Unauthorized access!";
  exit();
}
$lowStock = checkLowStock();
include 'header.php';
?>
<section id="low_stock_alert">
  <h2>Low Stock Alerts</h2>
  <?php if($lowStock->num_rows > 0){ ?>
    <table>
      <thead>
        <tr>
          <th>Product ID</th>
          <th>Name</th>
          <th>Stock</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $lowStock->fetch_assoc()){ ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['stock']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php } else { ?>
    <p>No low stock alerts.</p>
  <?php } ?>
</section>
<?php include 'footer.php'; ?>
