<?php
// update_product.php
require_once 'functions.php';
checkLogin();
if($_SESSION['role'] != 'admin'){
  echo "Unauthorized access!";
  exit();
}
if(!isset($_GET['id'])){
  echo "Product ID is missing.";
  exit();
}
$id = $_GET['id'];
global $conn;
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows != 1){
  echo "Product not found.";
  exit();
}
$product = $result->fetch_assoc();
if($_SERVER['REQUEST_METHOD'] == 'POST'){
  $name          = $_POST['name'];
  $description   = $_POST['description'];
  $price         = $_POST['price'];
  $stock         = $_POST['stock'];
  $expiry_date   = $_POST['expiry_date'];
  $rack_location = $_POST['rack_location'];
  
  if(updateProduct($id, $name, $description, $price, $stock, $expiry_date, $rack_location)){
    header("Location: products.php");
    exit();
  } else {
    $error = "Error updating product!";
  }
}
include 'header.php';
?>
<section id="update_product">
  <h2>Update Product</h2>
  <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
  <form method="POST" action="update_product.php?id=<?php echo $id; ?>">
    <label>Name:</label>
    <input type="text" name="name" value="<?php echo $product['name']; ?>" required>
    <label>Description:</label>
    <textarea name="description" required><?php echo $product['description']; ?></textarea>
    <label>Price:</label>
    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
    <label>Stock:</label>
    <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
    <label>Expiry Date:</label>
    <input type="date" name="expiry_date" value="<?php echo $product['expiry_date']; ?>" required>
    <label>Rack Location:</label>
    <input type="text" name="rack_location" value="<?php echo $product['rack_location']; ?>" required>
    <button type="submit">Update Product</button>
  </form>
</section>
<?php include 'footer.php'; ?>
