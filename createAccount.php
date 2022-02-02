<?php
require 'header.php';
// Ensure the user is meant to have access to this page, otherwise kick them back to the dashboard page.
if($adminLevel<2){
	header('location:dashboard.php');
	die();
}
echo(
	'<html>
		<head>
			<title>New Account</title>
		</head>
		<body>');

function addStudentRecord(){
	
	echo("<h2>Add Student Record</h2>");
	echo("<form method='post' action='addRecord.php?type=student'>
		<b>Staff title:</b><br><select name='title'>
		<option value='Master'>Master</option>
		<option value='Mr'>Mr</option>
		<option value='Miss'>Miss</option>
		<option value='Mrs'>Mrs</option>
		<option value='Ms'>Ms</option>
		<option value='Mx'>Mx</option></select><br>
		<b>Student first name:</b><br><input type='text' id='fname' name='fname' required><br>
		<b>Student surname:</b><br><input type='text' id='sname' name='sname' required><br>
		<b>Student email:</b><br><input type='email' id='email' name='email' required><br>
		<b>Year group:</b><br><input type='number' id='ygroup' name='ygroup' min='7' max='13' required><br>
		<b>Tutor group:</b><br><input type='text' id='tgroup' 
		pattern='[A-Za-z]+' title='Invalid tutor group' name='tgroup' maxlength='3' size='2' required><br><br>
		<button type='submit'>Add</button>
		</form>
		");

	
}


function addStaffRecord(){

	$adminLevel = $GLOBALS['adminLevel'];
	
	if($adminLevel<3){
		die(header('location:dashboard.php'));
	}

	echo("<h2>Add Staff Record</h2>");
	echo("<form method='post' action='addRecord.php?type=staff'>
		<b>Staff title:</b><br><select name='title'>
		<option value='Mr'>Mr</option>
		<option value='Miss'>Miss</option>
		<option value='Mrs'>Mrs</option>
		<option value='Ms'>Ms</option>
		<option value='Dr'>Dr</option>
		<option value='Prof.'>Professor</option>
		<option value='Mx'>Mx</option></select><br>
		<b>Staff first name:</b><br><input type='text' id='fname' name='fname' required><br>
		<b>Staff surname:</b><br><input type='text' id='sname' name='sname' required><br>
		<b>Email:</b><br><input type='email' id='email' name='email' required><br>
		<b>Admin Level:</b><br><input type='number' id='alevel' name='alevel' min='1' max='3' required><br><br>
		<button type='submit'>Add</button>
		</form>
	");

}


function addParentRecord(){
	
	$conn = $GLOBALS['conn'];

	echo("<h2>Add Parent Record</h2>");
	echo("<form method='post' action='addRecord.php?type=parent'>
	<b>Parent title:</b><br><select name='title'>
	<option value='Mr'>Mr</option>
	<option value='Miss'>Miss</option>
	<option value='Mrs'>Mrs</option>
	<option value='Ms'>Ms</option>
	<option value='Dr'>Dr</option>
	<option value='Prof.'>Professor</option>
	<option value='Mx'>Mx</option></select><br>
	<b>Parent first name:</b><br><input type='text' id='fname' name='fname' required><br>
	<b>Parent surname:</b><br><input type='text' id='sname' name='sname' required><br>
	<b>Email:</b><br><input type='email' id='email' name='email' required><br>
	<b>Contact Number:</b><br><input type='tel' pattern='[0-9]{11}' title='Must include exactly 11 numbers' id='cnumber' name='cnumber' required><br>
	");

	$sql = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students";
	if($results = $conn->prepare($sql)){
		$results->execute();
		echo('<b>Select student:</b><br><input list="students" name="studentID" required>
				<datalist id="students">');
		while($row = $results->fetch(PDO::FETCH_ASSOC)){
			echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname'].' '.$row['yearGroup'].$row['tutorGroup']);
		}
		echo('</datalist><br>');
		//Close database connection as early as possible
		$conn = null;

		echo("
		</select><br><br>
		<button type='submit'>Add</button>
		</form>
		");
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}

}


// Execute a certain piece of code based on the "type"

switch ($_GET['type']){
		
	case 'student':
		addStudentRecord();
		break;
		
	case 'staff':
		addStaffRecord();
		break;
		
	case 'parent':
		addParentRecord();
		break;
		
	default:
		die(header('location:dashboard.php'));
}


echo('<p><a href="dashboard.php">Return to dashboard</a></p></body>
</html>');



?>