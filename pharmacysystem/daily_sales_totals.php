<?php
require_once 'functions.php';
checkLogin();

// If a date is selected via GET, use it; otherwise, default to today's date.
$dateSelected = isset($_GET['sales_date']) ? $_GET['sales_date'] : date('Y-m-d');

global $conn;

// Prepare query to fetch daily sales totals for the selected date.
$query = "SELECT DATE(s.sale_date) AS sale_date, 
                 SUM(s.quantity) AS total_units, 
                 SUM(s.quantity * p.price) AS total_sales, 
                 COUNT(*) AS transactions
          FROM sales s 
          JOIN products p ON s.product_id = p.id 
          WHERE DATE(s.sale_date) = ?
          GROUP BY DATE(s.sale_date)";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $dateSelected);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

include 'header.php';
?>

<section id="daily_sales_by_date" class="sales-summary-page">
  <h2>Daily Sales Totals</h2>
  
  <!-- Date Selection Form -->
  <form method="GET" action="daily_sales_by_totals.php" class="date-form">
    <label for="date">Select Date:</label>
    <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($dateSelected); ?>">
    <button type="submit" class="btn">Show Sales</button>
  </form>
  
  <!-- Display Sales Totals -->
  <?php if ($data): ?>
    <div class="sales-summary">
      <p><strong>Date:</strong> <?php echo htmlspecialchars($data['sale_date']); ?></p>
      <p><strong>Total Units Sold:</strong> <?php echo $data['total_units']; ?></p>
      <p><strong>Total Sales Amount:</strong> NGN<?php echo number_format($data['total_sales'], 2); ?></p>
      <p><strong>Total Transactions:</strong> <?php echo $data['transactions']; ?></p>
    </div>
  <?php else: ?>
    <p>No sales records found for <?php echo htmlspecialchars($dateSelected); ?>.</p>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
