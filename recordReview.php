<?php
require 'header.php';
	
// Ensure the user is meant to have access to this page, otherwise kick them back to the dashboard page.
if($adminLevel<1){
	header('location:dashboard.php');
	die();
}

echo(
	'<html>
		<head>
			<title>Record Review</title>
		</head>
		<body>');



function studentRecordReview($searchType){
	
	$conn = $GLOBALS['conn'];
	
	echo("<h2>Student Record Search</h2>");
	/* OLD CODE, LESS EFFICIENT AND USERFRIENDLY.
	echo("<form method='post' action='searchOutput.php?type=".$searchType."'>
		<b>Student first name:</b><br><input type='text' id='fname' name='fname' required><br>
		<b>Student second name:</b><br><input type='text' id='sname' name='sname'required><br>
		<b>Year group:</b><br><input type='number' id='ygroup' name='ygroup' min='7' max='13' required><br>
		<b>Tutor group:</b><br><input type='text' id='tgroup' name='tgroup' maxlength='3' size='2' required><br><br>
		<button type='submit'>Search</button>
		</form>
	");
	*/
	
	// NEW CODE, MORE EFFICIENT, USERFRIENDLY AND STREAMLINED.
	$sql = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<form method="post" action="searchOutput.php?type='.$searchType.'">
				<b>Select student\'s unique ID:</b><br><input list="students" name="studentID" required>
					<datalist id="students">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname'].' '.$row['yearGroup'].$row['tutorGroup']);
		}
		echo('</datalist><br><br>
		<button type="submit">Search</button>
		</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}


function parentProfileReview(){
	
	$adminLevel = $GLOBALS['adminLevel'];
	
	if($adminLevel<2){
		die(header('location:dashboard.php'));
	}
	
	$conn = $GLOBALS['conn'];
	

	echo('<h2>Parent Record Search</h2>');
	
	
	$sql = "SELECT studentID, Firstname, Surname FROM Students";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<form method="post" action="searchOutput.php?type=parentProfile">
				<b>Select child:</b><br><input list="students" name="studentID">
					<datalist id="students">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname']);
		}
		echo('</datalist><br><br><p style="color:red;"><b>OR</b></p><br>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
	
	$sql = "SELECT parentID, Title, Firstname, Surname FROM Parents";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<b>Select parent:</b><br><input list="parents" name="parentID">
				<datalist id="parents">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['parentID'].'">'.$row['Title'].' '.$row['Firstname'].' '.$row['Surname']);
		}
		echo('</datalist><br><br>
		<button type="submit">Search</button>
		</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}



function staffProfileReview(){
	
	$adminLevel = $GLOBALS['adminLevel'];
	
	if($adminLevel!=3){
		die(header('location:dashboard.php'));
	}
	
	$conn = $GLOBALS['conn'];
	

	echo("<h2>Staff record search</h2>");
	
	$sql = "SELECT staffID, Title, Firstname, Surname FROM Staff";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<form method="post" action="searchOutput.php?type=staffProfile">
			<b>Select staff:</b><br><input list="staff" name="staffID" required>
				<datalist id="staff">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['staffID'].'">'.$row['Title'].' '.$row['Firstname'].' '.$row['Surname']);
		}
		echo('</datalist><br><br>');
		echo('<button type="submit">Search</button>
		</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}



function ipadReview(){
	
	$conn = $GLOBALS['conn'];
	
	echo("<h2>iPad Serial Number Search Form</h2>");
	
	//OLD CODE, LESS EFFICIENT AND USERFRIENDLY, MORE PRONE TO MAKING ERRORS.
	/*
	echo("<form method='post' action='searchOutput.php?type=ipadSearch'>
		<b>iPad serial number:</b><br><input type='text' maxlength='12' size='12' pattern='.{12}' title='Must include exactly 12 characters' id='snumber' name='snumber' required><br><br>
		<button type='submit'>Search</button>
		</form>
		");
	*/
	
	// NEW CODE, MORE EFFICIENT, USERFRIENDLY AND STREAMLINED.
	$sql = "SELECT serialNumber, Model FROM iPads";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<form method="post" action="searchOutput.php?type=ipadSearch">
				<b>Search iPad\'s serial number:</b><br><input list="iPads" name="snumber" required>
					<datalist id="iPads">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['serialNumber'].'">Model - '.$row['Model']);
		}
		echo('</datalist><br><br>
		<button type="submit">Search</button>
		</form>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}

// Execute a certain piece of code based on the "type"

switch($_GET['type']){
	case "studentProfileReview":
		studentRecordReview("studentProfile");
		break;
	case "studentIncidentReview":
		studentRecordReview("studentIpad");
		break;
	case "parentProfileReview":
		parentProfileReview();
		break;
	case "staffProfileReview":
		staffProfileReview();
		break;
	case "ipadReview":
		ipadReview();
		break;
	default:
		die(header('location:dashboard.php'));
}


echo('<p><a href="dashboard.php">Return to dashboard</a><p>
	</body>
</html>');
	



?>