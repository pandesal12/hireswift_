<?php 
session_start();
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../Index/Assets/HIRESWIFT.png" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="CSS/master.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Company Link Modal Styles */
        .company-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .company-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .company-modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            position: relative;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .company-modal-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .company-modal-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #4285f4, #34a853);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 24px;
        }

        .company-modal h2 {
            color: #212529;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .company-modal p {
            color: #6c757d;
            font-size: 14px;
        }

        .company-form-group {
            margin-bottom: 20px;
        }

        .company-form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }

        .company-input-wrapper {
            position: relative;
        }

        .company-form-control {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 16px;
            color: #212529;
            transition: all 0.2s ease;
        }

        .company-form-control:focus {
            outline: none;
            border-color: #4285f4;
            box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
        }

        .company-form-control.checking {
            border-color: #ffc107;
        }

        .company-form-control.available {
            border-color: #28a745;
        }

        .company-form-control.unavailable {
            border-color: #dc3545;
        }

        .company-input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 16px;
        }

        .company-status-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
        }

        .company-status-icon.checking {
            color: #ffc107;
            animation: spin 1s linear infinite;
        }

        .company-status-icon.available {
            color: #28a745;
        }

        .company-status-icon.unavailable {
            color: #dc3545;
        }

        .company-status-message {
            margin-top: 8px;
            font-size: 12px;
            min-height: 16px;
        }

        .company-status-message.available {
            color: #28a745;
        }

        .company-status-message.unavailable {
            color: #dc3545;
        }

        .company-btn {
            width: 100%;
            padding: 14px;
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
        }

        .company-btn:hover:not(:disabled) {
            background: #3367d6;
        }

        .company-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .company-btn-loading {
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
    </style>
</head>
<?php
    if (!isset($_SESSION['name'])) {
        header('location: ../index.php');
    }

    $name = $_SESSION['name']; 
    $user_id = $_SESSION['id'];
    $initials = '';
    $parts = explode(' ', trim($name));
    if (isset($parts[0]) && $parts[0] !== '') {
        $initials .= strtoupper($parts[0][0]);
    }
    if (isset($parts[1]) && $parts[1] !== '') {
        $initials .= strtoupper($parts[1][0]);
    }

    $page = 'dashboard';
    $content = 'contentview/dashboard.php'; // default
    if (isset($_GET['content'])) {
        $page = $_GET['content'];

        $allowed_pages = [
                'dashboard' => 'contentview/dashboard.php',
                'ranking' => 'contentview/ranking.php',
                'manage-jobs' => 'contentview/manage-jobs.php',
                'applicants' => 'contentview/applicants.php',
                'personal-settings' => 'contentview/personal-settings.php'
        ];

        if (array_key_exists($page, $allowed_pages)) {
            $content = $allowed_pages[$page];
        }
    }

    // Check if user has a company link
    require_once '../Query/connect.php';
    $checkLinkQuery = "SELECT * FROM link WHERE user_id = $user_id";
    $linkResult = mysqli_query($con, $checkLinkQuery);
    $hasLink = mysqli_num_rows($linkResult) > 0;
?>
<body>
    <div class="dashboard-container">
        <div class="mobile-overlay" id="mobileOverlay"></div>

        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="img-logo"></div>
                    <h1>HIRESWIFT</h1>
                </div>
            </div>

            <div class="nav-section">
                <a href="?content=dashboard" class="nav-item <?php echo $page == 'dashboard' ? 'active' : ''?>">
                    <i class="fa-solid fa-table-columns sidelogo"></i>
                    Dashboard
                </a>

                <a href="?content=ranking" class="nav-item <?php echo $page == 'ranking' ? 'active' : ''?>">
                    <i class="fa-solid fa-clipboard-list sidelogo"></i>
                    Ranking
                </a>

                <a href="?content=manage-jobs" class="nav-item <?php echo $page == 'manage-jobs' ? 'active' : ''?>">
                    <i class="fa-solid fa-briefcase sidelogo"></i>
                    Manage Jobs
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Submissions</div>
                
                <a href="?content=applicants" class="nav-item <?php echo $page == 'applicants' ? 'active' : ''?>"> 
                    <i class="fa-solid fa-user sidelogo"></i>
                    Applicants
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Settings</div>
                
                <a href="?content=personal-settings" class="nav-item <?php echo $page == 'personal-settings' ? 'active' : ''?>">
                    <i class="fa-regular fa-id-card sidelogo"></i>
                    Personal Settings
                </a>

                <a href="../Query/logout.php" class="nav-item">
                    <i class="fa-solid fa-arrow-right-from-bracket sidelogo"></i>
                    Log-out
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="header">
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                    </svg>
                </button>
                
                <div class="user-profile">
                    <div class="user-avatar"><?php echo $initials; ?></div>
                    <span class="user-name"><?php echo $name;?></span>
                </div>
            </header>

            <div class="content-area">
                <?php include($content); ?>
                <title><?php echo $title ?? 'Hireswift'; ?></title>
            </div>
        </main>
    </div>

    <!-- Company Link Modal -->
    <div id="companyModal" class="company-modal <?php echo !$hasLink ? 'active' : ''; ?>">
        <div class="company-modal-content">
            <div class="company-modal-header">
                <div class="company-modal-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h2>Setup Your Company</h2>
                <p>Create a unique company identifier to start receiving job applications</p>
            </div>

            <form id="companyForm">
                <div class="company-form-group">
                    <label class="company-form-label" for="companyName">Company Name</label>
                    <div class="company-input-wrapper">
                        <input type="text" id="companyName" name="company_name" class="company-form-control" 
                               placeholder="Enter your company name" required>
                        <i class="fas fa-building company-input-icon"></i>
                        <i class="company-status-icon" id="statusIcon"></i>
                    </div>
                    <div class="company-status-message" id="statusMessage"></div>
                </div>

                <button type="submit" class="company-btn" id="createCompanyBtn" disabled>
                    <span class="company-btn-loading" id="btnLoading"></span>
                    <i class="fas fa-check"></i>
                    Create Company Link
                </button>
            </form>
        </div>
    </div>

    <script src="JS/master.js"></script>
    <script>
        // Company name availability checking
        let checkTimeout;
        let isAvailable = false;

        const companyInput = document.getElementById('companyName');
        const statusIcon = document.getElementById('statusIcon');
        const statusMessage = document.getElementById('statusMessage');
        const createBtn = document.getElementById('createCompanyBtn');
        const companyForm = document.getElementById('companyForm');

        companyInput.addEventListener('input', function() {
            const companyName = this.value.trim();
            
            // Clear previous timeout
            clearTimeout(checkTimeout);
            
            // Reset states
            resetStatus();
            
            if (companyName.length < 3) {
                statusMessage.textContent = 'Company name must be at least 3 characters';
                statusMessage.className = 'company-status-message unavailable';
                createBtn.disabled = true;
                return;
            }

            // Show checking state
            showCheckingState();
            
            // Debounce the API call
            checkTimeout = setTimeout(() => {
                checkCompanyAvailability(companyName);
            }, 500);
        });

        function resetStatus() {
            companyInput.className = 'company-form-control';
            statusIcon.className = 'company-status-icon';
            statusMessage.textContent = '';
            statusMessage.className = 'company-status-message';
            isAvailable = false;
            createBtn.disabled = true;
        }

        function showCheckingState() {
            companyInput.className = 'company-form-control checking';
            statusIcon.className = 'company-status-icon checking';
            statusMessage.textContent = 'Checking availability...';
            statusMessage.className = 'company-status-message';
        }

        function checkCompanyAvailability(companyName) {
            fetch('../Query/check_company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ company_name: companyName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    showAvailableState();
                } else {
                    showUnavailableState();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showUnavailableState('Error checking availability');
            });
        }

        function showAvailableState() {
            companyInput.className = 'company-form-control available';
            statusIcon.className = 'company-status-icon available';
            statusIcon.innerHTML = '<i class="fas fa-check"></i>';
            statusMessage.textContent = 'Company name is available!';
            statusMessage.className = 'company-status-message available';
            isAvailable = true;
            createBtn.disabled = false;
        }

        function showUnavailableState(message = 'Company name is already taken') {
            companyInput.className = 'company-form-control unavailable';
            statusIcon.className = 'company-status-icon unavailable';
            statusIcon.innerHTML = '<i class="fas fa-times"></i>';
            statusMessage.textContent = message;
            statusMessage.className = 'company-status-message unavailable';
            isAvailable = false;
            createBtn.disabled = true;
        }

        // Form submission
        companyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!isAvailable) return;

            const btnLoading = document.getElementById('btnLoading');
            btnLoading.style.display = 'inline-block';
            createBtn.disabled = true;

            const companyName = companyInput.value.trim();

            fetch('../Query/create_company.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    company_name: companyName,
                    user_id: <?php echo $user_id; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide modal and reload page
                    document.getElementById('companyModal').classList.remove('active');
                    location.reload();
                } else {
                    alert('Error creating company: ' + data.message);
                    btnLoading.style.display = 'none';
                    createBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error creating company. Please try again.');
                btnLoading.style.display = 'none';
                createBtn.disabled = false;
            });
        });

        // Prevent modal from closing by clicking outside when user doesn't have a link
        <?php if (!$hasLink): ?>
        document.getElementById('companyModal').addEventListener('click', function(e) {
            e.stopPropagation();
        });
        <?php endif; ?>
    </script>
</body>
</html>
