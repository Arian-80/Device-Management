<?php
	require 'header.php';
// Ensure the user is meant to have access to this page, otherwise kick them back to the dashboard page.
if($adminLevel<2){
	header('location:dashboard.php');
	die();
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

$generatedPassword = generateRandomString();

$passlength = ['cost'=>12];
$hash = password_hash($generatedPassword, PASSWORD_DEFAULT, $passlength);

function emailMessage($to,$subject,$message){

	$headers = "From: noreply@stcyres.org\r\n";
	$headers .= "Reply-To: noreply@stcyres.org\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	//$to = "usertest@goorm.io";

	// apt-get purge sendmail*  TO REMOVE SENDMAIL CONFIG
	// NO DATA SENT WHEN MAIL FUNCTION IS ACTIVE: COMMENT THE FOLLOWING FEW LINES TO MAKE THE PROGRAM WORK.
	// $result = mail($to, $subject, $message, $headers);
	
	//echo($to."<br>".$subject."<br>".$message."<br>".$result."<br>");
	
	if(!$result){
		echo("Message accepted");
	}
	else{
		echo("Error: Message not accepted");
	}

}


function addRecord($sqlQry, $recordFields, $userType){
	
	global $firstname;
	global $surname;
	
	$conn = $GLOBALS['conn'];
	$hash = $GLOBALS['hash'];
	$firstname = $_POST['fname'];
	$surname = $_POST['sname'];

	$data = array($_POST['title'], $firstname, $surname, $_POST['email'], $hash);
	foreach ($recordFields as $arrayValue){
		array_push($data, $arrayValue);
	}
	
	if($insertRecord = $conn->prepare($sqlQry)){
		$insertRecord->execute($data);
		array_splice($data, 3);
		$recordValues = implode(" ", $data);
		$action = "Created a ".$userType." profile - ".$recordValues;
		logAction($action);
	}
	else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}


echo(
	'<html>
		<head>
			<title>New Record</title>
		</head>
		<body>');



if (!empty($_POST)){
	
	switch ($_GET['type']){
			
		case 'staff':
			
			if($adminLevel<3){
				die(header('location:dashboard.php'));
			}
			$sqlQry = "INSERT INTO Staff (Title, Firstname, Surname, Email, Password, adminLevel) VALUES (:title, :firstname, :surname, :email, :password, :adminLevel)";
			// $recordFields is anything after Password, as every procedure has the first 5 elements in that order.
			$recordFields = [
				':adminLevel' => $_POST['alevel'],
			];
			
			addRecord($sqlQry, $recordFields, "staff");
			break;
			
		case 'student':
			
			$sqlQry = "INSERT INTO Students (Title, Firstname, Surname, Email, Password, yearGroup, tutorGroup) VALUES (:title, :firstname, :surname, :email, :password, :yearGroup, :tutorGroup)";
			$recordFields = [
				':yearGroup' => $_POST['ygroup'], 
				':tutorGroup' => $_POST['tgroup'], 
			];
			
			addRecord($sqlQry, $recordFields, "student");
			break;

		case 'parent':	
			
			$sqlQry = "INSERT INTO Parents (Title, Firstname, Surname, Email, Password, contactNumber) VALUES (:title, :fName, :sName, :email, :password, :cNumber)";
			$recordFields = [
				':cNumber' => $_POST['cnumber'],
			];
			
			addRecord($sqlQry, $recordFields, "parent");
			
			$sqlQry = "SELECT parentID FROM Parents WHERE Email = :email";
			if($selectRecord = $conn->prepare($sqlQry)){
				$selectRecord->bindParam(':email', $_POST['email']);
				$selectRecord->execute();
				$row = $selectRecord->fetch(PDO::FETCH_ASSOC);
				$parentID = $row['parentID'];
				
				$recordValues = [
					':contactParentID' => $parentID,
					':studentID' => $_POST['studentID'],
				];
				
				$sqlQry = "UPDATE Students SET contactParentID = :contactParentID WHERE studentID = :studentID";
				if($updateRecord = $conn->prepare($sqlQry)){
					$updateRecord->execute($recordValues);
				}else{
					//die(print($GLOBALS['error']));
					die("Error: Please try again later.");
				}
			}else{
				//die(print($GLOBALS['error']));
				die("Error: Please try again later.");
			}
			break;
		default:
			die(header('location:dashboard.php'));
	}
	//Close database connection as early as possible
	$conn = null;
	
	$message = 'Dear '.$_POST['title'].' '.$surname.'.<br>Your account has successfully been set up!<br>These are your credentials:<br><br>Username: '.$_POST['email'].'<br>Password: '.$generatedPassword.'<br>';
	$to = $_POST['email'];
	$subject = "St Cyres account sign-up";
	emailMessage($to,$subject,$message);
	print($generatedPassword);
	
	echo("<h2><p style='color:green; text-align:center;'>Record added!</p></h2>");
	echo("<p><a href='dashboard.php'>Return to Dashboard</a></p>");
}

?>