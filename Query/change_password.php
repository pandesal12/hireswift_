<?php
include_once('connect.php');
if(isset($_POST['changePassword'])){
    $email =$_POST['email'];
	$password=$_POST['new_password'];
	$password=md5($password);

		// $checkEmail="SELECT * From users where email='$email'";
		// $result=$con->query($checkEmail);
        $updateQuery1 = "UPDATE `users` SET `password`='$password' WHERE email='$email';";
        
        if ($con->query($updateQuery1) === TRUE) {
            header("Location: ../Content/master.php?content=personal-settings");
            exit();
        } else {
            echo "Error Updating Password: " . $con->error;
        }
	

}
?> 