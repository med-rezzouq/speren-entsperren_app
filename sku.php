<?php

// Database credentials
$hostname = "127.0.0.1:3306";
$username = "root";
$password = "";
$database = "skulist";

// Connect to your MySQL database using PDO
try {
    $pdo = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Open and read the CSV file containing external keys
$externalKeysFile = fopen('sku.csv', 'r');

// Skip header row
fgetcsv($externalKeysFile);

while (($row = fgetcsv($externalKeysFile)) !== false) {
    // Extract external key from the CSV row
    $external_key_to_check = isset($row[0]) ? $row[0] : null;

    // Update 'exist' column directly
    $stmt = $pdo->prepare("UPDATE items SET approve = 'yes' WHERE externalkey = :external_key");
    $stmt->bindParam(':external_key', $external_key_to_check);
    $stmt->execute();
}

// Close the CSV file and database connection
fclose($externalKeysFile);
$pdo = null;
