<?php
require_once 'connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Job ID is required']);
    exit();
}

try {
    $job_id = (int)$_GET['id'];
    $user_id = $_SESSION['id'];
    
    $sql = "SELECT * FROM jobs WHERE id = $job_id AND created_by = '$user_id'";
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        error_log("SQL Error in get_job: " . mysqli_error($con));
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        exit();
    }
    
    if (mysqli_num_rows($result) == 0) {
        error_log("No job found for job_id: $job_id and user_id: $user_id");
        http_response_code(404);
        echo json_encode(['error' => 'Job not found']);
        exit();
    }
    
    $job = mysqli_fetch_assoc($result);
    
    // Decode JSON fields
    $job['skills'] = json_decode($job['skills'], true) ?: [];
    $job['education'] = json_decode($job['education'], true) ?: [];
    
    header('Content-Type: application/json');
    echo json_encode($job);
    
} catch (Exception $e) {
    error_log("Exception in get_job: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
