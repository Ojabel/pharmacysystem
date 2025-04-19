<?php
// pos.php
require_once 'functions.php';
checkLogin();

// Hide sidebar on this page.
$hideSidebar = true;

include 'header.php';

// Example customizable receipt settings
// In a production system, these can be loaded from a configuration file or database.
$receiptLogo = "images/logo.png";  // Path to logo image (ensure the file exists)
$receiptHeader = "Your Pharmacy Name";
$receiptFooter = "Thank you for your purchase!";

?>
<section id="pos" class="sales-page">
  <h2>Point of Sale</h2>
  
  <!-- Sales Form with Autocomplete Product Search -->
  <form method="POST" action="record_sale.php" class="sales-form">
    <label for="product_search">Product:</label>
    <input type="text" name="product_search" id="product_search" placeholder="Type product name..." autocomplete="off" required>
    <!-- Hidden field to store selected product ID -->
    <input type="hidden" name="product_id" id="product_id">
    
    <label for="quantity">Quantity:</label>
    <input type="number" name="quantity" id="quantity" min="1" required>
    
    <button type="submit" class="btn">Record Sale</button>
  </form>
  
  <!-- Receipt Modal: Displayed if sale is recorded successfully -->
  <?php if(isset($_GET['success'])): ?>
    <div id="receiptModal" class="modal">
      <div class="modal-content">
        <span class="close">&times;</span>
        <?php if(file_exists($receiptLogo)): ?>
          <img src="<?php echo $receiptLogo; ?>" alt="Logo" style="max-width: 100px; margin-bottom: 10px;">
        <?php endif; ?>
        <h3><?php echo $receiptHeader; ?></h3>
        <div id="receipt">
          <p><strong>Receipt</strong></p>
          <p>Product: <?php echo isset($_GET['product']) ? htmlspecialchars($_GET['product']) : ''; ?></p>
          <p>Quantity: <?php echo isset($_GET['quantity']) ? htmlspecialchars($_GET['quantity']) : ''; ?></p>
          <p>Unit Price: $<?php echo isset($_GET['price']) ? htmlspecialchars($_GET['price']) : ''; ?></p>
          <p>Total: $<?php echo isset($_GET['total']) ? htmlspecialchars($_GET['total']) : ''; ?></p>
        </div>
        <p><?php echo $receiptFooter; ?></p>
        <p>Do you want to print the receipt?</p>
        <button id="printReceipt" class="btn">Print Receipt</button>
        <button id="closeReceipt" class="btn">Back to POS</button>
      </div>
    </div>
  <?php elseif(isset($_GET['error'])): ?>
    <div class="error-message">
      <p><?php echo htmlspecialchars($_GET['error']); ?></p>
    </div>
  <?php endif; ?>
</section>

<!-- Include jQuery and jQuery UI for Autocomplete -->
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
$(document).ready(function(){
    // Initialize autocomplete on product search field
    $("#product_search").autocomplete({
        source: "autocomplete.php",
        minLength: 2,
        select: function(event, ui) {
            $("#product_search").val(ui.item.label);
            $("#product_id").val(ui.item.value);
            return false;
        }
    });
    
    // Modal functionality: If GET parameter success is set, show the modal
    <?php if(isset($_GET['success'])): ?>
      $("#receiptModal").fadeIn();
    <?php endif; ?>
    
    // Close modal when clicking the close icon or "Back to POS" button
    $(".close, #closeReceipt").click(function(){
      $("#receiptModal").fadeOut(function(){
        window.location.href = "pos.php";
      });
    });
    
    // When user clicks "Print Receipt" button, print and then redirect back to pos.php
    $("#printReceipt").click(function(){
      $("#receiptModal").fadeOut(function(){
        window.print();
        window.location.href = "pos.php";
      });
    });
});
</script>

<!-- Inline Modal CSS (can also be moved to your styles.css) -->
<style>
.modal {
  display: none; 
  position: fixed; 
  z-index: 2000; 
  left: 0;
  top: 0;
  width: 100%; 
  height: 100%; 
  overflow: auto; 
  background-color: rgba(0,0,0,0.4);
}
.modal-content {
  background-color: #fff;
  margin: 15% auto;
  padding: 20px;
  border-radius: 8px;
  width: 90%;
  max-width: 400px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.close {
  color: #aaa;
  float: right;
  font-size: 1.5rem;
  font-weight: bold;
  cursor: pointer;
}
.close:hover {
  color: #000;
}
</style>

<?php include 'footer.php'; ?>
