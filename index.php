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
			include_once "./includes/createTables.php";
			// Includes the menu for navigating the site, which will be generated differently for each user
			include "./includes/menu.php";

			// Test login details are added to the database here. These are used for debugging purposes only and should NOT be uncommented for general use.
			$bookingSystem->insertTable("Users", array("userID"=>"3", "emailAddress"=>"3@kps.woodard.co.uk", "password"=>"c6f3ac57944a531490cd39902d0f777715fd005efac9a30622d5f5205e7f6894", "userLevel"=>3, "firstName"=>"Thirty", "lastName"=>"Three"));
			$bookingSystem->insertTable("Users", array("userID"=>"2", "emailAddress"=>"2@kps.woodard.co.uk", "password"=>"785f3ec7eb32f30b90cd0fcf3657d388b5ff4297f2f9716ff66e9b69c05ddd09", "userLevel"=>2, "firstName"=>"Twenty", "lastName"=>"Two"));
			$bookingSystem->insertTable("Users", array("userID"=>"1", "emailAddress"=>"1@kps.woodard.co.uk", "password"=>"4fc82b26aecb47d2868c4efbe3581732a3e7cbcc6c2efb32062c08170a05eeb8", "userLevel"=>1, "firstName"=>"Onety", "lastName"=>"One"));
		?>

		<p>Welcome to the Kings Priory School Room Booking System (KPS RBS).</p>
		<?php
		// Displays a welcome message, depending on whether or not the user is logged in and also depending on what user level their user account is
		if (getUserLevel($bookingSystem, $_COOKIE['userID']) > 0)
			 echo "Your user level is: <strong>".$bookingSystem->queryTable("UserLevels", array("userLevel"=>getUserLevel($bookingSystem, $_COOKIE['userID'])), 1, "userLevel", false, false)['title']."</strong>. This means that you can view events and access other features using the main menu.";
		else
			echo "<p>You must <a href='./login.php'>login</a> to be able to view content.</p>";
		?>
	</body>
</html>