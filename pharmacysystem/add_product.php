<?php
// add_product.php
require_once 'functions.php';
checkLogin();
if($_SESSION['role'] != 'admin'){
  echo "Unauthorized access!";
  exit();
}
if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $name          = $_POST['name'];
  $description   = $_POST['description'];
  $price         = $_POST['price'];
  $stock         = $_POST['stock'];
  $expiry_date   = $_POST['expiry_date'];
  $rack_location = $_POST['rack_location'];
  
  if(addProduct($name, $description, $price, $stock, $expiry_date, $rack_location)){
    header("Location: products.php");
    exit();
  } else {
    $error = "Error adding product!";
  }
}
include 'header.php';
?>
<section id="add_product">
  <h2>Add Product</h2>
  <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="POST" action="add_product.php">
    <label>Name:</label>
    <input type="text" name="name" required>
    <label>Description:</label>
    <textarea name="description" required></textarea>
    <label>Price:</label>
    <input type="number" step="0.01" name="price" required>
    <label>Stock:</label>
    <input type="number" name="stock" required>
    <label>Expiry Date:</label>
    <input type="date" name="expiry_date" required>
    <label>Rack Location:</label>
    <input type="text" name="rack_location" required>
    <button type="submit">Add Product</button>
  </form>
</section>
<?php include 'footer.php'; ?>
