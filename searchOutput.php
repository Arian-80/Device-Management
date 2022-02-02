<?php
require 'header.php';

// Ensure the user is meant to have access to this page, otherwise kick them back to the dashboard page.
if($adminLevel<1){
    die(header('location:dashboard.php'));
}



function inputCheck($requiredFields){

    if(array_diff($requiredFields, array_keys($_POST))){
        die(header('location:dashboard.php'));
    }
}



function studentIpadReview(){

    $requiredFields = array('studentID');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];
    $adminLevel = $GLOBALS['adminLevel'];
    $studentID = $_POST['studentID'];

    echo("<h2 style='text-align:center;'>Student iPad and incident search results</h2>");


    // Allow the user to be able to select certain categories and get a list of students

    // OLD CODE, MATCHED WITH THE OLD CODE ON RECORDREVIEW.PHP
    /*

    $fname = $_POST['fname'];
    $sname = $_POST['sname'];
    $ygroup = $_POST['ygroup'];
    $tgroup = $_POST['tgroup'];

    $recordValues = [
        'firstname' => $_POST['fname'],
        'surname' => $_POST['sname'],
        'yearGroup' => $_POST['ygroup'],
        'tutorGroup' => $_POST['tgroup'],
    ];

    $searchQuery = "SELECT serialNumber, statusUpdate, reason, Requester, incidentDate, Returned, notes FROM Students INNER JOIN Allocated ON Students.studentID=Allocated.studentID INNER JOIN Incident ON Allocated.ipadNumber = Incident.ipadNumber INNER JOIN iPads ON Incident.ipadNumber = iPads.ipadNumber INNER JOIN Status ON Incident.incidentID = Status.incidentID WHERE Students.Firstname = :firstname AND Students.Surname = :surname AND Students.yearGroup = :yearGroup AND Students.tutorGroup = :tutorGroup";

    */

    //NEW CODE

    $searchQuery = "SELECT serialNumber, statusUpdate, reason, Requester, incidentDate, Returned, notes, deleted FROM Students INNER JOIN iPads ON Students.studentID = iPads.studentID LEFT JOIN Incident 		ON iPads.ipadNumber = Incident.ipadNumber INNER JOIN Allocated ON Incident.ipadNumber = Allocated.ipadNumber INNER JOIN Status ON Incident.incidentID = Status.incidentID WHERE Students.studentID =		:studentID";
    if($searchResult = $conn->prepare($searchQuery)){
        $searchResult->bindParam(':studentID', $studentID);
        $searchResult->execute();
        $numResults = $searchResult->rowCount();
        if($numResults>0){
            echo('<p id="deleteRecordText"></p>');
            echo('<table id="recordTable" style="width:100%" border=1>
			  <tr>
				<th>iPad serial number</th>
				<th>Status</th>
				<th>Incident</th>
				<th>Requested by</th>
				<th>Date handed in</th>
				<th>Returned</th>
				<th>Extra notes</th>
				<th>Concluded?</th>
			  </tr>');
            while($row = $searchResult->fetch(PDO::FETCH_ASSOC)){
                echo('<tr>');
                echo('<td>'.$row['serialNumber'].'</td>');
                echo('<td>'.$row['statusUpdate'].'</td>');
                echo('<td>'.$row['reason'].'</td>');
                echo('<td>'.$row['Requester'].'</td>');
                echo('<td>'.$row['incidentDate'].'</td>');
                if ($row['Returned'] != "0"){
                    echo('<td>Yes</td>');
                }
                else{
                    echo('<td>No</td>');
                }
                echo('<td>'.$row['notes'].'</td>');
                if($row['deleted']==0){
                    echo('<td>No</td>');
                }
                else{
                    echo('<td>Yes</td>');
                }
                echo('</tr>');
            }
            echo('</table>');
        }else{
            //tell the user it couldn't find anyone
            echo("<b><p style='color:red; text-align:center;'>No results found.</p></b>");
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}





function studentProfileReview(){

    $requiredFields = array('studentID');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];
    $adminLevel = $GLOBALS['adminLevel'];
    $studentID = $_POST['studentID'];

    echo("<h2 style='text-align:center;'>Student profile search results</h2>");

    // Allow the user to be able to select certain categories and get a list of students

    // OLD CODE, MATCHED WITH THE OLD CODE ON RECORDREVIEW.PHP
    /*

    $recordValues = [
        'firstname' => $_POST['fname'],
        'surname' => $_POST['sname'],
        'yearGroup' => $_POST['ygroup'],
        'tutorGroup' => $_POST['tgroup'],
    ];

    $searchQuery = "SELECT Students.studentID, Students.Title, Students.Email, Parents.Title, Parents.Firstname, Parents.Surname, Parents.Email, Parents.contactNumber, Students.Email AS sEmail AND 			Parents.Email AS pEmail AND Students.Title AS sTitle AND Parents.Title AS pTitle FROM Students INNER JOIN Parents ON Students.contactParentID = Parents.parentID INNER JOIN Allocated ON 					Students.studentID = Allocated.studentID INNER JOIN Incident ON Allocated.ipadNumber = Incident.ipadNumber INNER JOIN iPads ON Incident.ipadNumber = iPads.ipadNumber INNER JOIN Status ON					Incident.incidentID = Status.incidentID WHERE Students.Firstname = :firstname AND Students.Surname = :surname AND Students.yearGroup = :yearGroup AND Students.tutorGroup = :tutorGroup";

    */

    // NEW CODE

    $searchQuery = "SELECT Students.Title AS sTitle, Students.Email AS sEmail, Parents.Title AS pTitle, Parents.Firstname AS pFirstname, Parents.Surname AS pSurname, Parents.Email AS pEmail, 					Parents.contactNumber FROM Students LEFT JOIN Parents ON Students.contactParentID = Parents.parentID WHERE studentID = :studentID";
    if($searchResult = $conn->prepare($searchQuery)){
        $searchResult->bindParam(':studentID', $studentID);
        $searchResult->execute();
        $numResults = $searchResult->rowCount();
        if($numResults>0){
            echo('<p id="deleteRecordText"></p>');
            echo('<table id="recordTable" style="width:100%" border=1>
			  <tr>
				<th>Student title</th>
				<th>Student email</th>
				<th>Parent title</th>
				<th>Parent full name</th>
				<th>Parent email</th>
				<th>Parent phone number</th>
				<th>Edit record</th>
				<th style="color:red;">Delete record</th>
			  </tr>');
            while($row = $searchResult->fetch(PDO::FETCH_ASSOC)){
                echo('<tr>');
                echo('<td>'.$row['sTitle'].'</td>');
                echo('<td>'.$row['sEmail'].'</td>');
                echo('<td>'.$row['pTitle'].'</td>');
                echo('<td>'.$row['pFirstname'].' '.$row['pSurname'].'</td>');
                echo('<td>'.$row['pEmail'].'</td>');
                echo('<td>'.$row['contactNumber'].'</td>');
                if($adminLevel==3){
                    echo('<td><a href="editRecord.php?type=inputStudentEdit&studentID='.$studentID.'">Edit record</a></td>');
                    echo('<td id="deleteRecord" onclick="deleteRecord()" style="color:red;"><b>Click here to delete this record</b></td>');
                    echo('<button id="deleteRecordButton" onclick="confirmDeletion()">Delete record permanently</button>');
                    echo('<script>
					var deleteRecordButton = document.getElementById("deleteRecordButton");
					deleteRecordButton.style.display = "none";
					function deleteRecord(){
						var recordTable = document.getElementById("recordTable");
						recordTable.remove();
						var deleteRecordText = document.getElementById("deleteRecordText");
						deleteRecordText.innerHTML = "<p style=color:red;><b>Are you sure you want to delete this record? By completing this action you will be deleting this record permanently with no 							chance of retrieval. This action will be logged.</b></p>";
						deleteRecordButton.style.display = "block";
					}
					function confirmDeletion(){
						location.assign("deleteRecord.php?type=deleteStudentRecord&studentID='.$studentID.'");
					}
					</script>');
                }
                else{
                    echo('<td><p style="color:red;"> No permission. </p></td>');
                }
                echo('</tr>');
            }
            echo('</table>');
        }else{
            //tell the user it couldn't find anyone
            echo("<b><p style='color:red; text-align:center;'>No results found.</p></b>");
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}



function parentProfileActions($selectedRecord, $adminLevel){

    $conn = $GLOBALS['conn'];


    echo('<p id="deleteRecordText"></p>');
    echo('<p id="addChildText"></p>');
    echo('<table id="recordTable" style="width:100%" border=1>
	  <tr>
		<th>Parent title</th>
		<th>Parent full name</th>
		<th>Parent email</th>
		<th>Parent phone number</th>
		<th>Add child</th>
		<th>Edit record</th>
		<th style="color:red;">Delete record</th>
	  </tr>');
    while($row = $selectedRecord->fetch(PDO::FETCH_ASSOC)){
        $parentID = $row['parentID'];
        echo('<tr>');
        echo('<td>'.$row['pTitle'].'</td>');
        echo('<td>'.$row['pFirstname'].' '.$row['pSurname'].'</td>');
        echo('<td>'.$row['pEmail'].'</td>');
        echo('<td>'.$row['contactNumber'].'</td>');
        echo('<td><p onclick="addChild()"><b>Click here to add a child</b></p></td>');

        $sql = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students";
        if($results = $conn->prepare($sql)){
            $results->execute();
            echo('<div id="addChildForm">
			<form method="post" action="searchOutput.php?type=manageParent&parentID='.$parentID.'">
				<b>Select student:</b><br><input list="students" name="studentID">
					<datalist id="students">');
            while($row = $results->fetch(PDO::FETCH_ASSOC)){
                echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname'].' '.$row['yearGroup'].$row['tutorGroup']);
            }
            echo('</datalist><br><br>
			<button type="submit">Add</button>
			</form></div>
			');
        }else{
            //die(print($GLOBALS['error']));
            die("Error: Please try again later.");
        }
        echo('<script>
			var recordTable = document.getElementById("recordTable");
			var addChildForm = document.getElementById("addChildForm");
			var addChildText = document.getElementById("addChildText");
			addChildForm.style.display = "none";
			
			function addChild(){
				recordTable.remove();
				addChildText.innerHTML = "<h2>Add child</h2>";
				addChildForm.style.display = "block";
			}
			</script>');

        if($adminLevel==3){
            echo('<td><a href="editRecord.php?type=inputParentEdit&parentID='.$parentID.'">Edit record</a></td>');
            echo('<td id="deleteRecord" onclick="deleteRecord()" style="color:red;"><b>Click here to delete this record</b></td>');
            echo('<button id="deleteRecordButton" onclick="confirmDeletion()">Delete record permanently</button>');
            echo('<script>
			var deleteRecordButton = document.getElementById("deleteRecordButton");
			deleteRecordButton.style.display = "none";
			
			function deleteRecord(){
				recordTable.remove();
				var deleteRecordText = document.getElementById("deleteRecordText");
				deleteRecordText.innerHTML = "<p style=color:red;><b>Are you sure you want to delete this record? By completing this action you will be deleting this record permanently with no 							chance of retrieval. This action will be logged.</b></p>";
				deleteRecordButton.style.display = "block";
			}
			function confirmDeletion(){
				location.assign("deleteRecord.php?type=deleteParentRecord&parentID='.$parentID.'");
			}
			</script>');
        }
        else{
            echo('<td><p style="color:red;"> No permission. </td>');
        }
        echo('</tr>');
    }
    echo('</table>');
}



function parentProfileReview(){

    $adminLevel = $GLOBALS['adminLevel'];

    if($adminLevel<2){
        die(header('location:dashboard.php'));
    }

    $requiredFields = array('studentID', 'parentID');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];



    echo("<h2 style='text-align:center;'>Parent profile search results</h2>");


    $studentID = $_POST['studentID'];
    $searchQuery = "SELECT parentID, Parents.Title AS pTitle, Parents.Firstname AS pFirstname, Parents.Surname AS pSurname, Parents.Email AS pEmail, Parents.contactNumber FROM Students INNER JOIN Parents 	ON Students.contactParentID = Parents.parentID WHERE studentID = :studentID";
    if($selectedRecord = $conn->prepare($searchQuery)){
        $selectedRecord->bindParam(':studentID', $studentID);
        $selectedRecord->execute();
        $numResults = $selectedRecord->rowCount();
        if($numResults>0){
            parentProfileActions($selectedRecord, $adminLevel);
        }else{
            $parentID = $_POST['parentID'];
            $searchQuery = "SELECT parentID, Title AS pTitle, Firstname AS pFirstname, Surname AS pSurname, Email AS pEmail, contactNumber FROM Parents WHERE parentID = :parentID";
            if($selectedRecord = $conn->prepare($searchQuery)){
                $selectedRecord->bindParam(':parentID', $parentID);
                $selectedRecord->execute();
                $numResults = $selectedRecord->rowCount();
                if($numResults>0){
                    parentProfileActions($selectedRecord, $adminLevel);
                }else
                    echo('<p style="color:red; text-align:center;"><b>No parent account registered for this student.</b></p>');
            }
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}




function manageParentRecord(){

    $adminLevel = $GLOBALS['adminLevel'];

    if($adminLevel<2){
        die(header('location:dashboard.php'));
    }

    $requiredFields = array('studentID');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];

    $parentID = $_GET['parentID'];
    $studentID = $_POST['studentID'];

    $recordValues = [
        ':parentID' => $parentID,
        ':studentID' => $studentID,
    ];

    $sql = "UPDATE Students SET contactParentID = :parentID WHERE studentID = :studentID";
    if($updateRecord = $conn->prepare($sql)){
        try{
            $updateRecord->execute($recordValues);
        }
        catch(PDOException $e){
            die('<p style="text-align:center;"><a href="dashboard.php">Invalid input. Click here to return to dashboard.</a></p>');
        }
        echo('<h2 style="color:limegreen; text-align:center;">Record updated!</h2>');
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }

    $action = "Assigned a parent for a student";
    logAction($action);
}




function staffProfileReview(){

    $adminLevel = $GLOBALS['adminLevel'];

    if($adminLevel!=3){
        die(header('location:dashboard.php'));
    }

    $requiredFields = array('staffID');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];
    $staffID = $_POST['staffID'];



    $sql = "SELECT Title, Firstname, Surname, Email, adminLevel FROM Staff WHERE staffID = :staffID";
    if($selectedRecord = $conn->prepare($sql)){
        $selectedRecord->bindParam(':staffID', $staffID);
        $selectedRecord->execute();
        $numResults = $selectedRecord->rowCount();
        if($numResults>0){

            echo('<p id="deleteRecordText"></p>');
            echo('<table id="recordTable" style="width:100%" border=1>
			  <tr>
				<th>Staff title</th>
				<th>Staff firstname</th>
				<th>Staff surname</th>
				<th>Staff email</th>
				<th>Admin Level</th>
				<th>Edit record</th>
				<th style="color:red;">Delete record</th>
			  </tr>');
            while($row = $selectedRecord->fetch(PDO::FETCH_ASSOC)){
                echo('<tr>');
                echo('<td>'.$row['Title'].'</td>');
                echo('<td>'.$row['Firstname'].'</td>');
                echo('<td>'.$row['Surname'].'</td>');
                echo('<td>'.$row['Email'].'</td>');
                echo('<td>'.$row['adminLevel'].'</td>');
                echo('<td><a href="editRecord.php?type=inputStaffEdit&staffID='.$staffID.'">Edit record</a></td>');
                echo('<td id="deleteRecord" onclick="deleteRecord()" style="color:red;"><b>Click here to delete this record</b></td>');
                echo('<button id="deleteRecordButton" onclick="confirmDeletion()">Delete record permanently</button>');
                echo('<script>
				var deleteRecordButton = document.getElementById("deleteRecordButton");
				deleteRecordButton.style.display = "none";
				function deleteRecord(){
					var recordTable = document.getElementById("recordTable");
					recordTable.remove();
					var deleteRecordText = document.getElementById("deleteRecordText");
					deleteRecordText.innerHTML = "<p style=color:red;><b>Are you sure you want to delete this record? By completing this action you will be deleting this record permanently with no 							chance of retrieval. This action will be logged.</b></p>";
					deleteRecordButton.style.display = "block";
				}
				function confirmDeletion(){
					location.assign("deleteRecord.php?type=deleteStaffRecord&staffID='.$staffID.'");
				}
				</script>');
                echo('</tr>');
            }
            echo('</table>');

        }else{
            echo('<p style="color: red; text-align:center;"><b>No results found</b></p>');
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}





function fullReportLog(){

    $adminLevel = $GLOBALS['adminLevel'];

    if($adminLevel!=3){
        die(header('location:dashboard.php'));
    }

    $conn = $GLOBALS['conn'];

    echo('<h2 style="text-align:center;">Review all reports</h2>');

    $sql = "SELECT deleted, Firstname, Surname, yearGroup, tutorGroup, reason, Claim.incidentDate, incidentLocation, incidentDetails, notes, replacementCase, statusUpdate, crimeNumber, invoiceNumber, 		repairCost, workCarried, policeInfo, reportNumber, policeStation FROM Incident INNER JOIN Claim ON Incident.incidentID=Claim.incidentID INNER JOIN Status ON Incident.incidentID = Status.incidentID 		INNER JOIN iPads ON Incident.ipadNumber=iPads.ipadNumber INNER JOIN Students ON iPads.studentID=Students.studentID";

    if($results = $conn->prepare($sql)){
        $results->execute();
        echo('<table style="width:100%" align="center" border=1>
		<tr>
		<th>Concluded?</th>
		<th>Student full name</th>
		<th>Student group</th>
		<th>Incident</th>
		<th>Date of incident</th>
		<th>Incident location</th>
		<th>Incident details</th>
		<th>Extra notes</th>
		<th>Replacement case</th>
		<th>Status</th>
		<th>Crime number</th>
		<th>Invoice number</th>
		<th>Repair cost</th>
		<th>Work Carried</th>
		<th>Police information</th>
		<th>Report number</th>
		<th>Police station</th>
		</tr>');
        while($row = $results->fetch(PDO::FETCH_ASSOC)){
            echo("<tr>");
            if($row['deleted']==1){
                echo('<td style="color:limegreen;"><b>Yes</b></td>');
            }
            else{
                echo('<td style="color:red;"><b>No</b></td>');
            }
            echo('<td>'.$row['Firstname'].' '.$row['Surname'].'</td>');
            echo('<td>'.$row['yearGroup'].' '.$row['tutorGroup'].'</td>');
            echo('<td>'.$row['reason'].'</td>');
            echo('<td>'.$row['incidentDate'].'</td>');
            echo('<td>'.$row['incidentLocation'].'</td>');
            echo('<td>'.$row['incidentDetails'].'</td>');
            echo('<td>'.$row['notes'].'</td>');
            if($row['replacementCase']==1){
                echo('<td>Yes</td>');
            }else{
                echo('<td>No</td>');
            }
            echo('<td>'.$row['statusUpdate'].'</td>');
            echo('<td>'.$row['crimeNumber'].'</td>');
            echo('<td>'.$row['invoiceNumber'].'</td>');
            echo('<td>'.$row['repairCost'].'</td>');
            echo('<td>'.$row['workCarried'].'</td>');
            echo('<td>'.$row['policeInfo'].'</td>');
            echo('<td>'.$row['reportNumber'].'</td>');
            echo('<td>'.$row['policeStation'].'</td>');
            echo('</tr>');
        }
        echo('</table>');
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}





function activeIncidents(){


    $adminLevel = $GLOBALS['adminLevel'];
    if($adminLevel!=3){
        die(header('location:dashboard.php'));
    }

    echo('<h2 style="text-align:center;">Review active incidents</h2>');

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
                echo('<td><a href="incidentUpdate.php?reportNumber='.$row['incidentID'].'">Review this incident</a></td>');
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






function deviceNeeded(){

    $conn = $GLOBALS['conn'];

    echo('<h2 style="text-align:center;">List of students without an iPad</h2>');

    $studentRecords = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students";
    if($studentResults = $conn->prepare($studentRecords)){
        $studentResults->execute();
        echo("<table style='width:100%' border=1>
		<tr>
		<th>Student full name</th>
		<th>Student group</th>
		</tr>");
        while($studentRow = $studentResults->fetch(PDO::FETCH_ASSOC)){
            $combinedRecords = "SELECT * FROM iPads WHERE studentID = :studentID";
            $combinedResults = $conn->prepare($combinedRecords);
            $combinedResults->bindParam(':studentID', $studentRow['studentID']);
            $combinedResults->execute();
            $numResults = $combinedResults->rowCount();
            if($numResults<1){
                echo('<tr>');
                echo('<td>'.$studentRow['Firstname'].' '.$studentRow['Surname'].'</td>');
                echo('<td>'.$studentRow['yearGroup'].$studentRow['tutorGroup'].'</td>');
                echo('</tr>');
            }
        }
        echo('</table>');
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}


function ipadSearch(){

    $requiredFields = array('snumber');
    inputCheck($requiredFields);

    $conn = $GLOBALS['conn'];
    $adminLevel = $GLOBALS['adminLevel'];

    echo("<h2 style='text-align:center;'>iPad search results</h2>");


    $searchQuery = "SELECT Model, ipadNumber, Active, Firstname, Surname, yearGroup, tutorGroup, Email FROM iPads LEFT JOIN Students ON iPads.studentID = Students.studentID WHERE serialNumber = :snumber";
    if($searchResult = $conn->prepare($searchQuery)){
        $searchResult->bindParam(':snumber', $_POST['snumber']);
        $searchResult->execute();
        $numResults = $searchResult->rowCount();
        if ($numResults>0){
            $row = $searchResult->fetch(PDO::FETCH_ASSOC);
            $ipadNumber = $row['ipadNumber'];
            echo('<p id="reassignDeviceText"></p>');
            echo("<table id='recordTable' style='width:100%' border=1>
			<tr>
			<th>iPad Model</th>
			<th>Student name</th>
			<th>Student surname</th>
			<th>Year group</th>
			<th>Tutor group</th>
			<th>Email</th>
			<th>Active?</th>
			<th>Admin actions</th>
			</tr>
			<tr>");
            echo('<td>'.$row['Model'].'</td>');
            echo('<td>'.$row['Firstname'].'</td>');
            echo('<td>'.$row['Surname'].'</td>');
            echo('<td>'.$row['yearGroup'].'</td>');
            echo('<td>'.$row['tutorGroup'].'</td>');
            echo('<td>'.$row['Email'].'</td>');
            if($row['Active'] == 0){
                echo('<td>No</td>');
            }
            else{
                echo('<td>Yes</td>');
            }
            if($adminLevel==3){

                if($row['Active']==0){
                    echo('<td><a href="searchOutput.php?type=ipadManagement&action=statusAlteration&alteration=activation&ipadNumber='.$ipadNumber.'">Click here to activate this device</a>');
                }else{
                    echo('<td><a href="searchOutput.php?type=ipadManagement&action=statusAlteration&alteration=deactivation&ipadNumber='.$ipadNumber.'">Click here to deactivate this device</a>');
                }
                echo('<br><p onclick="reassignDevice()"><b>Click here to reassign this device</b></p></td>');

                $sql = "SELECT studentID, Firstname, Surname, yearGroup, tutorGroup FROM Students WHERE studentID NOT IN (SELECT studentID FROM iPads)";
                if($selectedRecord = $conn->prepare($sql)){
                    $selectedRecord->execute();
                    echo('<div id="reassignDeviceForm">
						<form method="post" action="searchOutput.php?type=ipadManagement&action=reallocation&ipadNumber='.$row['ipadNumber'].'">
							<b>Select student:</b><br><input list="students" name="studentID">
								<datalist id="students">');
                    while($row = $selectedRecord->fetch(PDO::FETCH_ASSOC)){
                        echo('<option value="'.$row['studentID'].'">'.$row['Firstname'].' '.$row['Surname'].' '.$row['yearGroup'].$row['tutorGroup']);
                    }
                    echo('</datalist><br><br>
					<button type="submit">Add</button>
					</form></div>
					');

                }else{
                    //die(print($GLOBALS['error']));
                    die("Error: Please try again later");
                }
                echo('<script>
					var reassignDeviceForm = document.getElementById("reassignDeviceForm");
					var recordTable = document.getElementById("recordTable");
					var reassignDeviceText = document.getElementById("reassignDeviceText");
					reassignDeviceForm.style.display = "none";
					
					function reassignDevice(){
						recordTable.remove();
						reassignDeviceForm.style.display = "block";
						reassignDeviceText.innerHTML = "<h2>Reassign device</h2>";
					}
					</script>');
            }
            else{
                echo('<td>None</td>');
            }
            echo("</tr></table>");
        }
        else{
            echo("<b><p style='color:red; text-align:center;'>No results found.</p></b>");
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}





function ipadManagement($action){

    $adminLevel = $GLOBALS['adminLevel'];
    if($adminLevel<3){
        die(header('location:dashboard.php'));
    }

    $conn = $GLOBALS['conn'];
    $ipadNumber = $_GET['ipadNumber'];

    $recordValues = [
        ':ipadNumber' => $ipadNumber,
    ];

    switch($action){

        case 'alteration':
            $sql = "UPDATE iPads SET Active = :alteration WHERE ipadNumber = :ipadNumber";
            if($updateRecord = $conn->prepare($sql)){

                switch($_GET['alteration']){

                    case "activation":
                        array_push($recordValues, 1);
                        break;
                    case "deactivation":
                        array_push($recordValues, 0);
                        break;
                    default:
                        die(header('location:dashboard.php'));
                }

                try{
                    $updateRecord->execute($recordValues);
                }
                catch(PDOException $e){
                    die('<p style="text-align:center;"><a href="dashboard.php">Invalid input. Click here to return to dashboard.</a></p>');
                }
                echo('<h2 style="color:limegreen; text-align:center;">Success - iPad status altered!</h2>');
            }else{
                //die(print($GLOBALS['error']));
                die("Error: Please try again later.");
            }

            $action = "Changed the activation status of an iPad";
            logAction($action);
            break;
        case 'reassign':

            $requiredFields = array('studentID');
            inputCheck($requiredFields);

            $studentID = $_POST['studentID'];

            array_push($recordValues, $studentID);
            $sql = "UPDATE iPads SET studentID = :studentID WHERE ipadNumber = :ipadNumber";
            if($updateRecord = $conn->prepare($sql)){
                try{
                    $updateRecord->execute($recordValues);
                }
                catch(PDOException $e){
                    die('<p style="text-align:center;"><a href="dashboard.php">Invalid input. Click here to return to							dashboard.</a></p>');
                }
                echo('<h2 style="color:limegreen; text-align:center;">Success - device reassigned!</h2>');
            }else{
                //die(print($GLOBALS['error']));
                die("Error: Please try again later.");
            }

            $action = "Reallocated an iPad to another student";
            logAction($action);
            break;
        default:
            die(header('location:dashboard.php'));
    }
}




function fullLogReview(){

    $adminLevel = $GLOBALS['adminLevel'];

    if($adminLevel!=3){
        die(header('location:dashboard.php'));
    }

    $conn = $GLOBALS['conn'];


    echo('<h2 style="text-align:center;">Log review</h2>');

    echo('<p id="deleteText"></p>');

    $sql = "SELECT userEmail, action, actionTime FROM Logs";
    if($selectedRecords = $conn->prepare($sql)){
        $selectedRecords->execute();
        $numResults = $selectedRecords->rowCount();
        if($numResults>0){
            echo('<table id="logTable" style="width:100%" align="center" border=1>
				<tr>
				<th>User Title</th>
				<th>User full name</th>
				<th>User Email</th>
				<th>Action</th>
				<th>Time of action</th>
				</tr>');
            while($row = $selectedRecords->fetch(PDO::FETCH_ASSOC)){
                echo('<tr>');
                $sqlQry = "SELECT Title, Firstname, Surname FROM Staff WHERE Email = :email";
                if($selectedRecord = $conn->prepare($sqlQry)){
                    $selectedRecord->bindParam(':email', $row['userEmail']);
                    $selectedRecord->execute();
                    $numResults = $selectedRecord->rowCount();
                    if($numResults<1){
                        $sqlQry = "SELECT Title, Firstname, Surname FROM Parents WHERE Email = :email";
                        $selectedRecord = $conn->prepare($sqlQry);
                        $selectedRecord->bindParam(':email', $row['userEmail']);
                        $selectedRecord->execute();
                        $numResults = $selectedRecord->rowCount();
                        if($numResults<1){
                            $sqlQry = "SELECT Title, Firstname, Surname, yearGroup, tutorGroup FROM Students WHERE Email =								:email";
                            $selectedRecord = $conn->prepare($sqlQry);
                            $selectedRecord->bindParam(':email', $row['userEmail']);
                            $selectedRecord->execute();
                            $numResults = $selectedRecord->rowCount();
                        }
                    }
                    if($numResults>0){
                        $newRow = $selectedRecord->fetch(PDO::FETCH_ASSOC);
                        echo('<td>'.$newRow['Title'].'</td>');
                        echo('<td>'.$newRow['Firstname'].' '.$newRow['Surname']);
                    }
                    else{
                        echo('<td>N/A</td>');
                        echo('<td>N/A</td>');
                    }
                }else{
                    //die(print($GLOBALS['error']));
                    die("Error: Please try again later.");
                }
                echo('<td>'.$row['userEmail'].'</td>');
                echo('<td>'.$row['action'].'</td>');
                echo('<td>'.$row['actionTime'].'</td>');
                echo('</tr>');
            }
            echo('</table>');
            echo('<br><br><h2 id="initialDeletionText" style="color:red" onclick="deleteLogs()"><u>Click this text to delete			all logs</u></h2>');
            echo('<button id="confirmDeletion" onclick="confirmDeleteLogs()">Delete all logs permanently</button>');
            echo('<script>
			var deleteButton = document.getElementById("confirmDeletion");
			var logTable = document.getElementById("logTable");
			var deleteText = document.getElementById("deleteText");
			var initialDeletionText = document.getElementById("initialDeletionText");
			deleteButton.style.display = "none";
			
			function deleteLogs(){
				logTable.remove();
				initialDeletionText.style.display = "none";
				deleteButton.style.display = "block";
				deleteText.innerHTML = "<h3 style=color:red;>Are you sure you want to delete all logs? There is no way of					retrieving this data after deletion.</h3>";
			}
			
			function confirmDeleteLogs(){
				location.assign("deleteRecord.php?type=logDeletion");
			}
			</script>');
        }
        else{
            echo('<h2 style="color:red; text-align:center;">No logs found.</h2>');
        }
    }else{
        //die(print($GLOBALS['error']));
        die("Error: Please try again later.");
    }
}






echo(
'<html>
		<head>
			<title>Search results</title>
		</head>
		<body>
	');



switch($_GET['type']){
    case "studentIpad":
        studentIpadReview();
        break;
    case "studentProfile":
        studentProfileReview();
        break;
    case "parentProfile":
        parentProfileReview();
        break;
    case "manageParent":
        manageParentRecord();
        break;
    case "staffProfile":
        staffProfileReview();
        break;
    case "fullReportLog":
        fullReportLog();
        break;
    case 'activeIncidents':
        activeIncidents();
        break;
    case "deviceNeeded":
        deviceNeeded();
        break;
    case "ipadSearch":
        ipadSearch();
        break;
    case "ipadManagement":
        switch($_GET['action']){
            case 'statusAlteration':
                ipadManagement('alteration');
                break;
            case 'reallocation':
                ipadManagement('reassign');
                break;
            default:
                die(header('location:dashboard.php'));
        }
        break;
    case "fullLogs":
        fullLogReview();
        break;
    default:
        die(header('location:dashboard.php'));
}


//Close database connection
$conn = null;

echo("<p><a href='dashboard.php?'>Return to Dashboard</a></p>
	</body>
	</html>
	");
?>