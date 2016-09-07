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
				// If the submit has been sent and it is set to "Delete selected" then:
				if (isset($_POST['userCheckboxSubmit']) AND ($_POST['userCheckboxSubmit'] == "Delete selected"))
				{
					// Loops through each ID selected and deletes them
					foreach ($_POST['checkbox'] as $singleCheckbox)
					{
						$bookingSystem->deleteFromTable("Users", array("userID"=>$singleCheckbox));
					}
				}

				echo "<h1>Admin Panel</h1>";
				echo "<h2>View All Users</h2>";
				
				// Returns all users in the Users table
				$usersResponse = $bookingSystem->queryTable("Users", "*", NULL, "userID");

				if (!empty($usersResponse))
				{
					echo "<form id='userCheckbox' method='POST'>";
						echo "<table class='usersTable' id='allUsersTable'>";
							echo "<tr>";
								echo "<th>Select</th>";
								echo "<th>User ID</th>";
								echo "<th>User Level</th>";
								echo "<th>Email Address</th>";
								echo "<th>First Name</th>";
								echo "<th>Last Name</th>";
								echo "<th>Edit</th>";
							echo "</tr>";

							// If the 0th element does not exist, then we presume that it is not multidimensional 
							if (!is_array($usersResponse[0]))
							{
								// Make the array multidimensional (so the foreach loop does not fail)
								$loopArray[0] = $usersResponse;
							}
							else
							{
								// Assign the $loopArray to the value of the response as it is already multidimensional
								$loopArray = $usersResponse;
							}

							// Loops through each user
							foreach($loopArray as $user)
							{
								// Finds the user's user level
								$userLevel = $bookingSystem->queryTable("UserLevels", array("userLevel"=>$user['userLevel']), 1, "userLevel");
								// Outputs a new row to the table with all of the data from the table associated with that user
								echo "<tr>";
									echo "<td><input value='".$user['userID']."' type='checkbox' id='checkbox' name='checkbox[]'></td>";
									echo "<td title='".$user['firstName']." ".$user['lastName']."&apos;s unique school ID.'>".$user['userID']."</td>";
									echo "<td title='".$userLevel['description']."'>".$userLevel['title']."</td>";
									echo "<td title='".$user['firstName']." ".$user['lastName']."&apos;s email address.'><a href='mailto:".$user['emailAddress']."'>".$user['emailAddress']."</a></td>";
									echo "<td title='The user&apos;s first name.'>".$user['firstName']."</td>";
									echo "<td title='The user&apos;s last name.'>".$user['lastName']."</td>";
									echo "<td title='A button to edit ".$user['firstName']." ".$user['lastName']."&apos;s details.'><a class='viewAllUsersEdit' href='editUserSingle.php?userID=".$user['userID']."'>Edit</a></td>";
								echo "</tr>";
							}
						echo "</table>";

						// A delete selected button
						echo "<h3>Operations</h3>";
						echo "<input type='submit' name='userCheckboxSubmit' form='userCheckbox' value='Delete selected' />";
					echo "</form>";
				}
				// If the array is empty, then no users are in the table
				else
				{
					echo "No users created.";
				}

			}
			else
			{
				echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
			}
		?>
	</body>
</html>