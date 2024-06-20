<?php
require_once '/xampp/htdocs/CHEAPTHRILLS/app/core/Database.php';
require_once '/xampp/htdocs/CHEAPTHRILLS/app/core/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection
$database = new Database();
$conn = $database->openConnection();

$response = ['price' => []];

if (isset($_POST['itemv_id'])) {
    $itemv_id = $_POST['itemv_id'];

    // Fetch item price
    $price_query = "SELECT ITEMV_PRICE FROM ITEM_VARIATION WHERE ITEMV_ID = :itemv_id";
    $stmt = $conn->prepare($price_query);
    $stmt->bindParam(':itemv_id', $itemv_id, PDO::PARAM_INT);
    $stmt->execute();
    $price = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($price) {
        $response['price'] = $price;
    }
}

// Close the database connection
$database->closeConnection();

// Set the response header to JSON
header('Content-Type: application/json');

// Output the response as JSON
echo json_encode($response);