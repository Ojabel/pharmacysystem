<?php
// functions.php - Common functions and user authentication

// Start session if not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration.
require_once 'config.php';

// --- User Authentication Functions ---

function loginUser($username, $password) {
    global $conn;
    // WARNING: In production, use hashed passwords and password_verify()
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        return true;
    }
    return false;
}

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

// --- Product Management Functions ---

function addProduct($name, $description, $price, $stock, $expiry_date, $rack_location) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, expiry_date, rack_location) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiss", $name, $description, $price, $stock, $expiry_date, $rack_location);
    return $stmt->execute();
}

function updateProduct($id, $name, $description, $price, $stock, $expiry_date, $rack_location) {
    global $conn;
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, expiry_date=?, rack_location=? WHERE id=?");
    $stmt->bind_param("ssdissi", $name, $description, $price, $stock, $expiry_date, $rack_location, $id);
    return $stmt->execute();
}

function deleteProduct($id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

function getProducts($search = '') {
    global $conn;
    if ($search !== '') {
        $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%') OR rack_location LIKE CONCAT('%', ?, '%')");
        $stmt->bind_param("sss", $search, $search, $search);
        $stmt->execute();
        return $stmt->get_result();
    } else {
        $sql = "SELECT * FROM products";
        return $conn->query($sql);
    }
}

// --- Sales Recording Functions ---

function recordSale($product_id, $quantity) {
    global $conn;
    // Check available stock first.
    $stmt = $conn->prepare("SELECT stock FROM products WHERE id=?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $product = $result->fetch_assoc();
        if ($product['stock'] >= $quantity) {
            $stmt2 = $conn->prepare("INSERT INTO sales (product_id, quantity, sale_date) VALUES (?, ?, NOW())");
            $stmt2->bind_param("ii", $product_id, $quantity);
            if ($stmt2->execute()) {
                $stmt3 = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id=?");
                $stmt3->bind_param("ii", $quantity, $product_id);
                return $stmt3->execute();
            }
        }
    }
    return false;
}

function checkLowStock() {
    global $conn;
    $sql = "SELECT * FROM products WHERE stock < 5";
    return $conn->query($sql);
}

// --- Settings Functions ---

if (!function_exists('getSettings')) {
    /**
     * Retrieves settings from the database for the given keys.
     *
     * @param mysqli $conn Database connection.
     * @param array  $keys Array in the format: 'setting_key' => 'default_value'
     * @return array Associative array of settings (key => value)
     */
    function getSettings($conn, $keys) {
        $settings = [];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $types = str_repeat('s', count($keys));
    
        $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
        if (!$stmt) {
            return $keys; // Return defaults if query fails.
        }
    
        $stmt->bind_param($types, ...array_keys($keys));
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        foreach ($keys as $key => $default) {
            if (!isset($settings[$key])) {
                $settings[$key] = $default;
            }
        }
        return $settings;
    }
}

if (!function_exists('updateSettings')) {
    /**
     * Updates settings in the database.
     *
     * @param mysqli $conn Database connection.
     * @param array  $settings Associative array of settings (key => value)
     * @return bool True on success, false on failure.
     */
    function updateSettings($conn, $settings) {
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        if (!$stmt) {
            return false;
        }
        foreach ($settings as $key => $value) {
            $stmt->bind_param("ss", $key, $value);
            if (!$stmt->execute()) {
                return false;
            }
        }
        return true;
    }
}


if (!function_exists('logActivity')) {
    /**
     * Logs an activity message for the current user.
     *
     * @param string $activity The activity description.
     * @return bool True on success, false on failure.
     */
    function logActivity($activity) {
        global $conn;
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $stmt = $conn->prepare("INSERT INTO activity_log (user_id, activity, activity_time) VALUES (?, ?, NOW())");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("is", $user_id, $activity);
        return $stmt->execute();
    }
}

?>
