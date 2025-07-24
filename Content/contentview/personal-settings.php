<?php
// Get user data from session or database
if (!isset($_SESSION['name'])) {
    header('location: ../index.php');
}
$title = "Hireswift - Personal Settings";
// Get user's company from the link table
require_once '../Query/connect.php';
$user_id = $_SESSION['id'];
$companyQuery = "SELECT company FROM link WHERE user_id = $user_id";
$companyResult = mysqli_query($con, $companyQuery);
$currentCompany = '';

if (mysqli_num_rows($companyResult) > 0) {
    $companyData = mysqli_fetch_assoc($companyResult);
    $currentCompany = $companyData['company'];
}

$user = [
    'name' => $_SESSION['name'],
    'email' => $_SESSION['email'],
    'phone' => $_SESSION['phone'],
    'company' => $currentCompany,
    'id' => $_SESSION['id'],
];

$initials = '';
$parts = explode(' ', trim($user['name']));
if (isset($parts[0]) && $parts[0] !== '') {
    $initials .= strtoupper($parts[0][0]);
}
if (isset($parts[1]) && $parts[1] !== '') {
    $initials .= strtoupper($parts[1][0]);
}

// Generate forms link
$formsLink = '';
if (!empty($currentCompany)) {
    // Convert company name to lowercase and URL-encode it
    $urlCompany = urlencode(strtolower($currentCompany));
    $formsLink = "http://localhost:8080/hireswift_/Forms/?link=" . $urlCompany;
}
?>

<link rel="stylesheet" href="CSS/personal-settings.css">

<div class="settings-container">
    <div class="page-header">
        <h1 style="color: #212529; margin-bottom: 8px;">Personal Settings</h1>
        <p style="color: #6c757d; margin-bottom: 32px;">Manage your account information and preferences</p>
    </div>

    <!-- Forms Link Section -->
    <?php if (!empty($currentCompany)): ?>
    <div class="settings-card">
        <h3 class="section-title">Application Forms Link</h3>
        <p style="color: #6c757d; margin-bottom: 16px;">Share this link with job applicants to allow them to apply for positions at your company.</p>
        
        <div class="forms-link-container">
            <div class="form-group">
                <label class="form-label" for="formsLink">Forms URL</label>
                <div class="link-input-wrapper">
                    <input type="text" class="form-control" id="formsLink" value="<?php echo htmlspecialchars($formsLink); ?>" readonly>
                    <button type="button" class="btn btn-copy" id="copyLinkBtn" onclick="copyFormsLink()">
                        <i class="fas fa-copy"></i>
                        <span class="copy-text">Copy Link</span>
                    </button>
                </div>
            </div>
            <div class="link-info">
                <i class="fas fa-info-circle"></i>
                <span>Job seekers can use this link to submit applications directly to <strong><?php echo htmlspecialchars($currentCompany); ?></strong></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Profile Information -->
    <div class="settings-card">
        <div class="profile-header">
            <div class="profile-avatar-large"><?php echo $initials; ?></div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <div class="profile-meta">
                    <div>HireSwift User</div>
                    <?php if (!empty($user['company'])): ?>
                    <div style="color: #4285f4; font-weight: 500;"><?php echo htmlspecialchars($user['company']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h3 class="section-title">Profile Information</h3>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == 'success'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Profile updated successfully!
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="../Query/update_profile.php" id="profileForm">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="fullName">Full Name</label>
                    <input type="text" class="form-control" id="fullName" name="full_name" 
                           value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <i class="status-icon" id="emailStatusIcon"></i>
                    </div>
                    <div class="status-message" id="emailStatusMessage"></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                           pattern="[0-9]{11}" placeholder="09991231234" maxlength="11">
                </div>

                <div class="form-group">
                    <label class="form-label" for="company">Company Name</label>
                    <div class="input-wrapper">
                        <input type="text" class="form-control" id="company" name="company" 
                               value="<?php echo htmlspecialchars($user['company']); ?>" 
                               placeholder="Enter your company name">
                        <i class="status-icon" id="companyStatusIcon"></i>
                    </div>
                    <div class="status-message" id="companyStatusMessage"></div>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary" name="updateProfile" id="updateBtn" disabled>
                    <i class="fas fa-save"></i>
                    Update Profile
                </button>
                <button type="reset" class="btn btn-secondary" id="resetBtn">
                    <i class="fas fa-undo"></i>
                    Reset
                </button>
            </div>
        </form>
    </div>

    <!-- Change Password -->
    <div class="settings-card">
        <h3 class="section-title">Change Password</h3>
        
        <?php if (isset($_GET['password']) && $_GET['password'] == 'success'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            Password changed successfully!
        </div>
        <?php elseif (isset($_GET['password']) && $_GET['password'] == 'error'): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Current password is incorrect. Please try again.'; ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="../Query/change_password.php" id="passwordForm">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
            
            <div class="form-group">
                <label class="form-label" for="currentPassword">Current Password</label>
                <div class="input-wrapper">
                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                    <i class="status-icon" id="currentPasswordIcon"></i>
                </div>
                <div class="status-message" id="currentPasswordMessage"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        <i class="status-icon" id="confirmPasswordIcon"></i>
                    </div>
                    <div class="status-message" id="confirmPasswordMessage"></div>
                </div>
            </div>

            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>At least 8 characters long</li>
                </ul>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" name="changePassword" class="btn btn-primary" id="changePasswordBtn" disabled>
                    <i class="fas fa-key"></i>
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script src="JS/personal-settings.js"></script>
