<?php
require_once '../Query/connect.php';

// Get company name from URL parameter
$companyName = isset($_GET['link']) ? trim($_GET['link']) : '';

if (empty($companyName)) {
    die('Invalid company link');
}

// Get company information
$companyQuery = "SELECT * FROM link WHERE company = '" . mysqli_real_escape_string($con, $companyName) . "'";
$companyResult = mysqli_query($con, $companyQuery);

if (mysqli_num_rows($companyResult) === 0) {
    die('Company not found');
}

$company = mysqli_fetch_assoc($companyResult);
$linkId = $company['link_id'];

// Get active jobs for this company
$jobsQuery = "SELECT * FROM jobs WHERE link_id = $linkId AND status = 'Active' ORDER BY title ASC";
$jobsResult = mysqli_query($con, $jobsQuery);
$jobs = [];
while ($row = mysqli_fetch_assoc($jobsResult)) {
    $row['skills'] = json_decode($row['skills'], true) ?: [];
    $row['education'] = json_decode($row['education'], true) ?: [];
    $jobs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply to <?php echo htmlspecialchars($companyName); ?> - HireSwift</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding: 20px;
            background-image: url('../Index/Assets/Background.svg');
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .application-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #4285f4;
            color: white;
            padding: 32px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .form-content {
            padding: 32px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            color: #212529;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
        }

        /* Job Details Styling - Make sure it's visible */
        .job-details {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border: 2px solid #4285f4;
            border-radius: 12px;
            padding: 24px;
            margin-top: 16px;
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: all 0.5s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .job-details.show {
            opacity: 1;
            max-height: 1000px;
            display: block !important;
        }

        .job-details-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 2px solid #dee2e6;
        }

        .job-details-header i {
            color: #4285f4;
            font-size: 24px;
            margin-right: 12px;
        }

        .job-details-header h3 {
            color: #212529;
            font-size: 20px;
            margin: 0;
        }

        .job-detail-item {
            margin-bottom: 20px;
            padding: 16px;
            background: white;
            border-radius: 8px;
            border-left: 4px solid #4285f4;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .job-detail-label {
            font-weight: 700;
            color: #495057;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .job-detail-value {
            color: #212529;
            line-height: 1.6;
        }

        .skill-tag {
            display: inline-block;
            background: linear-gradient(135deg, #4285f4, #34a853);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 4px 4px 4px 0;
            box-shadow: 0 2px 4px rgba(66, 133, 244, 0.3);
        }

        .education-tag {
            display: inline-block;
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 4px 4px 4px 0;
            box-shadow: 0 2px 4px rgba(255, 152, 0, 0.3);
        }

        .no-requirements {
            color: #6c757d;
            font-style: italic;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px dashed #dee2e6;
        }

        .job-description {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            font-size: 14px;
            line-height: 1.6;
        }

        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .file-upload-area:hover {
            border-color: #4285f4;
            background: #f0f7ff;
        }

        .file-upload-area.dragover {
            border-color: #4285f4;
            background: #f0f7ff;
        }

        .file-upload-area.has-file {
            border-color: #28a745;
            background: #f0fff4;
        }

        .file-icon {
            font-size: 48px;
            color: #6c757d;
            margin-bottom: 16px;
        }

        .file-text h3 {
            color: #495057;
            margin-bottom: 8px;
        }

        .file-text p {
            color: #6c757d;
            font-size: 14px;
        }

        .selected-file {
            margin-top: 16px;
            padding: 12px;
            background: #e7f3ff;
            border-radius: 8px;
            display: none;
        }

        .selected-file.show {
            display: block;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .file-info i {
            color: #4285f4;
            font-size: 20px;
        }

        .file-details h4 {
            color: #212529;
            margin-bottom: 4px;
        }

        .file-details p {
            color: #6c757d;
            font-size: 12px;
        }

        .btn {
            width: 100%;
            padding: 16px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
        }

        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(66, 133, 244, 0.4);
        }

        .btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .loading {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .application-container {
                margin: 10px;
            }
            
            .header {
                padding: 24px;
            }
            
            .form-content {
                padding: 20px;
            }

            .job-details {
                padding: 16px;
            }

            .job-detail-item {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="application-container">
        <div class="header">
            <h1>Apply to <?php echo htmlspecialchars($companyName); ?></h1>
            <p>Submit your resume and apply for available positions</p>
        </div>

        <div class="form-content">
            <?php if (empty($jobs)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                No active job openings available at this time.
            </div>
            <?php else: ?>
            
            <form id="applicationForm" enctype="multipart/form-data">
                <input type="hidden" name="link_id" value="<?php echo $linkId; ?>">
                
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="full_name" class="form-control" 
                           placeholder="Enter your full name" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" 
                           placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="jobSelect">Select Position</label>
                    <select id="jobSelect" name="job_id" class="form-control" required>
                        <option value="">Choose a position...</option>
                        <?php foreach ($jobs as $job): ?>
                        <option value="<?php echo $job['id']; ?>" 
                                data-title="<?php echo htmlspecialchars($job['title']); ?>"
                                data-type="<?php echo htmlspecialchars($job['employment_type']); ?>"
                                data-description="<?php echo htmlspecialchars($job['description']); ?>"
                                data-skills="<?php echo htmlspecialchars(json_encode($job['skills'])); ?>"
                                data-education="<?php echo htmlspecialchars(json_encode($job['education'])); ?>">
                            <?php echo htmlspecialchars($job['title']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="jobDetails" class="job-details">
                    <div class="job-details-header">
                        <i class="fas fa-briefcase"></i>
                        <h3>Position Details</h3>
                    </div>

                    <div class="job-detail-item">
                        <div class="job-detail-label">
                            <i class="fas fa-clock"></i>
                            Employment Type
                        </div>
                        <div class="job-detail-value" id="employmentType"></div>
                    </div>
                    
                    <div class="job-detail-item">
                        <div class="job-detail-label">
                            <i class="fas fa-cogs"></i>
                            Skills Required
                        </div>
                        <div class="job-detail-value" id="skillsRequired"></div>
                    </div>
                    
                    <div class="job-detail-item">
                        <div class="job-detail-label">
                            <i class="fas fa-graduation-cap"></i>
                            Education Required
                        </div>
                        <div class="job-detail-value" id="educationRequired"></div>
                    </div>
                    
                    <div class="job-detail-item">
                        <div class="job-detail-label">
                            <i class="fas fa-file-text"></i>
                            Job Description
                        </div>
                        <div class="job-detail-value">
                            <div class="job-description" id="jobDescription"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Resume (PDF Only)</label>
                    <div class="file-upload-area" id="fileUploadArea">
                        <input type="file" id="resumeFile" name="resume_file" accept=".pdf" 
                               style="display: none;" required>
                        <div class="file-text">
                            <div class="file-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h3>Click to upload or drag and drop</h3>
                            <p>PDF files only (Max 10MB)</p>
                        </div>
                    </div>
                    <div class="selected-file" id="selectedFile">
                        <div class="file-info">
                            <i class="fas fa-file-pdf"></i>
                            <div class="file-details">
                                <h4 id="fileName"></h4>
                                <p id="fileSize"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn" id="submitBtn">
                    <span class="loading" id="submitLoading"></span>
                    <i class="fas fa-paper-plane"></i>
                    Submit Application
                </button>
            </form>
            
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing job selection...');
            
            // Get elements
            const jobSelect = document.getElementById('jobSelect');
            const jobDetails = document.getElementById('jobDetails');

            
            if (!jobSelect || !jobDetails) {
                console.error('Required elements not found!');
                return;
            }
            
            console.log('Elements found, setting up event listener...');
            
            // Job selection handler
            jobSelect.addEventListener('change', function() {
                console.log('Job selection changed, value:', this.value);
                
                if (this.value) {
                    const selectedOption = this.options[this.selectedIndex];
                    console.log('Selected option:', selectedOption);
                    
                    // Get data from individual data attributes (more reliable)
                    const jobTitle = selectedOption.getAttribute('data-title');
                    const employmentType = selectedOption.getAttribute('data-type');
                    const description = selectedOption.getAttribute('data-description');
                    const skillsJson = selectedOption.getAttribute('data-skills');
                    const educationJson = selectedOption.getAttribute('data-education');
                    
                    console.log('Job data:', {
                        title: jobTitle,
                        type: employmentType,
                        description: description,
                        skills: skillsJson,
                        education: educationJson
                    });
                    
                    // Parse skills and education
                    let skills = [];
                    let education = [];
                    
                    try {
                        skills = JSON.parse(skillsJson) || [];
                        education = JSON.parse(educationJson) || [];
                    } catch (e) {
                        console.error('Error parsing JSON:', e);
                        skills = [];
                        education = [];
                    }
                    
                    // Update employment type
                    const employmentTypeEl = document.getElementById('employmentType');
                    if (employmentTypeEl) {
                        employmentTypeEl.textContent = employmentType || 'Not specified';
                    }
                    
                    // Display skills
                    const skillsContainer = document.getElementById('skillsRequired');
                    if (skillsContainer) {
                        skillsContainer.innerHTML = '';
                        
                        if (skills && skills.length > 0) {
                            skills.forEach(skill => {
                                const tag = document.createElement('span');
                                tag.className = 'skill-tag';
                                tag.textContent = skill;
                                skillsContainer.appendChild(tag);
                            });
                        } else {
                            skillsContainer.innerHTML = '<div class="no-requirements">No specific skills required</div>';
                        }
                    }
                    
                    // Display education
                    const educationContainer = document.getElementById('educationRequired');
                    if (educationContainer) {
                        educationContainer.innerHTML = '';
                        
                        if (education && education.length > 0) {
                            education.forEach(edu => {
                                const tag = document.createElement('span');
                                tag.className = 'education-tag';
                                tag.textContent = edu;
                                educationContainer.appendChild(tag);
                            });
                        } else {
                            educationContainer.innerHTML = '<div class="no-requirements">No specific education requirements</div>';
                        }
                    }
                    
                    // Update job description
                    const jobDescriptionEl = document.getElementById('jobDescription');
                    if (jobDescriptionEl) {
                        jobDescriptionEl.textContent = description || 'No description available';
                    }
                    
                    // Show job details
                    console.log('Showing job details...');
                    jobDetails.classList.add('show');
                    
                    // Smooth scroll to job details after a short delay
                    setTimeout(() => {
                        jobDetails.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'nearest' 
                        });
                    }, 300);
                    
                } else {
                    console.log('No job selected, hiding details...');
                    jobDetails.classList.remove('show');
                }
            });

            // File upload handling
            const fileUploadArea = document.getElementById('fileUploadArea');
            const resumeFile = document.getElementById('resumeFile');
            const selectedFile = document.getElementById('selectedFile');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            if (fileUploadArea && resumeFile) {
                fileUploadArea.addEventListener('click', () => resumeFile.click());

                resumeFile.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        if (file.type !== 'application/pdf') {
                            alert('Please select a PDF file only.');
                            this.value = '';
                            return;
                        }

                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            alert('File size exceeds 10MB limit.');
                            this.value = '';
                            return;
                        }

                        fileUploadArea.classList.add('has-file');
                        if (fileName) fileName.textContent = file.name;
                        if (fileSize) fileSize.textContent = formatFileSize(file.size);
                        if (selectedFile) selectedFile.classList.add('show');
                    }
                });

                // Drag and drop
                fileUploadArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    this.classList.add('dragover');
                });

                fileUploadArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                });

                fileUploadArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('dragover');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        resumeFile.files = files;
                        resumeFile.dispatchEvent(new Event('change'));
                    }
                });
            }

            // Form submission
            const applicationForm = document.getElementById('applicationForm');
            const submitBtn = document.getElementById('submitBtn');
            const submitLoading = document.getElementById('submitLoading');

            if (applicationForm) {
                applicationForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Validate that a job is selected
                    if (!jobSelect.value) {
                        alert('Please select a position before submitting.');
                        jobSelect.focus();
                        return;
                    }
                    
                    // Validate that a file is selected
                    if (!resumeFile.files.length) {
                        alert('Please upload your resume before submitting.');
                        fileUploadArea.scrollIntoView({ behavior: 'smooth' });
                        return;
                    }
                    
                    if (submitLoading) submitLoading.style.display = 'inline-block';
                    if (submitBtn) submitBtn.disabled = true;

                    const formData = new FormData(this);

                    fetch('process_application.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Application submitted successfully! We will review your application and get back to you soon.');
                            this.reset();
                            jobDetails.classList.remove('show');
                            if (selectedFile) selectedFile.classList.remove('show');
                            fileUploadArea.classList.remove('has-file');
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while submitting your application. Please try again.');
                    })
                    .finally(() => {
                        if (submitLoading) submitLoading.style.display = 'none';
                        if (submitBtn) submitBtn.disabled = false;
                    });
                });
            }

            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Add visual feedback when form fields are filled
            const formInputs = document.querySelectorAll('.form-control');
            formInputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#28a745';
                    } else {
                        this.style.borderColor = '#e9ecef';
                    }
                });
            });
            
            console.log('Job selection setup complete!');
        });
    </script>
</body>
</html>
