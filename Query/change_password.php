<?php
include_once('connect.php');
session_start();

if(isset($_POST['changePassword'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $userId = (int)$_POST['user_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Verify user is changing their own password
    if (!isset($_SESSION['id']) || $_SESSION['id'] != $userId) {
        header("Location: ../Content/master.php?content=personal-settings&password=error&msg=" . urlencode("Unauthorized access"));
        exit();
    }

    // Validate passwords match
    if ($newPassword !== $confirmPassword) {
        header("Location: ../Content/master.php?content=personal-settings&password=error&msg=" . urlencode("New passwords do not match"));
        exit();
    }

    // Validate password strength
    if (strlen($newPassword) < 8) {
        header("Location: ../Content/master.php?content=personal-settings&password=error&msg=" . urlencode("Password must be at least 8 characters"));
        exit();
    }

    $hashedCurrentPassword = md5($currentPassword);
    $hashedNewPassword = md5($newPassword);

    // Verify current password
    $verifyQuery = "SELECT id FROM users WHERE id = $userId AND password = '$hashedCurrentPassword'";
    $verifyResult = mysqli_query($con, $verifyQuery);

    if (mysqli_num_rows($verifyResult) === 0) {
        header("Location: ../Content/master.php?content=personal-settings&password=error&msg=" . urlencode("Current password is incorrect"));
        exit();
    }

    // Update password
    $updateQuery = "UPDATE users SET password='$hashedNewPassword' WHERE id=$userId";
    
    if (mysqli_query($con, $updateQuery)) {
        header("Location: ../Content/master.php?content=personal-settings&password=success");
        exit();
    } else {
        header("Location: ../Content/master.php?content=personal-settings&password=error&msg=" . urlencode("Error updating password"));
        exit();
    }
}
?>
