<?php
require_once 'functions.php';
checkLogin();

// Only allow admin users to access this page.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

global $conn;

// Define the settings we want to manage and default values.
$settingsKeys = [
    'receipt_header' => 'Your Pharmacy Name',
    'receipt_footer' => 'Thank you for your purchase! Please visit us again.',
    'receipt_logo'   => 'images/logo.png'
];

// Function to retrieve settings from the database.
function getSettings($conn, $keys) {
    $settings = [];
    $placeholders = implode(',', array_fill(0, count($keys), '?'));
    $types = str_repeat('s', count($keys));
    
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
    $stmt->bind_param($types, ...array_keys($keys));
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    // Ensure each key has a value (default if not set)
    foreach ($keys as $key => $default) {
        if (!isset($settings[$key])) {
            $settings[$key] = $default;
        }
    }
    return $settings;
}

// Function to update settings in the database.
function updateSettings($conn, $settings) {
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $key => $value) {
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }
}

$message = "";
$settings = getSettings($conn, $settingsKeys);

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newSettings = [
        'receipt_header' => isset($_POST['receipt_header']) ? trim($_POST['receipt_header']) : $settings['receipt_header'],
        'receipt_footer' => isset($_POST['receipt_footer']) ? trim($_POST['receipt_footer']) : $settings['receipt_footer'],
        'receipt_logo'   => isset($_POST['receipt_logo'])   ? trim($_POST['receipt_logo'])   : $settings['receipt_logo'],
    ];
    updateSettings($conn, $newSettings);
    // Reload settings after update.
    $settings = getSettings($conn, $settingsKeys);
    $message = "Settings updated successfully.";
}

include 'header.php';
?>
<section id="settings" class="settings-page">
  <h2>System Settings</h2>
  
  <?php if ($message): ?>
    <div class="success-message">
      <p><?php echo htmlspecialchars($message); ?></p>
    </div>
  <?php endif; ?>
  
  <form method="POST" action="settings.php" class="settings-form">
    <label for="receipt_header">Receipt Header:</label>
    <input type="text" name="receipt_header" id="receipt_header" value="<?php echo htmlspecialchars($settings['receipt_header']); ?>" required>
    
    <label for="receipt_footer">Receipt Footer:</label>
    <input type="text" name="receipt_footer" id="receipt_footer" value="<?php echo htmlspecialchars($settings['receipt_footer']); ?>" required>
    
    <label for="receipt_logo">Receipt Logo URL:</label>
    <input type="text" name="receipt_logo" id="receipt_logo" value="<?php echo htmlspecialchars($settings['receipt_logo']); ?>" required>
    
    <div class="logo-preview">
      <p>Logo Preview:</p>
      <img id="logoPreview" src="<?php echo htmlspecialchars($settings['receipt_logo']); ?>" alt="Logo Preview">
    </div>
    
    <button type="submit" class="btn">Save Settings</button>
  </form>
</section>
<?php include 'footer.php'; ?>
<script>
  // Update logo preview on input change
  document.getElementById('receipt_logo').addEventListener('input', function() {
      var url = this.value;
      document.getElementById('logoPreview').src = url;
  });
</script>
