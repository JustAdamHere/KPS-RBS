<?php
	include_once "./includes/functions.php";
	include_once "./includes/SQLDetails.php";

	// Builds the success and error messages
	$errorMessage = "";
	$successMessage = "";
	if ($_POST['editSingleHolidayFormSubmit'])
	{
		// Error messages for variables being 0 or empty
		if ($_POST['editSingleHolidayFormLabel'] == "")
			$errorMessage .= "Please enter a holiday label.<br />";
		if ($_POST['editSingleHolidayFormStart'] == 0) // We can presume that 0 is an unfilled field
			$errorMessage .= "Please enter a holiday start.<br />";
		if ($_POST['editSingleHolidayFormEnd'] == 0) // We can presume that 0 is an unfilled field
			$errorMessage .= "Please enter a holiday end.<br />";

		// Error message for the start timestamp being after the end timestamp
		if ($_POST['editSingleHolidayFormStart'] > $_POST['editSingleHolidayFormEnd'])
			$errorMessage .= "The start date is after the end date of the holiday.";

		// Assigns the value of the post variables to the ones that will be used to fill in the details on the form
		$holidayID = $_POST['holidayID'];
		$editSingleHolidayFormLabel = $_POST['editSingleHolidayFormLabel'];
		$editSingleHolidayFormStart = $_POST['editSingleHolidayFormStart'];
		$editSingleHolidayFormEnd = $_POST['editSingleHolidayFormEnd'];

		// If there are no error messages, then try to update the record
		if ($errorMessage == "")
		{
			// The array containing the items to be updated
			$updateArray = array(
				"label"=>$editSingleHolidayFormLabel,
				"startTimestamp"=>strtotime($editSingleHolidayFormStart),
				"endTimestamp"=>strtotime($editSingleHolidayFormEnd)
			);

			if($bookingSystem->updateTable("Holidays", $updateArray, array("ID"=>$_POST['holidayID'])))
			{
				$successMessage .= "Holiday, ".$editSingleHolidayFormLabel.", updated successfully.";
				$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllHolidays.php'>";
			}
			else
			{
				$errorMessage .= "Holiday, ".$editSingleHolidayFormLabel.", failed to update.";
			}
		}
	}
	else if (isset($_GET['holidayID']))
	{
		// Finds the record with the holiday ID
		$holidayResponse = $bookingSystem->queryTable("Holidays", array("ID"=>$_GET['holidayID']), 1, "ID", "ASC");

		// Assigns the value of the post variables to the ones that will be used to fill in the details on the form
		$holidayID = $_GET['holidayID'];
		$editSingleHolidayFormLabel = $holidayResponse['label'];
		$editSingleHolidayFormStart = date('Y-m-d', $holidayResponse['startTimestamp']);
		$editSingleHolidayFormEnd = date('Y-m-d', $holidayResponse['endTimestamp']);
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
		<?php
		// Includes the menu for navigating the site, which will be generated differently for each user
		include "./includes/menu.php";

		// This page's content can only be accessed by administrators
		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<h1><a href="adminPanel.php">Admin Panel</a></h1>
			<h2>Edit Holiday</h2>
			<h3><a href="addHolidaySingle.php">Edit a Single Holiday</a></h3>
			
			<form id="editSingleHolidayForm" name="editSingleHolidayForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="editSingleHolidayFormFieldSet" id="editSingleHolidayFormFieldSet" class="userFormFieldSet">
					<legend>Edit a Single Holiday</legend>

					<!-- Shows the user the success and error messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="editSingleHolidayFormLabel">Holiday Label:</label>
					<input type="text" name="editSingleHolidayFormLabel" maxlength="50" autofocus value="<?php echo $editSingleHolidayFormLabel; ?>" />
					<label for="editSingleHolidayFormStart">Holiday Start:</label>
					<input type="date" name="editSingleHolidayFormStart" value="<?php echo $editSingleHolidayFormStart; ?>" />
					<label for="editSingleHolidayFormEnd">Holiday End:</label>
					<input type="date" name="editSingleHolidayFormEnd" value="<?php echo $editSingleHolidayFormEnd; ?>" />

					<!-- This is needed to retain the holiday ID -->
					<input type="hidden" name="holidayID" value="<?php echo $holidayID; ?>" />

					<input type="submit" value="edit Holiday" id="editSingleHolidayFormSubmit" name="editSingleHolidayFormSubmit" form="editSingleHolidayForm" />
				</fieldset>
			</form>

		<?php
		}
		else
		{
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>