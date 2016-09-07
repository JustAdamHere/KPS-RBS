<?php
	include_once "./includes/SQLDetails.php";
	include_once "./includes/functions.php";

	// Initialise the success and error messages
	$successMessage = "";
	$errorMessage = "";

	// Sets $_POST['loginFormSubmit'] to false if it isn't set
	if (!isset($_POST['loginFormSubmit']))
		$_POST['loginFormSubmit'] = false;

	if ($_POST['loginFormSubmit'])
	{
		// This lets the user know if it is their user ID or their password that is the issue
		$userIDCheckResponse = $bookingSystem->queryTable("Users", array("userID"=>$_POST['loginFormUserID']), 1, "userID");
		if($userIDCheckResponse)
		{
			$loginResponse = $bookingSystem->queryTable("Users", array("userID"=>$_POST['loginFormUserID'], "password"=>saltAndHashPassword($_POST['loginFormUserID'], $_POST['loginFormPassword'])), 1, "userID");
			if($loginResponse)
			{
				setcookie("userID", $loginResponse['userID'], time() + (60*60*24*7*2), "/"); // Sets login details for 2 weeks
				setcookie("userPassword", $loginResponse['password'], time() + (60*60*24*7*2), "/"); // Sets login details for 2 weeks
				$successMessage = "Logging in...";
				$successMessage .= "<META HTTP-EQUIV=REFRESH CONTENT='1; URL=./index.php'>";
			}
			else
			{
				$errorMessage = "Incorrect password.";
			}
		}
		else
		{
			$errorMessage = "The user does not exist.";
		}
	}
	else
	{
		// Defining variables that will be used later, even if they're empty
		$errorMessage = "";

		// This is a security measure - it helps to prevent user ID and password boxes being autofilled
		$_POST['loginFormUserID'] = "";
		$_POST['loginFormPassword'] = "";
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
		<?php include "./includes/menu.php";
		
		// If the user cookies don't exist:
		if (!checkUserLogin($bookingSystem, $_COOKIE['userID'], $_COOKIE['userPassword']))
				{ ?>
					<form id="loginForm" name="loginForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
						<fieldset name="loginFormFieldSet" id="loginFormFieldSet">
							<legend>Login</legend>

							<div id="successMessage" name="successMessage"><?php echo $successMessage; ?></div>
							<div id="errorMessage" name="errorMessage"><?php echo $errorMessage; ?></div>

							<label for="loginFormUserID">User ID:</label>
							<input type="text" name="loginFormUserID" maxlength="254" autofocus value="<?php echo $_POST['loginFormUserID']; ?>" />
							<label for="loginFormPassword">Password:</label>
							<input type="password" name="loginFormPassword" maxlength="32" value="<?php echo $_POST['loginFormPassword']; ?>" />

							<input type="submit" value="Login" id="loginFormSubmit" name="loginFormSubmit" form="loginForm" />
						</fieldset>
					</form>
				<?php
				}
				else
				{ ?>
					<p>You are already logged in. Please <a href="./logout.php">logout</a> to use the login page.</p>
				<?php
				}
				?>
	</body>
</html>