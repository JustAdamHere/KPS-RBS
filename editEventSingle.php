<?php
	include_once "./includes/SQLDetails.php";
	include_once "./includes/functions.php";

	// Error message variable to store error messages
	$errorMessage = "";
	if (isset($_POST['editFormDelete']))
	{
		// Query to delete the record
		if ($bookingSystem->deleteFromTable("Events", array("ID"=>$_POST['editFormEventID'])))
		{
			// Tells the user which event ID has been deleted and redirects them
			$successMessage = "Event ID ".$_POST['editFormEventID']." has been deleted successfully.";
			$successMessage .= "<br />Loading viewAllEvents.php...";
			// Unserialises the GET variables and returns the user to viewAllEvents.php with what ever the parameters were set as before the user wanted to add an event
			$returnGET = unserialize($_POST['editFormReturnGET']);
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='5; URL=./viewAllEvents.php?view=".$returnGET['view'];

			if (isset($returnGET['roomID']))
				$successMessage .= "&roomID=".$returnGET['roomID'];
			else if (isset($returnGET['teacherID']))
				$successMessage .= "&teacherID=".$returnGET['teacherID'];

			if (isset($returnGET['referenceWeek']))
				$successMessage .= "&referenceWeek=".$returnGET['referenceWeek'];

			$successMessage .= "'>";
		}
		else
		{
			$errorMessage = "Something went wrong when deleting the event.";
		}
	}
	else if ($_POST['editFormSubmit'])
	{
		// Sets the POST variables into more conviniently named variables that can be assigned from any other method
		$editFormName = $_POST['editFormName'];
		$editFormOwnerID = $_POST['editFormOwnerID'];
		$editFormStartTimestamp = $_POST['editFormStartTimestamp'];
		$editFormRoomID = $_POST['editFormRoomID'];

		// If the form has been submitted, we test to see if any of the fields are blank
		if ($editFormName == "")
			$errorMessage .= "Please enter an event name.<br />";
		if ($editFormOwnerID == "")
			$errorMessage .= "There is no owner attached to this event.<br />";;
		if ($editFormRoomID == "")
			$errorMessage .= "There is no room attached to this.<br />";

		// Set variables to post values
	}
	elseif (!isset($_GET['eventID']))
	{
		// If there is no userID speicifed by GET and there's no POST submission set, something has gone wrong and we tell the user this
		$errorMessage .= "Something has gone wrong; no event ID specified.<br />";
	}
	else
	{
		// Else, everything is working and we assign the form variables the values of the userID taken from the database
		$eventResult = $bookingSystem->queryTable("Events", array("ID"=>$_GET['eventID']), 1);

		$editFormEventID = $_GET['eventID'];
		$editFormName = $eventResult['name'];
		$editFormOwnerID = $eventResult['ownerID'];
		$editFormStartTimestamp = $eventResult['startTimestamp'];
		$editFormRoomID = $eventResult['roomID'];

		// This code figures out what the timestamp of the date and period is
		$explodedTimestamp = explode("T", date("Y-m-d\THi", $editFormStartTimestamp));
		$eventDate = $explodedTimestamp[0];
		$periodTime = $explodedTimestamp[1];

		$editFormPeriod = $bookingSystem->queryTable("Periods", array("startTime"=>$periodTime), 1, "periodNumber")['periodNumber'];
	}

	// If the form has been submitted and there have been no errors found
	if (($_POST['editFormSubmit']) and ($errorMessage == ""))
	{
		$editFormEventID = $_POST['editFormEventID'];
		$editFormName = $_POST['editFormName'];
		$editFormOwnerID = $_POST['editFormOwnerID'];
		$editFormDate = $_POST['editFormDate'];
		$editFormRoomID = $_POST['editFormRoomID'];
		$editFormPeriod = $_POST['editFormPeriod'];

		$updateArray = array(
							"name"=>$editFormName, 
							"ownerID"=>$editFormOwnerID, 
							"roomID"=>$editFormRoomID
							);

		// The code below figures out what the start timestamp for the event will be
		$periodTime = $bookingSystem->queryTable("Periods", array("periodNumber"=>$editFormPeriod), 1, "periodNumber")['startTime'];
		$insertPeriodTimeFormatted = formatTime($periodTime); // Formats the period time with a colon
		$editFormStartTimestamp = strtotime(date("Y-m-d", strtotime($editFormDate))."T".$insertPeriodTimeFormatted); // Formats the date and time for the period into the correct format then to a unix timestamp

		$updateArray['startTimestamp'] = $editFormStartTimestamp;

		if($bookingSystem->updateTable("Events", $updateArray, array("ID"=>$editFormEventID)))
		{
			$errorMessage = "";
			$successMessage = "Saving changes...";

			// If there was a get string provided
			if (strlen($_POST['editFormReturnGET']) > 0)
			{
				// Unserialises the GET variables and returns the user to viewAllEvents.php with what ever the parameters were set as before the user wanted to add an event
				$returnGET = unserialize($_POST['editFormReturnGET']);
				$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllEvents.php?view=".$returnGET['view'];

				if (isset($returnGET['roomID']))
					$successMessage .= "&roomID=".$returnGET['roomID'];
				else if (isset($returnGET['teacherID']))
					$successMessage .= "&teacherID=".$returnGET['teacherID'];

				if (isset($returnGET['referenceWeek']))
					$successMessage .= "&referenceWeek=".$returnGET['referenceWeek'];

				$successMessage .= "'>";
			}
			else
			{
				// Redirects the user to addEvent.php if there were no GET variables set
				$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllEvents.php'>";							
			}
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
		<!-- // Includes the menu for navigating the site, which will be generated differently for each user -->
		<?php include_once "./includes/menu.php";

		$userLevelNeeded = 2;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>

			<h1><a href="./viewEvents.php">View Events</a></h1>
			<h2>Edit Single Event</h2>

			<form id="editForm" name="editForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="editFormFieldSet" id="editFormFieldSet" class="userFormFieldSet">
					<legend>Manually edit Event</legend>

					<!-- Displays the success and error messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="editFormName">Event Name:</label>
					<input type="text" name="editFormName" maxlength="20" value="<?php echo $editFormName; ?>" />

					<?php
					// This makes sure that only administrators or owners of the event can change the owner
					if ((getUserLevel($bookingSystem, $_COOKIE['userID']) == 3) OR ($_COOKIE['userID'] == $editFormOwnerID))
						$disabled = "";
					else
						$disabled = " disabled";

					?>

					<!-- Hidden field for the event ID -->
					<input type="hidden" name="editFormEventID" id="editFormEventID" value="<?php echo $editFormEventID; ?>" />

					<label for="editFormOwnerID">Teacher ID:</label> <!-- Different for administrators and teachers -->
					<select<?php echo $disabled; ?> name="editFormOwnerID">
						<option value="" disabled selected>Select a teacher</option>
						<?php
						$returnedTeacherList = $bookingSystem->queryTable("Users", array("userLevel" => 2), NULL, " userID, lastName, firstName", "ASC"); // Picks all teachers

						// Creates a multidimensional array for the foreach loop
						if (isset($returnedTeacherList['userID']))
							$teacherResult[0] = $returnedTeacherList;
						else
							$teacherResult = $returnedTeacherList;

						foreach ($teacherResult as $teacher)
						{
							if (isset($editFormOwnerID) AND ($teacher['userID'] == $editFormOwnerID))
								$selected = " selected";
							else
								$selected = "";

							echo "<option value='".$teacher['userID']."'".$selected.">[".$teacher['userID']."] ".$teacher['firstName']." ".$teacher['lastName']."</option>";
						}
						?>
					</select>

					<label for="editFormDate">Date:</label>
					<input type="date" name="editFormDate" value="<?php echo date("Y-m-d", $editFormStartTimestamp); ?>" />

					<label for="editFormPeriod">Period:</label>
					<select name="editFormPeriod">
						<option value="" disabled selected>Select a period</option>
						<?php
						$periodResult = $bookingSystem->queryTable("Periods", "*", NULL, "periodNumber", "ASC"); // Picks all periods
						foreach ($periodResult as $period)
						{
							// If the period number sought is the same as the one in the loop, then set it as selected
							if (isset($editFormPeriod) AND ($period['periodNumber'] == $editFormPeriod))
								$selected = " selected";
							else
								$selected = "";

							echo "<option value='".$period['periodNumber']."'".$selected.">Period ".$period['periodNumber']." (".formatTime($period['startTime'])." to ".formatTime($period['endTime']).")</option>";
						}
						?>
					</select>

					<label for="editFormRoomID">Room:</label>
					<select name="editFormRoomID">
						<option value="" disabled selected>Select a Room</option>
						<?php
						$returnedRoomList = $bookingSystem->queryTable("Rooms", "*", NULL, "roomID", "ASC"); // Picks all rooms

						// Creates a multidimensional array for the foreach loop
						if (isset($returnedRoomList['roomID']))
							$roomResult[0] = $returnedRoomList;
						else
							$roomResult = $returnedRoomList;

						foreach ($roomResult as $room)
							{
								if (isset($editFormRoomID) AND ($room['roomID'] == $editFormRoomID))
									$selected = " selected";
								else
									$selected = "";

								if ($room['capacity'] == "")
									$room['capacity'] = "Unknown capacity"; // If there is no capacity specified, unknown is listed next to the room.

								echo "<option value='".$room['roomID']."'".$selected.">".$room['name']." (".$room['capacity'].")</option>";
							}
						?>
					</select>

					<!-- A hidden field to ensure that we don't lose the GET variables from the previous page when we submit the form -->
					<input type="hidden" value="<?php echo htmlspecialchars($_GET['serializedGetVariables']); ?>" id="editFormReturnGET" name="editFormReturnGET" form="editForm" />

					<input type="submit" value="Save Edited Event" id="editFormSubmit" name="editFormSubmit" form="editForm" />
					<input type="submit" value="Delete Event" id="editFormDelete" name="editFormDelete" form="editForm" />
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