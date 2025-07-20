<?php
include_once('connect.php');
if(isset($_POST['signUp'])){
	$fullname=$_POST['fullname'];
	$email=$_POST['email'];
	$phone=$_POST['phone'];
	$password=$_POST['password'];
	$password=md5($password);

		$checkEmail="SELECT * From users where email='$email'";
		$result=$con->query($checkEmail);
		if($result->num_rows>0){
			// header("Location: ../Pages/login_page.php?error_register");
			echo 'Error Register [Email already exist] Temporary Page';
			exit();
		} else {
		$insertQuery1 = "INSERT INTO users VALUES ('$email', '$password', '$fullname', '$phone');";
		if ($con->query($insertQuery1) === TRUE) {
			header("Location: ../index.php");
			exit();
		} else {
			echo "Error Signing Up: " . $con->error;
		}
	}
	

}

if(isset($_POST['signIn'])) {
	$email=$_POST['email'];
	$password=$_POST['password'];
	$password=md5($password); 
	
	$sql="SELECT * FROM users WHERE email = '$email' AND password = '$password'";
	$result=$con->query($sql);
	if($result->num_rows>0){
		session_start();
		$row=$result->fetch_assoc();
		$_SESSION['email']=$row['email'];
		$_SESSION['name']=$row['name'];
		$_SESSION['phone']=$row['phone'];
		$_SESSION['id']=$row['id'];
		// $_SESSION['company']=$row['company'];
		header("Location: ../Content/master.php");
		exit();


		// $_SESSION['isAdmin']='0';
		//Include this once admin is implemented

		// if ($row['isAdmin'] == 1) {
		// 	$_SESSION['isAdmin']='1';
		// 	header("Location: ../Content/master.php");
		// 	exit();
		// } else {
		// 	// header("Location: ../Customer/services_page.php");
		// 	echo 'Temporary Customer Page'
		// 	exit();
		// }
		
	} else {
		// header("Location: ../Pages/login_page.php?error_login=1");
		echo 'Temporary Invalid Account';
		exit();
		
	}
	
	}
?> 




