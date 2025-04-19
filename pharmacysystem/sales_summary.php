<?php
require_once 'functions.php';
checkLogin();
global $conn;

// Determine if user is admin
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

// Get selected date from GET parameter; if none provided, use an empty string.
$dateSelected = isset($_GET['date']) ? $_GET['date'] : '';

// Build the daily sales query based on whether a date was selected
if ($dateSelected !== '') {
    $dailySalesQuery = "SELECT 
                            DATE(s.sale_date) AS sale_date,
                            SUM(s.quantity) AS total_units,
                            SUM(s.quantity * p.price) AS total_sales,
                            COUNT(*) AS transaction_count
                        FROM sales s 
                        JOIN products p ON s.product_id = p.id 
                        WHERE DATE(s.sale_date) = ?
                        GROUP BY DATE(s.sale_date)
                        ORDER BY sale_date DESC";
    $stmt = $conn->prepare($dailySalesQuery);
    $stmt->bind_param("s", $dateSelected);
    $stmt->execute();
    $dailySalesResult = $stmt->get_result();
} else {
    $dailySalesQuery = "SELECT 
                            DATE(s.sale_date) AS sale_date,
                            SUM(s.quantity) AS total_units,
                            SUM(s.quantity * p.price) AS total_sales,
                            COUNT(*) AS transaction_count
                        FROM sales s 
                        JOIN products p ON s.product_id = p.id 
                        GROUP BY DATE(s.sale_date)
                        ORDER BY sale_date DESC";
    $dailySalesResult = $conn->query($dailySalesQuery);
}
include 'header.php';
?>

<section id="sales_summary" class="sales-summary-page">
  <h2>Daily Sales Summary</h2>
  
  <?php if(!$isAdmin): ?>
    <div class="user-actions" style="text-align: center; margin-bottom: 20px;">
      <a href="pos.php" class="btn">Back to POS</a>
      <a href="logout.php" class="btn logout-btn">Logout</a>
    </div>
  <?php endif; ?>
  
  <!-- Date Search Form -->
  <form method="GET" action="sales_summary.php" class="date-form">
    <label for="date">Select Date:</label>
    <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($dateSelected); ?>">
    <button type="submit" class="btn">Search</button>
  </form>
  
  <?php if ($dailySalesResult && $dailySalesResult->num_rows > 0): ?>
    <?php while($daily = $dailySalesResult->fetch_assoc()):
            $saleDate = $daily['sale_date'];
    ?>
      <div class="daily-summary">
        <h3>
          <?php echo htmlspecialchars($saleDate); ?> &mdash;
          Total Sales: NGN<?php echo number_format($daily['total_sales'], 2); ?>,
          Units Sold: <?php echo $daily['total_units']; ?>,
          Transactions: <?php echo $daily['transaction_count']; ?>
        </h3>
        <div class="daily-details">
          <table class="daily-sales-table">
            <thead>
              <tr>
                <th>Sale Date/Time</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $detailsQuery = "SELECT s.sale_date, p.name, s.quantity, p.price, (s.quantity * p.price) AS subtotal 
                                 FROM sales s 
                                 JOIN products p ON s.product_id = p.id 
                                 WHERE DATE(s.sale_date) = '$saleDate'
                                 ORDER BY s.sale_date DESC";
                $detailsResult = $conn->query($detailsQuery);
                while($detail = $detailsResult->fetch_assoc()):
              ?>
                <tr>
                  <td><?php echo date("Y-m-d H:i", strtotime($detail['sale_date'])); ?></td>
                  <td><?php echo htmlspecialchars($detail['name']); ?></td>
                  <td><?php echo $detail['quantity']; ?></td>
                  <td>NGN<?php echo number_format($detail['price'], 2); ?></td>
                  <td>NGN<?php echo number_format($detail['subtotal'], 2); ?></td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align:center;">No sales records found for <?php echo $dateSelected !== '' ? htmlspecialchars($dateSelected) : "any date"; ?>.</p>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
