<?php

// Connect to your MySQL database
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "skulist");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Perform the join operation
$query = "
    SELECT products.pdmid,items.productid
    FROM items
    JOIN products ON items.productid = products.pdmid
    WHERE items.approve = 'yes'
    GROUP BY products.pdmid
";

// Execute the select query
$result = $mysqli->query($query);
print_r($result);
// Check if the query was successful
if ($result) {
    // Fetch the results into an associative array
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    // Loop through the results
    foreach ($rows as $row) {
        $pdmidToUpdate = $row['pdmid'];

        // Update items table
        $updateQuery = "UPDATE items SET exist = 'yes' WHERE productid = '$pdmidToUpdate'";
        $mysqli->query($updateQuery);
    }

    // Free the result set
    $result->free();
}

// Close the database connection
$mysqli->close();
