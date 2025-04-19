<?php
// sales.php
require_once 'functions.php';
checkLogin();

// Set flag to hide the sidebar on this page.
$hideSidebar = true;

// Get search query (if any) and fetch products accordingly.
$search = isset($_GET['search']) ? $_GET['search'] : '';
$products = getProducts($search);

// Fetch all products for the available stock listing (ignoring search filter)
$allProducts = getProducts();

include 'header.php';
?>

<section id="sales" class="sales-page">
  <h2>Sales Transactions</h2>
  
  <!-- Success/Error message & Receipt -->
  <?php if(isset($_GET['success'])): ?>
    <div class="success-message">
      <p>Sale recorded successfully!</p>
      <div id="receipt">
        <p><strong>Receipt:</strong></p>
        <p>Product: <?php echo isset($_GET['product']) ? htmlspecialchars($_GET['product']) : ''; ?></p>
        <p>Quantity: <?php echo isset($_GET['quantity']) ? htmlspecialchars($_GET['quantity']) : ''; ?></p>
        <p>Unit Price: NGN<?php echo isset($_GET['price']) ? htmlspecialchars($_GET['price']) : ''; ?></p>
        <p>Total: NGN<?php echo isset($_GET['total']) ? htmlspecialchars($_GET['total']) : ''; ?></p>
      </div>
      <button onclick="window.print()" class="btn">Print Receipt</button>
    </div>
  <?php elseif(isset($_GET['error'])): ?>
    <div class="error-message">
      <p><?php echo htmlspecialchars($_GET['error']); ?></p>
    </div>
  <?php endif; ?>
  
  <!-- Search Section -->
  <div class="search-section">
    <form method="GET" action="sales.php" class="search-form">
      <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
      <button type="submit" class="btn">Search</button>
    </form>
  </div>
  
  <!-- Sales Form -->
  <form method="POST" action="record_sale.php" class="sales-form">
    <label for="product_id">Select Product:</label>
    <select name="product_id" id="product_id" required>
      <?php while($row = $products->fetch_assoc()): ?>
        <option value="<?php echo $row['id']; ?>">
          <?php echo $row['name']; ?> | NGN<?php echo number_format($row['price'], 2); ?> | <?php echo substr($row['description'], 0, 30); ?>... | Rack: <?php echo $row['rack_location']; ?>
        </option>
      <?php endwhile; ?>
    </select>
    
    <label for="quantity">Enter Quantity:</label>
    <input type="number" name="quantity" id="quantity" min="1" required>
    
    <button type="submit" class="btn">Record Sale</button>
  </form>
  
  <!-- Available Stock Listing -->
  <div class="stock-list">
    <h3>Available Stock</h3>
    <div class="table-responsive">
      <table class="products-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Expiry Date</th>
            <th>Rack Location</th>
          </tr>
        </thead>
        <tbody>
          <?php while($prod = $allProducts->fetch_assoc()): ?>
            <tr>
              <td><?php echo $prod['id']; ?></td>
              <td><?php echo $prod['name']; ?></td>
              <td>NGN<?php echo number_format($prod['price'], 2); ?></td>
              <td><?php echo $prod['stock']; ?></td>
              <td><?php echo $prod['expiry_date']; ?></td>
              <td><?php echo $prod['rack_location']; ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
