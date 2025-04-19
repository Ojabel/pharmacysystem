<?php
require_once 'functions.php';
checkLogin();
global $conn;

// Only allow admin access.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// --- Handle Clear Activity Log Action ---
if (isset($_GET['clear']) && $_GET['clear'] == '1') {
    // For production, consider archiving instead.
    $conn->query("TRUNCATE TABLE activity_log");
    header("Location: activity_log.php?msg=" . urlencode("Activity log cleared."));
    exit();
}

// --- Filtering and Searching ---
// Default filter values.
$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$activity = isset($_GET['activity']) ? trim($_GET['activity']) : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo   = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;

// Build SQL WHERE conditions.
$whereClauses = [];
$params = [];
$paramTypes = '';

if ($search !== '') {
    // Search by username (from joined users table)
    $whereClauses[] = "u.username LIKE ?";
    $params[] = "%$search%";
    $paramTypes .= "s";
}

if ($activity !== '') {
    $whereClauses[] = "al.activity LIKE ?";
    $params[] = "%$activity%";
    $paramTypes .= "s";
}

if ($dateFrom !== '') {
    $whereClauses[] = "DATE(al.activity_time) >= ?";
    $params[] = $dateFrom;
    $paramTypes .= "s";
}

if ($dateTo !== '') {
    $whereClauses[] = "DATE(al.activity_time) <= ?";
    $params[] = $dateTo;
    $paramTypes .= "s";
}

$whereSQL = "";
if (count($whereClauses) > 0) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

// --- Pagination Logic ---
// Count total records.
$countSql = "SELECT COUNT(*) as total 
             FROM activity_log al 
             JOIN users u ON al.user_id = u.id 
             $whereSQL";
$stmt = $conn->prepare($countSql);
if ($paramTypes) {
    $stmt->bind_param($paramTypes, ...$params);
}
$stmt->execute();
$countResult = $stmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);
$page = max(1, min($page, $totalPages));
$offset = ($page - 1) * $recordsPerPage;

// --- Query Activity Log Records ---
// Join activity_log (alias al) with users (alias u) to get the username.
$sql = "SELECT al.id, u.username, al.activity, al.activity_time 
        FROM activity_log al 
        JOIN users u ON al.user_id = u.id 
        $whereSQL 
        ORDER BY al.activity_time DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
if ($paramTypes) {
    // Append integer types for offset and limit.
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
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Log - Pharmacy System</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <?php include 'header.php'; ?>
  <section id="activity_log" class="recent-logins-page">
    <h2>Activity Log</h2>
    
    <?php if($msg): ?>
      <div class="success-message">
        <p><?php echo htmlspecialchars($msg); ?></p>
      </div>
    <?php endif; ?>
    
    <!-- Filter Form -->
    <form method="GET" action="activity_log.php" class="filter-form">
      <input type="text" name="search" placeholder="Search by username..." value="<?php echo htmlspecialchars($search); ?>">
      <input type="text" name="activity" placeholder="Search activity..." value="<?php echo htmlspecialchars($activity); ?>">
      <input type="date" name="date_from" value="<?php echo htmlspecialchars($dateFrom); ?>">
      <input type="date" name="date_to" value="<?php echo htmlspecialchars($dateTo); ?>">
      <button type="submit" class="btn">Filter</button>
      <a href="activity_log.php" class="btn">Reset</a>
    </form>
    
    <!-- Clear History Button -->
    <div style="text-align: center; margin-bottom: 20px;">
      <a href="activity_log.php?clear=1" class="btn" onclick="return confirm('Are you sure you want to clear the activity log?');">Clear Activity Log</a>
    </div>
    
    <!-- Activity Log Table -->
    <div class="table-responsive">
      <table class="recent-logins-table">
        <thead>
          <tr>
            <th>Username</th>
            <th>Activity</th>
            <th>Time</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['username']); ?></td>
                <td><?php echo htmlspecialchars($row['activity']); ?></td>
                <td><?php echo date("Y-m-d H:i:s", strtotime($row['activity_time'])); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="3" style="text-align: center;">No activity records found.</td>
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
</body>
</html>
