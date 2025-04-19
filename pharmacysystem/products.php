<?php
require_once 'functions.php';
checkLogin();

// Retrieve search term if provided
$search = isset($_GET['search']) ? $_GET['search'] : '';
$products = getProducts($search);

include 'header.php';
?>
<section id="products" class="products-section">
  <h2>Manage Products</h2>
  
  <!-- Search Form -->
  <form method="GET" action="products.php" class="search-form">
    <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit" class="btn">Search</button>
  </form>
  
  <?php if($_SESSION['role'] == 'admin'): ?>
    <a href="add_product.php" class="btn add-btn">Add New Product</a>
  <?php endif; ?>
  
  <!-- Responsive Table Container -->
  <div class="table-responsive">
    <table class="products-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Expiry Date</th>
          <th>Rack Location</th>
          <?php if($_SESSION['role'] == 'admin'): ?>
            <th>Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
        <?php if($products->num_rows > 0): ?>
          <?php while($row = $products->fetch_assoc()): ?>
            <tr>
              <td><?php echo $row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['description']); ?></td>
              <td>NGN<?php echo number_format($row['price'], 2); ?></td>
              <td><?php echo $row['stock']; ?></td>
              <td><?php echo $row['expiry_date']; ?></td>
              <td><?php echo htmlspecialchars($row['rack_location']); ?></td>
              <?php if($_SESSION['role'] == 'admin'): ?>
                <td>
                  <a href="update_product.php?id=<?php echo $row['id']; ?>" class="btn edit-btn">Edit</a>
                  <a href="delete_product.php?id=<?php echo $row['id']; ?>" class="btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                </td>
              <?php endif; ?>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="<?php echo ($_SESSION['role'] == 'admin') ? '8' : '7'; ?>" style="text-align: center;">No products found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<?php include 'footer.php'; ?>
