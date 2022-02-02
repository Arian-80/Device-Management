<?php

session_start();

function databaseConnection(){
	
	global $conn;

	$username = "arian";
	$password = "arian";
	$dsn = "mysql:host=localhost;dbname=ipad";

	// Create connection PDO
	try{
		$conn = new PDO($dsn, $username, $password);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e){
		die("Connection failed: ".$e->getMessage());
	}
}




function insertEmail(){
	
	$conn = $GLOBALS['conn'];
	
	echo('<form method="post" action="resetPassword.php?type=sendToken">
			<b>Email:</b><br><input type="text" id="username" name="username" required><br><br>
			<button type="submit">Submit</button>
		</form>');
	
}




function generateToken($length = 8){
	
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomToken = '';
	for($i = 0; $i < $length; $i++){
		$randomToken .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomToken;
}


function insertToken($randomToken){
	
	$conn = $GLOBALS['conn'];
	$sql = "SELECT tokenID FROM Tokens WHERE token = :generatedToken";
	if($selectedResults = $conn->prepare($sql)){
		$selectedResults->bindParam(':generatedToken', $randomToken);
		$selectedResults->execute();
		$numResults = $selectedResults->rowCount();
		if($numResults>0){
			generateToken();
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$recordValues = [
		':token' => $randomToken,
		':userEmail' => $_POST['username'],
	];
	
	$sql = "INSERT INTO Tokens (token, userEmail) VALUES (:token, :userEmail)";
	if($insertedRecord = $conn->prepare($sql)){
		$insertedRecord->execute($recordValues);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	return $randomToken;
}





function SQL_SelectRecord($sqlQry){
		
	$conn = $GLOBALS['conn'];
	
	global $numResults;
	global $selectedRecord;
	
	$selectedRecord = $conn->prepare($sqlQry);
	$selectedRecord->bindParam(':email', $_SESSION['email']);
	$selectedRecord->execute();
	$numResults = $selectedRecord->rowCount();

}





function sendToken(){
	
	$_SESSION['email'] = $_POST['username'];

	$sqlQry = "SELECT studentID FROM Students WHERE Email = :email";
	SQL_SelectRecord($sqlQry);
	$numResults = $GLOBALS['numResults'];
	$userID = "studentID";
	$userGroup = "Students";
	if($numResults<1){
		$sqlQry = "SELECT parentID FROM Parents WHERE Email = :email";
		SQL_SelectRecord($sqlQry);
		$numResults = $GLOBALS['numResults'];
		$userID = "parentID";
		$userGroup = "Parents";
		if($numResults<1){
			$sqlQry = "SELECT staffID FROM Staff WHERE Email = :email";
			SQL_SelectRecord($sqlQry);
			$numResults = $GLOBALS['numResults'];
			$userID = "staffID";
			$userGroup = "Staff";
			if($numResults<1){
				die("<a href='indextest.php'>There is no user registered with the provided email. Click here to log in.</a>");
			}
		}
	}
	$selectedRecord = $GLOBALS['selectedRecord'];
	
	$row = $selectedRecord->fetch(PDO::FETCH_ASSOC);
	$_SESSION['userID'] = $row[$userID];
	$_SESSION['userGroup'] = $userGroup;
	$_SESSION['ID_Type'] = $userID;
	
		
	$generatedToken = insertToken(generateToken());
	
	$to = $_POST['username'];
	$subject = "Reset password request";
	$message = ("Hello. A request to reset your password has been sent; please click on the link below in order to
	reset your password. If you did not request this, you can safely ignore this email.\r\n
	<a href='resetPassword.php?type=inputPassword&token=".$generatedToken."'>Reset password</a>");
	print($message);
	
	$headers = "From: noreply@stcyres.org\r\n";
	$headers .= "Reply-To: noreply@stcyres.org\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	//$to = "usertest@goorm.io";

	// apt-get purge sendmail*  TO REMOVE SENDMAIL CONFIG
	// NO DATA SENT WHEN MAIL FUNCTION IS ACTIVE: COMMENT THE FOLLOWING FEW LINES TO MAKE THE PROGRAM WORK.
	// $result = mail($to, $subject, $message, $headers);
	
		
	if(!$result){
		echo("<br>An email has been sent to you, please follow the instructions in order to reset your password.<br>If you can not locate the email in your inbox, please search through your spam/junk folder.");
	}
	else{
		die("Error: Request not processed");
	}
}




function inputPassword(){
	
	$conn = $GLOBALS['conn'];
	$generatedToken = $_GET['token'];
	$currentTime = time();
	
	$sql = "SELECT * FROM Tokens WHERE token = :token";
	if($selectedResults = $conn->prepare($sql)){
		$selectedResults->bindParam(':token', $generatedToken);
		$selectedResults->execute();
		$numResults = $selectedResults->rowCount();
		if($numResults<1){
			die("<a href='indextest.php'>This token does not exist. Click here to log in.</a>");
		}
		$row = $selectedResults->fetch(PDO::FETCH_ASSOC);
		$tokenTime = strtotime($row['tokenTime']);
		if((($currentTime - $tokenTime)>86400)or($row['used']!=0)){
			die("<a href='indextest.php'>This token no longer exists. Click here to log in.</a>");
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	echo('<h2 style="text-align:center;">Reset password</h2>');
	
	echo('<form method="post" action="resetPassword.php?type=resetPassword&token='.$generatedToken.'">
		<b>New password (include at least 8 characters long, 1 number, 1 lowercase and 1 highercase letter):</b><br><input type="text" name="password" id="password" minlength="8" maxlength="255" onkeyup="checkPassword()" required><br>
		<b>Confirm password:</b><br><input type="text" name="confirmPassword" id="confirmPassword" onkeyup="checkPassword()" required>
		<p style="color:red;" id="noMatch"><i>Passwords do not match!</i></p><br><br>
		<p style="color:red;" id="requirementsNotMet"><i>Your password does not meet the requirements!</i></p><br><br>
		<button type="submit" id="resetButton">Reset password</button>
		</form>
		<script>
		var passwordInput = document.getElementById("password");
		var confirmPassword = document.getElementById("confirmPassword");
		var resetButton = document.getElementById("resetButton");
		var noMatchText = document.getElementById("noMatch");
		var requirementText = document.getElementById("requirementsNotMet");
		var passwordValidation = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,255}$/;
		
		noMatchText.style.display = "none";
		requirementText.style.display = "none";
		
		function checkPassword(){
			noMatchText.style.display = "none";
			requirementText.style.display = "none";
			
			if(passwordInput.value!=confirmPassword.value){
				noMatchText.style.display = "block";
				resetButton.disabled = true;
			}
			else if(!passwordInput.value.match(passwordValidation)){
				requirementText.style.display = "block";
				resetButton.disabled = true;
			}
			else{
				resetButton.disabled = false;
			}
		}
		</script>
		');
}





function resetPassword(){
	
	$conn = $GLOBALS['conn'];

	$password = $_POST['password'];
	
	if ((strlen($password)<8)or(!preg_match_all('/([A-Z]|[a-z]|[0-9])/', $password))){
		die(header('location:resetPassword.php?type=inputPassword'));
	}
	
	$sql = "UPDATE Tokens SET used = 1 WHERE Token = :token";
	if($updatedToken = $conn->prepare($sql)){
		$updatedToken->bindParam(':token', $_GET['token']);
		$updatedToken->execute();
		
		$generatedHash = password_hash($password, PASSWORD_DEFAULT, ['cost'=>12]);
		$table = $_SESSION['userGroup'];
		$ID_Type = $_SESSION['ID_Type'];
		
		$recordValues = [
			':password' => $generatedHash,
			':userID' => $_SESSION['userID'],
		];
		
		$sqlQry = "UPDATE ".$table." SET Password = :password WHERE ".$ID_Type." = :userID";
		$updatedRecord = $conn->prepare($sqlQry);
		$updatedRecord->execute($recordValues);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	echo('<p style="color:limegreen; text-align:center;"><strong>Password reset!</strong></p>');
	
}

databaseConnection();

echo('<html>
	<head>
	<title>Reset password</title>
	</head>
	<body>');


switch($_GET['type']){
	case 'insertEmail':
		insertEmail();
		break;
	case 'sendToken':
		sendToken();
		break;
	case 'inputPassword':
		inputPassword();
		break;
	case 'resetPassword':
		resetPassword();
		break;
	default:
		die(header('location:logout.php'));
}

echo('<p><a href="indextest.php">Click here to log in</a></p>');

?>