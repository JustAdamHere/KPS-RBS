<?php
	include_once "./includes/functions.php";
	destroyCookies();
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
		<?php include_once "./includes/menu.php"; ?>
		<p>Logging out...</p>
		<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./index.php'>
	</body>
</html>