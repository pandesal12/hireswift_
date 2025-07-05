<?php
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Check if file was uploaded
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['pdf_file'];
    
    // Validate file type
    if ($file['type'] !== 'application/pdf') {
        throw new Exception('Only PDF files are allowed');
    }
    
    // Validate file size (5MB max)
    $maxSize = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $maxSize) {
        throw new Exception('File size exceeds 5MB limit');
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
    
    // Record start time
    $startTime = microtime(true);
    
    // Run NLP.py script
    $python_path = "C:\Users\Kazumi\AppData\Local\Programs\Python\Python310\python.exe";
    $pythonScript = '../Python/NLP.py'; 
    $command = "$python_path \"$pythonScript\" \"$absolutePath\" 2>&1";
    $command = "$python_path \"$pythonScript\" 2>&1";
    
    // Execute the command
    $output = shell_exec($command);
    $exitCode = 0; // shell_exec doesn't return exit code, use exec if needed hehe
    
    
    // Record end time
    $endTime = microtime(true);
    $processingTime = round($endTime - $startTime, 2);
    
    // Prepare response
    $response['success'] = true;
    $response['message'] = 'File uploaded and processed successfully';
    $response['filename'] = $uniqueName;
    $response['file_path'] = $uploadPath;
    $response['absolute_path'] = $absolutePath;
    $response['nlp_results'] = $output;
    $response['processing_time'] = $processingTime;
    $response['command_executed'] = $command;
    
    // Log the activity
    $logEntry = date('Y-m-d H:i:s') . " - File: $uniqueName, Processing time: {$processingTime}s\n";
    file_put_contents('upload_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    
    // Log errors
    $errorEntry = date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n";
    file_put_contents('error_log.txt', $errorEntry, FILE_APPEND | LOCK_EX);
}

echo json_encode($response);
?>
