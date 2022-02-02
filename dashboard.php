<?php
require 'header.php';


//print_r($_COOKIE);
//die();


/*
In phpMyAdmin, click on the table you want to reset or change the AUTO_INCREMENT value
Click on the Operations Tab
In the Table Options box find the auto_increment field.
Enter the new auto_increment starting value
Click on the Go button for the Table Options box
*/



if(!isset($_SESSION['userType'])){
	header('location:logout.php');
	die(header('location:indextest.php'));
}

/*if((!isset($_GET['type']))or($_GET['type']!=$_SESSION['userType'])){
	header('location:dashboard.php?type='.$_SESSION['userType']);
	die();
}*/



$notificationBar = '';

function displayParentNotifications(){
	
	$conn = $GLOBALS['conn'];
	$parentID = $_SESSION['parentID'];
	
	
	$sql = "SELECT * FROM Students INNER JOIN Parents ON Students.contactParentID = Parents.parentID INNER JOIN iPads ON Students.studentID = iPads.studentID INNER JOIN Incident ON iPads.ipadNUmber = Incident.ipadNumber INNER JOIN Claim ON Incident.incidentID = Claim.incidentID WHERE parentSignature=0 AND parentID = :parentID";
	if($results = $conn->prepare($sql)){
		$results->bindParam(':parentID', $parentID);
		$results->execute();
		$numResults = $results->rowCount();
		if($numResults>0){
			$GLOBALS['notificationBar']='<p style="text-align: center; color: #8B0000"><strong>You have pending incidents to review! Please visit the child iPad incident page.</strong></p>';
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}


function displayStaffNotifications(){
	
	$conn = $GLOBALS['conn'];
	
	$sql = "SELECT * FROM Status WHERE statusUpdate = 'Awaiting staff review'";
	if($results = $conn->prepare($sql)){
		$results->execute();
		$numResults = $results->rowCount();
		if($numResults>0){
			$GLOBALS['notificationBar']='<p style="text-align: center; color: #8B0000"><strong>You have pending incidents to review! Please visit the incident review page.</strong></p>';
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




function studentOptions(){
	
	$conn = $GLOBALS['conn'];
	$email = $_SESSION['username'];
	
	$qry = "SELECT * FROM Incident INNER JOIN iPads ON Incident.ipadNumber = iPads.ipadNumber INNER JOIN Students ON iPads.studentID = Students.studentID WHERE Students.Email = :email AND deleted=0";
	if($results = $conn->prepare($qry)){
		$results->bindParam(':email', $email);
		$results->execute();
		$numResults = $results->rowCount();

		//Close database connection as early as possible
		$conn = null;
		if($numResults>0){
			$_SESSION['activeIncident']=true;
		}
		else{
			unset($_SESSION['activeIncident']);
			echo('<p><a href="studentDashboard.php?type=report">Report an incident</a></p>');
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	echo('<p><a href="studentDashboard.php?type=myipad">Review my reports</a></p>');
}



function staffOptions(){

	$adminLevel = $GLOBALS['adminLevel'];

	echo('
	<p><a href="recordReview.php?type=ipadReview">iPad review</a></p>
	<p><a href="recordReview.php?type=studentIncidentReview">Student iPad and incident review</a></p>
	<p><a href="recordReview.php?type=studentProfileReview">Student profile review</a></p>');

	if($adminLevel>1){
		echo('
		<p><a href="recordReview.php?type=parentProfileReview">Parent profile review</a></p>
		<p><a href="createAccount.php?type=student">Create Student Account</a></p>
		<p><a href="createAccount.php?type=parent">Create Parent Account</a></p>');
	}

	if($adminLevel==3){
		echo('
			<p><a href="createAccount.php?type=staff">Create Staff Account</a></p>
			<p><a href="recordReview.php?type=staffProfileReview">Staff profile review</a></p>
			<p><a href="searchOutput.php?type=activeIncidents">Review active incidents</a></p>
			<p><a href="iPadAssignment.php">Add a new iPad</a></p>
			<p><a href="searchOutput.php?type=fullReportLog">Review all incidents</a></p>
			<p><a href="searchOutput.php?type=deviceNeeded">List of students without iPads</a></p>
			<p><a href="searchOutput.php?type=fullLogs">Review full activity logs</a></p>');
			
		displayStaffNotifications();
	}
}



function userOptions(){
	
	$userType = $_SESSION['userType'];
	
	switch($userType){
			
		case 'student':
			studentOptions();
			break;
			
		case 'parent':
			
			displayParentNotifications();
			echo("<p><a href='studentDashboard.php?type=childIpad'>Child iPad - Active incidents</a></p>");
			break;
			
		case 'staff':
			staffOptions();
			break;
			
		default:
			$_SESSION = [];
			die(header('location:indextest.php'));
			
	}
	
}

/*function displayStudentNotifications(){
	
	$conn = $GLOBALS['conn'];
	$triggerNotification = "CREATE TRIGGER `notifyStudent` AFTER UPDATE ON `Status` FOR EACH ROW INSERT INTO `Notifications` (`studentID`, `notification`) VALUES (`1`, `asfasf`)";
	print($triggerNotification);
	$conn->query($triggerNotification);
}

displayStudentNotifications();*/

/*if(($_SESSION['userType']=="staff")and($adminLevel==3)){
	displayStaffNotifications();
}
else if($_SESSION['userType']=="parent"){
	displayParentNotifications();
}*/



// Main code


echo(
	'<html>
		<head>
			<title>Dashboard</title>
		</head>
		<body>
			<h1>Dashboard</h1>'.$notificationBar.'
			<h3>Welcome '.$name.'</h3>');


if(isset($_GET['reported'])){
	echo("<h3>Report submitted!</h3>");
}


if(isset($_GET['reportUpdated'])){
	echo("<b><p style='color:limegreen';>Report updated!</p></b>");
}

userOptions();


echo('<p><a href="logout.php">Log out</a></p>
	</body>
</html>');

$conn = null;
?>