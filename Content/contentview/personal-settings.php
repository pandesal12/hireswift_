<?php
// Get user data from session or database
$user = [
    'name' => $_SESSION['name'] ?? 'John Doe',
    'email' => $_SESSION['email'] ?? 'john.doe@example.com',
    'phone' => $_SESSION['phone'] ?? '+1 (555) 123-4567',
    'company' => $_SESSION['company'] ?? 'HireSwift Inc.',
    'position' => $_SESSION['position'] ?? 'HR Manager',
    'joined' => $_SESSION['joined'] ?? '2023-01-15'
];

$initials = '';
$parts = explode(' ', trim($user['name']));
if (isset($parts[0]) && $parts[0] !== '') {
    $initials .= strtoupper($parts[0][0]);
}
if (isset($parts[1]) && $parts[1] !== '') {
    $initials .= strtoupper($parts[1][0]);
}
?>

<style>
.settings-container {
    max-width: 800px;
}

.settings-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border: 1px solid #e9ecef;
    margin-bottom: 24px;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-bottom: 32px;
    padding-bottom: 24px;
    border-bottom: 1px solid #e9ecef;
}

.profile-avatar-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 28px;
}

.profile-info h2 {
    color: #212529;
    margin-bottom: 8px;
    font-size: 24px;
}

.profile-meta {
    color: #6c757d;
    font-size: 14px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

.form-control:disabled {
    background-color: #f8f9fa;
    color: #6c757d;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}

.btn-primary {
    background: #4285f4;
    color: white;
}

.btn-primary:hover {
    background: #3367d6;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.section-title {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 20px;
}

.password-requirements {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 16px;
    margin-top: 12px;
}

.password-requirements h4 {
    font-size: 14px;
    margin-bottom: 8px;
    color: #495057;
}

.password-requirements ul {
    margin: 0;
    padding-left: 20px;
    font-size: 12px;
    color: #6c757d;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    margin-bottom: 20px;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    
    .settings-card {
        padding: 20px;
    }
}
</style>

<div class="settings-container">
    <div class="page-header">
        <h1 style="color: #212529; margin-bottom: 8px;">Personal Settings</h1>
        <p style="color: #6c757d; margin-bottom: 32px;">Manage your account information and preferences</p>
    </div>

    <!-- Profile Information -->
    <div class="settings-card">
        <div class="profile-header">
            <div class="profile-avatar-large"><?php echo $initials; ?></div>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                <div class="profile-meta">
                    <div><?php echo htmlspecialchars($user['position']); ?> at <?php echo htmlspecialchars($user['company']); ?></div>
                    <div>Member since <?php echo date('F Y', strtotime($user['joined'])); ?></div>
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

        <form method="POST" action="update_profile.php">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="firstName">First Name</label>
                    <input type="text" class="form-control" id="firstName" name="first_name" 
                           value="<?php echo htmlspecialchars(explode(' ', $user['name'])[0]); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="lastName">Last Name</label>
                    <input type="text" class="form-control" id="lastName" name="last_name" 
                           value="<?php echo htmlspecialchars(explode(' ', $user['name'])[1] ?? ''); ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="company">Company</label>
                    <input type="text" class="form-control" id="company" name="company" 
                           value="<?php echo htmlspecialchars($user['company']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="position">Position</label>
                    <input type="text" class="form-control" id="position" name="position" 
                           value="<?php echo htmlspecialchars($user['position']); ?>">
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Update Profile
                </button>
                <button type="reset" class="btn btn-secondary">
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
            Current password is incorrect. Please try again.
        </div>
        <?php endif; ?>

        <form method="POST" action="../Query/change_password.php" id="passwordForm">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
            <div class="form-group">
                <label class="form-label" for="currentPassword">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="current_password" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="newPassword">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                </div>
            </div>

            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>At least 8 characters long</li>
                    <li>Contains at least one uppercase letter</li>
                    <li>Contains at least one lowercase letter</li>
                    <li>Contains at least one number</li>
                    <li>Contains at least one special character</li>
                </ul>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" name="changePassword" class="btn btn-primary">
                    <i class="fas fa-key"></i>
                    Change Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('New password and confirm password do not match.');
        return;
    }
    
    // Basic password validation
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(newPassword)) {
        e.preventDefault();
        alert('Password does not meet the requirements. Please check the password requirements below.');
        return;
    }
});
</script>
