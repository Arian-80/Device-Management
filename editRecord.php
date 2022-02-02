<?php
	require 'header.php';	

if($adminLevel!=3){
	die(header('location:dashboard.php'));
}



function titleSelection(){
	
	echo("<td><select name='title'>
		<option value='Mr'>Mr</option>
		<option value='Miss'>Miss</option>
		<option value='Mrs'>Mrs</option>
		<option value='Ms'>Ms</option>
		<option value='Dr'>Dr</option>
		<option value='Prof.'>Professor</option>
		<option value='Mx'>Mx</option></select></td>"
	);
}




function inputStudentEdit(){
	
	$conn = $GLOBALS['conn'];
	$userID = $_GET['studentID'];
	
	$sql = "SELECT Title, Firstname, Surname, Email, yearGroup, tutorGroup FROM Students WHERE studentID = :userID";
	if($selectedRecord = $conn->prepare($sql)){
		$selectedRecord->bindParam(':userID', $userID);
		$selectedRecord->execute();
		$numResults = $selectedRecord->rowCount();
		if($numResults>0){
			echo('<table id="recordTable" style="width:100%" border=1>
				<tr>
				<th>Title</th>
				<th>Firstname</th>
				<th>Surname</th>
				<th>Email</th>
				<th>Year group</th>
				<th>Tutor group</th>
				</tr>');
			$row = $selectedRecord->fetch(PDO::FETCH_ASSOC);
			echo('<tr>');
			echo('<form method="post" action="editRecord.php?type=submitRecordEdit&userID='.$userID.'&role=student">');
			titleSelection();
			echo('<td><input type="text" name="fname" id="fname" value="'.$row['Firstname'].'" required></td>');
			echo('<td><input type="text" name="sname" id="sname" value="'.$row['Surname'].'" required></td>');
			echo('<td><input type="email" name="email" id="email" value="'.$row['Email'].'" required></td>');
			echo('<td><input type="number" name="ygroup" id="ygroup" value="'.$row['yearGroup'].'" required></td>');
			echo('<td><input type="text" name="tgroup" id="tgroup" pattern="[A-Za-z]+" title="Invalid tutor group" maxlength="3" size="2" value="'.$row['tutorGroup'].'" required></td><br><br>');
			echo('</tr></table><br>');
			echo('<button type="submit">Edit</button>');
			echo('</form>');
		}
		else{
			echo('<h2 style="color:red; text-align:center;">No results found</h2>');
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}





function inputParentEdit(){
	
	$conn = $GLOBALS['conn'];
	$userID = $_GET['parentID'];
	
	$sql = "SELECT Title, Firstname, Surname, Email, contactNumber FROM Parents WHERE parentID = :userID";
	if($selectedRecord = $conn->prepare($sql)){
		$selectedRecord->bindParam(':userID', $userID);
		$selectedRecord->execute();
		$numResults = $selectedRecord->rowCount();
		if($numResults>0){
			echo('<table id="recordTable" style="width:50%" border=1>
				<tr>
				<th>Title</th>
				<th>Firstname</th>
				<th>Surname</th>
				<th>Email</th>
				<th>Contact number</th>
				</tr>');
			$row = $selectedRecord->fetch(PDO::FETCH_ASSOC);
			echo('<tr>');
			echo('<form method="post" action="editRecord.php?type=submitRecordEdit&userID='.$userID.'&role=parent">');
			titleSelection();
			echo('<td><input type="text" name="fname" id="fname" value="'.$row['Firstname'].'" size="50" required></td>');
			echo('<td><input type="text" name="sname" id="sname" value="'.$row['Surname'].'" size="50" required></td>');
			echo('<td><input type="email" name="email" id="email" value="'.$row['Email'].'" size = "50" required></td>');
			echo('<td><input type="tel" pattern="[0-9]{11}" title="Must include exactly 11 numbers" id="cnumber" name="cnumber" value="'.$row['contactNumber'].'" size="50" required></td>');
			echo('</tr></table><br>');
			echo('<button type="submit">Edit</button>');
			echo('</form>');
		}
		else{
			echo('<h2 style="color:red; text-align:center;">No results found</h2>');
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}






function inputStaffEdit(){
	
	$conn = $GLOBALS['conn'];
	$userID = $_GET['staffID'];
	
	$sql = "SELECT Title, Firstname, Surname, Email, adminLevel FROM Staff WHERE staffID = :userID";
	if($selectedRecord = $conn->prepare($sql)){
		$selectedRecord->bindParam(':userID', $userID);
		$selectedRecord->execute();
		$numResults = $selectedRecord->rowCount();
		if($numResults>0){
			echo('<table id="recordTable" style="width:100%" border=1>
				<tr>
				<th>Title</th>
				<th>Firstname</th>
				<th>Surname</th>
				<th>Email</th>
				<th>Admin access level</th>
				</tr>');
			$row = $selectedRecord->fetch(PDO::FETCH_ASSOC);
			echo('<tr>');
			echo('<form method="post" action="editRecord.php?type=submitRecordEdit&userID='.$userID.'&role=staff">');
			titleSelection();
			echo('<td><input type="text" name="fname" id="fname" value="'.$row['Firstname'].'" required></td>');
			echo('<td><input type="text" name="sname" id="sname" value="'.$row['Surname'].'" required></td>');
			echo('<td><input type="email" name="email" id="email" value="'.$row['Email'].'" required></td>');
			echo('<td><input type="number" id="alevel" name="alevel" min="1" max="3" value="'.$row['adminLevel'].'" required></td>');
			echo('</tr></table><br>');
			echo('<button type="submit">Edit</button>');
			echo('</form>');
		}
		else{
			echo('<h2 style="color:red; text-align:center;">No results found</h2>');
		}
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}





function editRecord($sqlQry, $arrayValues){
	
	$conn = $GLOBALS['conn'];
	$userID_Value = $_GET['userID'];
	
	
	$recordValues = array($_POST['title'], $_POST['fname'],$_POST['sname'], $_POST['email']);
	
	foreach($arrayValues as $arrayFields){
		array_push($recordValues, $arrayFields);
	}
	
	if($updatedRecord = $conn->prepare($sqlQry)){
		$updatedRecord->execute($recordValues);
		array_splice($recordValues, 3);
		$requiredFields = implode(" ", $recordValues);
		$action = ("Edited ".$_GET['role']." record - ".$requiredFields);
		logAction($action);
		echo('<h2 style="color:limegreen; text-align:center;">Success - record edited!</h2>');
	}else{
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}





function uniqueSqlValues(){
	
	global $sqlQry;
	global $arrayValues;
	
	switch($_GET['role']){
		case 'staff':
			$sqlQry = "UPDATE Staff SET Title = :title, Firstname = :firstname, Surname = :surname, Email = :email, adminLevel = :adminLevel WHERE staffID = :staffID"; 
			$arrayValues = [
				':adminLevel' => $_POST['alevel'],	
				':staffID' => $_GET['userID'],
			];
			break;
		case 'parent':
			$sqlQry = "UPDATE Parents SET Title = :title, Firstname = :firstname, Surname = :surname, Email = :email, contactNumber = :contactNumber WHERE parentID = :parentID";
			$arrayValues = [
				':contactNumber' => $_POST['cnumber'],
				':parentID' => $_GET['userID'],
			];
			break;
		case 'student':
			$sqlQry = "UPDATE Students SET Title = :title, Firstname = :firstname, Surname = :surname, Email = :email, yearGroup = :yearGroup, tutorGroup = :tutorGroup WHERE studentID = :studentID";
			$arrayValues = [
				':yearGroup' => $_POST['ygroup'],
				':tutorGroup' => $_POST['tgroup'],
				':studentID' => $_GET['userID'],
			];
			break;
		default:
			die(header('location:dashboard.php'));
	}
}


echo('<html>
	<head>
	<title>Edit record</title>
	</head>
	<body>');


echo('<h2 style="text-align:center;">Edit record</h2>');


switch($_GET['type']){
	case 'inputStudentEdit':
		inputStudentEdit();
		break;
	case 'inputParentEdit':
		inputParentEdit();
		break;
	case 'inputStaffEdit':
		inputStaffEdit();
		break;
	case 'submitRecordEdit':
		uniqueSqlValues();
		editRecord($sqlQry, $arrayValues);
		break;
	default:
		die(header('location:dashboard.php'));
}

//Close the database connection
$conn = null;
echo("<p><a href='dashboard.php'>Return to Dashboard</a></p>");

?>