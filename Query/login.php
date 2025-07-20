<?php
include_once('connect.php');

// Registration handler
if(isset($_POST['signUp'])){
    $fullname = mysqli_real_escape_string($con, trim($_POST['fullname']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($fullname) || empty($email) || empty($phone) || empty($password)) {
        header("Location: ../index.php?show=register&error=" . urlencode("All fields are required"));
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?show=register&error=" . urlencode("Please enter a valid email address"));
        exit();
    }
    
    // Validate phone number (10 digits)
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        header("Location: ../index.php?show=register&error=" . urlencode("Phone number must be exactly 10 digits"));
        exit();
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        header("Location: ../index.php?show=register&error=" . urlencode("Password must be at least 8 characters long"));
        exit();
    }
    
    $password = md5($password);

    // Check if email already exists
    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $con->query($checkEmail);
    
    if($result->num_rows > 0){
        header("Location: ../index.php?show=register&error=" . urlencode("An account with this email already exists"));
        exit();
    } else {
        // Fixed SQL query with proper column specification
        $insertQuery1 = "INSERT INTO users (email, password, name, phone) 
                        VALUES ('$email', '$password', '$fullname', '$phone')";
        
        if ($con->query($insertQuery1) === TRUE) {
            header("Location: ../index.php?show=register&success=" . urlencode("Account created successfully! You can now sign in."));
            exit();
        } else {
            error_log("Registration error: " . $con->error);
            header("Location: ../index.php?show=register&error=" . urlencode("Registration failed. Please try again."));
            exit();
        }
    }
}

// Login handler
if(isset($_POST['signIn'])) {
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        header("Location: ../index.php?error=" . urlencode("Please enter both email and password"));
        exit();
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?error=" . urlencode("Please enter a valid email address"));
        exit();
    }
    
    $password = md5($password); 
    
    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = $con->query($sql);
    
    if($result->num_rows > 0){
        session_start();
        $row = $result->fetch_assoc();
        $_SESSION['email'] = $row['email'];
        $_SESSION['name'] = $row['name'];
        $_SESSION['phone'] = $row['phone'];
        $_SESSION['id'] = $row['id'];
        
        header("Location: ../Content/master.php");
        exit();
    } else {
        header("Location: ../index.php?error=" . urlencode("Invalid email or password. Please check your credentials and try again."));
        exit();
    }
}

echo "TEST";
?>
