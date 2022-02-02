<?php
$_SESSION = [];
setcookie("username", "", 1, "/");
header('location:indextest.php?logout=true');
	
?>