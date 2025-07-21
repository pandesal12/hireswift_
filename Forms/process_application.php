<?php
header('Content-Type: application/json');
require_once '../Query/connect.php';

$response = ['success' => false, 'message' => ''];

try {
    // Log the start of processing
    $logEntry = date('Y-m-d H:i:s') . " - Application processing started\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

    // Validate required fields (removed full_name and email validation)
    if (!isset($_POST['job_id']) || !isset($_POST['link_id'])) {
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
    $jobId = (int)$_POST['job_id'];
    $linkId = (int)$_POST['link_id'];

    // Log the extracted data
    $logEntry = date('Y-m-d H:i:s') . " - Data extracted: JobID=$jobId, LinkID=$linkId\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

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

    // Log file upload success
    $logEntry = date('Y-m-d H:i:s') . " - File uploaded successfully: $uploadPath\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

    // Store relative path in database (without ../)
    $relativePath = 'Uploads/' . $uniqueName;

    // Insert application record with placeholder values for name and email (will be updated by Python script)
    $insertQuery = "INSERT INTO applications (job_id, applicant_name, email, resume_pdf_path, status, created_at) 
                    VALUES ($jobId, 'Processing...', 'processing@temp.com', '$relativePath', 'Pending', NOW())";

    if (!mysqli_query($con, $insertQuery)) {
        // Clean up uploaded file if database insert fails
        unlink($uploadPath);
        throw new Exception('Failed to save application: ' . mysqli_error($con));
    }

    $applicationId = mysqli_insert_id($con);

    // Log database insert success
    $logEntry = date('Y-m-d H:i:s') . " - Application saved to database with ID: $applicationId\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

    // Get absolute path for the Python script
    $absolutePath = realpath($uploadPath);
    
    // Log Python execution attempt
    $logEntry = date('Y-m-d H:i:s') . " - Attempting to execute Python script with file: $absolutePath\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

    // Execute NLP processing
    $python_path = "C:\\Users\\Kazumi\\AppData\\Local\\Programs\\Python\\Python310\\python.exe";
    $pythonScript = realpath('../Python/NLP.py');
    
    // Log the command that will be executed
    $command = "\"$python_path\" \"$pythonScript\" \"$absolutePath\" $applicationId";
    $logEntry = date('Y-m-d H:i:s') . " - Python command: $command\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);
    
    // Execute the command and capture output
    $output = [];
    $returnCode = 0;
    exec($command . " 2>&1", $output, $returnCode);
    
    // Log Python execution result
    $logEntry = date('Y-m-d H:i:s') . " - Python execution completed. Return code: $returnCode\n";
    $logEntry .= "Python output: " . implode("\n", $output) . "\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

    $response['success'] = true;
    $response['message'] = 'Application submitted successfully';
    $response['application_id'] = $applicationId;
    $response['python_return_code'] = $returnCode;
    $response['python_output'] = $output;

    // Log success
    $logEntry = date('Y-m-d H:i:s') . " - Application processing completed successfully\n\n";
    file_put_contents('../Temporary/application_debug.txt', $logEntry, FILE_APPEND | LOCK_EX);

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Log errors
    $errorEntry = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n\n";
    file_put_contents('../Temporary/application_debug.txt', $errorEntry, FILE_APPEND | LOCK_EX);
}

echo json_encode($response);
?>
