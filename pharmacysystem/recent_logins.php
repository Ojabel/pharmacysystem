<?php
require_once 'functions.php';
checkLogin();
global $conn;

// Only allow admin access.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// --- Handle Clear History Action ---
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    // Delete all login history records.
    $conn->query("TRUNCATE TABLE login_history");
    header("Location: recent_logins.php?msg=" . urlencode("Login history cleared."));
    exit();
}

// --- Filtering and Searching ---
// Default values.
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo   = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;

// Build SQL WHERE conditions.
$whereClauses = [];
$params = [];
$paramTypes = '';

if ($search !== '') {
    $whereClauses[] = "username LIKE ?";
    $params[] = "%$search%";
    $paramTypes .= "s";
}

if ($dateFrom !== '') {
    $whereClauses[] = "DATE(login_time) >= ?";
    $params[] = $dateFrom;
    $paramTypes .= "s";
}

if ($dateTo !== '') {
    $whereClauses[] = "DATE(login_time) <= ?";
    $params[] = $dateTo;
    $paramTypes .= "s";
}

$whereSQL = "";
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// --- Pagination Logic ---
// Count total records
$countSql = "SELECT COUNT(*) as total FROM login_history $whereSQL";
$stmt = $conn->prepare($countSql);
if ($paramTypes) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$countResult = $stmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
if ($page < 1) $page = 1;
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $recordsPerPage;

// --- Query Login History ---
$sql = "SELECT id, username, login_time, user_agent 
        FROM login_history 
        $whereSQL 
        ORDER BY login_time DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);

// Bind parameters. We have to bind offset and limit as integers.
if ($paramTypes) {
    $fullTypes = $paramTypes . "ii";
    $params[] = $offset;
    $params[] = $recordsPerPage;
    $stmt->bind_param($fullTypes, ...$params);
} else {
    $stmt->bind_param("ii", $offset, $recordsPerPage);
}
$stmt->execute();
$result = $stmt->get_result();

// Optional message
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
?>

<?php include 'header.php'; ?>

<section id="recent_logins" class="recent-logins-page">
  <h2>Recent Logins</h2>
  
  <?php if($msg): ?>
    <div class="success-message">
      <p><?php echo htmlspecialchars($msg); ?></p>
    </div>
  <?php endif; ?>
  
  <!-- Filter Form -->
  <form method="GET" action="recent_logins.php" class="filter-form">
    <input type="text" name="search" placeholder="Search by username..." value="<?php echo htmlspecialchars($search); ?>">
    <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
    <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
    <button type="submit" class="btn">Filter</button>
    <a href="recent_logins.php" class="btn">Reset</a>
  </form>
  
  <!-- Clear History Button -->
  <div style="text-align: center; margin-bottom: 20px;">
    <a href="recent_logins.php?clear=1" class="btn" onclick="return confirm('Are you sure you want to clear the login history?');">Clear History</a>
  </div>
  
  <!-- Login History Table -->
  <div class="table-responsive">
    <table class="recent-logins-table">
      <thead>
        <tr>
          <th>Username</th>
          <th>Login Time</th>
          <th>User Agent</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo date("Y-m-d H:i:s", strtotime($row['login_time'])); ?></td>
              <td><?php echo htmlspecialchars($row['user_agent']); ?></td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="3" style="text-align: center;">No login records found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Pagination Controls -->
  <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php if ($page > 1): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn">Previous</a>
      <?php endif; ?>
      <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
      <?php if ($page < $totalPages): ?>
        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn">Next</a>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
