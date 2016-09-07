<?php
	include_once "./includes/SQLDetails.php";
	include_once "./includes/functions.php";

	// Error message variable
	$errorMessage = "";

	// Code to be executed if the room is to be deleted
	if (isset($_POST['editFormDelete']))
	{
		if ($bookingSystem->deleteFromTable("Rooms", array("roomID"=>$_POST['editFormRoomID'])))
		{
			$successMessage = $_POST['editFormRoomID']." has been deleted successfully.";
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllRooms.php'>";
		}
		else
		{
			$errorMessage = "Something went wrong when deleting the room.";
		}
	}
	else if ($_POST['editFormSubmit'])
	{
		$editFormRoomID = $_POST['editFormRoomID'];
		$editFormRoomName = $_POST['editFormRoomName'];
		$editFormCapacity = $_POST['editFormCapacity'];

		// If the form has been submitted, we assign the values of the POST variables into the form variables
		//$editFormUsername = $_POST['editFormUsername'];
	}
	elseif (!isset($_GET['roomID']))
	{
		// If there is no userID speicifed by GET and there's no POST submission set, something has gone wrong and we tell the user this
		$errorMessage .= "Something has gone wrong; no room ID specified.<br />";
	}
	else
	{
		// Else, everything is working and we assign the form variables the values of the userID taken from the database
		$roomResult = $bookingSystem->queryTable("Rooms", array("roomID"=>$_GET['roomID']), 1, "roomID");

		$editFormRoomID = $_GET['roomID'];
		$editFormRoomName = $roomResult['name'];
		$editFormCapacity = $roomResult['capacity'];
	}

	// If the form has been submitted and there have been no errors found
	if (($_POST['editFormSubmit']) and ($errorMessage == ""))
	{
		$updateArray = array(
							"roomID"=>$editFormRoomID, 
							"name"=>$editFormRoomName, 
							"capacity"=>$editFormCapacity
							);

		if($bookingSystem->updateTable("Rooms", $updateArray, array("roomID"=>$editFormRoomID), true))
		{
			$errorMessage = "";
			$successMessage = "Saving changes...";
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllRooms.php'>";
		}
		else
		{
			$errorMessage = "There was a problem inserting the data to the table.";
		}
	}
?>

<!DOCTYPE html>
<html>
	<head>
		<!-- This includes the stylesheet for the system -->
		<link rel="stylesheet" href="./includes/styles.css" type="text/css" />
		<!-- Includes the header, which includes other useful files, checks user login details and generates an appropriate title -->
		<?php include "./includes/header.php"; ?>
	</head>
	<body>
		<!-- Includes the menu for navigating the site, which will be generated differently for each user -->
		<?php include_once "./includes/menu.php";

		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>

			<h1><a href="./admin.php">Admin Panel</a></h1>
			<h2><a href="./viewAllRooms.php">View All Rooms</a></h2>
			<h3><a href="<?php echo $_SERVER['PHP_SELF']."?roomID=".$_GET['roomID']; ?>">Edit Single Room</a></h3>

			<form id="editForm" name="editForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="editFormFieldSet" id="editFormFieldSet" class="userFormFieldSet">
					<legend>Manually edit Room</legend>

					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="editFormRoomID">Room ID:</label>
					<input type="text" name="editFormRoomIDDisabled" maxlength="10" disabled value="<?php echo $editFormRoomID; ?>" />
					<input type="hidden" name="editFormRoomID" value="<?php echo $editFormRoomID; ?>" />
					<label for="editFormRoomName">Room Name:</label>
					<input type="text" name="editFormRoomName" maxlength="40" autofocus value="<?php echo $editFormRoomName; ?>" />
					<label for="editFormCapacity">Room Capacity:</label>
					<input type="number" name="editFormCapacity" maxlength="4" value="<?php echo $editFormCapacity; ?>" />

					<input type="submit" value="Save Edited Room" id="editFormSubmit" name="editFormSubmit" form="editForm" />
					<input type="submit" value="Delete Room" id="editFormDelete" name="editFormDelete" form="editForm" />
				</fieldset>
			</form>
		<?php
		}
		else
		{
		?>
			<p><?php returnUserLevelError($bookingSystem, $userLevelNeeded); ?></p>
		<?php
		}
		?>
	</body>
</html>