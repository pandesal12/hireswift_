<?php
session_start();
$error_message = '';
$success_message = '';
$show_register = false;

// Handle error and success messages
if (isset($_GET['error'])) {
    $error_message = $_GET['error'];
}
if (isset($_GET['success'])) {
    $success_message = $_GET['success'];
}
if (isset($_GET['show']) && $_GET['show'] === 'register') {
    $show_register = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="Index/Assets/HIRESWIFT.png" type="image/png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireSwift - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="Index/style.css">
    <style>
        /* Terms Modal Styles */
        .terms-modal {
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

        .terms-modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .terms-modal-content {
            background: white;
            border-radius: 16px;
            padding: 32px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
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

        .terms-modal-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f8f9fa;
        }

        .terms-modal-icon {
            width: 60px;
            height: 60px;
            background:#4285f4;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: white;
            font-size: 24px;
        }

        .terms-modal h2 {
            color: #212529;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .terms-content {
            color: #495057;
            line-height: 1.6;
            font-size: 14px;
        }

        .terms-content h3 {
            color: #212529;
            margin: 20px 0 12px 0;
            font-size: 16px;
        }

        .terms-content p {
            margin-bottom: 16px;
        }

        .terms-content ul {
            margin: 12px 0;
            padding-left: 20px;
        }

        .terms-content li {
            margin-bottom: 8px;
        }

        .terms-close-btn {
            width: 100%;
            padding: 12px;
            background: #4285f4;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 24px;
        }

        .terms-close-btn:hover {
            background: #3367d6;
        }

        .privacy-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .privacy-checkbox input[type="checkbox"] {
            margin-top: 2px;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .privacy-checkbox label {
            font-size: 14px;
            color: #495057;
            cursor: pointer;
            line-height: 1.4;
        }

        .privacy-checkbox .terms-link {
            color: #4285f4;
            text-decoration: underline;
            cursor: pointer;
            font-weight: 500;
        }

        .privacy-checkbox .terms-link:hover {
            color: #3367d6;
        }

        .privacy-checkbox.error {
            border-color: #dc3545;
            background: #fff5f5;
        }

        .privacy-error {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
            display: none;
        }

        .privacy-error.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="logo-section">
            <div class="logo-icon">
            </div>
            <div class="logo-text">HIRESWIFT</div>
            <div class="logo-subtitle">Smart Resume Analysis Platform</div>
        </div>

        <div class="form-container">
            <!-- Login Form -->
            <form class="auth-form <?php echo !$show_register ? 'active' : ''; ?>" id="login-form" method="POST" action="Query/login.php">
                <h2 class="form-title">Sign In</h2>
                
                <?php if ($error_message && !$show_register): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-control" placeholder="Enter your email" required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                </div>

                <button type="submit" name="signIn" class="btn">
                    <span class="loading" id="login-loading"></span>
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>

                <div class="form-footer">
                    <p>Don't have an account? <a href="#" id="show-register">Create one here</a></p>
                </div>
            </form>

            <!-- Register Form -->
            <form class="auth-form <?php echo $show_register ? 'active' : ''; ?>" id="register-form" method="POST" action="Query/login.php">
                <h2 class="form-title">Create Account</h2>
                
                <?php if ($error_message && $show_register): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>

                <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label" for="fullname">Full Name</label>
                    <div class="input-wrapper">
                        <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Enter your full name" required>
                        <i class="fas fa-user input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" id="reg-email" name="email" class="form-control" placeholder="Enter your email" required>
                        <i class="fas fa-envelope input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-phone">Phone Number</label>
                    <div class="input-wrapper">
                        <input type="tel" id="reg-phone" name="phone" class="form-control" placeholder="Enter 11-digit phone number" pattern="[0-9]{11}" maxlength="11" required>
                        <i class="fas fa-phone input-icon"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="reg-password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" id="reg-password" name="password" class="form-control" placeholder="Create a strong password" required>
                        <i class="fas fa-lock input-icon"></i>
                    </div>
                    <div class="password-requirements">
                        <strong>Password Requirements:</strong>
                        <ul>
                            <li>At least 8 characters long</li>
                        </ul>
                    </div>
                </div>

                <!-- Data Privacy Checkbox -->
                <div class="privacy-checkbox" id="privacyCheckbox">
                    <input type="checkbox" id="termsCheckbox" name="terms_accepted" required>
                    <label for="termsCheckbox">
                        I have read and agree to the <span class="terms-link" onclick="openTermsModal()">Terms and Conditions</span>
                    </label>
                </div>
                <div class="privacy-error" id="privacyError">
                    Please accept the Terms and Conditions to continue.
                </div>

                <button type="submit" name="signUp" class="btn">
                    <span class="loading" id="register-loading"></span>
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>

                <div class="form-footer">
                    <p>Already have an account? <a href="#" id="show-login">Sign in here</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="terms-modal">
        <div class="terms-modal-content">
            <div class="terms-modal-header">
                <div class="terms-modal-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2>Data Privacy Notice</h2>
            </div>

            <div class="terms-content">
                <p><strong>As an HR personnel/member of Hireswift, you are responsible for handling applicant resumes and personal data securely and ethically.</strong></p>
                
                <h3><i class="fas fa-lock"></i> Confidentiality:</h3>
                <p>Access is restricted to recruitment purposes only.</p>
                
                <h3><i class="fas fa-shield-alt"></i> Data Security:</h3>
                <p>Applicant data must be protected against unauthorized access, sharing, or leaks.</p>
                
                <h3><i class="fas fa-archive"></i> Retention & Deletion:</h3>
                <p>Data should only be stored as necessary and securely deleted when no longer needed.</p>
                
                <h3><i class="fas fa-gavel"></i> Compliance:</h3>
                <p>You must adhere to data protection laws (e.g., GDPR, CCPA) and company policies.</p>
                
                <p style="margin-top: 24px; padding: 16px; background: #e7f3ff; border-radius: 8px; border-left: 4px solid #4285f4;">
                    <strong>By proceeding, you confirm your compliance with this Data Privacy Notice.</strong>
                </p>
            </div>

            <button class="terms-close-btn" onclick="closeTermsModal()">
                <i class="fas fa-check"></i>
                I Understand and Agree
            </button>
        </div>
    </div>

    <script>
        // Terms Modal Functions
        function openTermsModal() {
            document.getElementById('termsModal').classList.add('active');
        }

        function closeTermsModal() {
            document.getElementById('termsModal').classList.remove('active');
            // Check the checkbox when modal is closed
            document.getElementById('termsCheckbox').checked = true;
            validatePrivacyCheckbox();
        }

        // Form switching functionality
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');
        const showRegisterLink = document.getElementById('show-register');
        const showLoginLink = document.getElementById('show-login');

        showRegisterLink.addEventListener('click', function(e) {
            e.preventDefault();
            loginForm.classList.remove('active');
            registerForm.classList.add('active');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.set('show', 'register');
            window.history.pushState({}, '', url);
        });

        showLoginLink.addEventListener('click', function(e) {
            e.preventDefault();
            registerForm.classList.remove('active');
            loginForm.classList.add('active');
            
            // Update URL without page reload
            const url = new URL(window.location);
            url.searchParams.delete('show');
            window.history.pushState({}, '', url);
        });

        // Phone number formatting
        const phoneInput = document.getElementById('reg-phone');
        phoneInput.addEventListener('input', function(e) {
            // Remove all non-digits
            let value = e.target.value.replace(/\D/g, '');
            // Limit to 11 digits
            if (value.length > 11) {
                value = value.slice(0, 11);
            }
            e.target.value = value;
        });

        // Password strength indicator (optional enhancement)
        const passwordInput = document.getElementById('reg-password');
        passwordInput.addEventListener('input', function(e) {
            const password = e.target.value;
            const requirements = document.querySelector('.password-requirements');
            
            if (password.length >= 8) {
                requirements.style.borderLeft = '3px solid #28a745';
            } else {
                requirements.style.borderLeft = '3px solid #dc3545';
            }
        });

        // Privacy checkbox validation
        const termsCheckbox = document.getElementById('termsCheckbox');
        const privacyCheckbox = document.getElementById('privacyCheckbox');
        const privacyError = document.getElementById('privacyError');

        function validatePrivacyCheckbox() {
            if (termsCheckbox.checked) {
                privacyCheckbox.classList.remove('error');
                privacyError.classList.remove('show');
                return true;
            } else {
                privacyCheckbox.classList.add('error');
                privacyError.classList.add('show');
                return false;
            }
        }

        termsCheckbox.addEventListener('change', validatePrivacyCheckbox);

        // Form submission validation
        document.getElementById('register-form').addEventListener('submit', function(e) {
            if (!validatePrivacyCheckbox()) {
                e.preventDefault();
                termsCheckbox.focus();
                return false;
            }
        });

        // Close modal when clicking outside
        document.getElementById('termsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTermsModal();
            }
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    </script>
</body>
</html>