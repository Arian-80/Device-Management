<?php

// Connect to the database. Make sure that the user is in fact logged in.
	session_start();
if(!isset($_SESSION['username'])){
	die(header("location:indextest.php"));
}

// Ensure error reporting is enabled
error_reporting(-1);
ini_set('display_errors', 'On');
//set_error_handler("var_dump");


function databaseConnection(){
	
	global $conn;
	//connect to a database, must be at the top

	//$servername = "localhost";  // MYSQLI
	$username = "arian";
	$password = "arian";
	//$database = "ipad";  // MYSQLI
	// dsn = data source name
	$dsn = "mysql:host=localhost;dbname=ipad";  // PDO

	// Create connection  // MYSQLI
	//$conn = new mysqli($servername, $username, $password, $database);

	// Create connection PDO
	try{
		$conn = new PDO($dsn, $username, $password);
		$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch(PDOException $e){
		die("Connection failed: ".$e->getMessage());
	}
	
	// Check connection  // MYSQLI
	/*if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}*/
}




function logAction($action){
	
	$conn = $GLOBALS['conn'];
	
	$logValues = [
		':userEmail' => $_SESSION['username'],
		':action' => $action,
	];
	
	$sql = "INSERT INTO Logs (userEmail, action) VALUES (:userEmail, :action)";
	if($insertLog = $conn->prepare($sql)){
		$insertLog->execute($logValues);
	}else{
		//die(print($GLOBALS['error']))
		die("Error: Please try again later.");
	}
	
}




function selectRecord($table, $query){
	
	global $selectedUser;
	global $numResults;
	//global $error;
	
	$conn = $GLOBALS['conn'];
	
	
	$qry = "SELECT ".$query." FROM ".$table." WHERE Email= :username";
	if($selectedUser = $conn->prepare($qry)){
		$selectedUser->bindParam(':username', $_SESSION['username']);
		$selectedUser->execute();
		$numResults = $selectedUser->rowCount();
	}
	else{
		$error = $conn->errno.' '.$conn->error;
		//die(print($GLOBALS['error']));
		die("Error: Please try again later.");
	}
}


// Main program

databaseConnection();
selectRecord('Staff', 'Email, adminLevel, Firstname, Surname');
if ($numResults<1){
	selectRecord('Parents', 'Email, Firstname, Surname');
	if ($numResults<1){
		selectRecord('Students', 'Email, Firstname, Surname');
	}
}

	$userDetails = $selectedUser->fetch(PDO::FETCH_ASSOC);

    $name = $userDetails['Firstname']." ".$userDetails['Surname'];

    if(isset($userDetails['adminLevel'])){
		$adminLevel = $userDetails['adminLevel'];
	}
?>