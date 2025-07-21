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

if (!isset($input['password']) || !isset($input['user_id'])) {
    echo json_encode(['valid' => false, 'message' => 'Missing required fields']);
    exit();
}

$password = $input['password'];
$userId = (int)$input['user_id'];

// Verify user is logged in and matches session
if (!isset($_SESSION['id']) || $_SESSION['id'] != $userId) {
    echo json_encode(['valid' => false, 'message' => 'Unauthorized']);
    exit();
}

$hashedPassword = md5($password);

// Check if password matches
$query = "SELECT id FROM users WHERE id = $userId AND password = '$hashedPassword'";
$result = mysqli_query($con, $query);

if (!$result) {
    echo json_encode(['valid' => false, 'message' => 'Database error']);
    exit();
}

$valid = mysqli_num_rows($result) > 0;

echo json_encode([
    'valid' => $valid,
    'message' => $valid ? 'Password is correct' : 'Password is incorrect'
]);
?>
