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

if (!isset($input['email']) || empty(trim($input['email']))) {
    echo json_encode(['available' => false, 'message' => 'Email is required']);
    exit();
}

$email = mysqli_real_escape_string($con, trim($input['email']));
$currentUserId = $_SESSION['id'];

// Check if email already exists (excluding current user)
$query = "SELECT id FROM users WHERE email = '$email' AND id != $currentUserId";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['available' => false, 'message' => 'Database error']);
    exit();
}

$available = mysqli_num_rows($result) === 0;

echo json_encode([
    'available' => $available,
    'message' => $available ? 'Email is available' : 'Email is already taken'
]);
?>
