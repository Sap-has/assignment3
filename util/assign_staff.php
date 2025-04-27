<?php
/**
 * Utility function to randomly assign a staff member to a request
 */
function assignRandomStaff($conn) {
    // Get all staff members
    $query = "SELECT SId FROM Staff";
    $result = $conn->query($query);
    
    // Store staff IDs in an array
    $staffIds = array();
    while ($row = $result->fetch_assoc()) {
        $staffIds[] = $row['SId'];
    }
    
    // If no staff exists, return 1 (default)
    if (count($staffIds) == 0) {
        return 1;
    }
    
    // Return a random staff ID
    $randomIndex = array_rand($staffIds);
    return $staffIds[$randomIndex];
}
?>