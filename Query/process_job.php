<?php
require_once 'connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get user information
        $user_id = $_SESSION['id'];
        
        // Get user's link_id
        $linkQuery = "SELECT link_id FROM link WHERE user_id = $user_id";
        $linkResult = mysqli_query($con, $linkQuery);
        
        if (mysqli_num_rows($linkResult) === 0) {
            throw new Exception('No company link found for this user. Please set up your company first.');
        }
        
        $linkData = mysqli_fetch_assoc($linkResult);
        $link_id = $linkData['link_id'];
        
        // Sanitize and validate input
        $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $title = mysqli_real_escape_string($con, trim($_POST['job_title']));
        $employment_type = mysqli_real_escape_string($con, $_POST['employment_type']);
        $status = mysqli_real_escape_string($con, $_POST['job_status']);
        $skills = json_decode($_POST['skills'], true) ?: [];
        $education = json_decode($_POST['education'], true) ?: [];
        $description = mysqli_real_escape_string($con, trim($_POST['job_description']));
        
        // Validation
        if (empty($title)) {
            throw new Exception('Job title is required');
        }
        
        if (empty($employment_type)) {
            throw new Exception('Employment type is required');
        }
        
        if (empty($status)) {
            throw new Exception('Job status is required');
        }
        
        if (empty($description)) {
            throw new Exception('Job description is required');
        }
        
        // Validate employment type
        $valid_types = ['Full Time', 'Part Time', 'Contract', 'Internship'];
        if (!in_array($employment_type, $valid_types)) {
            throw new Exception('Invalid employment type');
        }
        
        // Validate status
        $valid_statuses = ['Active', 'Inactive'];
        if (!in_array($status, $valid_statuses)) {
            throw new Exception('Invalid job status');
        }
        
        // Prepare JSON data
        $skills_json = mysqli_real_escape_string($con, json_encode($skills));
        $education_json = mysqli_real_escape_string($con, json_encode($education));
        
        if ($job_id > 0) {
            // Update existing job
            $sql = "UPDATE jobs SET 
                    title = '$title',
                    employment_type = '$employment_type',
                    status = '$status',
                    skills = '$skills_json',
                    education = '$education_json',
                    description = '$description',
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = $job_id AND created_by = '$user_id'";
            
            if (mysqli_query($con, $sql)) {
                if (mysqli_affected_rows($con) > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Job updated successfully';
                } else {
                    throw new Exception('Job not found or no changes made');
                }
            } else {
                throw new Exception('Error updating job: ' . mysqli_error($con));
            }
            
        } else {
            // Check for duplicate job
            $check_sql = "SELECT id FROM jobs 
                        WHERE title = '$title' 
                        AND employment_type = '$employment_type' 
                        AND created_by = '$user_id'
                        LIMIT 1";

            $check_result = mysqli_query($con, $check_sql);

            if (mysqli_num_rows($check_result) > 0) {
                throw new Exception('Duplicate job detected. A job with the same title and employment type already exists.');
            }

            // Insert new job with link_id and user_id (default status is Active)
            $sql = "INSERT INTO jobs (title, employment_type, status, skills, education, description, created_by, link_id) 
                    VALUES ('$title', '$employment_type', '$status', '$skills_json', '$education_json', '$description', '$user_id', $link_id)";

            if (mysqli_query($con, $sql)) {
                $response['success'] = true;
                $response['message'] = 'Job created successfully';
                $response['job_id'] = mysqli_insert_id($con);
            } else {
                throw new Exception('Error creating job: ' . mysqli_error($con));
            }
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Process job error: " . $e->getMessage());
    }
}

if ($response['success']) {
    header('Location: ../Content/master.php?content=manage-jobs&success=' . urlencode($response['message']));
} else {
    header('Location: ../Content/master.php?content=manage-jobs&error=' . urlencode($response['message']));
}
exit();
?>
