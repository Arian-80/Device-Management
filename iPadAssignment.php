<?php
	require 'header.php';

if($adminLevel<3){
	header('location:dashboard.php');
	die();
}

function assignDevice(){
	
	$conn = $GLOBALS['conn'];
	
	$sql = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students WHERE studentID NOT IN (SELECT studentID FROM iPads WHERE Active=1)";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<form method="post" action="iPadAssignment.php">
				<b>Select student:</b><br><input list="students" name="studentID" required>
					<datalist id="students">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname'].' '.$row['yearGroup'].$row['tutorGroup']);
		}
		echo('</datalist><br>
		<b>iPad serial number:</b><br><input type="text" maxlength="12" pattern=".{12}" title="Must include exactly 12 	characters" id="snumber" name="snumber" required><br>
		<b>iPad Model:</b><br><input type="text" id="model" name="model" required><br><br>
		<input type="checkbox" name="originalDevice" value="true"><b>This is a new iPad</b><br><br>
		<button type="submit">Submit</button>
		</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}

function addDevice(){
	
	$conn = $GLOBALS['conn'];
	
	$originalDevice = 0;
	
	$sql = "SELECT * FROM Students WHERE StudentID = :studentID";
	if($results = $conn->prepare($sql)){
		$results->bindParam(':studentID', $_POST['studentID']);
		$results->execute();
		$numResults = $results->rowCount();
		if($numResults<1){
			echo("<p style='color:red; text-align:center;'><b>The student account has not been signed up yet!</b></p>");
			echo("<p><a href='iPadAssignment.php'>Click here to try again</a><p>");
			echo("<p><a href='dashboard.php'>Return to dashboard</a></p>");
			die();
		}
		if(isset($_POST['originalDevice'])){
			$originalDevice = 1;
		}

		$sql = "INSERT INTO iPads (studentID, serialNumber, Model, originalDevice) VALUES (:studentID, :serialNumber, :model, :originalDevice)";
		if($addRecord = $conn->prepare($sql)){
			
			$insertValues = [
				'studentID' => $_POST['studentID'],
				'serialNumber' => $_POST['snumber'],
				'model' => $_POST['model'],
				'originalDevice' => $originalDevice,
			];
			
			$addRecord->execute($insertValues);
			echo('<b><p style="color:limegreen">Success!</p></b>');
			$logValues = array_splice($insertValues, 1, 2);
			$action = ("Added device - ".$logValues);
			logAction($action);
		}else{
			//die(print($GLOBALS['error']));
			die("Error: Please try again later.");
		}
	}
}

echo('<html>
	<head>
	<title>Assign Device</title>
	</head>
	<body>');

if(empty($_POST)){
	assignDevice();
}else{
	addDevice();
}

//Close database connection
$conn = null;
	
echo("<p><a href='dashboard.php'>Return to dashboard</a></p>")
?>