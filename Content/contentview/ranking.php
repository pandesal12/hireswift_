<?php
require_once '../Query/connect.php';

// Get user's jobs for filtering
$title = "Hireswift - Ranking";
$user_id = $_SESSION['id'];
$jobsQuery = "SELECT id, title FROM jobs WHERE created_by = $user_id ORDER BY title ASC";
$jobsResult = mysqli_query($con, $jobsQuery);
$jobs = [];
while ($row = mysqli_fetch_assoc($jobsResult)) {
    $jobs[] = $row;
}

// Get selected job filter
$selectedJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Build applications query with score ordering
$applicationsQuery = "SELECT a.*, j.title as job_title 
                     FROM applications a 
                     INNER JOIN jobs j ON a.job_id = j.id 
                     WHERE j.created_by = $user_id";

if ($selectedJobId > 0) {
    $applicationsQuery .= " AND a.job_id = $selectedJobId";
}

$applicationsQuery .= " ORDER BY a.score DESC, a.created_at DESC";
$applicationsResult = mysqli_query($con, $applicationsQuery);
$applications = [];
while ($row = mysqli_fetch_assoc($applicationsResult)) {
    $applications[] = $row;
}
?>

<link rel="stylesheet" href="CSS/ranking.css">

<div class="ranking-container">
    <div class="page-header">
        <div>
            <h1>Applicant Rankings</h1>
            <p>View applicants ranked by their resume matching scores</p>
        </div>
        <div class="filter-section">
            <select id="jobFilter" class="form-control" onchange="filterRankings()">
                <option value="0">All Jobs</option>
                <?php foreach ($jobs as $job): ?>
                <option value="<?php echo $job['id']; ?>" <?php echo $selectedJobId == $job['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($job['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="ranking-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Applicant Name</th>
                    <th>Email</th>
                    <th>Job Applied</th>
                    <th>Match Score</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="8" style="text-align: center; color: #6c757d; padding: 40px;">
                        No scored applications found for the selected criteria.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($applications as $index => $application): ?>
                <tr>
                    <td>
                        <div class="rank-badge rank-<?php echo $index < 3 ? $index + 1 : 'other'; ?>">
                            #<?php echo $index + 1; ?>
                        </div>
                    </td>
                    <td>
                        <strong><?php echo htmlspecialchars($application['applicant_name']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($application['email']); ?></td>
                    <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                    <td>
                        <div class="score-container">
                            <div class="score-value"><?php echo number_format($application['score'], 1); ?>%</div>
                            <div class="score-bar">
                                <div class="score-fill" style="width: <?php echo $application['score']; ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo htmlspecialchars($application['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($application['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <button class="btn btn-secondary btn-sm" onclick="viewParsedResume(<?php echo $application['id']; ?>)">
                                <i class="fas fa-eye"></i>
                                View Details
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="viewResume('<?php echo htmlspecialchars($application['resume_pdf_path']); ?>')">
                                <i class="fas fa-file-pdf"></i>
                                PDF
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="deleteApplication(<?php echo $application['id']; ?>)">
                                <i class="fas fa-trash"></i>
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Parsed Resume Modal -->
<div id="resumeModal" class="modal">
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2 class="modal-title">Resume Analysis</h2>
            <button class="close" onclick="closeResumeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div id="resumeContent">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading resume analysis...
                </div>
            </div>
        </div>
    </div>
</div>

<script src="JS/ranking.js"></script>
