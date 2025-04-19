<?php
require_once 'functions.php';
checkLogin();

// Log activity: user visited POS page.
logActivity("Visited POS page");

include 'header.php';

global $conn;

// Query total sales for today (units and amount)
$querySales = "SELECT SUM(s.quantity) AS total_units, SUM(s.quantity * p.price) AS total_amount 
                FROM sales s 
                JOIN products p ON s.product_id = p.id 
                WHERE DATE(s.sale_date) = CURDATE()";
$resultSales = $conn->query($querySales);
$salesData = $resultSales->fetch_assoc();

// Query available stock details
$queryStock = "SELECT SUM(stock) AS total_stock, COUNT(*) AS product_count FROM products";
$resultStock = $conn->query($queryStock);
$stockData = $resultStock->fetch_assoc();

// Query low stock count (products with stock less than 5)
$queryLowStock = "SELECT COUNT(*) AS low_stock_count FROM products WHERE stock < 5";
$resultLowStock = $conn->query($queryLowStock);
$lowStockData = $resultLowStock->fetch_assoc();

/* --- Real-Time Analytics Chart Data --- */
// Prepare hourly labels (0:00 to 23:00) and initialize units to zero.
$hourLabels = [];
$unitsData = array_fill(0, 24, 0);
for ($i = 0; $i < 24; $i++) {
    $hourLabels[] = $i . ":00";
}
// Query today's sales grouped by hour.
$queryHourly = "SELECT HOUR(s.sale_date) AS sale_hour, SUM(s.quantity) AS total_units
                FROM sales s 
                WHERE DATE(s.sale_date) = CURDATE()
                GROUP BY sale_hour
                ORDER BY sale_hour ASC";
$resultHourly = $conn->query($queryHourly);
while ($row = $resultHourly->fetch_assoc()) {
    $hour = (int)$row['sale_hour'];
    $unitsData[$hour] = $row['total_units'];
}
$jsLabels = json_encode($hourLabels);
$jsUnits  = json_encode(array_values($unitsData));
?>

<section id="dashboard">
  <h2>Dashboard</h2>
  
  <?php if ($_SESSION['role'] != 'admin'): ?>
    <!-- For non-admin users, include a logout button -->
    <div class="user-actions">
      <a href="logout.php" class="btn logout-btn">Logout</a>
    </div>
  <?php endif; ?>
  
  <div class="card-container">
    <!-- Total Sales Card -->
    <a href="sales_summary.php" class="card-link">
      <div class="card sales">
        <div class="card-icon">
          <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="card-content">
          <h3>Total Sales Today</h3>
          <p><?php echo $salesData['total_units'] ? $salesData['total_units'] : 0; ?> units sold</p>
          <p>Total Amount: NGN<?php echo $salesData['total_amount'] ? number_format($salesData['total_amount'], 2) : '0.00'; ?></p>
        </div>
      </div>
    </a>
    
    <!-- Available Stock Card -->
    <a href="products.php" class="card-link">
      <div class="card stock">
        <div class="card-icon">
          <i class="fas fa-box"></i>
        </div>
        <div class="card-content">
          <h3>Available Stock</h3>
          <p><?php echo $stockData['total_stock'] ? $stockData['total_stock'] : 0; ?> units available</p>
          <p>Across <?php echo $stockData['product_count'] ? $stockData['product_count'] : 0; ?> products</p>
        </div>
      </div>
    </a>
    
    <!-- Low Stock Card -->
    <a href="low_stock_alert.php" class="card-link">
      <div class="card low-stock">
        <div class="card-icon">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="card-content">
          <h3>Low Stock Products</h3>
          <p><?php echo $lowStockData['low_stock_count'] ? $lowStockData['low_stock_count'] : 0; ?> products with low stock</p>
        </div>
      </div>
    </a>
    
    <!-- POS Card -->
    <a href="pos.php" class="card-link">
      <div class="card pos">
        <div class="card-icon">
          <i class="fas fa-shopping-bag"></i>
        </div>
        <div class="card-content">
          <h3>Point of Sale</h3>
          <p>Record sales quickly and efficiently.</p>
        </div>
      </div>
    </a>
  </div>
  
  <!-- Sales Chart Section (Bar Chart) -->
  <div class="chart-container">
    <h3>Hourly Sales for Today</h3>
    <canvas id="salesChart"></canvas>
  </div>
</section>

<!-- Include Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  var ctx = document.getElementById('salesChart').getContext('2d');
  var salesChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo $jsLabels; ?>,
      datasets: [{
        label: 'Units Sold',
        data: <?php echo $jsUnits; ?>,
        backgroundColor: 'rgba(52,152,219,0.5)',
        borderColor: 'rgba(52,152,219,1)',
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            stepSize: 1
          }
        }
      },
      responsive: true,
      maintainAspectRatio: false
    }
  });
</script>

<?php include 'footer.php'; ?>
