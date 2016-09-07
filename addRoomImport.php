<?php
	include_once "./includes/SQLDetails.php";

	$errorMessage = "";
	$successMessage = "";
	if (isset($_POST['importAddRoomFormSubmit']))
	{
		if (!isset($_POST['importAddRoomFormCSVUpload']))
			$errorMessage .= "Please upload a CSV file.<br />";
		else
			$successMessage .= "Inserting...";
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
			<h3>Add a Single Room</h3>

			<?php
			if (isset($_FILES['importAddRoomFormCSVUpload'])) // Can't get uploaded files to work
			{
				//var_dump(file_get_contents($_FILES['importAddRoomFormCSVUpload']['tmp_name']));	

				$fullImportedCSV = file($_FILES['importAddRoomFormCSVUpload']['tmp_name']);

				$CSVHeaders = explode(",", $fullImportedCSV[0]); // This keeps the headings
				$importedCSV = $fullImportedCSV;
				array_shift($importedCSV); // This removes the headings element of the array

				foreach($importedCSV as $row)
				{
					$row = array_map("trim", explode(",", $row)); // Put in here a splitter on ',' to break up the string into elements of an array and removes any remaining whitespace (with the array_map function)

					// Inserts each room
					if($bookingSystem->insertTable("Rooms", array("name"=>$row[0], "roomID"=>$row[1], "capacity"=>$row[2])))
					{
						$successful[] = "The room '".$row[0]."' was added sucessfully.";
					}
					else
					{
						$unsuccessful[] = "There was an error when adding the room '".$row[0]."'. This may be because a room of the same room ID exists.";
					}
				}
			}
			?>
			
			<form id="importAddRoomForm" name="importAddRoomForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" autocomplete="off" enctype="multipart/form-data">
				<fieldset name="importAddRoomFormFieldSet" id="importAddRoomFormFieldSet" class="createSinglEventFieldSet">
					<legend>Add Room: Import</legend>

					<!-- Success and error messages -->
					<div id='errorMessage' name='errorMessage'><?php echo $errorMessage; ?></div>
					<div id='successMessage' name='successMessage'><?php echo $successMessage; ?></div>
					
					<label for="importAddRoomFormCSVUpload">Upload a room CSV containing: Room name; Room ID; and Room capacity.</label>
					<input type="file" name="importAddRoomFormCSVUpload" id="importAddRoomFormCSVUpload" />
					<input type="submit" name="importAddRoomFormSubmit" />
					<br />
					<br />
				</fieldset>
			</form>
		<?php

			// Displays all succesful inserts
			if (count($successful) > 0)
			{
				echo "<h3>Successful Items</h3>";
				echo "<ul>";
					foreach ($successful as $item)
					{
						echo "<li>".$item."</li>";
					}
				echo "</ul>";
			}

			// Displays all unsuccessful inserts
			if (count($unsuccessful) > 0)
			{
				echo "<h3>Unsuccessful Items</h3>";
				echo "<ul>";
					foreach ($unsuccessful as $item)
					{
						echo "<li>".$item."</li>";
					}
				echo "</ul>";
			}
		}
		else
		{
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>