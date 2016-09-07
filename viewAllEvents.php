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

		// Any logged in user can view this page
		$userLevelNeeded = 1;
		if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= $userLevelNeeded)
		{
			// If the view has been set
			if (isset($_GET['view']))
			{
				// Checks if only the roomID or only the teacherID has been set
				if ((isset($_GET['roomID'])) XOR (isset($_GET['teacherID'])))
				{
					// Needs to detect what the view is set for so the following if statement can work correctly
					if($_GET['view'] == "room")
					{
						$table = "Rooms";
						$column = "roomID";
						$value = $_GET['roomID'];
						$order = "roomID";
					}
					elseif ($_GET['view'] == "teacher")
					{
						$table = "Users";
						$column = "userID";
						$value = $_GET['teacherID'];
						$order = "userID";
					}

					// Checks to see if the room or teacher actually exists and, if not, outputs an error message later in the code
					if (!empty(($bookingSystem->queryTable($table, array($column=>$value), 1, $order, "ASC", false))))
					{
						if (isset($_GET['referenceWeek'])) // Checks if the GET is set and sets the referenceWeek to the GET value, and if not uses the current week as the default referenceweek value
							$referenceWeek = $_GET['referenceWeek'];
						else
							$referenceWeek = date("Y-\WW", time());

						// Returns the timestamp of the week provided
						$referenceTime = date("U", strtotime($referenceWeek));

						// Update week boxes for either a teacher or a room -- this needs to be different for the hidden fields in each as they will either have a teacher ID or room ID
						if (isset($_GET['teacherID']))	
						{
							// Fetches details from the database used for gathering the different elements needed for displaying the teacher's name and ID
							$teacherName = $bookingSystem->queryTable("Users", array("userID"=>$_GET['teacherID']), 1, "userID", "ASC"); ?>

							<!-- Outputs the teacher's name and ID -->
							<h1><?php echo $teacherName['firstName']." ".$teacherName['lastName']." (".$teacherName['userID'].")"; ?></h1>
							<!-- Outputs the part that deals with the week chooser -->
							<form name="referenceTimeChooser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" autocomplete="off">
								<input name="referenceWeek" type="week" value="<?php echo $referenceWeek; ?>" />
								<!-- The hidden fields ensure that the $_GET values are still passed through when a new week is chosen -->
								<input type="hidden" name="view" value="<?php echo $_GET['view']; ?>" />
								<input type="hidden" name="teacherID" value="<?php echo $_GET['teacherID']; ?>" />
								<!-- The title attribute gives a hint to the user as to what the button does. -->
								<input title="Update to the selected week." type="submit" value="Update">
							</form>
							<?php
						}
						elseif (isset($_GET['roomID']))
						{
							?>
							<!-- Outputs the room name -->
							<h1><?php echo $bookingSystem->queryTable("Rooms", array("roomID"=>$_GET['roomID']), 1, "roomID")['name']; ?></h1>
							<!-- Outputs the part that deals with the week chooser -->
							<form name="referenceTimeChooser" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" autocomplete="off">
								<input name="referenceWeek" type="week" value="<?php echo $referenceWeek; ?>" />
								<!-- The hidden fields ensure that the $_GET values are still passed through when a new week is chosen -->
								<input type="hidden" name="view" value="<?php echo $_GET['view']; ?>" />
								<input type="hidden" name="roomID" value="<?php echo $_GET['roomID']; ?>" />
								<!-- The title attribute gives a hint to the user as to what the button does. -->
								<input title="Update to the selected week." type="submit" value="Update">
							</form>
							<?php
						}
						
						// Table that is created to hold all of the events
						echo "<table class='eventTable' id='eventTable'>";
							if (isset($_GET['teacherID']))
							{
								// A query build to gather all events between the start and end of the week for a given teacher ID
								$eventResult = $bookingSystem->queryTable(NULL, "SELECT * FROM Events WHERE `ownerID` = '".$_GET['teacherID']."' AND `startTimestamp` BETWEEN ".strtotime("last Monday 00:00", $referenceTime)." AND ".strtotime("next Friday 23:59", $referenceTime)." ORDER BY startTimestamp, ID ASC", NULL, NULL, NULL, false, true);
								
								if (!is_array($eventResult[0])) // If the array isn't multidimensional, make it a multidimensional array so that the foreach loop doesn't fail later
									$eventResult[0] = $eventResult;
							}
							elseif (isset($_GET['roomID']))
							{
								// A query build to gather all events between the start and end of the week for a given room ID
								$eventResult = $bookingSystem->queryTable(NULL, "SELECT * FROM Events WHERE `roomID` = '".$_GET['roomID']."' AND `startTimestamp` BETWEEN ".strtotime("last Monday 00:00", $referenceTime)." AND ".strtotime("next Friday 23:59", $referenceTime)." ORDER BY startTimestamp, ID ASC", NULL, NULL, NULL, false, true);
							}

							// If it is a single array, then add as an element to another array
							if (isset($eventResult['ID']))
								$eventResult = array($eventResult);

							// Displays column headings
							echo "<tr>";
								echo "<th>Day</th>";
								echo "<th>Period 1</th>";
								echo "<th>Period 2</th>";
								echo "<th>Period 3</th>";
								echo "<th>Period 4</th>";
								echo "<th>Period 5</th>";
								echo "<th>Period 6</th>";
								echo "<th>Period 7</th>";
							echo "</tr>";

							// A counter for each day
							for ($dayCounter = 0; $dayCounter <= 4; $dayCounter++)
							{
								echo "<tr>";
									echo "<td><strong>".jddayofweek($dayCounter, 1)."</strong> (".date("d/m/Y", strtotime(jddayofweek($dayCounter, 1), $referenceTime)).")</td>"; // Displays the days of the week from Monday to Friday due to the for loop above
									// A counter for each period of each day
									for ($periodCounter = 1; $periodCounter <= 7; $periodCounter++)
									{
										echo "<td>";
										// Finds the start time associated with the given period number
										$timeToCheck = $bookingSystem->queryTable("Periods", array("periodNumber" => $periodCounter), 1, "periodNumber")['startTime'];
										
										// Pads $timeToCheck with zeros at the front until it is 4 characters long
										while (strlen($timeToCheck) < 4)
										{
											$timeToCheck = "0".$timeToCheck;
										}

										// These variables limit the number of a events that can appear in an event (even if they're double booked, the first wil be shown)
										$resultCount = 0;
										$resultLimit = 1; // This can be changed to allow for more events to be shown in each cell, though it is not recommended
										foreach($eventResult as $singleResult) // A foreach loop to go through each event returned for the week
										{
											if ($resultCount < $resultLimit) // This limits the number of results that can be displayed per period
											{
												// The '1970-01-01' part of the convesion is so that the time converts into straight-forward seconds
												$fullSearchTime = $referenceTime + $dayCounter * (60*60*24) + strtotime("1970-01-01 ".$timeToCheck);
												if(array_search(intval($fullSearchTime), $singleResult, false)) // Sees if the current period number, once converted to a time, is found in the returned set of results
												{
													// Creates a link to edit the page if the name of the event is clicked on
													echo "<a href='./editEventSingle.php?serializedGetVariables=".serializeGetVariables()."&eventID=".$singleResult['ID']."'><strong>".$singleResult['name']."</strong></a>";
													echo "<br />";

													// The part below adds information regarding the room or teacher, depending on what $_GET variable has been set
													if (isset($_GET['roomID']))
													{
														// Gets the owner of the event's name and user ID
														$userResult = $bookingSystem->queryTable("Users", array("userID" => $singleResult['ownerID']), 1, "userID", "ASC", false);
														echo "<i>".$userResult['firstName']." ".$userResult['lastName']." (".$singleResult['ownerID'].")"."</i>";
													}
													elseif (isset($_GET['teacherID']))
													{
														// Gets the room of the event's name
														$roomResult = $bookingSystem->queryTable("Rooms", array("roomID" => $singleResult['roomID']), 1, "roomID", "ASC", false);
														echo "<i>".$roomResult['name']."</i>";
													}
													// Increments the result count and doesn't let the number of results in a cell exceed $resultLimit, and is also used for the "Book now" button
													$resultCount++;
												}
											}
										}

										// If there were no results found for the given day and period, a book now button is shown
										if($resultCount == 0)
										{
											// Lets administrators always book and event
											if(getUserLevel($bookingSystem, $_COOKIE['userID']) >= 3)
											{
												// Fills in the correct $_GET variables for the edit page
												echo "<div id='bookNow'><a href='addEventSingle.php?time=".$fullSearchTime."&serializedGetVariables=".serializeGetVariables()."'>Book now</a></div>";
											}
											// Makes sure that teachers can only book when the view is as a room, or if the userID is their own
											elseif ((getUserLevel($bookingSystem, $_COOKIE['userID']) >= 2) AND (($_GET['view'] == "room") OR ($_GET['teacherID'] == $_COOKIE['userID'])))
											{
												// Fills in the correct $_GET variables for the edit page
												echo "<div id='bookNow'><a href='addEventSingle.php?time=".$fullSearchTime."&serializedGetVariables=".serializeGetVariables()."'>Book now</a></div>";
											}
											// Notice that the button isn't ever shown to students
										}

										echo "</td>";
									}
								echo "</tr>";
							}
							
						echo "</table>";
					}
					else
					{
						echo "<p>There were no records found for what has been selected. Please <a href='./viewAllEvents.php'>try again</a>.";
					}
				}
				// If the teacher ID XOR the room ID isn't set, then if the view is set to teacher:
				elseif($_GET['view'] == "teacher")
				{
					echo "<h1>Choose a Teacher</h1>";
					// Returns a list of all teachers
					$returnedTeacherList = $bookingSystem->queryTable("Users", array("userLevel" => 2), NULL, " userID, lastName, firstName", "ASC");
					echo "<form id='teacherChooser'>";
						echo "<select name='teacherID'>";

							// Creates a multidimensional array for the foreach loop
							if (isset($returnedTeacherList['userID']))
								$buildTeacherList[0] = $returnedTeacherList;
							else
								$buildTeacherList = $returnedTeacherList;

							// Builds a list of teachers as options in a select box on the form
							foreach ($buildTeacherList as $teacher)
							{
								echo "<option value='".$teacher['userID']."'>[".$teacher['userID']."] ".$teacher['firstName']." ".$teacher['lastName']."</option>";
							}
						echo "</select>";

						echo "<input type='hidden' name='view' value='teacher' />"; // Need to set the view to room for the next stage on the page

						echo "<input type='submit' value='View Events' />";
					echo "</form>";
				}
				// If the teacher ID XOR the room ID isn't set, then if the view is set to room:
				elseif($_GET['view'] == "room")
				{
					echo "<h1>Choose a Room</h1>";
					echo "<p>Note: The number in parentheses is the capacity of the room.	</p>";
					// Returns a list of all rooms
					$returnedRoomList = $bookingSystem->queryTable("Rooms", "*", NULL, "name", "ASC");
					echo "<form id='roomChooser'>";
						echo "<select name='roomID'>";

							// Creates a multidimensional array for the foreach loop
							if (isset($returnedRoomList['roomID']))
								$buildRoomList[0] = $returnedRoomList;
							else
								$buildRoomList = $returnedRoomList;

							// Builds a list of rooms as options in a select box on the form
							foreach ($buildRoomList as $room)
							{
								if ($room['capacity'] == "")
									$room['capacity'] = "Unknown capacity"; // If there is no capacity specified, unknown is listed next to the room.

								echo "<option value='".$room['roomID']."'>".$room['name']." (".$room['capacity'].")</option>";
							}
						echo "</select>";

						echo "<input type='hidden' name='view' value='room' />"; // Need to set the view to room for the next stage on the page

						echo "<input type='submit' value='View Events' />";
					echo "</form>";
				}
			}
			// If the view, teacher ID and room ID isn't set, then let the user select to view rooms by either teacher or room:
			else
			{
				echo "<h1>View All Events</h1>";
					echo "<h3><a href='".$_SERVER['PHP_SELF']."?view=teacher'>By Teacher</a></h3>";
					echo "<h3><a href='".$_SERVER['PHP_SELF']."?view=room'>By Room</a></h3>";
			}
		}
		else
		{
			echo "<p>".returnUserLevelError($bookingSystem, $userLevelNeeded)."</p>";
		}
		?>
	</body>
</html>