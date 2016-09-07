<?php
	include_once "./includes/SQLDetails.php";

	// Error messages for the data fields
	$errorMessage = "";
	if ($_POST['importAddUserFormSubmit'])
	{
		if (!isset($_FILES['importAddUserFormCSVUpload']['tmp_name']))
			$errorMessage .= "Please upload a CSV.<br />";
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

		$userLevelNeeded = 3;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
		?>
			<h1>Admin Panel</h1>
			<h2>Create Events</h2>
			<h3>Add a Single User</h3>
			
			<form id="importAddUserForm" name="importAddUserForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off" enctype="multipart/form-data">
				<fieldset name="importAddUserFormFieldSet" id="importAddUserFormFieldSet" class="createSinglEventFieldSet">
					<legend>Add User: Import</legend>

					<!-- Error and success messages -->
					<div id='errorMessage' name='errorMessage'><?php echo $errorMessage; ?></div>
					<div id='successMessage' name='successMessage'><?php echo $successMessage; ?></div>

					<label for="importAddUserFormCSVUpload">Upload a user CSV containing: First Name; Last Name; and User ID; </label> <!-- Need to change containing fields -->
					<input type="file" name="importAddUserFormCSVUpload" id="importAddUserFormCSVUpload" />
					<input type="submit" name="importAddUserFormSubmit" />
					<br />
					<br />
					<?php
					if (isset($_FILES['importAddUserFormCSVUpload']))
					{
						// Starts to extract the data from the file that has been uploaded
						$fullImportedCSV = file($_FILES['importAddUserFormCSVUpload']['tmp_name']);

						$CSVHeaders = explode(",", $fullImportedCSV[0]); // This keeps the headings
						$importedCSV = $fullImportedCSV;
						array_shift($importedCSV); // This removes the headings element of the array

						foreach($importedCSV as $row)
						{
							$row = explode(",", $row); // Put in here a splitter on ',' to break up the string into elements of an array

							$foreachCount = 0;
							foreach($row as $rowItem) // A loop to strip spaces, tabs and other dangerous characters
							{
								$row[$foreachCount] = trim($row[$foreachCount]);
								$foreachCount++;
							}

							$insertArray = array("firstName"=>$row[0], "lastName"=>$row[1], "userID"=>$row[2], "password"=>saltAndHashPassword($row[2], "password"));

							if (preg_match('~[0-9]~', $row[2])) // If the user's code contains numbers then they are a student, else they are a teacher
							{
								$insertArray["userLevel"] = 1;
								$insertArray["emailAddress"] = $row[2]."@kps.woodard.co.uk"; // You can figure out the school email address if they are a student, so we will add this now
							}
							else
							{
								$insertArray["userLevel"] = 2;
								$insertArray["emailAddress"] = $row[0].".".$row[1]."@kps.woodard.co.uk";
							}

							if($bookingSystem->insertTable("Users", $insertArray))
								echo "The user '".$row[0]." ".$row[1]."' was added sucessfully.<br />";
							else
								echo "There was an error when adding the user '".$row[0]." ".$row[1]."'. This may be because a user of the same user ID exists.<br />";
						}
					}
					?>
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