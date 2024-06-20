<?php
require_once '/xampp/htdocs/CHEAPTHRILLS/app/core/Database.php';
require_once '/xampp/htdocs/CHEAPTHRILLS/app/core/config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Database connection
$database = new Database();
$conn = $database->openConnection();

$response = ['sizes' => [], 'colors' => []];

if (isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];

    // Fetch sizes
    $sizes_query = "SELECT ITEM_SIZE_ID, ITEM_SIZE_DESCRIPT FROM ITEM_SIZE WHERE ITEM_ID = :item_id";
    $stmt = $conn->prepare($sizes_query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch colors
    $colors_query = "SELECT ITEM_COL_ID, ITEM_COL_DESCRIPT FROM ITEM_COLOR WHERE ITEM_ID = :item_id";
    $stmt = $conn->prepare($colors_query);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();
    $colors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['sizes'] = $sizes;
    $response['colors'] = $colors;
}

// Close the database connection
// $database->closeConnection();

// Set the response header to JSON
header('Content-Type: application/json');

// Output the response as JSON
echo json_encode($response);
?>
