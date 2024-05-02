<?php

// Connect to your MySQL database
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "skulist");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Perform the select operation for 'items' table
$queryItems = "
    SELECT items.pdmid AS PdmarticleID, items.externalkey AS ExternalKey, items.parentid AS ParentID,
            items.productlevel AS ProductLevel,
            items.approve AS show_in_2024 , items.exist
    FROM items
";

// Execute the select query for 'items' table
$resultItems = $mysqli->query($queryItems);

// Check if the query for 'items' table was successful
if ($resultItems) {

    // Fetch the results into an associative array for 'items' table
    $rowsItems = $resultItems->fetch_all(MYSQLI_ASSOC);

    // Initialize arrays for approved and blocked entries for 'items' table
    $approvedEntriesItems = [];
    $blockedEntriesItems = [];

    // Loop through the results for 'items' table
    foreach ($rowsItems as $row) {
        // Separate entries based on approve and exist values for 'items' table
        if (isset($row['show_in_2024']) && $row['show_in_2024'] == 'yes') {
            $approvedEntry = $row;
            // Remove the 'exist' key
            unset($approvedEntry['exist']);
            $approvedEntriesItems[] = $approvedEntry;
        } elseif (isset($row['exist']) && $row['exist'] == 'yes' && $row['show_in_2024'] == 'no') {
            $blockedEntry = $row;
            // Remove the 'exist' key
            unset($blockedEntry['exist']);
            $blockedEntriesItems[] = $blockedEntry;
        }
    }

    // Close the result set for 'items' table
    $resultItems->free();
}


// Perform the select operation for 'groups' table
$queryGroups = "
    SELECT pdmid AS PdmarticleID, externalkey AS ExternalKey, parentid AS ParentID, 'Group classification' AS ProductLevel, exist AS show_in_2024
    FROM `groups`
    WHERE exist = 'yes'
";

// Execute the select query for 'groups' table
$resultGroups = $mysqli->query($queryGroups);

// Check if the query for 'groups' table was successful
if ($resultGroups) {

    // Fetch the results into an associative array for 'groups' table
    $rowsGroups = $resultGroups->fetch_all(MYSQLI_ASSOC);

    // Initialize array for approved entries for 'groups' table
    $approvedEntriesGroups = [];

    // Loop through the results for 'groups' table
    foreach ($rowsGroups as $row) {
        // Remove the 'exist' key
        unset($row['exist']);
        // Add entries to the approved array for 'groups' table
        $approvedEntriesGroups[] = $row;
    }

    // Close the result set for 'groups' table
    $resultGroups->free();
}

// Perform the select operation for 'ranges' table
$queryRanges = "
    SELECT pdmid AS PdmarticleID, externalkey AS ExternalKey, parentid AS ParentID, 'Range' AS ProductLevel, exist AS show_in_2024
    FROM `ranges`
    WHERE exist = 'yes'
";

// Execute the select query for 'ranges' table
$resultRanges = $mysqli->query($queryRanges);

// Check if the query for 'ranges' table was successful
if ($resultRanges) {

    // Fetch the results into an associative array for 'ranges' table
    $rowsRanges = $resultRanges->fetch_all(MYSQLI_ASSOC);

    // Initialize array for approved entries for 'ranges' table
    $approvedEntriesRanges = [];

    // Loop through the results for 'ranges' table
    foreach ($rowsRanges as $row) {
        // Remove the 'exist' key
        unset($row['exist']);
        // Add entries to the approved array for 'ranges' table
        $approvedEntriesRanges[] = $row;
    }

    // Close the result set for 'ranges' table
    $resultRanges->free();
}

// Close the database connection
$mysqli->close();

// Combine entries from 'items', 'groups', and 'ranges'
$combinedEntries = array_merge($approvedEntriesItems, $approvedEntriesGroups, $approvedEntriesRanges, $blockedEntriesItems);

// Write to approved_items.csv for combined entries
$approvedFileItems = fopen('approved_items.csv', 'w');
fputcsv($approvedFileItems, array_keys($combinedEntries[0]), ';'); // Write column headings with ';'
foreach ($combinedEntries as $entry) {
    fputcsv($approvedFileItems, $entry, ';');
}
fclose($approvedFileItems);
