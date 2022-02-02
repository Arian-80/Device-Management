<?php

	require 'header.php';

function updateReport(){
	
	$conn = $GLOBALS['conn'];
	
	if($_COOKIE['replacementCase']=="true"){
		$replacementCase = 1;
	}
	else{
		$replacementCase = 0;
	}
	
	$updateValues = [
		'passcode' => $_POST['passcode'],
		'replacementCase' => $replacementCase,
		'ipadFault' => $_POST['reason'],
		'incidentDate' => $_POST['incidentDate'],
		'incidentLocation' => $_POST['location'],
		'incidentDetails' => $_POST['incidentDetails'],
		'claimID' => $_SESSION['claimID'],
	];
	
	$sql = "UPDATE Claim SET passcode = :passcode, replacementCase = :replacementCase, ipadFault = :ipadFault, incidentDate = :incidentDate, incidentLocation = :incidentLocation, incidentDetails = 		:incidentDetails, parentSignature = 1 WHERE claimID = :claimID";
	if($updateRecord = $conn->prepare($sql)){
		$updateRecord->execute($updateValues);

		$updateValues = [
			'notes' => $_POST['notes'],
			'incidentDate' => $_POST['incidentDate'],
			'reason' => $_POST['reason'],
			'incidentID' => $_SESSION['incidentID'],
		];

		$sql = "UPDATE Incident SET notes = :notes, incidentDate = :incidentDate, reason = :reason WHERE incidentID = :incidentID";
		$updateRecord = $conn->prepare($sql);
		$updateRecord->execute($updateValues);

		$sql = "UPDATE Status SET statusUpdate = 'Awaiting staff review' WHERE incidentID = :incidentID";
		$updateRecord = $conn->prepare($sql);
		$updateRecord->bindParam(':incidentID', $_SESSION['incidentID']);
		$updateRecord->execute();
		
		$action = ("Signed and updated an incident report");
		logAction($action);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
}

function updateStatus(){
	
	$conn = $GLOBALS['conn'];
	
    $reportDetails = "SELECT ipadFault, Incident.incidentDate, incidentLocation, incidentDetails, notes, passcode, replacementCase, serialNumber, iPads.ipadNumber, Firstname, Surname, Email, yearGroup, 		tutorGroup, statusUpdate
	FROM ((((Claim 
    INNER JOIN Incident ON Claim.incidentID = Incident.incidentID)
    INNER JOIN Status ON Incident.incidentID = Status.incidentID)
    INNER JOIN iPads ON iPads.ipadNumber = Incident.ipadNumber)
    INNER JOIN Students ON iPads.studentID = Students.studentID)
    WHERE Claim.incidentID = :incidentID";
	if($results = $conn->prepare($reportDetails)){
		$results->bindParam(':incidentID', $_GET['reportNumber']);
		$results->execute();
		echo('<table style="width:100%" border=1>
		<tr>
			<th>The incident</th>
			<th>Time of incident</th>
			<th>Incident location</th>
			<th>Details of incident</th>
			<th>Additional notes</th>
			<th>iPad passcode</th>
			<th>Replacement case authorised?</th>
			<th>iPad serial number</th>
			<th>Student full name</th>
			<th>Student year & tutor group</th>
			<th>Current status</th>
		</tr>');
		$row = $results->fetch(PDO::FETCH_ASSOC);
		$_SESSION['iPadSerialNumber'] = $row['serialNumber'];
		$_SESSION['ipadNumber'] = $row['ipadNumber'];
		echo('<tr>');
		echo('<td>'.$row['ipadFault'].'</td>');
		echo('<td>'.$row['incidentDate'].'</td>');
		echo('<td>'.$row['incidentLocation'].'</td>');
		echo('<td>'.$row['incidentDetails'].'</td>');
		echo('<td>'.$row['notes'].'</td>');
		echo('<td>'.$row['passcode'].'</td>');
		if($row['replacementCase']==1){
			echo('<td>Yes</td>');
		}else{
			echo('<td>No</td>');
		}
		echo('<td>'.$row['serialNumber'].'</td>');
		echo('<td>'.$row['Firstname'].' '.$row['Surname'].'</td>');
		echo('<td>'.$row['yearGroup'].$row['tutorGroup'].'</td>');
		echo('<td>'.$row['statusUpdate'].'</td>');
		echo('</tr></table><br><br>');
		echo('<form method="post" action="incidentUpdate.php?type=incidentReview">
				<b>Update status:</b><br><input list="status" name="status" value="'.$row['statusUpdate'].'">
				<datalist id="status">
					<option value="Awaiting physical inspection of iPad">
					<option value="Awaiting repair">
					<option value="Awaiting collection">
				</datalist><br>
				<input type="checkbox" name="deviceStatus" value="deactivate"><b>Deactivate this device</b><br>
				<input type="checkbox" name="incidentStatus" value="conclude"><b>Conclude this incident</b><br><br>
				<button type="submit">Submit</button>
			</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}

function updateProcedure(){
	
	$conn = $GLOBALS['conn'];
		
	$updateValues = [
		'status' => $_POST['status'],
		'staffID' => $_SESSION['staffID'],
		'incidentID' => $_SESSION['incidentID'],
	];
	
	$sql = "UPDATE Status SET statusUpdate = :status, staffID = :staffID WHERE incidentID = :incidentID";
	if($incidentUpdate = $conn->prepare($sql)){
		$incidentUpdate->execute($updateValues);

		$concludeIncident = 0;
		$activeDevice = 1;

		if(isset($_POST['incidentStatus'])){
			$concludeIncident = 1;
		}
		if(isset($_POST['deviceStatus'])){
			$activeDevice = 0;
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$sql = "UPDATE Incident SET deleted = '".$concludeIncident."' WHERE incidentID = :incidentID";
	if($updateIncident = $conn->prepare($sql)){
		$updateIncident->bindParam(':incidentID', $_SESSION['incidentID']);
		$updateIncident->execute();
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}

	$sql = "UPDATE iPads SET Active = '".$activeDevice."' WHERE serialNumber = :serialNumber";
	if($updateIncident = $conn->prepare($sql)){
		$updateIncident->bindParam(':serialNumber', $_SESSION['iPadSerialNumber']);
		$updateIncident->execute();
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	
	$updateValues = [
		'requesterDetails' => $_SESSION['Title'].' '.$_SESSION['Surname'],
		'ipadNumber' => $_SESSION['ipadNumber'],
	];
	
	$sql = "UPDATE Allocated SET Requester = :requesterDetails WHERE ipadNumber = :ipadNumber";
	if($updateIncident = $conn->prepare($sql)){
		$updateIncident->execute($updateValues);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$action = "Updated an incident report";
	logAction($action);
	
}


echo('<html>
	<head>
	<title>Incident update</title>
	</head>
	<body>');


if(!empty($_POST)){
	
	if(($_GET['type']=="incidentReview")and($adminLevel==3)){
		updateProcedure();
		die(header('location:dashboard.php?reportUpdated=true'));
	}
	else{
		updateReport();
		die(header('location:studentDashboard.php?type=childIpad'));
	}

}else{
	if($adminLevel!=3){
		die(header('location:dashboard.php'));
	}
	else{
		if(!isset($_GET['reportNumber'])){
			die(header('location:dashboard.php'));
		}
		else if($_GET['reportNumber']==null){
			die(header('location:dashboard.php'));
		}
		else if($_GET['reportNumber']!=$_SESSION['incidentID'.$_GET['reportNumber']]){
			die(header('location:dashboard.php'));
		}
		else{
			$_SESSION['incidentID'] = $_GET['reportNumber'];
			updateStatus();
		}
}
}

	
//Close database connection
$conn = null;
	
echo("<a href='dashboard.php'>Return to dashboard</a>");
?>
