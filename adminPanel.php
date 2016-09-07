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

			// Only administrators can view the content of this page
			$userLevelNeeded = 3;
			if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
			{
				// A hierarchical structure of the administrator-only pages 
				echo "<h1>Admin Panel</h1>";
					echo "<h2>Users</h2>";
						echo "<h3><a href='viewAllUsers.php'>View All Users</a></h3>";
							echo "<h3><a href='addUserImport.php'>Add Users: By Import</a></h3>";
							echo "<h3><a href='addUserSingle.php'>Add Users: Single</a></h3>";
					echo "<br />";
					echo "<h2>Rooms</h2>";
						echo "<h3><a href='viewAllRooms.php'>View All Rooms</a></h3>";
							echo "<h3><a href='addRoomImport.php'>Add Rooms: By Import</a></h3>";
							echo "<h3><a href='addRoomSingle.php'>Add Rooms: Single</a></h3>";
					echo "<br />";
					echo "<h2>Holidays</h2>";
						echo "<h3><a href='viewAllHolidays.php'>View All Holidays</a></h3>";
							echo "<h3><a href='addHolidaySingle.php'>Add Holidays: Single</a></h3>";
			}
			else
			{
				echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
			}
		?>
	</body>
</html>