<?php
	require 'header.php';

if($adminLevel!=3){
	die(header('location:dashboard.php'));
}

function deleteRecord($userID, $table){
	
	$conn = $GLOBALS['conn'];
	
	$sql = "SELECT Title, Firstname, Surname FROM ".$table." WHERE ".$userID." = :userID";
	if($selectedRecord = $conn->prepare($sql)){
		$selectedRecord->bindParam(":userID", $_GET[$userID]);
		$selectedRecord->execute();
		$row = $selectedRecord->fetch(PDO::FETCH_ASSOC);
		$recordFullName = ($row['Title'].' '.$row['Firstname'].' '.$row['Surname']);
		$sql = "DELETE FROM ".$table." WHERE ".$userID." = :userID";
		if($deletedRecord  = $conn->prepare($sql)){
			$deletedRecord->bindParam(":userID", $_GET[$userID]);
			$deletedRecord->execute();
			$action = ("Deleted record from ".$table." - ".$recordFullName);
			logAction($action);
		echo("<p style='color:limegreen; text-align:center;'><strong>Record deleted!</strong></p>");
		}else{
			//die(print($GLOBALS['error']));
			die("Error: Please try again later.");
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




function deleteLogs(){
	
	$conn = $GLOBALS['conn'];
	
	$sql = "TRUNCATE TABLE Logs";
	if($deleteRecords = $conn->prepare($sql)){
		$deleteRecords->execute();
		echo('<h2 style="color:limegreen; text-align:center;">All logs have been deleted!</h2>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}



echo('<html>
	<head>
	<title>Delete record</title>
	</head>
	<body>');

if(!isset($_GET['type'])){
	die(header('location:dashboard.php'));
}
switch($_GET['type']){
	case "deleteStudentRecord":
		deleteRecord("studentID", "Students");
		break;
	case "deleteParentRecord":
		deleteRecord("parentID", "Parents");
		break;
	case "deleteStaffRecord":
		deleteRecord("staffID", "Staff");
		break;
	case 'logDeletion':
		deleteLogs();
		break;
	default:
		echo("<p style='color:red; text-align:center;'><strong>Error 404: Page not found</strong></p>");
}

	
//Close database connection
$conn = null;
	
echo("<p><a href='dashboard.php'>Return to dashboard</a></p>");



?>