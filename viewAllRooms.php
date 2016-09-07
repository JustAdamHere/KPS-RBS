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

			// The user needs to be an administrator to view the content of the page
			$userLevelNeeded = 3;
			if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
			{
				// Notice that this deletion block of code is within the check for the user level -- maliciously posted data cannot cause a mis-delete
				// If the form has been submitted and the value is "Delete selected"
				if (isset($_POST['roomCheckboxSubmit']) AND ($_POST['roomCheckboxSubmit'] == "Delete selected"))
				{
					// Deletes all from the "Rooms" table where the ID is the one selected in the checkbox array
					foreach ($_POST['checkbox'] as $singleCheckbox)
					{
						$bookingSystem->deleteFromTable("Rooms", array("roomID"=>$singleCheckbox));
					}
				}

				echo "<h1>Admin Panel</h1>";
				echo "<h2>View All Rooms</h2>";
				
				// Returns all rooms
				$roomResponse = $bookingSystem->queryTable("Rooms", "*", NULL, "roomID");

				// If some rooms exist in the table:
				if (!empty($roomResponse))
				{
					echo "<form id='roomCheckbox' method='POST'>";
						echo "<table class='roomTable' id='allRoomTable'>";
							echo "<tr>";
								echo "<th>Select</th>";
								echo "<th>Room ID</th>";
								echo "<th>Name</th>";
								echo "<th>Capacity</th>";
								echo "<th>Edit</th>";
							echo "</tr>";

							// If the 0th element does not exist, then it is not a multidimensional array
							if (!is_array($roomResponse[0]))
							{
								// Makes the array multidimensional
								$loopArray[0] = $roomResponse;
							}
							else
							{
								// Just straight assigns the value of the array if it is already multidimensional
								$loopArray = $roomResponse;
							}

							// Loops through each room, displaying its details
							foreach($loopArray as $room)
							{
								echo "<tr>";
									echo "<td><input value='".$room['roomID']."' type='checkbox' id='checkbox' name='checkbox[]'></td>";
									echo "<td>".$room['roomID']."</td>";
									echo "<td>".$room['name']."</td>";
									echo "<td>".$room['capacity']."</td>";
									echo "<td><a class='viewAllRoomsEdit' href='editRoomSingle.php?roomID=".$room['roomID']."'>Edit</a></td>";
								echo "</tr>";
							}
						echo "</table>";

						// A submit button used for deleting records that are selected
						echo "<h3>Operations</h3>";
						echo "<input type='submit' name='roomCheckboxSubmit' form='roomCheckbox' value='Delete selected' />";
					echo "</form>";
				}
				else
				{
					// No rooms have been found in the database table:
					echo "No rooms created.";
				}

			}
			else
			{
				echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
			}
		?>
	</body>
</html>