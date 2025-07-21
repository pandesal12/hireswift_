<?php
header('Content-Type: application/json');
require_once 'connect.php';
session_start();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['application_id'])) {
            throw new Exception('Application ID is required');
        }
        
        $applicationId = (int)$input['application_id'];
        $userId = $_SESSION['id'];
        
        // Get application details and verify ownership
        $getAppQuery = "SELECT a.resume_pdf_path, a.parsed_resume_path 
                       FROM applications a 
                       INNER JOIN jobs j ON a.job_id = j.id 
                       WHERE a.id = $applicationId AND j.created_by = $userId";
        $getAppResult = mysqli_query($con, $getAppQuery);
        
        if (mysqli_num_rows($getAppResult) === 0) {
            throw new Exception('Application not found or unauthorized');
        }
        
        $application = mysqli_fetch_assoc($getAppResult);
        $resumePath = $application['resume_pdf_path'];
        $parsedPath = $application['parsed_resume_path'];
        
        // Delete files
        $filesDeleted = [];
        $fileErrors = [];
        
        // Delete resume PDF file - handle both absolute and relative paths
        if (!empty($resumePath)) {
            // Try the path as stored first
            $pdfPath = $resumePath;
            
            // If it doesn't exist, try with ../ prefix (for files stored as relative paths)
            if (!file_exists($pdfPath) && !str_starts_with($resumePath, '../')) {
                $pdfPath = '../' . $resumePath;
            }
            
            if (file_exists($pdfPath)) {
                if (unlink($pdfPath)) {
                    $filesDeleted[] = 'Resume PDF';
                } else {
                    $fileErrors[] = 'Failed to delete resume PDF';
                }
            } else {
                $fileErrors[] = "Resume PDF file not found at: $pdfPath";
            }
        }
        
        // Delete parsed JSON file
        if (!empty($parsedPath)) {
            // Try the path as stored first
            $jsonPath = $parsedPath;
            
            // If it doesn't exist, try with ../ prefix (for files stored as relative paths)
            if (!file_exists($jsonPath) && !str_starts_with($parsedPath, '../')) {
                $jsonPath = '../' . $parsedPath;
            }
            
            if (file_exists($jsonPath)) {
                if (unlink($jsonPath)) {
                    $filesDeleted[] = 'Parsed resume data';
                } else {
                    $fileErrors[] = 'Failed to delete parsed resume data';
                }
            } else {
                $fileErrors[] = "Parsed resume file not found at: $jsonPath";
            }
        }
        
        // Delete application from database
        $deleteQuery = "DELETE FROM applications WHERE id = $applicationId";
        
        if (mysqli_query($con, $deleteQuery)) {
            if (mysqli_affected_rows($con) > 0) {
                $response['success'] = true;
                $response['message'] = 'Application deleted successfully';
                
                if (!empty($filesDeleted)) {
                    $response['message'] .= '. Files deleted: ' . implode(', ', $filesDeleted);
                }
                
                if (!empty($fileErrors)) {
                    $response['message'] .= '. File warnings: ' . implode(', ', $fileErrors);
                }
            } else {
                throw new Exception('Application not found');
            }
        } else {
            throw new Exception('Failed to delete application: ' . mysqli_error($con));
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
}

echo json_encode($response);
?>
