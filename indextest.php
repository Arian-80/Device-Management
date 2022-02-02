<?php

echo("<h2>Login menu</h2><hr>");
session_start();


error_reporting(-1);
ini_set('display_errors', 'On');

if((isset($_SESSION['blockedTime'])) and (($_SESSION['blockedTime']+180)<=time())){
	$_SESSION['loginAttempts']=0;
}



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



function userLogged(){
	//print_r($_COOKIE);
	//print_r($_POST);
	//print($_POST['setCookies']);
	$_SESSION['username'] = $_COOKIE['username'];
	$_SESSION['userType'] = $_COOKIE['userType'];
	//print($_SESSION['userType']);
	//print($_COOKIE['userType']);
	echo('<html>
			<head>
				<h3>Welcome back '.$_COOKIE['Firstname'].'!</h3>
			</head>
			<body>
				<p><a href="dashboard.php">Proceed to Dashboard</a></p>
				<p><a href="logout.php">Log out</a></p>
			</body>
		</html>');
	die();
}



function loginOptions(){
		  echo('<html>
					<head>
						<title>Login menu</title>
					</head>
					<body>');
		if(isset($_GET['logout'])){
			echo("<h2>You have successfully logged out!</h2>");
		}

		echo('
			<form method="post" action="indextest.php">
				<b>Email</b><br> <input type="text" id="username" name="username" required><br>
				<b>Password</b><br> <input type="password" id="pass" name="pass" required><br>
				<input type="radio" name="setCookies" value="true" Checked><b>Log me in automatically</b><br>
				<input type="radio" name="setCookies" value="false"><b>Do not remember my credentials</b><br><br>
				<a href="resetPassword.php?type=insertEmail">Reset my password</a><br><br>
				<button type="submit">Login</button>
			</form>
			</body>
			</html>');
}


function searchRecord($table, $userID, $userRole){
	
	global $verifiedPassword;
	global $numResults;
	global $row;
	global $userID;
	global $userType;
	global $username;
	
	$username = $_POST['username'];
 	$password = $_POST['pass'];
	$conn = $GLOBALS['conn'];
	
	//$qry = "SELECT * FROM Staff WHERE Email = '".$username."'";
	//$results = $conn->query($qry);
	//$row = $results->fetch_assoc();
	$qry = "SELECT * FROM ".$table." WHERE Email= :username"; //PDO
	//$qry = "SELECT * FROM Staff WHERE Email = ?"; // MYSQLI, ? = REPLACE
	if($selectedUser = $conn->prepare($qry)){
		$selectedUser->bindParam(':username', $username); //PDO
		//$selectedUser->bind_param('s', $username); // MYSQLI, S = STRING
		$selectedUser->execute();
		$row = $selectedUser->fetch(PDO::FETCH_ASSOC); // PDO
	}
	else{
		$error = $conn->errno . ' ' . $conn->error;
		//die(print($error));
		die("Error: Please try again later.");
	}

	if(password_verify($password, $row['Password'])){
		$verifiedPassword = true;
	}
	else{
		$verifiedPassword = false;
	}
	$numResults = $selectedUser->rowCount();
	
	$userType = $userRole;

}


function setCookies(){
	
	//print("<br>1");
	setcookie("username", $_SESSION['username'], time() + (86400*30), "/");
	//print("<br>2");
	setcookie("userType", $_SESSION['userType'], time() + (86400*30), "/");
	//print("<br>3");
	setcookie("Title", $_SESSION['Title'], time() + (86400*30), "/");
	//print("<br>4");
	setcookie("studentID", $_SESSION['studentID'], time() + (86400*30), "/");
	//print("<br>5");
	setcookie("parentID", $_SESSION['parentID'], time() + (86400*30), "/");
	//print("<br>6");
	setcookie("staffID", $_SESSION['staffID'], time() + (86400*30), "/");
	//print("<br>7");
	
}



// Main program

$loginAttempts = 0;


if(isset($_SESSION['loginAttempts'])){
	
	$loginAttempts = $_SESSION['loginAttempts'];
	
}




if(isset($_COOKIE['username'])){
	userLogged();
}

if(!empty($_POST)){ //if the log in button has been pressed
	
	databaseConnection();
	searchRecord('Staff', $userID = 'staffID', 'staff');


	if(($numResults<1)or($verifiedPassword==false)){
		//not staff, so try students
		searchRecord('Students', $userID = 'studentID', 'student');	

		if(($numResults<1)or($verifiedPassword==false)){	
			//not staff or students, so try Parents
			searchRecord('Parents', $userID = 'parentID', 'parent');
		}
	}


	
	if(($numResults>0)and($verifiedPassword==true)){
		$_SESSION = [];
		$_SESSION['Firstname'] = $row['Firstname'];
		$_SESSION['Surname'] = $row['Surname'];
		$_SESSION['Title'] = $row['Title'];
		$_SESSION['studentID'] = $studentID;
		$_SESSION['parentID'] = $parentID;
		$_SESSION['staffID'] = $staffID;
		$_SESSION['username'] = $username;
		$_SESSION['userType'] = $userType;

		setcookie("Firstname", $_SESSION['Firstname'], time() + (86400*30), "/");

		//print($_POST['setCookies']=='true');


		if($_POST['setCookies']=='true'){
			setCookies();
		}
		header('location:dashboard.php');
  }
  else{
	  $loginAttempts++;
	  $_SESSION['loginAttempts']=$loginAttempts;
	  $_SESSION['blockedTime']=time();
	  header('location:indextest.php');  
  }
}
else{
	if($loginAttempts>2){
		echo("<h2>You have reached your limit of attempts!</h2>");
		echo("<h3>Please try again later.</h3>");
		echo("<p><a href='resetLockout.php'>Reset counter</a></p>");
		die();
	}
	elseif($loginAttempts>0){
		echo("<p style='color:red; text-align: center'><b>Incorrect username or password, try again.</b></p>");
	}
	loginOptions();
}

//Close mysqli database connection
//$conn->close();


//Close PDO database connection
$conn = null;

?>
