<?php
// delete_product.php
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
if(deleteProduct($id)){
  header("Location: products.php");
  exit();
} else {
  echo "Error deleting product.";
}
?>
