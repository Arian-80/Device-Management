<?php
	require 'header.php';

if($_SESSION['userType']!="student"){
	header('location:dashboard.php');
}

if(isset($_SESSION['activeIncident'])){
	header('location:dashboard.php');
}


function incidentType(){
	echo('
		<html>
			<head>
				<title>Report an incident</title>
			</head>
			<body>
			<h2>Report an incident</h2>
			<p><b>Was your iPad lost/stolen?</b></p>
			<button id="stolen" onclick="incidentType(stolen)">Yes</button>
			<button id="damaged" onclick="incidentType(damaged)">No</button>
			<script>
			function incidentType(incType){
				if (incType==stolen){
					location.assign("reportData.php?type=stolen");
					document.cookie = "typeOfData=stolen; path=/";
				}
				else{
					location.assign("reportData.php?type=damaged");
					document.cookie = "typeOfData=damaged; path=/";
				}
			}
			</script>
	');
}

$qry = "SELECT * FROM iPads WHERE iPads.studentID = :studentID";
if($results = $conn->prepare($qry)){
	$results->bindParam(':studentID', $_SESSION['studentID']);
	$results->execute();
	$row = $results->fetch(PDO::FETCH_ASSOC);
	$numResults = $results->rowCount();
}else{
	//die(print($GLOBALS['error']));
	die("Error: Please try again later.");
}

//Close database connection as early as possible
$conn = null;

if($numResults>0){
	incidentType();
}else{
	echo("<p  style=color:red><b>No results found. Speak to your IT administrators.</b></p>");	
}

echo("<p><a href='dashboard.php'>Return to dashboard</a></p>
	</body>
	</html>");

?>