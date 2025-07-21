<?php
header('Content-Type: application/json');
require_once '../Query/connect.php';

$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    if (!isset($_POST['full_name']) || !isset($_POST['email']) || !isset($_POST['job_id']) || !isset($_POST['link_id'])) {
        throw new Exception('Missing required fields');
    }

    // Validate file upload
    if (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Resume file is required');
    }

    $file = $_FILES['resume_file'];
    
    // Validate file type
    if ($file['type'] !== 'application/pdf') {
        throw new Exception('Only PDF files are allowed');
    }
    
    // Validate file size (10MB max)
    $maxSize = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 10MB limit');
    }

    // Sanitize input data
    $fullName = mysqli_real_escape_string($con, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $jobId = (int)$_POST['job_id'];
    $linkId = (int)$_POST['link_id'];

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    // Verify job exists and belongs to the company
    $jobQuery = "SELECT * FROM jobs WHERE id = $jobId AND link_id = $linkId AND status = 'Active'";
    $jobResult = mysqli_query($con, $jobQuery);
    
    if (mysqli_num_rows($jobResult) === 0) {
        throw new Exception('Invalid job selection');
    }

    // Create uploads directory if it doesn't exist
    $uploadDir = '../Uploads/';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Failed to create uploads directory');
        }
    }

    // Generate unique filename
    $originalName = pathinfo($file['name'], PATHINFO_FILENAME);
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $uniqueName = $originalName . '_' . uniqid() . '.' . $extension;
    $uploadPath = $uploadDir . $uniqueName;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to save uploaded file');
    }

    // Get absolute path for the Python script
    $absolutePath = realpath($uploadPath);

    // Insert application record
    $insertQuery = "INSERT INTO applications (job_id, applicant_name, email, resume_pdf_path, status, created_at) 
                    VALUES ($jobId, '$fullName', '$email', '$uploadPath', 'Pending', NOW())";

    if (!mysqli_query($con, $insertQuery)) {
        // Clean up uploaded file if database insert fails
        unlink($uploadPath);
        throw new Exception('Failed to save application: ' . mysqli_error($con));
    }

    $applicationId = mysqli_insert_id($con);

    // Execute NLP processing in background
    $python_path = "C:\\Users\\Kazumi\\AppData\\Local\\Programs\\Python\\Python310\\python.exe"; // Update this path
    $pythonScript = '../Python/NLP.py';
    $command = "\"$python_path\" \"$pythonScript\" \"$absolutePath\" $applicationId > /dev/null 2>&1 &";
    
    // For Windows, use different background execution
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = "start /B \"$python_path\" \"$pythonScript\" \"$absolutePath\" $applicationId";
    }
    
    exec($command);

    $response['success'] = true;
    $response['message'] = 'Application submitted successfully';
    $response['application_id'] = $applicationId;

    // Log the activity
    $logEntry = date('Y-m-d H:i:s') . " - Application ID: $applicationId, File: $uniqueName, Job ID: $jobId\n";
    file_put_contents('../Temporary/application_log.txt', $logEntry, FILE_APPEND | LOCK_EX);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Log errors
    $errorEntry = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    file_put_contents('../Temporary/error_log.txt', $errorEntry, FILE_APPEND | LOCK_EX);
}

echo json_encode($response);
?>
