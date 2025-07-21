<?php
session_start();
require_once 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id']) || isset($_POST['id'])) {
    try {
        $job_id = (int)($_GET['id'] ?? $_POST['id']);
        $user_id = $_SESSION['id'];
        
        if ($job_id <= 0) {
            throw new Exception('Invalid job ID');
        }
        
        // Check if job exists and belongs to the user
        $check_sql = "SELECT id FROM jobs WHERE id = $job_id AND created_by = '$user_id'";
        $check_result = mysqli_query($con, $check_sql);
        
        if (!$check_result) {
            throw new Exception('Database error: ' . mysqli_error($con));
        }
        
        if (mysqli_num_rows($check_result) == 0) {
            throw new Exception('Job not found or you do not have permission to delete this job');
        }
        
        // Delete the job
        $sql = "DELETE FROM jobs WHERE id = $job_id AND created_by = '$user_id'";
        if (mysqli_query($con, $sql)) {
            if (mysqli_affected_rows($con) > 0) {
                $response['success'] = true;
                $response['message'] = 'Job deleted successfully';
            } else {
                throw new Exception('Failed to delete job');
            }
        } else {
            throw new Exception('Error deleting job: ' . mysqli_error($con));
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Delete job error: " . $e->getMessage());
    }
} else {
    $response['message'] = 'Job ID is required';
}

// Redirect for regular requests
if ($response['success']) {
    header('Location: ../Content/master.php?content=manage-jobs&success=' . urlencode($response['message']));
} else {
    header('Location: ../Content/master.php?content=manage-jobs&error=' . urlencode($response['message']));
}
exit();
?>
