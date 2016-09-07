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

		// These variables store the messages that will be shown after the form has been submitted
		$successMessage = "";
		$errorMessage = "";

		// This page requries the user to be a teacher
		$userLevelNeeded = 2;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<h1>Add Event</h1>
			<h2>Add Event: Single</h2>

			<?php
				// If the form has been submitted:
				if (isset($_POST['addEventSingleFormSubmit']))
				{
					if (strlen($_POST['addEventSingleFormEventName']) < 1)
						$errorMessage .= "Please enter an event name.<br />";
					if (strlen($_POST['addEventSingleFormTeacherID']) < 1)
						$errorMessage .= "Please enter a teacher ID.<br />";
					if (strlen($_POST['addEventSingleFormDate']) < 1)
						$errorMessage .= "Please enter a date.<br />";
					if (strlen($_POST['addEventSingleFormPeriod']) < 1)
						$errorMessage .= "Please enter a period.<br />";
					if (strlen($_POST['addEventSingleFormRoom']) < 1)
						$errorMessage .= "Please enter a room.<br />";

					// Builds an insert array for each of the columns in the table
					$insertArray['name'] = $_POST['addEventSingleFormEventName'];
					$insertArray['ownerID'] = $_POST['addEventSingleFormTeacherID'];
					$insertArray['roomID'] = $_POST['addEventSingleFormRoom'];

					// The code below figures out what the start timestamp for the event will be
					$periodTime = $bookingSystem->queryTable("Periods", array("periodNumber"=>$_POST['addEventSingleFormPeriod']), 1, "periodNumber")['startTime'];
					$insertPeriodTimeFormatted = formatTime($periodTime); // Formats the period time with a colon
					$insertArray['startTimestamp'] = strtotime(date("Y-m-d", strtotime($_POST['addEventSingleFormDate']))."T".$insertPeriodTimeFormatted); // Formats the date and time for the period into the correct format then to a unix timestamp

					// If there are no error messages and the insert was successful
					if(($errorMessage == "") AND ($bookingSystem->insertTable("Events", $insertArray)))
					{
						$successMessage = "Event inserted successfully.";

						// If there was a get string provided
						if (strlen($_POST['addEventSingleFormReturnGET']) > 0)
						{
							// Unserialises the GET variables and returns the user to viewAllEvents.php with what ever the parameters were set as before the user wanted to add an event
							$returnGET = unserialize($_POST['addEventSingleFormReturnGET']);
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
							$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./addEvent.php'>";							
						}
					}
					else
					{
						// Error message if the event fails to insert
						$errorMessage .= "Event failed to insert.";
					}
				}

				if (isset($_GET['time']))
				{
					// If the time is set, we need to figure out what period the event would have been for

					$explodedTimestamp = explode("T", date("Y-m-d\THi", $_GET['time']));
					$eventDate = $explodedTimestamp[0];
					$periodTime = $explodedTimestamp[1];

					$eventPeriod = $bookingSystem->queryTable("Periods", array("startTime"=>$periodTime), 1, "periodNumber")['periodNumber'];
				}
				else if(isset($_POST['addEventSingleFormSubmit']))
				{
					// Sets all the variables if the form has been submitted
					$eventName = $_POST['addEventSingleFormEventName'];
					$eventTeacherID = $_POST['addEventSingleFormTeacherID'];
					$eventDate = $_POST['addEventSingleFormDate'];
					$eventPeriod = $_POST['addEventSingleFormPeriod'];
					$eventRoom = $_POST['addEventSingleFormRoom'];
				}

				if (isset($_GET['serializedGetVariables']))
				{
					// Finds out what the values of each of the variables in the GET array were
					$returnGET = unserialize($_GET['serializedGetVariables']);

					if (isset($returnGET['teacherID']))
						$eventTeacherID = $returnGET['teacherID'];
					elseif (isset($returnGET['roomID']))
						$eventRoom = $returnGET['roomID'];
				}
			?>
			
			<form id="addEventSingleForm" name="addEventSingleForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="addEventSingleFormFieldSet" id="addEventSingleFormFieldSet" class="userFormFieldSet">
					<legend>Add Event: Single</legend>

					<!-- Shows the error and success messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="addEventSingleFormEventName">Event Name:</label>
					<input type="text" name="addEventSingleFormEventName" maxlength="50" autofocus value="<?php echo $eventName; ?>" />

					<label for="addEventSingleFormTeacherID">Teacher ID:</label> <!-- Different for administrators and teachers -->
					<?php
					// Disables the selection of a teacher (and set it to themself) when a teacher is logged in
					if (getUserLevel($bookingSystem, $_COOKIE['userID']) == 2)
						$disabled = " disabled";
					else
						$disabled = "";
					?>
					<select<?php echo $disabled;?> name="addEventSingleFormTeacherID">
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
							// If the current user is one of the teachers in the list, select them as default
							if (isset($eventTeacherID))
							{
								if ($teacher['userID'] == $eventTeacherID)
									$selected = " selected";
								else
									$selected = "";
							}
							// If a teacher is logged in, then restrict them to only adding events for themselves
							elseif (getUserLevel($bookingSystem, $_COOKIE['userID']) == 2)
							{
								if ($teacher['userID'] == $_COOKIE['userID'])
									$selected = " selected";
								else
									$selected = "";
							}

							echo "<option value='".$teacher['userID']."'".$selected.">[".$teacher['userID']."] ".$teacher['firstName']." ".$teacher['lastName']."</option>";
						}
						?>
					</select>

					<?php
					// Ensuring that there is a submittable teacher ID field for teachers that are logged in trying to add events for themselves 
					if(getUserLevel($bookingSystem, $_COOKIE['userID']) == 2)
					{
						echo "<input name='addEventSingleFormTeacherID' type='hidden' value='".$_COOKIE['userID']."' />";
					}
					?>

					<!-- If the date isn't set, then use today's date by default -->
					<?php if(isset($eventDate)) { $defaultDateValue = $eventDate; } else { $defaultDateValue = date("Y-m-d"); } ?>

					<label for="addEventSingleFormDate">Date:</label>
					<input type="date" name="addEventSingleFormDate" value="<?php echo $defaultDateValue; ?>" />

					<label for="addEventSingleFormPeriod">Period:</label>
					<select name="addEventSingleFormPeriod">
						<option value="" disabled selected>Select a period</option>
						<?php
						$periodResult = $bookingSystem->queryTable("Periods", "*", NULL, "periodNumber", "ASC"); // Picks all periods
						foreach ($periodResult as $period)
						{
							// If the period number sought is the same as the one in the loop, then set it as selected
							if (isset($eventPeriod) AND ($period['periodNumber'] == $eventPeriod))
								$selected = " selected";
							else
								$selected = "";

							echo "<option value='".$period['periodNumber']."'".$selected.">Period ".$period['periodNumber']." (".formatTime($period['startTime'])." to ".formatTime($period['endTime']).")</option>";
						}
						?>
					</select>

					<label for="addEventSingleFormFirstName">Room:</label>
					<select name="addEventSingleFormRoom">
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
								if (isset($eventRoom) AND ($room['roomID'] == $eventRoom))
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
					<input type="hidden" value="<?php echo htmlspecialchars($_GET['serializedGetVariables']); ?>" id="addEventSingleFormReturnGET" name="addEventSingleFormReturnGET" form="addEventSingleForm" />

					<input type="submit" value="Add Event" id="addEventSingleFormSubmit" name="addEventSingleFormSubmit" form="addEventSingleForm" />
				</fieldset>
			</form>
		<?php
		}
		else
		{
			// Returns an appropriate error message if the user can't view the page
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>