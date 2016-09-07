<!DOCTYPE html>
<html>
	<head>
		<!-- This includes the stylesheet for the system -->
		<link rel="stylesheet" href="./includes/styles.css" type="text/css" />
		<!-- Includes the header, which includes other useful files, checks user login details and generates an appropriate title -->
		<?php include "./includes/header.php"; ?>
	</head>
	<body>
		<?php
		// Includes the menu for navigating the site, which will be generated differently for each user
		include "./includes/menu.php";

		// The user needs to be an administrator to view the page
		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
			// Variables that contain error and success messages
			$errorMessage = "";
			$successMessage = "";

			if (isset($_POST['addRoomFormSubmit']))
			{
				if ($_POST['addRoomFormRoomID'] == "")
					$errorMessage .= "Please enter a room ID.";

				// If the form has been submitted, use the posted values as the default values for the fields
				$addRoomFormRoomID = $_POST['addRoomFormRoomID'];
				$addRoomFormRoomName = $_POST['addRoomFormRoomName'];
				$addRoomFormCapacity = $_POST['addRoomFormCapacity'];
			}

			// If the form has been submitted and there are no errors:
			if ((isset($_POST['addRoomFormSubmit'])) AND ($errorMessage == ""))
			{
				if ($addRoomFormCapacity == "")
					$addRoomFormCapacity = 0;

				// Builds the insert array
				$insertArray = array("roomID"=>$addRoomFormRoomID,
										"name"=>$addRoomFormRoomName,
										"capacity"=>$addRoomFormCapacity
										);
				if($bookingSystem->insertTable("Rooms", $insertArray))
				{
					$successMessage .= "Insert successful.";
					$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllRooms.php'>";
				}
				else
				{
					$errorMessage .= "Insert  for ".$_POST['addRoomFormRoomID']." failed.";
				}
			}
			?>
			<h1>Add Event</h1>
			<h2>Add Event: Single</h2>
			
			<form id="addRoomForm" name="addRoomForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="addRoomFormFieldSet" id="addRoomFormFieldSet" class="userFormFieldSet">
					<legend>Add Event: Single</legend>

					<!-- Displays the success and error messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="addRoomFormRoomID">RoomID:</label>
					<input type="text" name="addRoomFormRoomID" maxlength="10" autofocus value="<?php echo $_POST['addRoomFormRoomID']; ?>" />
					<label for="addRoomFormRoomName">Room Name:</label>
					<input type="text" name="addRoomFormRoomName" maxlength="40" autofocus value="<?php echo $_POST['addRoomFormRoomName']; ?>" />
					<label for="addRoomFormCapacity">Capacity:</label>
					<input type="number" name="addRoomFormCapacity" maxlength="4" value="<?php echo $_POST['addRoomFormCapacity']; ?>" />

					<input type="submit" value="Add Room" id="addRoomFormSubmit" name="addRoomFormSubmit" form="addRoomForm" />
				</fieldset>
			</form>
		<?php
		}
		else
		{
			// The user is shown a suitable error message if they aren't eligible to view the page
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>