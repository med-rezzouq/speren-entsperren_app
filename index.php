<?php
// Connect to your MySQL database
$mysqli = new mysqli("127.0.0.1:3306", "root", "", "skulist");

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Open and read the CSV file
$csvFile = fopen('pdmarticles.csv', 'r');

// Skip header row
fgetcsv($csvFile);

// ... (Your existing code)

while (($row = fgetcsv($csvFile, 0, ';', '"')) !== false) {

    // Extract relevant columns from the CSV row
    $line = $row;
    $pdmarticle_id =  isset($row[0]) ? $row[0] : null;
    $external_key = isset($row[1]) ? $row[1] : null;
    $product_level = isset($row[2]) ? trim($row[2]) : null;
    $product_name = isset($row[3]) ? trim($row[3]) : null;
    $marketing_item_group = isset($row[5]) ? $row[5] : null;  // Column F
    $parent_id =  isset($row[6]) ? (int)$row[6] : 1;

    // Print product level for debugging

    // Check if product_level is 'item', 'group', 'range', or 'product'
    if ($product_level === "Item" and $parent_id != '10742') {
        // print_r($row);
        if ($marketing_item_group == ''  or $marketing_item_group == 'Not relevant') {
            $relevance = 'Not relevant';
        } else {
            $relevance = 'Relevant';
        }
        // Insert data into 'items' table
        try {
            $product_name = str_replace("'", "\'", $product_name);
            $product_name = str_replace("'", "", $product_name);
            $mysqli->query("INSERT INTO items (pdmid, externalkey, relevance, parentid,productlevel,productname) VALUES ('$pdmarticle_id', '$external_key', '$relevance', '$parent_id','$product_level','" . $product_name . "')");
        } catch (\Throwable $th) {
            //throw $th;
            echo $product_name . '<br>';
        }
    } elseif ($product_level == 'Group classification') {
        // Insert data into 'groups' table
        $mysqli->query("INSERT INTO `groups` (pdmid, externalkey, parentid) VALUES ('$pdmarticle_id', '$external_key', '$parent_id')");
    } elseif ($product_level == 'Range') {
        // Insert data into 'ranges' table
        $mysqli->query("INSERT INTO ranges (pdmid, externalkey, parentid) VALUES ('$pdmarticle_id', '$external_key', '$parent_id')");
    } elseif ($product_level == 'Product') {
        // Insert data into 'products' table
        $mysqli->query("INSERT INTO products (pdmid, externalkey) VALUES ('$pdmarticle_id', '$external_key')");
    }
}

// ... (Your existing code)

// Close the CSV file and database connection
fclose($csvFile);
$mysqli->close();
