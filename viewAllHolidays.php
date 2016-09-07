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

			// The user needs to be an administrator to view this page
			$userLevelNeeded = 3;
			if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
			{
				// Notice that this deletion block of code is within the check for the user level -- maliciously posted data cannot cause a mis-delete
				// If the form has been submitted and the value is "Delete selected"
				if (isset($_POST['holidayCheckboxSubmit']) AND ($_POST['holidayCheckboxSubmit'] == "Delete selected"))
				{
					// Deletes all from the "Holidays" table where the ID is the one selected in the checkbox array
					foreach ($_POST['checkbox'] as $singleCheckbox)
					{
						$bookingSystem->deleteFromTable("Holidays", array("ID"=>$singleCheckbox));
					}
				}

				echo "<h1>Admin Panel</h1>";
				echo "<h2>View All Holidays</h2>";
				
				// Finds all holidays and sorts them by their start timestamp and then their end timestamp
				$holidaysResponse = $bookingSystem->queryTable("Holidays", "*", NULL, "startTimestamp, endTimestamp", "ASC");

				// If there are holidays in the table:
				if (!empty($holidaysResponse))
				{
					if (isset($holidaysResponse['label'])) // If the label element exists, then there must only be one record found but we add this to a multidimensional array to ensure the foreach loop works later
						$holidaysResponse = array($holidaysResponse);

					echo "<form id='holidayCheckbox' method='POST'>";
						echo "<table class='holidaysTable' id='holidaysTable'>";
							echo "<tr>";
								echo"<th>Select</th>";
								echo "<th>Label</th>";
								echo "<th>Holiday Start</th>";
								echo "<th>Holiday End</th>";
								echo "<th>Edit</th>";
							echo "</tr>";

							// Loops through each found holiday in the table
							foreach($holidaysResponse as $holiday)
							{
								echo "<tr>";
									echo "<td><input value='".$holiday['ID']."' type='checkbox' id='checkbox' name='checkbox[]'></td>";
									echo "<td>".$holiday['label']."</td>";
									echo "<td>".date('d/m/Y', $holiday['startTimestamp'])."</td>";
									echo "<td>".date('d/m/Y', $holiday['endTimestamp'])."</td>";
									echo "<td><a href='editHolidaySingle.php?holidayID=".$holiday['ID']."'>Edit</a></td>";
								echo "</tr>";
							}
						echo "</table>";

						// Shows a button that can be used to delete the selected holidays
						echo "<h3>Operations</h3>";
						echo "<input type='submit' name='holidayCheckboxSubmit' form='holidayCheckbox' value='Delete selected' />";
					echo "</form>";
				}
				// else there are no holidays
				else
				{
					echo "No holidays created. Create one <a href='./addHolidaySingle.php'>here</a>.";
				}

			}
			else
			{
				echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
			}
		?>
	</body>
</html>