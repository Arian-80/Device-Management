<?php

require 'header.php';



function studentIpadReview(){
	
	$conn = $GLOBALS['conn'];
	$userEmail = $GLOBALS['userEmail'];
	
	if($_SESSION['userType']!="student"){
	die(header('location:dashboard.php'));
	}
	
	
	//$sql = "SELECT Model, incidentDate, reason, statusUpdate, Staff.Title, deleted FROM Students INNER JOIN iPads ON Students.studentID=iPads.studentID LEFT JOIN Incident ON iPads.ipadNumber = 				Incident.ipadNumber INNER JOIN Status ON Incident.incidentID = Status.incidentID INNER JOIN Staff ON Status.staffID = Staff.staffID WHERE Students.Email ='".$_SESSION['username']."'";

	$sql = "SELECT Model, incidentDate, reason, statusUpdate, Staff.Title, deleted FROM Students INNER JOIN iPads ON Students.studentID=iPads.studentID LEFT JOIN Incident ON iPads.ipadNumber = 				Incident.ipadNumber INNER JOIN Status ON Incident.incidentID = Status.incidentID INNER JOIN Staff ON Status.staffID = Staff.staffID WHERE Students.Email = :email";
	if($results = $conn->prepare($sql)){
		$results->bindParam(':email', $userEmail);
		$results->execute();
		$numResults = $results->rowCount();
		if ($numResults>0){	
			echo('<table style="width:100%" border=1>
			<tr>
				<th>iPad Model</th>
				<th>Date of incident</th>
				<th>Reason of incident</th>
				<th>Status of iPad</th>
				<th>Staff in charge of this process</th>
				<th>Concluded?</th>
			</tr>');
			while($row = $results->fetch(PDO::FETCH_ASSOC)){
				echo('<tr>');
				echo('<td>'.$row['Model'].'</td>');
				echo('<td>'.$row['incidentDate'].'</td>');
				echo('<td>'.$row['reason'].'</td>');
				echo('<td>'.$row['statusUpdate'].'</td>');
				echo('<td>'.$row['Title'].' '.$row['Surname'].'</td>');
				if($row['deleted']==0){
					echo('<td>No</td>');
				}
				else{
					echo('<td>Yes</td>');
				}
				echo('</tr>');
			}
		echo('</table>');
		}
		else{
			echo("<p style='color:red; text-align:center;'>No reports found.</p>");
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




function childIpadReview(){
	

	$conn = $GLOBALS['conn'];
	$userEmail = $GLOBALS['userEmail'];
	
	setcookie("parentApproval", "", 1, "/");

	if($_SESSION['userType']!="parent"){
		header('location:dashboard.php');
	}

	echo("<h2>Active incidents</h2>");

	//$sql = "SELECT Students.Firstname, Students.Surname, Model, serialNumber, reason, Incident.incidentDate, notes, Requester, claimID, statusUpdate, Active, Returned, deleted, parentSignature FROM Parents INNER JOIN Students ON Parents.parentID = Students.contactParentID INNER JOIN iPads ON Students.studentID = iPads.studentID INNER JOIN Allocated ON iPads.ipadNumber = Allocated.ipadNumber INNER JOIN Incident ON Allocated.ipadNumber = Incident.ipadNumber INNER JOIN Claim ON Incident.incidentID = Claim.incidentID INNER JOIN Status ON Claim.incidentID = Status.incidentID WHERE Parents.Email = '".$_SESSION['username']."'";
	
	$sql = "SELECT Students.Firstname AS sFirstname, Students.Surname AS sSurname, Model, serialNumber, reason, Incident.incidentDate, notes, Requester, claimID, statusUpdate, Active, Returned, deleted, parentSignature FROM Parents INNER JOIN Students ON Parents.parentID = Students.contactParentID INNER JOIN iPads ON Students.studentID = iPads.studentID LEFT JOIN Allocated ON iPads.ipadNumber = Allocated.ipadNumber INNER JOIN Incident ON Allocated.ipadNumber = Incident.ipadNumber INNER JOIN Claim ON Incident.incidentID = Claim.incidentID INNER JOIN Status ON Claim.incidentID = Status.incidentID WHERE Parents.Email = :email"
	if($results = $conn->prepare($sql)){
		$results->bindParam(':email', $userEmail);
		$results->execute();
		$numResults = $results->rowCount();
		if($numResults>0){
			echo('<table style="width=100%" border=1>
			<tr>
				<th>Firstname</th>
				<th>Surname</th>
				<th>iPad Model</th>
				<th>Serial number</th>
				<th>Description of incident</th>
				<th>Time of incident</th>
				<th>Extra notes</th>
				<th>Staff in charge</th>
				<th>Status</th>
				<th>Returned?</th>
				<th>Signed?</th>
				<th>Concluded?</th>
			<tr>');
			while($row = $results->fetch(PDO::FETCH_ASSOC)){
				echo('<tr>');
				echo('<td>'.$row['Firstname'].'</td>');
				echo('<td>'.$row['Surname'].'</td>');
				echo('<td>'.$row['Model'].'</td>');
				echo('<td>'.$row['serialNumber'].'</td>');
				echo('<td>'.$row['reason'].'</td>');
				echo('<td>'.$row['incidentDate'].'</td>');
				echo('<td>'.$row['notes'].'</td>');
				echo('<td>'.$row['Requester'].'</td>');
				echo('<td>'.$row['statusUpdate'].'</td>');
				if($row['Active']!='1'){
					if($row['Returned']=='1'){
						echo('<td>Yes</td>');
					}else if($row['Returned']=='0'){
						echo('<td>No</td>');
					}
				}
				else{
					echo('<td></td>');
				}

				if($row['parentSignature']!='1'){
					$claimID = $row['claimID'];
					echo("<td><b><p name='parentSign' onclick='dataSubmission()'><a href='reportData.php?type=parentSignature&reportNumber=".$claimID."'>This incident is not approved by you.  Click here 						to sign it.</a></p></b></td>");
					$_SESSION['claimID'.$claimID] = $claimID;
					echo('<script>
						function dataSubmission(){
							document.cookie = "typeOfData=parentSignature; path=/";
						}
						</script>');
				}else{
					echo("<td><b><p style='color:limegreen;'>This incident has been approved by you.</p></b></td>");
				}
				if($row['deleted']==0){
					echo('<td>No</td>');
				}else{
					echo('<td>Yes</td>');
				}
			echo('</tr>');
			}
			echo('</table>');
		}else{
			echo("<p style='color:red; text-align:center;'>No results found.</p>");
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




function reportIncident(){

	$conn = $GLOBALS['conn'];
	$userEmail = $GLOBALS['userEmail'];

	if($_SESSION['userType']!="student"){
		header('location:dashboard.php');
	}
	if(isset($_SESSION['activeIncident'])){
		die('<h3><p style="color:red; text-align:center;">There is already an active incident for this iPad.</p></h3>');
	}
	$studentDetails = "SELECT Firstname, Surname, Email FROM Students LEFT JOIN iPads ON Students.studentID = iPads.studentID LEFT JOIN Allocated ON iPads.studentID = Allocated.studentID LEFT JOIN			Incident ON Allocated.ipadNumber=Incident.ipadNumber LEFT JOIN  Status ON Incident.incidentID=Status.incidentID WHERE Email = :email;"
	if($results = $conn->prepare($studentDetails)){
		$results->bindParam(':email', $userEmail);
		$results->execute();
		$row = $results->fetch(PDO::FETCH_ASSOC);
		echo('<div id="fullValidation">
				<body>
				<h2>Report an incident</h2>
				<b>The following details will be logged alongside the incident:</b><br><br>
				<b>Name: </b>'.$row['Firstname'].'<br>
				<b>Surname: </b>'.$row['Surname'].'<br>
				<b>Email: </b>'.$row['Email'].'<br><br>
				<p id="validationText"></p>
				<button id="continue" onclick="validationCheck()">Continue</button>
				<button onclick="falseInformation()">No</button>
			</div>
				<p id="falseInformationText"></p>
			<script>
			function validationCheck(){
				var button = document.getElementById("continue");
				var validationText = document.getElementById("validationText");
				validationText.innerHTML = "Are you sure?";
				button.innerHTML = "Yes";
				button.onclick = submitReport;
			}
			function falseInformation(){
				var text = document.getElementById("falseInformationText");
				var fullText = document.getElementById("fullValidation");
				fullText.style.display = "none";
				text.innerHTML = "<b>Please speak to your systems administrator if the details were incorrect.</b>";
				text.style.color = "red";
			}
			function submitReport(){
				location.assign("incidentReport.php");
			}
			</script>
			</body>
		</html>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




$userEmail = $_SESSION['username'];

echo('<html>
	<head>
	<title>Student Dashboard</title>
	</head>
	<body>');

switch($_GET['type']){
	case 'myipad':
		studentIpadReview();
		break;
	case 'childIpad':
		childIpadReview();
		break;
	case 'report':
		reportIncident();
		break;
	default:
		die(header('location:dashboard.php'));
}
	
//Close database connection
$conn = null;
	
echo("<p><a href='dashboard.php'>Return to dashboard</a></p>");
?>