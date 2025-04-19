<?php
// autocomplete.php
require_once 'functions.php';
global $conn;

$term = isset($_GET['term']) ? $_GET['term'] : '';
$results = [];

if ($term != '') {
    $stmt = $conn->prepare("SELECT id, name FROM products WHERE name LIKE CONCAT('%', ?, '%') LIMIT 10");
    $stmt->bind_param("s", $term);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $results[] = ['value' => $row['id'], 'label' => $row['name']];
    }
}

echo json_encode($results);
?>
