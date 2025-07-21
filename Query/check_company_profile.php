<?php
header('Content-Type: application/json');
require_once 'connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['company_name'])) {
    echo json_encode(['available' => false, 'message' => 'Company name is required']);
    exit();
}

$companyName = mysqli_real_escape_string($con, trim($input['company_name']));
$currentUserId = $_SESSION['id'];

// If company name is empty, it's allowed
if (empty($companyName)) {
    echo json_encode(['available' => true, 'message' => 'Company name can be empty']);
    exit();
}

// Check if company name already exists (excluding current user's company)
$query = "SELECT link_id FROM link WHERE company = '$companyName' AND user_id != $currentUserId";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['available' => false, 'message' => 'Database error']);
    exit();
}

$available = mysqli_num_rows($result) === 0;

echo json_encode([
    'available' => $available,
    'message' => $available ? 'Company name is available' : 'Company name is already taken'
]);
?>
