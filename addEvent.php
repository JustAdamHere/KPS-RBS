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

		// Checks to make sure that only users of a sufficient user level can view the content of the page
		$userLevelNeeded = 2;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
			?>
			<!-- Links to the add event pages -->
			<h1>Add Event</h1>
				<h3><a href="addEventSingle.php">Add Event: Single</a></h3>
				<?php
				if (getUserLevel($bookingSystem, $_COOKIE['userID']) == 3)
				{ ?>
					<h3><a href="addEventImport.php">Add Event: Import</a></h3>
				<?php
				}
		}
		else
		{
			// If the user doesn't have the correct user level, they are shown an error message
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>