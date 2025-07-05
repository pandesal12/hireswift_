<?php
require_once 'connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize and validate input
        $job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
        $title = mysqli_real_escape_string($con, trim($_POST['job_title']));
        $employment_type = mysqli_real_escape_string($con, $_POST['employment_type']);
        $skills = json_decode($_POST['skills'], true) ?: [];
        $education = json_decode($_POST['education'], true) ?: [];
        $description = mysqli_real_escape_string($con, trim($_POST['job_description']));
        // $created_by = $_SESSION['email'];
        $created_by = $_SESSION['email'];
        // Validation
        if (empty($title)) {
            throw new Exception('Job title is required');
        }
        
        if (empty($employment_type)) {
            throw new Exception('Employment type is required');
        }
        
        if (empty($description)) {
            throw new Exception('Job description is required');
        }
        
        // Validate employment type
        $valid_types = ['Full Time', 'Part Time', 'Contract', 'Internship'];
        if (!in_array($employment_type, $valid_types)) {
            throw new Exception('Invalid employment type');
        }
        
        // Prepare JSON data
        $skills_json = mysqli_real_escape_string($con, json_encode($skills));
        $education_json = mysqli_real_escape_string($con, json_encode($education));
        
        if ($job_id > 0) {
            // Update existing job
            $sql = "UPDATE jobs SET 
                    title = '$title',
                    employment_type = '$employment_type',
                    skills = '$skills_json',
                    education = '$education_json',
                    description = '$description',
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = $job_id AND created_by = '$created_by'";
            
            if (mysqli_query($con, $sql)) {
                if (mysqli_affected_rows($con) > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Job updated successfully';
                } else {
                    throw new Exception('Job not found');
                }
            } else {
                throw new Exception('Error updating job: ' . mysqli_error($con));
            }
            
        } else {
            // Create new job
            $sql = "INSERT INTO jobs (title, employment_type, skills, education, description, created_by) 
                    VALUES ('$title', '$employment_type', '$skills_json', '$education_json', '$description', '$created_by')";
            
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
    }
}

if ($response['success']) {
    header('Location: ../Content/master.php?content=manage-jobs&success=' . urlencode($response['message']));
} else {
    header('Location: ..//Content/master.php?content=manage-jobs&error=' . urlencode($response['message']));
}
exit();
?>
