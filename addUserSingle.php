<?php
	// Includes other files needed for database connectivity and for system-wide functionality
	include_once "./includes/functions.php";
	include_once "./includes/SQLDetails.php";

	// Sets the variable for storing error messages
	$errorMessage = "";
	if ($_POST['registerFormSubmit'])
	{
		if ($_POST['registerFormUserID'] == "")
			$errorMessage .= "Please enter a user ID.<br />";
		if ($_POST['registerFormEmailAddress'] == "")
			$errorMessage .= "Please enter an email address.<br />";
		if ($_POST['registerFormPassword'] == "")
			$errorMessage .= "Please enter a password.<br />";
		if ($_POST['registerFormPasswordConfirm'] == "")
			$errorMessage .= "Please enter a password confirmation.<br />";
		if ($_POST['registerFormFirstName'] == "")
			$errorMessage .= "Please enter a first name.<br />";
		if ($_POST['registerFormLastName'] == "")
			$errorMessage .= "Please enter a last name.<br />";
		if ($_POST['registerFormPassword'] <> $_POST['registerFormPasswordConfirm'])
			$errorMessage .= "The password and confirm password do not match.";
	}

	if (($errorMessage == "") AND (isset($_POST['registerFormSubmit'])))
	{
		if($bookingSystem->insertTable("Users", array("userID"=>$_POST['registerFormUserID'], "emailAddress"=>$_POST['registerFormEmailAddress'], "password"=>saltAndHashPassword($_POST['registerFormUserID'], $_POST['registerFormPassword']), "firstName"=>$_POST['registerFormFirstName'], "lastName"=>$_POST['registerFormLastName'], "userLevel"=>$_POST['registerFormUserLevel'])))
		{
			// Success message which will redirect the user back to the viewAllUsers.php page
			$successMessage = "Saving changes...";
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllUsers.php'>";
		}
		else
		{
			$errorMessage .= "There was a problem inserting the data to the table.";
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
		<!-- Includes the menu for navigating the site, which will be generated differently for each user --> 
		<?php include_once "./includes/menu.php";

		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<h1>Admin Panel</h1>
			<h2>Add Users</h2>
			<h3>Manually</h3>
			
			<form id="registerForm" name="registerForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="registerFormFieldSet" id="registerFormFieldSet" class="userFormFieldSet">
					<legend>Manually Register User</legend>

					<!-- Displays the success and error messages -->
					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<label for="registerFormUserID">User ID:</label>
					<input type="text" name="registerFormUserID" maxlength="20" autofocus value="<?php echo $_POST['registerFormUserID']; ?>" />
					<label for="registerFormUserID">Email Address:</label>
					<input type="email" name="registerFormEmailAddress" maxlength="254" autofocus value="<?php echo $_POST['registerFormEmailAddress']; ?>" />
					<label for="registerFormPassword">Password:</label>
					<input type="password" name="registerFormPassword" maxlength="32" value="<?php echo $_POST['registerFormPassword']; ?>" />
					<label for="registerFormPasswordConfirm">Password Confirm:</label>
					<input type="password" name="registerFormPasswordConfirm" maxlength="32" value="<?php echo $_POST['registerFormPasswordConfirm']; ?>" />
					<label for="registerFormFirstName">First Name:</label>
					<input type="text" name="registerFormFirstName" maxlength="35" value="<?php echo $_POST['registerFormFirstName']; ?>" />
					<label for="registerFormLastName">Last Name:</label>
					<input type="text" name="registerFormLastName" maxlength="50" value="<?php echo $_POST['registerFormLastName']; ?>" />
					<label for="registerFormUserLevel">User Level:</label>
					<select name="registerFormUserLevel">
						<?php
							foreach ($bookingSystem->queryTable("UserLevels", "*", NULL, "userLevel") as $result) // Builds a drop down list with all user levels
							{
								if ($registerFormUserLevel == $result['userLevel'])
									$selected = " selected";
								else
									$selected = "";

								echo "<option".$selected." value='".$result['userLevel']."'>".$result['title']."</option>";
							}
						?>
					</select>

					<input type="submit" value="Register User" id="registerFormSubmit" name="registerFormSubmit" form="registerForm" />
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