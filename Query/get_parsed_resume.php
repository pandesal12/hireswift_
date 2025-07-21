<?php
header('Content-Type: application/json');
require_once 'connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if (!isset($_GET['id'])) {
    $response['message'] = 'Application ID is required';
    echo json_encode($response);
    exit();
}

$applicationId = (int)$_GET['id'];
$userId = $_SESSION['id'];

try {
    // Verify the application belongs to a job created by this user
    $verifyQuery = "SELECT a.parsed_resume_path FROM applications a 
                   INNER JOIN jobs j ON a.job_id = j.id 
                   WHERE a.id = $applicationId AND j.created_by = $userId";
    $verifyResult = mysqli_query($con, $verifyQuery);
    
    if (mysqli_num_rows($verifyResult) === 0) {
        throw new Exception('Application not found or unauthorized');
    }
    
    $application = mysqli_fetch_assoc($verifyResult);
    $parsedPath = $application['parsed_resume_path'];

    // Get job requirements for comparison
    $jobQuery = "SELECT j.skills, j.education FROM jobs j 
             INNER JOIN applications a ON a.job_id = j.id 
             WHERE a.id = $applicationId";
    $jobResult = mysqli_query($con, $jobQuery);

    if (mysqli_num_rows($jobResult) > 0) {
        $jobData = mysqli_fetch_assoc($jobResult);
        $jobSkills = json_decode($jobData['skills'], true) ?: [];
        $jobEducation = json_decode($jobData['education'], true) ?: [];
        
        $response['job_requirements'] = [
            'skills' => $jobSkills,
            'education' => $jobEducation
        ];
    } else {
        $response['job_requirements'] = [
            'skills' => [],
            'education' => []
        ];
    }
    
    if (empty($parsedPath)) {
        throw new Exception('Parsed resume file not found');
    }
    
    // Convert relative path to absolute path
    $absolutePath = '../' . $parsedPath;
    
    if (!file_exists($absolutePath)) {
        throw new Exception('Parsed resume file does not exist at: ' . $absolutePath);
    }
    
    // Read and decode JSON file
    $jsonContent = file_get_contents($absolutePath);
    $resumeData = json_decode($jsonContent, true);
    
    if (!$resumeData) {
        throw new Exception('Invalid resume data format');
    }
    
    $response['success'] = true;
    $response['resume'] = $resumeData;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?>
