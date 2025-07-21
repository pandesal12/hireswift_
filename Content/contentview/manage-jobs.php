<?php
require_once '../Query/get_jobs.php';

// Get jobs from database using user_id instead of email
$user_id = $_SESSION['id'] ?? null;
$jobs = getAllJobs($user_id);

// Handle success/error messages
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';

// Debug information (remove in production)
// echo "<!-- Debug: User ID: " . htmlspecialchars($user_id) . ", Jobs count: " . count($jobs) . " -->";
?>
<link rel="stylesheet" href="CSS/manage-jobs.css">
<div class="page-header">
    <div>
        <h1 style="color: #212529; margin-bottom: 8px;">Manage Jobs</h1>
        <p style="color: #6c757d;">Create and manage your job listings</p>
    </div>
    <button class="btn btn-primary" onclick="openJobModal()">
        <i class="fas fa-plus"></i>
        Add New Job
    </button>
</div>

<?php if ($success_message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>

<?php if ($error_message): ?>
<div class="alert alert-danger">
    <i class="fas fa-exclamation-circle"></i>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<div class="jobs-table">
    <table class="table">
        <thead>
            <tr>
                <th>Job Title</th>
                <th>Type</th>
                <th>Skills Required</th>
                <th>Education</th>
                <th>Applicants</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($jobs)): ?>
            <tr>
                <td colspan="8" style="text-align: center; color: #6c757d; padding: 40px;">
                    No jobs found. <a href="#" onclick="openJobModal()">Create your first job</a>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td>
                    <strong><?php echo htmlspecialchars($job['title']); ?></strong>
                </td>
                <td><?php echo htmlspecialchars($job['employment_type']); ?></td>
                <td>
                    <?php if (!empty($job['skills'])): ?>
                        <?php foreach ($job['skills'] as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #6c757d; font-style: italic;">No skills specified</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($job['education'])): ?>
                        <?php foreach ($job['education'] as $edu): ?>
                            <span class="education-tag"><?php echo htmlspecialchars($edu); ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color: #6c757d; font-style: italic;">No education specified</span>
                    <?php endif; ?>
                </td>
                <td><?php echo $job['applicant_count'] ?? 0; ?></td>
                <td>
                    <span class="status-badge status-<?php echo strtolower($job['status']); ?>">
                        <?php echo $job['status']; ?>
                    </span>
                </td>
                <td><?php echo date('M j, Y', strtotime($job['created_at'])); ?></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-sm" onclick="editJob(<?php echo $job['id']; ?>)" title="Edit Job">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteJob(<?php echo $job['id']; ?>)" title="Delete Job">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Job Modal -->
<div id="jobModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title" id="modalTitle">Add New Job</h2>
            <button class="close" onclick="closeJobModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="jobForm" method="POST" action="../Query/process_job.php">
                <input type="hidden" id="jobId" name="job_id">
                
                <div class="form-group">
                    <label class="form-label" for="jobTitle">Job Title</label>
                    <input type="text" class="form-control" id="jobTitle" name="job_title" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="employmentType">Employment Type</label>
                    <select class="form-control" id="employmentType" name="employment_type" required>
                        <option value="">Select Type</option>
                        <option value="Full Time">Full Time</option>
                        <option value="Part Time">Part Time</option>
                        <option value="Contract">Contract</option>
                        <option value="Internship">Internship</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Required Skills</label>
                    <div class="tags-input" id="skillsInput">
                        <input type="text" class="tag-input" placeholder="Type skill and press Enter" onkeypress="addSkillTag(event)">
                    </div>
                    <input type="hidden" id="skillsHidden" name="skills">
                </div>

                <div class="form-group">
                    <label class="form-label">Education Requirements</label>
                    <div class="tags-input" id="educationInput">
                        <input type="text" class="tag-input" placeholder="Type education requirement and press Enter" onkeypress="addEducationTag(event)">
                    </div>
                    <input type="hidden" id="educationHidden" name="education">
                </div>

                <div class="form-group">
                    <label class="form-label" for="jobDescription">Job Description</label>
                    <textarea class="form-control" id="jobDescription" name="job_description" rows="6" required></textarea>
                </div>

                <div style="display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeJobModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Job</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="JS/manage-jobs.js"></script>
