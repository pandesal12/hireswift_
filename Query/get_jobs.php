<?php
require_once 'connect.php';

function getAllJobs($user_id) {
    global $con;
    try {
        if ($user_id) {
            $sql = "SELECT j.*, 
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as applicant_count
                    FROM jobs j 
                    WHERE j.created_by = '$user_id' 
                    ORDER BY j.created_at DESC";
        } else {
            $sql = "SELECT j.*, 
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) as applicant_count
                    FROM jobs j 
                    ORDER BY j.created_at DESC";
        }
        
        $result = mysqli_query($con, $sql);
        
        if (!$result) {
            error_log("SQL Error in getAllJobs: " . mysqli_error($con));
            return [];
        }
        
        $jobs = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Decode JSON fields
            $row['skills'] = json_decode($row['skills'], true) ?: [];
            $row['education'] = json_decode($row['education'], true) ?: [];
            $jobs[] = $row;
        }
        
        return $jobs;
        
    } catch (Exception $e) {
        error_log("Error fetching jobs: " . $e->getMessage());
        return [];
    }
}
?>
