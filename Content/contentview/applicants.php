<?php
require_once '../Query/connect.php';

// Get user's jobs for filtering
$user_id = $_SESSION['id'];
$jobsQuery = "SELECT id, title FROM jobs WHERE created_by = 8 ORDER BY title ASC";
$jobsResult = mysqli_query($con, $jobsQuery);
$jobs = [];
while ($row = mysqli_fetch_assoc($jobsResult)) {
    $jobs[] = $row;
}

// Get selected job filter
$selectedJobId = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

// Build applications query
$applicationsQuery = "SELECT a.*, j.title as job_title 
                     FROM applications a 
                     INNER JOIN jobs j ON a.job_id = j.id 
                     WHERE j.created_by = $user_id";

if ($selectedJobId > 0) {
    $applicationsQuery .= " AND a.job_id = $selectedJobId";
}

$applicationsQuery .= " ORDER BY a.created_at DESC";
$applicationsResult = mysqli_query($con, $applicationsQuery);
$applications = [];
while ($row = mysqli_fetch_assoc($applicationsResult)) {
    $applications[] = $row;
}
?>

<link rel="stylesheet" href="CSS/applicants.css">

<div class="applicants-container">
    <div class="page-header">
        <div>
            <h1>Job Applicants</h1>
            <p>Manage and review job applications</p>
        </div>
        <div class="filter-section">
            <select id="jobFilter" class="form-control" onchange="filterApplications()">
                <option value="0">All Jobs</option>
                <?php foreach ($jobs as $job): ?>
                <option value="<?php echo $job['id']; ?>" <?php echo $selectedJobId == $job['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($job['title']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="applicants-table">
        <table class="table">
            <thead>
                <tr>
                    <th>Applicant Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Job Applied</th>
                    <th>Status</th>
                    <th>Applied Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #6c757d; padding: 40px;">
                        No applications found for the selected criteria.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($applications as $application): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($application['applicant_name']); ?></strong>
                    </td>
                    <td><?php echo htmlspecialchars($application['email']); ?></td>
                    <td><?php echo htmlspecialchars($application['phone'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($application['job_title']); ?></td>
                    <td>
                        <span class="status-badge status-<?php echo strtolower($application['status']); ?>">
                            <?php echo htmlspecialchars($application['status']); ?>
                        </span>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($application['created_at'])); ?></td>
                    <td>
                        <div class="actions">
                            <button class="btn btn-secondary btn-sm" onclick="viewResume('<?php echo htmlspecialchars($application['resume_pdf_path']); ?>')">
                                <i class="fas fa-file-pdf"></i>
                                View Resume
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="updateStatus(<?php echo $application['id']; ?>)">
                                <i class="fas fa-edit"></i>
                                Update Status
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

<!-- Status Update Modal -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Update Application Status</h2>
            <button class="close" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="statusForm">
                <input type="hidden" id="applicationId" name="application_id">
                
                <div class="form-group">
                    <label class="form-label" for="applicationStatus">Status</label>
                    <select class="form-control" id="applicationStatus" name="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Reviewing">Reviewing</option>
                        <option value="Shortlisted">Shortlisted</option>
                        <option value="Interviewed">Interviewed</option>
                        <option value="Accepted">Accepted</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="JS/applicants.js"></script>
