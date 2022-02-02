<?php
	
require 'header.php';

if($_SESSION['userType']=="student"){
	$qry = "SELECT originalDevice, ipadNumber, parentID FROM iPads LEFT JOIN Students on iPads.studentID = Students.studentID LEFT JOIN Parents ON Students.contactParentID = Parents.parentID WHERE 			iPads.studentID = :studentID";
	if($results = $conn->prepare($qry)){
		$results->bindParam(':studentID', $_SESSION['studentID']);
		$results->execute();
		$row = $results->fetch(PDO::FETCH_ASSOC);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}

if($_GET['type']!=$_COOKIE['typeOfData']){
	die(header('location:reportData.php?type='.$_COOKIE['typeOfData']));
}

else if(!isset($_COOKIE['typeOfData'])){
	die(header('location:dashboard.php'));
}



function stolenDevice(){
		echo('
			<form method="post" id="stolenForm" action="reportData.php?type=stolen">
					<b>iPad passcode:</b><br><input type="text" placeholder="E.G 1234" id="passcode" name="passcode" required><br>
					<b>The issue:</b><br><input type="text" placeholder="E.G I lost my iPad" id="reason" name="reason" required><br>
					<b>Date of incident:</b><br><input type="date" id="incidentDate" name="incidentDate" required><br>
					<b>Last known location of the iPad:</b><br><input type="text" placeholder="E.G H02, St Cyres School" id="lastLocation" name="location" required><br><br>
					<b>Details of the loss/theft of the school\'s iPad. Please ALSO give details if you have previously<br>made a claim before for loss, damage or theft of the school\'s iPad:</b><br>							<textarea rows="6" cols="50" id="lossDetails" name="incidentDetails" placeholder="Enter the details here..." required></textarea><br><br>
					<b>Extra notes (optional):</b><br><input type="text" id="notes" name="notes"><br><br>
					<b>I hereby declare that the information provided above is correct to the best of my knowledge:</b><br><input type="checkbox" id="pupilSignature"name="pupilSignature" required><br><br>
					<button type="submit">Submit incident</button>
			</form>
		');
	}


function damagedDevice(){
		echo('
			<form method="post" id="damagedForm" action="reportData.php?type=damaged">
				<b>iPad passcode:</b><br><input type="text" placeholder="E.G 1234" id="passcode" name="passcode" required><br>
				<b>The issue:</b><br><input type="text" placeholder="E.G I dropped my iPad" id="reason" name="reason" required><br>
				<b>Date of incident:</b><br><input type="date" id="incidentDate" name="incidentDate" required><br>
				<b>Address or location where the damage occured:</b><br><input type="text" placeholder="E.G H02, St Cyres School" id="incidentLocation" name="location" required><br><br>
				<b>Please describe the exact circumstances which resulted in the damage to the iPad. If there are signs of multiple damage to the iPad,<br>please ensure that all damage is explained.
				Please describe in as much detail as possible and give details of any witnesses to the damage occurring.</b><br><textarea rows="6" cols="50" id="damageDetails" name="incidentDetails" 						placeholder="Enter the details here..." required></textarea><br><br>
				<b>Extra notes (optional):</b><br><input type="text" id="notes" name="notes"><br><br>
				<input type="checkbox" id="pupilSignature" name="pupilSignature" required><b>I hereby declare that the information provided above is correct to the best of my knowledge.</b><br><br>
				<button type="submit">Submit incident</button>
			</form>
		');
	}


function parentApproval(){
	
	$row = $GLOBALS['row'];
	
	if($GLOBALS['numResults']>0){
		
		echo('<h2>Confirm report details</h2>
			<b>Are the following details correct?:</b><br><br>
			<b>iPad passcode: </b>'.$row["passcode"].'<br>
			<b>Incident: </b>'.$row["ipadFault"].'<br>
			<b>Incident location: </b>'.$row["incidentLocation"].'<br>
			<b>Incident date: </b>'.$row["incidentDate"].'<br>
			<b>Incident details: </b>'.$row["incidentDetails"].'<br><br>
			<input type="checkbox" id="replacementCase" name="replacementCase" onclick="replacementCase()"><b>Please allow my child to purchase a replacement case from Finance Department.</b><br><br>
			<button id="correct" onclick="reportDetails(correct)">These details are correct and I hereby sign this report to be sent off.</button>
			<button id="incorrect" onclick="reportDetails(incorrect)">These details are incorrect. Amend the details.</button>

			<script>
			document.cookie = "replacementCase=false; path=/";
			function replacementCase(){
				var replacementCaseCheck = document.getElementById("replacementCase");
				if(replacementCaseCheck.checked==true){
					document.cookie = "replacementCase=true; path=/";
				}
			}
			function reportDetails(status){
				if (status==correct){
					document.cookie = "parentApproval=true; path=/";
				}
				else{
					document.cookie = "parentApproval=false; path=/";
				}
				location.reload();
			}
			</script>');
		
	}else{
		echo("<h2>Page not found.</h2>");
	}
}

function changeRecord(){
	
	$row = $GLOBALS['row'];
		echo('
			<form method="post" action="incidentUpdate.php">
				<b>iPad passcode: </b><br><input type="text" id="passcode" name="passcode" value="'.$row['passcode'].'" required><br>
				<b>The issue: </b><br><input type="text" id="reason" name="reason" value="'.$row['ipadFault'].'" required><br>
				<b>Date of incident: </b><br><input type="date" id="incidentDate" name="incidentDate" value="'.$row['incidentDate'].'" required><br>
				<b>Address or location of incident: </b><br><input type="text" id="location" name="location" value="'.$row['incidentLocation'].'" required><br><br>
				<b>Detailed explanation of the incident:</b><br><textarea rows="6" cols="50" id="incidentDetails" name="incidentDetails" required>'.$row['incidentDetails'].'</textarea><br><br>
				<b>Extra notes (optional):</b><br><input type="text" id="notes" name="notes" value="'.$row['notes'].'" required><br><br>
				<input type="checkbox" id="parentSignature" name="parentSignature" required><b>I hereby declare that the information provided above is correct to the best of my knowledge and sign this 					report to be sent off.</b><br><br>
				<button type="submit">Submit report</button>
			</form>
		');
}


echo('<html>
	<head>
	<title>Incident data input</title>
	</head>
	<body>');


if(empty($_POST)){
	
	switch($_GET['type']){
			
		case 'stolen':
			
			if($_SESSION['userType']!="student"){
				die(header('location:dashboard.php'));
			}
			stolenDevice();
			break;
			
		case 'damaged':
		
			if($_SESSION['userType']!="student"){
				die(header('location:dashboard.php'));
			}
			damagedDevice();
			break;
	
		case 'parentSignature':
		
			if($_SESSION['userType']!="parent"){
				die(header('location:dashboard.php'));
			}
		
			$claimID = $_SESSION['claimID'.$_GET['reportNumber']];
			$_SESSION['claimID'] = $claimID;
			$reportDetails = "SELECT * FROM Claim INNER JOIN Incident ON Claim.incidentID = Incident.incidentID WHERE claimID = :claimID";
			if($results = $conn->prepare($reportDetails)){
				$results->bindParam(':claimID', $claimID);
				$results->execute();
				$row = $results->fetch(PDO::FETCH_ASSOC);
				$numResults = $results->rowCount();
				$_SESSION['incidentID'] = $row['incidentID'];
			}else{
				//die(print($GLOBALS['error']));
				die("Error: Please try again later.");
			}

		
			if(!isset($_GET['reportNumber'])){
				die(header('location:dashboard.php'));
			}

			else if($_GET['reportNumber']==null){
				die(header('location.dashboard.php'));
			}

			if(isset($_COOKIE['parentApproval'])){

				if($_COOKIE['replacementCase']=="true"){
					$replacementCase = 1;
				}
				else{
					$replacementCase = 0;
				}

				if($_COOKIE['parentApproval']=="true"){
					$qry = "UPDATE `Claim` SET `parentSignature` = '1' , `replacementCase` = '".$replacementCase."' WHERE `claimID` = :claimID";
					if($updateSignature = $conn->prepare($qry)){
						$updateSignature->bindParam(':claimID', $claimID);
						$updateSignature->execute();
					}else{
						//die(print($GLOBALS['error']));
						die("Error: Please try again later.");
					}
					
					$qry = "UPDATE `Status` SET `statusUpdate` = 'Awaiting staff review' WHERE `incidentID` = :incidentID";
					if($updateStatus = $conn->prepare($qry)){
						$updateStatus->bindParam(':incidentID', $row['incidentID']);
						$updateStatus->execute();
					}else{
						//die(print($GLOBALS['error']));
						die("Error: Please try again later.");
					}
					die(header('location:studentDashboard.php?type=childIpad'));
					
					$action = "Updated an incident report";
					logAction($action);
				}
				else{
					changeRecord();
				}
			}
			else{
				parentApproval();
			}
			break;
		default:
			die(header('location:dashboard.php'));
	}
}

else{
	
	$originalDevice = $row['originalDevice'];
	$ipadNumber = $row['ipadNumber'];
	$parentID = $row['parentID'];
	$passcode = str_replace("'", "&#39;", $_POST['passcode']);
	$reason =  str_replace("'","&#39;",$_POST['reason']);
	$location = str_replace("'","&#39;",$_POST['location']);
	$incidentDetails = str_replace("'", "&#39;", $_POST['incidentDetails']);
	$notes =  str_replace("'","&#39;",$_POST['notes']);
	$date = $_POST['incidentDate'];
	
	
	if($_POST['pupilSignature']){
		$pupilSignature=1;
	}
	
	else{
		$pupilSignature=0;
	}
	
	$recordValues = [
		'date' => $_POST['date'],
		'ipadNumber' => $ipadNumber,
		'reason' => $_POST['reason'],
		'notes' => $_POST['notes'],
	];
	
	$sql = "INSERT INTO Incident (incidentDate, ipadNumber, reason, notes) VALUES (:date, :ipadNumber, :reason, :notes)";
	if($insertRecord = $conn->prepare($sql)){
		$insertRecord->execute($recordValues)
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	
	$recordValues = [
		'ipadNumber' => $ipadNumber,
		'studentID' => $_SESSION['studentID'],
	];
	
	$sql = "INSERT INTO Allocated (ipadNumber, studentID) VALUES (:ipadNumber, :studentID)";
	if($insertRecord = $conn->prepare($sql)){
		$insertRecord->execute($recordValues)
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$qry = "SELECT incidentID FROM Incident INNER JOIN iPads ON Incident.ipadNumber = iPads.ipadNumber INNER JOIN Students ON iPads.studentID = Students.studentID WHERE Students.Email = :username";
	if($insertRecord = $conn->prepare($sql)){
		$insertRecord->bindParam(':username', $_SESSION['username']);
		$insertRecord->execute($recordValues)
		$row = $results->fetch(PDO::FETCH_ASSOC);
		$incidentID = $row['incidentID'];
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	

	
	$recordValues = [
		'incidentID' => $incidentID,
		'parentID' => $parentID,
		'passcode' => $_POST['passcode'],
		'date' => $_POST['incidentDate'],
		'location' => $_POST['location'],
		'reason' => $_POST['reason'],
		'incidentDetails' => $_POST['incidentDetails'],
		'pupilSignature' => $pupilSignature,
		'originalDevice' => $originalDevice,
	];
	
	$sql = "INSERT INTO Claim (incidentID, parentID, passcode, incidentDate, incidentLocation, ipadFault, incidentDetails, pupilSignature, originalDevice) VALUES (:incidentID, :parentID, :passcode, :date,	:location, :reason, :incidentDetails, :pupilSignature, :originalDevice)";
	if($insertRecord = $conn->prepare($sql)){
		$insertRecord->execute($recordValues);
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	
	$sql = "INSERT INTO `Status` (`incidentID`, `statusUpdate`) VALUES (:incidentID, 'Awaiting parent signature')";
	if($insertRecord = $conn->prepare($sql)){
		$insertRecord->bindParam(':incidentID', $incidentID);
		$insertRecord->execute();
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$action = "Submitted an incident report";
	logAction($action);
	setcookie("typeOfData", "", 1, "/");
	die(header('location:dashboard.php?type=student&reported=true'));
	
}
	
//Close database connection
$conn = null;

echo("<p><a href='dashboard.php'>Return to dashboard</a></p>");


?>