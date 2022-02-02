<?php

require 'header.php';
// Ensure the user is meant to have access to this page, otherwise kick them back to the dashboard page.
if($adminLevel!=3){
	die(header('location:dashboard.php'));
}



function reviewAllIncidents(){
	
	$conn = $GLOBALS['conn'];
	
	$sql = "SELECT Incident.incidentID, reason, Incident.incidentDate, incidentLocation, notes, serialNumber, Firstname, Surname, yearGroup, tutorGroup, statusUpdate FROM Incident INNER JOIN Claim ON 		Incident.incidentID = Claim.incidentID INNER JOIN Status ON Claim.incidentID = Status.incidentID INNER JOIN iPads ON Incident.ipadNumber = iPads.ipadNumber INNER JOIN Students ON iPads.studentID = 		Students.studentID WHERE parentSignature=1 AND Incident.deleted=0";
	if($results = $conn->prepare($sql)){
		$results->execute();
		$numResults = $results->rowCount();
		if ($numResults>0){
			echo("<table style='width:100%' border=1>
				<tr>
					<th>The incident</th>
					<th>Time of incident</th>
					<th>Incident location</th>
					<th>Additional notes</th>
					<th>iPad serial number</th>
					<th>Student firstname</th>
					<th>Student surname</th>
					<th>Student yeargroup</th>
					<th>Student tutorgroup</th>
					<th>Current status</th>
					<th>Admin actions:</th>
				</tr>");
			while($row = $results->fetch(PDO::FETCH_ASSOC)){
				$_SESSION['incidentID'.$row['incidentID']] = $row['incidentID'];
				echo("<tr>");
				echo('<td>'.$row['reason'].'</td>');
				echo('<td>'.$row['incidentDate'].'</td>');
				echo('<td>'.$row['incidentLocation'].'</td>');
				echo('<td>'.$row['notes'].'</td>');
				echo('<td>'.$row['serialNumber'].'</td>');
				echo('<td>'.$row['Firstname'].'</td>');
				echo('<td>'.$row['Surname'].'</td>');
				echo('<td>'.$row['yearGroup'].'</td>');
				echo('<td>'.$row['tutorGroup'].'</td>');
				echo('<td>'.$row['statusUpdate'].'</td>');
				echo('<td><a href="incidentUpdate.php?reportNumber='.$row['incidentID'].'">Update this incident</a></td>');
				echo("</tr>");
			}
			echo("</table>");
		}
		else{
			echo("<b><p style='colour:red; text-align:center;'>No active incidents found.</p></b>");
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}




if(isset($_GET['reportUpdated'])){
	echo("<b><p style='color:limegreen';>Report updated!</p></b>");
}
echo('
	<html>
		<head>
			<title>Incident review</title>
		</head>
		<body>');

reviewAllIncidents();

//Close database connection
$conn = null;
	
echo('<p><a href="dashboard.php">Return to Dashboard</a></p>
</body></html>');

?>