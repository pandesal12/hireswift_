<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Hireswift'; ?></title>
    <link rel="stylesheet" href="CSS/master.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css" integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<?php
    if (!isset($_SESSION['name'])) {
        header('location: ../index.php');
    }

    $name = $_SESSION['name']; 
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
    } else {

    }
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
                    <!-- <span class="nav-badge">16</span> -->
                </a>

                <a href="?content=manage-jobs" class="nav-item <?php echo $page == 'manage-jobs' ? 'active' : ''?>">
                    <i class="fa-solid fa-briefcase sidelogo"></i>
                    Manage Jobs
                </a>

            </div>

            <div class="nav-section">
                <div class="nav-section-title">Miscellaneous</div>
                

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
            </div>
        </main>
    </div>
    <script src="JS/master.js"></script>
</body>
</html>