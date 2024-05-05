<?php

// Connect to your MySQL database
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "skulist");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

///

// Perform the select operation for 'groups' table to export all items
$queryAllGroups = "SELECT * FROM `groups`";

// Perform the select operation for 'ranges' table to export all items
$queryAllRanges = "SELECT * FROM ranges";

// Execute the select queries
$resultAllGroups = $mysqli->query($queryAllGroups);
$resultAllRanges = $mysqli->query($queryAllRanges);

// Check if the queries were successful
if ($resultAllGroups && $resultAllRanges) {
    // Fetch the results into associative arrays
    $allGroups = $resultAllGroups->fetch_all(MYSQLI_ASSOC);
    $allRanges = $resultAllRanges->fetch_all(MYSQLI_ASSOC);

    // Close the result sets
    $resultAllGroups->free();
    $resultAllRanges->free();

    // Close the database connection
    $mysqli->close();

    // Merge the results into one array
    $allData = array_merge($allGroups, $allRanges);

    // Write to all_items.csv
    $allItemsFile = fopen('all_groups_ranges.csv', 'w');
    fputcsv($allItemsFile, array_keys($allData[0]), ';'); // Write column headings with ';'
    foreach ($allData as $data) {
        fputcsv($allItemsFile, $data, ';');
    }
    fclose($allItemsFile);

    echo "Exported all_groups_ranges.csv successfully!";
} else {
    echo "Error: " . $mysqli->error;
}
