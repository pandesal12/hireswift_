<?php
include_once('connect.php');
if(isset($_POST['updateProfile'])){
    $id =$_POST['id'];
    $email =$_POST['email'];
    $name =$_POST['full_name'];
    $phone =$_POST['phone'];
    // $company =$_POST['company'];

		// $checkEmail="SELECT * From users where email='$email'";
		// $result=$con->query($checkEmail);
        $updateQuery1 = "UPDATE `users` SET `email`='$email', `name`='$name', `phone`='$phone' WHERE id=$id;";
        
        //TODO: Add company duplicate check
        
        if ($con->query($updateQuery1) === TRUE) {
            session_start();
            $_SESSION['email']=$email;
            $_SESSION['name']=$name;
            $_SESSION['phone']=$phone;

            header("Location: ../Content/master.php?content=personal-settings");
            exit();
        } else {
            echo "Error Updating Password: " . $con->error;
        }
	

}
?> 