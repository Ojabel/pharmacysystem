<?php
// config.php - Database connection details
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "pharmacy";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
