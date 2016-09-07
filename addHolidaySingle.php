<?php
	// Builds the success and error messages
	$errorMessage = "";
	$successMessage = "";
	if (isset($_POST['createSingleHolidayFormSubmit']))
	{
		if ($_POST['createSingleHolidayFormLabel'] == "")
			$errorMessage .= "Please enter a holiday label.<br />";
		if ($_POST['createSingleHolidayFormStart'] == 0) // We can presume that 0 is an unfilled field
			$errorMessage .= "Please enter a holiday start.<br />";
		if ($_POST['createSingleHolidayFormEnd'] == 0) // We can presume that 0 is an unfilled field
			$errorMessage .= "Please enter a holiday end.<br />";

		if ($_POST['createSingleHolidayFormStart'] > $_POST['createSingleHolidayFormEnd'])
			$errorMessage .= "The start date is after the end date of the holiday.";
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

		// The user must be an administrator
		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<?php
			// If the form has been submitted and there have been no errors:
			if((isset($_POST['createSingleHolidayFormSubmit'])) AND ($errorMessage == ""))
			{
				// Fills in the fields with the data that have been submitted
				$holidayLabel = $_POST['createSingleHolidayFormLabel'];
				$holidayStart = strtotime($_POST['createSingleHolidayFormStart']);
				$holidayEnd = strtotime($_POST['createSingleHolidayFormEnd']);

				// Checks if the insert was successful or not
				if($bookingSystem->insertTable("Holidays", array("label"=>$holidayLabel, "startTimestamp"=>$holidayStart, "endTimestamp"=>$holidayEnd)))
				{	
					$successMessage .= "Insert successful.";
					$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllHolidays.php'>";
				}
				else
				{
					$errorMessage .= "Insert for ".$holidayLabel." failed.<br />";
				}
			}
			?>

			<h1><a href="adminPanel.php">Admin Panel</a></h1>
			<h2>Add Holidays</h2>
			<h3><a href="addHolidaySingle.php">Add a Single Holiday</a></h3>
			
			<form id="createSingleHolidayForm" name="createSingleHolidayForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="createSingleHolidayFormFieldSet" id="createSingleHolidayFormFieldSet" class="userFormFieldSet">
					<legend>Add a Single Holiday</legend>

					<!-- Shows the success and error messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="createSingleHolidayFormLabel">Holiday Label:</label>
					<input type="text" name="createSingleHolidayFormLabel" maxlength="50" autofocus value="<?php echo $_POST['createSingleHolidayFormLabel']; ?>" />
					<label for="createSingleHolidayFormStart">Holiday Start:</label>
					<input type="date" name="createSingleHolidayFormStart" value="<?php echo $_POST['createSingleHolidayFormStart']; ?>" />
					<label for="createSingleHolidayFormEnd">Holiday End:</label>
					<input type="date" name="createSingleHolidayFormEnd" value="<?php echo $_POST['createSingleHolidayFormEnd']; ?>" />

					<input type="submit" value="Create Holiday" id="createSingleHolidayFormSubmit" name="createSingleHolidayFormSubmit" form="createSingleHolidayForm" />
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