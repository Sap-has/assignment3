<?php
// Include this function in your config.php or in a utility file
function assignRandomStaff($conn) {
    // Get all staff IDs
    $query = "SELECT SId FROM Staff";
    $result = $conn->query($query);
    
    $staff_ids = array();
    while ($row = $result->fetch_assoc()) {
        $staff_ids[] = $row['SId'];
    }
    
    // Return a random staff ID or null if no staff exists
    return !empty($staff_ids) ? $staff_ids[array_rand($staff_ids)] : null;
}
?>