<?php
include_once('connect.php');
session_start();

if(isset($_POST['updateProfile'])){
    $id = (int)$_POST['id'];
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $name = mysqli_real_escape_string($con, trim($_POST['full_name']));
    $phone = mysqli_real_escape_string($con, trim($_POST['phone']));
    $company = mysqli_real_escape_string($con, trim($_POST['company']));

    // Verify user is updating their own profile
    if (!isset($_SESSION['id']) || $_SESSION['id'] != $id) {
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Unauthorized access"));
        exit();
    }

    // Validate input
    if (empty($name) || empty($email)) {
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Name and email are required"));
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Invalid email format"));
        exit();
    }

    if (!empty($phone) && !preg_match('/^[0-9]{11}$/', $phone)) {
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Phone number must be 11 digits; ex (09952224444"));
        exit();
    }

    // Check if email is already taken by another user
    $checkEmailQuery = "SELECT id FROM users WHERE email = '$email' AND id != $id";
    $emailResult = mysqli_query($con, $checkEmailQuery);

    if (mysqli_num_rows($emailResult) > 0) {
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Email is already taken"));
        exit();
    }

    // Check if company name is already taken (if company is being changed and not empty)
    if (!empty($company)) {
        // Get current company for this user
        $currentCompanyQuery = "SELECT company FROM link WHERE user_id = $id";
        $currentCompanyResult = mysqli_query($con, $currentCompanyQuery);
        $currentCompany = '';
        
        if (mysqli_num_rows($currentCompanyResult) > 0) {
            $currentCompanyData = mysqli_fetch_assoc($currentCompanyResult);
            $currentCompany = $currentCompanyData['company'];
        }
        
        // Only check availability if company name is different from current
        if ($company !== $currentCompany) {
            $checkCompanyQuery = "SELECT link_id FROM link WHERE company = '$company' AND user_id != $id";
            $companyResult = mysqli_query($con, $checkCompanyQuery);
            
            if (mysqli_num_rows($companyResult) > 0) {
                header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode("Company name is already taken"));
                exit();
            }
        }
    }

    // Start transaction
    mysqli_begin_transaction($con);

    try {
        // Update user profile
        $updateUserQuery = "UPDATE users SET email='$email', name='$name', phone='$phone' WHERE id=$id";
        
        if (!mysqli_query($con, $updateUserQuery)) {
            throw new Exception("Error updating user profile: " . mysqli_error($con));
        }
        
        // Handle company link
        $linkExistsQuery = "SELECT link_id FROM link WHERE user_id = $id";
        $linkExistsResult = mysqli_query($con, $linkExistsQuery);
        
        if (!empty($company)) {
            if (mysqli_num_rows($linkExistsResult) > 0) {
                // Update existing company link
                $updateCompanyQuery = "UPDATE link SET company = '$company' WHERE user_id = $id";
                if (!mysqli_query($con, $updateCompanyQuery)) {
                    throw new Exception("Error updating company: " . mysqli_error($con));
                }
            } else {
                // Create new company link
                $insertCompanyQuery = "INSERT INTO link (user_id, company) VALUES ($id, '$company')";
                if (!mysqli_query($con, $insertCompanyQuery)) {
                    throw new Exception("Error creating company link: " . mysqli_error($con));
                }
            }
        } else {
            // If company is empty, delete the link if it exists
            if (mysqli_num_rows($linkExistsResult) > 0) {
                $deleteCompanyQuery = "DELETE FROM link WHERE user_id = $id";
                if (!mysqli_query($con, $deleteCompanyQuery)) {
                    throw new Exception("Error removing company link: " . mysqli_error($con));
                }
            }
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        // Update session variables
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['phone'] = $phone;

        header("Location: ../Content/master.php?content=personal-settings&updated=success");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        header("Location: ../Content/master.php?content=personal-settings&error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>
