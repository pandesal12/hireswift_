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

if (!isset($input['company_name']) || !isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$companyName = mysqli_real_escape_string($con, trim($input['company_name']));
$userId = (int)$input['user_id'];

// Verify user is logged in and matches session
if (!isset($_SESSION['id']) || $_SESSION['id'] != $userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Double-check if company name is still available
$checkQuery = "SELECT link_id FROM link WHERE company = '$companyName'";
$checkResult = mysqli_query($con, $checkQuery);

if (mysqli_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'Company name is no longer available']);
    exit();
}

// Check if user already has a company
$userCheckQuery = "SELECT link_id FROM link WHERE user_id = $userId";
$userCheckResult = mysqli_query($con, $userCheckQuery);

if (mysqli_num_rows($userCheckResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'User already has a company']);
    exit();
}

// Insert new company link
$insertQuery = "INSERT INTO link (user_id, company) VALUES ($userId, '$companyName')";

if (mysqli_query($con, $insertQuery)) {
    $linkId = mysqli_insert_id($con);
    echo json_encode([
        'success' => true, 
        'message' => 'Company created successfully',
        'link_id' => $linkId,
        'company_name' => $companyName
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create company: ' . mysqli_error($con)]);
}
?>
