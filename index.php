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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HireSwift - Smart Resume Analysis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css">
    <link rel="stylesheet" href="Index/style.css">
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
                <h2 class="form-title">Welcome Back</h2>
                
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

                <!-- <?php if ($success_message): ?> -->
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
                <!-- <?php endif; ?> -->

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
                        <input type="tel" id="reg-phone" name="phone" class="form-control" placeholder="Enter 10-digit phone number" pattern="[0-9]{10}" required>
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

    <script>
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
            // Limit to 10 digits
            if (value.length > 10) {
                value = value.slice(0, 10);
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

        Auto-hide alerts after 5 seconds
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
