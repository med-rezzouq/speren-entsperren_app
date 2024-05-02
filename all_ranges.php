<?php

// Connect to your MySQL database
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "skulist");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Perform the select operation for 'items' table to export all items
$queryAllItems = "
    SELECT * FROM ranges
";

// Execute the select query for 'items' table to export all items
$resultAllItems = $mysqli->query($queryAllItems);

// Check if the query for 'items' table was successful
if ($resultAllItems) {
    // Fetch the results into an associative array for 'items' table
    $allItems = $resultAllItems->fetch_all(MYSQLI_ASSOC);

    // Close the result set for 'items' table
    $resultAllItems->free();

    // Close the database connection
    $mysqli->close();

    // Write to all_items.csv for 'items' table
    $allItemsFile = fopen('all_items.csv', 'w');
    fputcsv($allItemsFile, array_keys($allItems[0]), ';'); // Write column headings with ';'
    foreach ($allItems as $item) {
        fputcsv($allItemsFile, $item, ';');
    }
    fclose($allItemsFile);

    echo "Exported all_items.csv successfully!";
} else {
    echo "Error: " . $mysqli->error;
}
