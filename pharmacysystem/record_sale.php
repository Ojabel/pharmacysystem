<?php
require_once 'functions.php';
checkLogin();

global $conn;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity   = $_POST['quantity'];

    // Retrieve product details for the given product_id.
    $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $product = $result->fetch_assoc();
        // Record the sale. The recordSale() function should update the stock and insert the sale record.
        if (recordSale($product_id, $quantity)) {
            $unitPrice = $product['price'];
            $total = $unitPrice * $quantity;
            // Redirect to the receipt page with the necessary details.
            header("Location: receipt.php?product=" . urlencode($product['name']) .
                   "&quantity=" . urlencode($quantity) .
                   "&price=" . urlencode(number_format($unitPrice, 2)) .
                   "&total=" . urlencode(number_format($total, 2)));
            exit();
        } else {
            header("Location: pos.php?error=" . urlencode("Insufficient stock or error recording sale."));
            exit();
        }
    } else {
        header("Location: pos.php?error=" . urlencode("Product not found."));
        exit();
    }
}
?>
