<?php
header('Content-Type: application/json');
require_once 'connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $applicationId = (int)$_POST['application_id'];
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $userId = $_SESSION['id'];
        
        // Validate status
        $validStatuses = ['Pending', 'Reviewing', 'Shortlisted', 'Interviewed', 'Accepted', 'Rejected'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status');
        }
        
        // Verify the application belongs to a job created by this user
        $verifyQuery = "SELECT a.id FROM applications a 
                       INNER JOIN jobs j ON a.job_id = j.id 
                       WHERE a.id = $applicationId AND j.created_by = $userId";
        $verifyResult = mysqli_query($con, $verifyQuery);
        
        if (mysqli_num_rows($verifyResult) === 0) {
            throw new Exception('Application not found or unauthorized');
        }
        
        // Update status
        $updateQuery = "UPDATE applications SET status = '$status', updated_at = NOW() WHERE id = $applicationId";
        
        if (mysqli_query($con, $updateQuery)) {
            $response['success'] = true;
            $response['message'] = 'Status updated successfully';
        } else {
            throw new Exception('Failed to update status: ' . mysqli_error($con));
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
?>
