<?php
// Database variables
$host = "dbserver2.utep.edu"; #enter the DB server location
$db = "s25_mmt_team3";   # 1. Enter your team database here for your group project.
            # OR 2. Enter your individual database here to complete this exercise.
$username = "esarinana";  # If 1 above (for your group project), enter the username of the interface or reports lead.
                 # If 2 above (for this individual exercise), enter your username.
$password = "Sweatpea95*";  # If 1 above (for your group project), enter the password of the interface or reports lead. Make sure it is not used anywhere else as it will be shared among team members.
                 # If 2 above (for this individual exercise), enter your individual password.    
                   
                 

/**
 * Making the connection to the database.
 * Parameters - host, username, password, team database.
 */
$conn = new mysqli($host, $username, $password, $db);

/**
 * Validating the connection to server.
 */
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>