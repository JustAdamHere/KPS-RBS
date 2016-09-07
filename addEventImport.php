 <?php
	include_once "./includes/SQLDetails.php";

	$errorMessage = "";
	if (isset($_POST['importRoomBookingFormSubmit']))
	{
		// Need to create error messages for empty fields
		if ($_FILES['importRoomBookingFormCSVUpload'] == NULL)
			$errorMessage .= "Please upload a CSV file.<br />";
		if ($_POST['importRoomBookingFormInsertDate'] == NULL)
			$errorMessage .= "Please select an insert date.";

		if ($_POST['importRoomBookingFormRepeatEvent'])
		{
			if ($_POST['importRoomBookingFormInsertDate'] > $_POST['importRoomBookingFormRepeatToDate'])
				$errorMessage .= "The repeat-to date is earlier than the insert date.";

			// FINISH ERROR MESSAGES -- I don't think that there's any more?
		}			
	}

?>

<!-- A JS function for hiding and showing repeat functions on the form -->
<script>
	function hideRepeats()
	{
		if (document.getElementById('importRoomBookingFormRepeatEvent').checked)
		{
			document.getElementById('importRoomBookingFormRepeatEventHideableFields').style.display = 'block';
		}
		else
		{
			document.getElementById('importRoomBookingFormRepeatEventHideableFields').style.display = 'none';
		}
	}
</script>

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

		// Verifies that the current user has the right user level
		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<h1>Add Event</h1>
			<h2>Add Event: Import</h2>

			<!-- Some messages to warn the user about upload times -->
			<p>After submitting the upload file, the upload process will start. <strong>Be patient</strong>, especially for large inserts.</p>
			<!-- <p>The events will start inserting the day after the one that you select.</p> -->
			
			<form id="importRoomBookingForm" name="importRoomBookingForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off" enctype="multipart/form-data">
				<fieldset name="importRoomBookingFormFieldSet" id="importRoomBookingFormFieldSet" class="editFieldSet">
					<legend>Add Event: Import</legend>
					<?php
						// A message to the user to let them know that the events are inserting as soon as the CSV has been uploaded
						if ((isset($_FILES['importRoomBookingFormCSVUpload'])) AND ($errorMessage == ""))
							echo "<div id='successMessage' name='successMessage'>Inserting... See insertion details below.</div>";
					?>
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<!-- The date that the events should start from - if the form has already been sumitted with an insert date, that will be the default; if not, today's date will be used -->
					<label for="importRoomBookingFormInsertDate">The date that the events should start from</label>
					<input type="date" id="importRoomBookingFormInsertDate" name="importRoomBookingFormInsertDate" value="<?php if(isset($_POST['importRoomBookingFormInsertDate'])) { echo $_POST['importRoomBookingFormInsertDate']; } else { echo date("Y-m-d", time()); } ?>" />
					<!-- Calls the JS function below once the button has been clicked; this displays the other data fields concerned with repeating -->
					<label for="importRoomBookingFormRepeatEvent">Repeat event weekly</label>
					<input type="checkbox" id="importRoomBookingFormRepeatEvent" name="importRoomBookingFormRepeatEvent" onClick="hideRepeats()" />
					<!-- These hideable fields are shown when the repeat event weekly checkbox is checked -->
					<div id="importRoomBookingFormRepeatEventHideableFields" style="display:none" class="eventHideableFields">
						<!-- The date that the events should repeat up to -->
						<label for="importRoomBookingFormRepeatToDate">The date that the event should repeat up until</label>
						<input type="date" id="importRoomBookingFormRepeatToDate" name="importRoomBookingFormRepeatToDate" />
						<!-- If this is checked, the events will repeat even when there are holidays -->
						<label for="importRoomBookingFormRepeatInHolidays">Continue to repeat in holidays</label>
						<input type="checkbox" value="true" id="importRoomBookingFormRepeatInHolidays" name="importRoomBookingFormRepeatInHolidays" />
					</div>
					<!-- Allows for a CSV for parsing to be uploaded -->
					<label for="importRoomBookingFormCSVUpload">Upload a user CSV containing: Mon:1; Mon:2; ...; Fri:7, listed either with staff or rooms.</label>
					<input type="file" accept=".csv" name="importRoomBookingFormCSVUpload" id="importRoomBookingFormCSVUpload" />
					<!-- Submits the form -->
					<input type="submit" name="importRoomBookingFormSubmit" />
					<br />
					<br />
					<?php
					if ((isset($_FILES['importRoomBookingFormCSVUpload'])) AND ($errorMessage == ""))
					{
						$fullImportedCSV = file($_FILES['importRoomBookingFormCSVUpload']['tmp_name']);

						$CSVHeaders = explode(",", $fullImportedCSV[0]); // This keeps the headings in an array called $CSVHeaders
						$importedCSV = $fullImportedCSV;
						array_shift($importedCSV); // This removes the headings element of the array

						// For each $importedCSV array_shift() ? This should remove teacher and room headings

						$successful = array(); // An array to store a list of successful imported items
						$unsuccessful = array(); // An array to store a list of unsuccessful imported items

						if($_POST['importRoomBookingFormRepeatInHolidays'] == "true")
							$repeatInHolidays = true;
						else
							$repeatInHolidays = false;

						// Loops through each row of the CSV, row by row
						foreach($importedCSV as $row)
						{
							$row = explode(",", $row); // Put in here a splitter on ',' to break up the string into elements of an array (turning Comma Separated Values into an array)

							$daysOfTheWeek = array("Mon", "Tue", "Wed", "Thu", "Fri");
							$dayPeriodCounter = -1; // Starts at -1 so that the first column is ignored

							// Loops through each element of row (so each value) and assigns it to cell
							foreach($row as $cell)
							{
								// Resets values used for each import
								$roomID = "";
								$ownerID = "";
								$eventName = "";
								$insertDay = 0;
								$insertPeriod = 0;

								// Need to calculate day and period of the event
								$insertDay = (int) ($dayPeriodCounter / 7);
								$insertDay = $daysOfTheWeek[$insertDay]; // Figures out which day of the week it is
								$insertPeriod = $dayPeriodCounter % 7 + 1; // Figures out which period it is for insert

								$insertDate = strtotime($_POST['importRoomBookingFormInsertDate']); // Turns the date format into a unix timestamp

								// If the terminate date isn't set, then use the insert date as the terminate date
								if (isset($_POST['importRoomBookingFormRepeatEvent'])) // If the repeat checkbox is not checked, then set the terminate date to the insert date, otherwise set the terminate date to the date selected
									$terminateDate = strtotime($_POST['importRoomBookingFormRepeatToDate']);
								else
									$terminateDate = $insertDate;

								// Parses the cell string to see what the room name is
								if (strpos($cell, "#"))
									$roomID = substr($cell, strpos($cell, "#") + 1, strpos($cell, ")") - strpos($cell, "#") - 1); //only count for the length between # and 1 before )

								// Parses the cell string to see what the ownerID is
								if (strpos($cell, "$"))
									$ownerID = substr($cell, strpos($cell, "$") + 1, 3); //start 3 after where $ is found

								// Parses the cell string to see what the event name is
								if (strpos($cell, "$"))
									$eventName = substr($cell, 0, strpos($cell, "$") - 1); //stop 1 before where $ is found

								// If the RoomID contains letters and numbers we can presume that the room name is the same as the roomID
								if (preg_match('/[A-Z]+[0-9]+/', $roomID))
									$roomName = $roomID;
								else
									$roomName = "";

								if ((!$bookingSystem->queryTable("Rooms", array("roomID"=>$roomID), "roomID")) AND (strlen($roomID) > 0) AND ($roomID != " ")) // Checks to see if the room exists and, if not, adds it (as long as the roomID isn't blank)
									$bookingSystem->insertTable("Rooms", array("roomID"=>$roomID, "name"=>$roomName));

								// Figures out what the time for the period for insert is
								$insertPeriodTime = $bookingSystem->queryTable("Periods", array("periodNumber"=>$insertPeriod), 1, "periodNumber")['startTime'];

								$insertPeriodTimeFormatted = formatTime($insertPeriodTime); // Formats the time with a colon, according to the function's definition in ./includes/functions.php

								if ((!$bookingSystem->queryTable("Users", array("userID"=>$ownerID), "userID")) AND (strlen($ownerID) > 0) AND ($ownerID != " ")) // Checks to see if the room exists and, if not, adds it (as long as the roomID isn't blank)
									$bookingSystem->insertTable("Users", array("userID"=>$ownerID, "userLevel"=>2, "password"=>saltAndHashPassword($ownerID, "password")));

								// I NEED TO TEST THIS

								if ((strlen($cell) > 0) AND (strlen($eventName) > 0) AND (strlen($roomID) > 0) AND (strlen($insertPeriodTimeFormatted) > 0) AND (strlen($ownerID) > 0))
								{
									// Need to calculate when to import the event
									while ($insertDate <= $terminateDate) // Need to loop through all of the weeks until the specified date
									{
										// Need to calculate the timestamp of each iteration
										$insertTimestamp = strtotime(date("Y-m-d\TH:i", $insertDate - 24*60*60)." next ".$insertDay." ".$insertPeriodTimeFormatted); // Makes sure that the insert timestamp for this insert is calculated for the day specified, not the day after (that's why is goes back and forwards a day, whilst avoiding weekend-based errors)

										// If the event is not set to repeat, it is inserted once:
										if (!$_POST['importRoomBookingFormRepeatEvent'])
										{
											// Attempts to insert the event
											if($bookingSystem->insertTable("Events", array("name"=>$eventName, "roomID"=>$roomID, "startTimestamp"=>$insertTimestamp, "ownerID"=>$ownerID)))
												$successful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
											else
												$unsuccessful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
										}
										else
										{
											// If the event is set to repeat in holidays and the repeat setting has been set (the event will be inserted even if the event falls in a holiday date range):
											if($repeatInHolidays)
											{
												// Attempts to insert the event
												if($bookingSystem->insertTable("Events", array("name"=>$eventName, "roomID"=>$roomID, "startTimestamp"=>$insertTimestamp, "ownerID"=>$ownerID)))
													$successful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
												else
													$unsuccessful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
											}
											else
											{
												// If the event is set to repeat and there are no holidays set 
												if (empty($bookingSystem->queryTable(NULL, "SELECT * FROM Holidays WHERE ".$insertDate." BETWEEN `startTimestamp` AND `endTimestamp`", NULL, "ID", "ASC", false, true)))
												{
													// Attempts to insert the event
													if($bookingSystem->insertTable("Events", array("name"=>$eventName, "roomID"=>$roomID, "startTimestamp"=>$insertTimestamp, "ownerID"=>$ownerID)))
														$successful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
													else
														$unsuccessful[] = $cell." {<i>".date("d/m/Y", $insertTimestamp)." Period".$insertPeriod."</i>}";
												}
												else
												{
													// No events will be inserted if there are holidays set
												}			
											}
										}

										$insertDate += 60*60*24*7; // Adds on one week to the last insert date
									}
								}
								else
								{
									// If the cell's contents resembles a teacher ID or a room ID:
									if (!(($bookingSystem->queryTable(NULL, "SELECT * FROM `Rooms` WHERE `roomID` LIKE '%{$cell}%'", NULL, NULL, NULL, false, true))) OR ($bookingSystem->queryTable(NULL, "SELECT * FROM `Users` WHERE `userID` LIKE '%{$cell}%'", NULL, NULL, NULL, false, true))) // If it is a teacher or a room as the cell's value, then ignore and don't add to $unsuccessful[]
									{
										// If the cell string has a valid make-up, or if it contains LUNCH, LEAD or PPA, then it is ignored
										if ((strlen($cell) > 0) AND ($cell != " ") AND (strlen(trim($cell)) != 0) AND ($cell != NULL) AND (!strpos($cell, "LUNCH")) AND (!strpos($cell, "LEAD")) AND (!strpos($cell, "PPA")))
											$unsuccessful[] = $cell." {<i>Missing (or too much) data</i>}";
									}
								}

								$dayPeriodCounter++;
							}
						}

						// Displays the number of successful and unsuccessful inserts
						echo "<p>".count($successful)." items inserted successfully.</p>";
						echo "<p>".count($unsuccessful)." items failed to insert.</p>";
						echo "<p>See details below.</p>";

						// Displays all succesful inserts
						if (count($successful) > 0)
						{
							echo "<h3>Successful Items</h3>";
							echo "<ul>";
								foreach ($successful as $item)
								{
									echo "<li>".$item."</li>";
								}
							echo "</ul>";
						}

						// Displays all unsuccessful inserts
						if (count($unsuccessful) > 0)
						{
							echo "<h3>Unsuccessful Items</h3>";
							echo "<ul>";
								foreach ($unsuccessful as $item)
								{
									echo "<li>".$item."</li>";
								}
							echo "</ul>";
						}
					}
					?>
				</fieldset>
			</form>
		<?php
		}
		else
		{
			// An error message is shown if the user isn't allowed to view the page due to their user level
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>