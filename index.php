<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hireswift</title>
    <link rel="stylesheet" href="Index/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="logo">
            <div class="img-logo"></div>
            <h1>HIRESWIFT</h1>
        </div>
        <form autocomplete="off" action="Query/login.php" method="post" id="login-form">
            <h1>Login</h1>
            <div class="input-section">
                <input type="email" id="email" placeholder="Email" name="email" required>
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="input-section">
                <input type="password" id="password" placeholder="Password" name="password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="signup">
                <p>Don't Have an account? <a href="" id="show-register">Register here</a></p>
            </div>
            <button type="submit" class="btn" name="signIn">Login</button>
        </form>
  
        <form autocomplete="off" action="Query/login.php" method="post" id="register-form" style="display: none">
            <h1>Register</h1>
            <div class="input-section">
                <input type="text" id="fullname" placeholder="Full Name" name="fullname" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-section">
                <input type="email" id="reg-email" placeholder="Email" name="email" required>
                <i class="fa-solid fa-envelope"></i>
            </div>
            <div class="input-section">
                <input type="tel" id="reg-phone" placeholder="Phone No. (Ex: 996 420 6912)" name="phone" required pattern="[0-9]{10}">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div class="input-section">
                <input type="password" id="reg-password" placeholder="Password" name="password" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="signup">
                <p>Already have an account? <a href="" id="show-login">Login here</a></p>
            </div>
            <button type="submit" class="btn" name="signUp">Register</button>
        </form>
    </div>
    <script src="Index/login.js"></script>
</body>
</html>