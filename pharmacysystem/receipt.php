<?php
require_once 'functions.php';
checkLogin();

// Retrieve receipt details from GET parameters (or use default values)
$product       = isset($_GET['product']) ? htmlspecialchars($_GET['product']) : 'Unknown Product';
$quantity      = isset($_GET['quantity']) ? htmlspecialchars($_GET['quantity']) : '0';
$unitPrice     = isset($_GET['price']) ? htmlspecialchars($_GET['price']) : '0.00';
$total         = isset($_GET['total']) ? htmlspecialchars($_GET['total']) : '0.00';
$saleTimestamp = date("Y-m-d H:i:s");

// Retrieve customizable receipt settings from the database.
$receiptSettings = getSettings($conn, [
    'receipt_header' => 'Your Pharmacy Name',
    'receipt_footer' => 'Thank you for your purchase! Please visit us again.',
    'receipt_logo'   => 'images/logo.png'
]);

$receiptHeader = $receiptSettings['receipt_header'];
$receiptFooter = $receiptSettings['receipt_footer'];
$receiptLogo   = $receiptSettings['receipt_logo'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt - <?php echo $receiptHeader; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Base Styles */
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f7f9fb;
      margin: 0;
      padding: 20px;
      color: #333;
    }
    .receipt-container {
      max-width: 400px;  /* Reduced width */
      margin: 30px auto;
      background: #fff;
      padding: 15px 20px;  /* Reduced padding */
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      font-size: 0.9rem;  /* Reduced font size */
    }
    /* Header */
    .receipt-header {
      background: linear-gradient(135deg, #3498db, #2980b9);
      color: #fff;
      padding: 15px;
      text-align: center;
    }
    .receipt-header img {
      max-width: 100px;  /* Adjust logo size */
      margin-bottom: 8px;
    }
    .receipt-header h2 {
      font-size: 1.5rem;  /* Reduced header font size */
      margin-bottom: 5px;
    }
    .receipt-header p {
      font-size: 0.8rem;  /* Reduced font size */
      opacity: 0.9;
    }
    /* Receipt Details */
    .receipt-details {
      padding: 15px;
      font-size: 0.9rem;  /* Reduced font size */
      line-height: 1.5;
    }
    .receipt-details h3 {
      margin-bottom: 10px;
      color: #2c3e50;
      border-bottom: 2px solid #ddd;
      padding-bottom: 5px;
      text-align: center;
      font-size: 1.3rem;
    }
    .receipt-details p {
      margin: 8px 0;
    }
    .receipt-details p span {
      display: inline-block;
      width: 120px;  /* Reduced label width */
      font-weight: 500;
      color: #34495e;
      font-size: 0.9rem;
    }
    /* Footer */
    .receipt-footer {
      text-align: center;
      padding: 10px;
      font-style: italic;
      color: #555;
      border-top: 2px solid #3498db;
      margin-top: 15px;
      font-size: 0.9rem;
    }
    /* Print Controls */
    .print-controls {
      text-align: center;
      margin-top: 15px;
    }
    .print-controls .btn {
      padding: 8px 16px;
      margin: 5px;
      background-color: #3498db;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      transition: background-color 0.3s ease;
      font-size: 0.9rem;
      text-decoration: none;
    }
    .print-controls .btn:hover {
      background-color: #2980b9;
    }
    /* Print Media Query: Print Only the Receipt Container */
    @media print {
      body * {
        visibility: hidden;
      }
      .receipt-container, .receipt-container * {
        visibility: visible;
      }
      .receipt-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <div class="receipt-container" id="receiptContent">
    <div class="receipt-header">
      <?php if(file_exists($receiptLogo)): ?>
        <img src="<?php echo $receiptLogo; ?>" alt="Logo">
      <?php endif; ?>
      <h2><?php echo $receiptHeader; ?></h2>
      <p><?php echo $saleTimestamp; ?></p>
    </div>
    <div class="receipt-details">
      <h3>Receipt</h3>
      <p><span>Product:</span> <?php echo $product; ?></p>
      <p><span>Quantity:</span> <?php echo $quantity; ?></p>
      <p><span>Unit Price:</span> NGN<?php echo $unitPrice; ?></p>
      <p><span>Total:</span> NGN<?php echo $total; ?></p>
    </div>
    <div class="receipt-footer">
      <p><?php echo $receiptFooter; ?></p>
    </div>
  </div>
  <div class="print-controls">
    <button class="btn" onclick="printReceipt()">Print Receipt</button>
    <a href="pos.php" class="btn">Back to POS</a>
  </div>
  <script>
    function printReceipt() {
      window.print();
    }
  </script>
</body>
</html>
