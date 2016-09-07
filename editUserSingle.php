<?php
	include_once "./includes/SQLDetails.php";
	include_once "./includes/functions.php";

	$errorMessage = "";
	
	// Code to be run if the user is to be deleted, including a redirect if the deletion was successful
	if (isset($_POST['editFormDelete']))
	{
		if ($bookingSystem->deleteFromTable("Users", array("userID"=>$_POST['editFormUserID'])))
		{
			$successMessage = $_POST['editFormUserID']." has been deleted successfully.";
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllUsers.php'>";
		}
		else
		{
			$errorMessage = "Something went wrong when deleting the user.";
		}
	}
	else if (isset($_POST['editFormSubmit']))
	{
		$editFormUserID = $_POST['editFormUserID'];
		$editFormEmailAddress = $_POST['editFormEmailAddress'];
		$editFormPassword = $_POST['editFormPassword'];
		$editFormFirstName = $_POST['editFormFirstName'];
		$editFormLastName = $_POST['editFormLastName'];
		$editFormUserLevel = $_POST['editFormUserLevel'];

		// If the form has been submitted, we test to see if any of the fields are blank
		if ($editFormUserID == "")
			$errorMessage .= "Please enter a user ID.<br />";
		if ($editFormEmailAddress == "")
			$errorMessage .= "Please enter an email address.<br />";
		if ($editFormFirstName == "")
			$errorMessage .= "Please enter a first name.<br />";
		if ($editFormLastName == "")
			$errorMessage .= "Please enter a last name.<br />";

		// If the form has been submitted, we assign the values of the POST variables into the form variables
		$editFormUserID = $_POST['editFormUserID'];
	}
	elseif (!isset($_GET['userID']))
	{
		// If there is no userID speicifed by GET and there's no POST submission set, something has gone wrong and we tell the user this
		$errorMessage .= "Something has gone wrong; no user ID specified.<br />";
	}
	else
	{
		// Else, everything is working and we assign the form variables the values of the userID taken from the database
		$userResult = $bookingSystem->queryTable("Users", array("userID"=>$_GET['userID']), 1, "userID");

		$editFormUserID = $_GET['userID'];
		$editFormEmailAddress = $userResult['emailAddress'];
		$editFormPassword = "";
		$editFormFirstName = $userResult['firstName'];
		$editFormLastName = $userResult['lastName'];
		$editFormUserLevel = $userResult['userLevel'];
	}

	// If the form has been submitted and there have been no errors found
	if (($_POST['editFormSubmit']) and ($errorMessage == ""))
	{
		$updateArray = array(
							"userID"=>$editFormUserID, 
							"firstName"=>$editFormFirstName, 
							"lastName"=>$editFormLastName, 
							"emailAddress"=>$editFormEmailAddress, 
							"userLevel"=>$editFormUserLevel
							);
		if ($editFormPassword != "")
			$updateArray['password'] = saltAndHashPassword($editFormUserID, $editFormPassword); // If the password field isn't blank, we insert the new hashed password to the update array for the database

		// The record is attempted to be updated:
		if($bookingSystem->updateTable("Users", $updateArray, array("userID"=>$editFormUserID)))
		{
			$errorMessage = "";
			$successMessage = "Saving changes...";
			$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./viewAllUsers.php'>";
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
		<!-- Includes the menu for navigating the site, which will be generated differently for each user -->
		<?php include_once "./includes/menu.php";

		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>

			<h1><a href="./admin.php">Admin Panel</a></h1>
			<h2><a href="./viewAllUsers.php">All Users</a></h2>
			<h3><a href="<?php echo $_SERVER['PHP_SELF']."?userID=".$_GET['userID']; ?>">Edit Single User</a></h3>

			<form id="editForm" name="editForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off">
				<fieldset name="editFormFieldSet" id="editFormFieldSet" class="userFormFieldSet">
					<legend>Manually edit User</legend>

					<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>
					<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>

					<p><?php echo "Editing: ".$editFormUserID; ?></p>
					<input type="hidden" name="editFormUserID" value="<?php echo $editFormUserID; ?>" />
					<label for="editFormUserID">Email Address:</label>
					<input type="email" name="editFormEmailAddress" maxlength="254" autofocus value="<?php echo $editFormEmailAddress; ?>" />
					<label for="editFormPassword">Password:</label>
					<input type="password" name="editFormPassword" maxlength="32" placeholder="If left blank, password is unchanged" value="<?php echo $editFormPassword; ?>" />
					<label for="editFormFirstName">First Name:</label>
					<input type="text" name="editFormFirstName" maxlength="35" value="<?php echo $editFormFirstName; ?>" />
					<label for="editFormLastName">Last Name:</label>
					<input type="text" name="editFormLastName" maxlength="50" value="<?php echo $editFormLastName; ?>" />
					<label for="editFormUserLevel">User Level:</label>
					<select name="editFormUserLevel">
						<?php
							// This stops an administrator changing their user level so that they can't be "locked-out" of the system
							if ($_GET['userID'] == $_COOKIE['userID'])
							{
								$disabled = " disabled";
								$disabledMessage = "You can't change your own user level from administrator.";
							}
							else
							{
								$disabled = "";
								$disabledMessage = "";
							}

							foreach ($bookingSystem->queryTable("UserLevels", "*", NULL, "userLevel") as $result) // Builds a drop down list with all user levels
							{
								if ($editFormUserLevel == $result['userLevel'])
									$selected = " selected";
								else
									$selected = "";

								echo "<option".$selected.$disabled." value='".$result['userLevel']."'>".$result['title']."</option>";
							}
						?>
					</select>
					<?php echo $disabledMessage; ?>

					<input type="submit" value="Save Edited User" id="editFormSubmit" name="editFormSubmit" form="editForm" />
					<input type="submit" value="Delete User" id="editFormDelete" name="editFormDelete" form="editForm" />
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