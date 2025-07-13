<?php
require_once 'connect.php';

class DashboardData {
    private $con;
    private $userId;
    
    public function __construct($userId = null) {
        global $con;
        $this->con = $con;
        $this->userId = $userId;
    }
    
    // Set user ID for filtering
    public function setUserId($userId) {
        $this->userId = $userId;
    }
    
    // Get WHERE clause for user filtering
    private function getUserFilter() {
        if ($this->userId) {
            return " AND j.created_by = '" . mysqli_real_escape_string($this->con, $this->userId) . "'";
        }
        return "";
    }
    
    // Get total applications count
    public function getTotalApplications() {
        $query = "SELECT COUNT(*) as total 
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id 
                  WHERE 1=1" . $this->getUserFilter();
        $result = mysqli_query($this->con, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Get applications by status
    public function getApplicationsByStatus($status) {
        $status = mysqli_real_escape_string($this->con, $status);
        $query = "SELECT COUNT(*) as total 
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id 
                  WHERE a.status = '$status'" . $this->getUserFilter();
        $result = mysqli_query($this->con, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Get new applications (last 24 hours)
    public function getNewApplications() {
        $query = "SELECT COUNT(*) as total 
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id 
                  WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)" . $this->getUserFilter();
        $result = mysqli_query($this->con, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    
    // Get applications trend for last 7 days
    public function getApplicationsTrend() {
        $query = "SELECT DATE(a.created_at) as date, COUNT(*) as applications 
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id 
                  WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)" . $this->getUserFilter() . "
                  GROUP BY DATE(a.created_at)
                  ORDER BY date ASC";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get applications per job
    public function getApplicationsPerJob() {
        $query = "SELECT j.title as job, COUNT(a.id) as applications
                  FROM applications a
                  INNER JOIN jobs j ON a.job_id = j.id
                  WHERE 1=1" . $this->getUserFilter() . "
                  GROUP BY a.job_id, j.title
                  HAVING j.title IS NOT NULL
                  ORDER BY applications DESC
                  LIMIT 5";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get status distribution
    public function getStatusDistribution() {
        $query = "SELECT 
                    CASE 
                        WHEN a.status IS NULL OR a.status = '' THEN 'Pending'
                        ELSE CONCAT(UPPER(SUBSTRING(a.status, 1, 1)), LOWER(SUBSTRING(a.status, 2)))
                    END as status,
                    COUNT(*) as count
                  FROM applications a
                  INNER JOIN jobs j ON a.job_id = j.id
                  WHERE 1=1" . $this->getUserFilter() . "
                  GROUP BY a.status
                  ORDER BY count DESC";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get recent applications
    public function getRecentApplications($limit = 10) {
        $query = "SELECT a.applicant_name, a.email, a.phone, a.status, a.score, a.created_at, j.title as job_title
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id
                  WHERE 1=1" . $this->getUserFilter() . "
                  ORDER BY a.created_at DESC 
                  LIMIT $limit";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get average score
    public function getAverageScore() {
        $query = "SELECT AVG(a.score) as avg_score 
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id 
                  WHERE a.score > 0" . $this->getUserFilter();
        $result = mysqli_query($this->con, $query);
        $row = mysqli_fetch_assoc($result);
        return round($row['avg_score'], 2);
    }
    
    // Get growth percentage
    public function getGrowthPercentage($metric, $days = 30) {
        $currentQuery = "";
        $previousQuery = "";
        
        switch($metric) {
            case 'total':
                $currentQuery = "SELECT COUNT(*) as current 
                                FROM applications a 
                                INNER JOIN jobs j ON a.job_id = j.id 
                                WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)" . $this->getUserFilter();
                $previousQuery = "SELECT COUNT(*) as previous 
                                 FROM applications a 
                                 INNER JOIN jobs j ON a.job_id = j.id 
                                 WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL " . ($days * 2) . " DAY) 
                                 AND a.created_at < DATE_SUB(NOW(), INTERVAL $days DAY)" . $this->getUserFilter();
                break;
            case 'shortlisted':
                $currentQuery = "SELECT COUNT(*) as current 
                                FROM applications a 
                                INNER JOIN jobs j ON a.job_id = j.id 
                                WHERE a.status = 'Shortlisted' 
                                AND a.created_at >= DATE_SUB(NOW(), INTERVAL $days DAY)" . $this->getUserFilter();
                $previousQuery = "SELECT COUNT(*) as previous 
                                 FROM applications a 
                                 INNER JOIN jobs j ON a.job_id = j.id 
                                 WHERE a.status = 'Shortlisted' 
                                 AND a.created_at >= DATE_SUB(NOW(), INTERVAL " . ($days * 2) . " DAY) 
                                 AND a.created_at < DATE_SUB(NOW(), INTERVAL $days DAY)" . $this->getUserFilter();
                break;
            case 'new':
                $currentQuery = "SELECT COUNT(*) as current 
                                FROM applications a 
                                INNER JOIN jobs j ON a.job_id = j.id 
                                WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)" . $this->getUserFilter();
                $previousQuery = "SELECT COUNT(*) as previous 
                                 FROM applications a 
                                 INNER JOIN jobs j ON a.job_id = j.id 
                                 WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 2 DAY) 
                                 AND a.created_at < DATE_SUB(NOW(), INTERVAL 1 DAY)" . $this->getUserFilter();
                break;
        }
        
        $result = mysqli_query($this->con, $currentQuery);
        $current = mysqli_fetch_assoc($result)['current'];
        
        $result = mysqli_query($this->con, $previousQuery);
        $previous = mysqli_fetch_assoc($result)['previous'];
        
        if ($previous == 0) return $current > 0 ? 100 : 0;
        
        return round((($current - $previous) / $previous) * 100, 1);
    }
    
    // Get all applications for the current user (for the main applications table)
    public function getAllApplications() {
        $query = "SELECT a.*, j.title as job_title, j.employment_type, j.description
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id
                  WHERE 1=1" . $this->getUserFilter() . "
                  ORDER BY a.created_at DESC";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get applications with pagination
    public function getApplicationsWithPagination($page = 1, $limit = 25) {
        $offset = ($page - 1) * $limit;
        $query = "SELECT a.*, j.title as job_title, j.employment_type
                  FROM applications a 
                  INNER JOIN jobs j ON a.job_id = j.id
                  WHERE 1=1" . $this->getUserFilter() . "
                  ORDER BY a.created_at DESC
                  LIMIT $limit OFFSET $offset";
        $result = mysqli_query($this->con, $query);
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        return $data;
    }
    
    // Get total pages for pagination
    public function getTotalPages($limit = 25) {
        $total = $this->getTotalApplications();
        return ceil($total / $limit);
    }
}
?>