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

// Fetch all rows from the 'items' table
$fetchItemsStmt = $pdo->prepare("SELECT pdmid, parentid, relevance, approve FROM items where productlevel='Item' ");
$fetchItemsStmt->execute();
$items = $fetchItemsStmt->fetchAll(PDO::FETCH_ASSOC);

// Loop through each item and update 'productid'
foreach ($items as $item) {
    $pdmid = $item['pdmid'];
    $parentid = $item['parentid'];
    $relevance = $item['relevance'];
    $approve = $item['approve'];

    // Update 'productid' based on 'relevance'
    if ($relevance != 'Not relevant') {
        // Get pdmid from 'groups'
        $groupPdmidStmt = $pdo->prepare("SELECT parentid FROM `groups` WHERE `groups`.pdmid = :parentid");
        $groupPdmidStmt->bindParam(':parentid', $parentid);
        $groupPdmidStmt->execute();
        $groupPdmid = $groupPdmidStmt->fetchColumn();

        // Use group pdmid to get parentid from 'ranges'
        if ($groupPdmid == '30644') {
            echo 'range with id 20517' . $groupPdmid;
        }
        $rangesParentidStmt = $pdo->prepare("SELECT parentid FROM ranges WHERE ranges.pdmid = :groupPdmid");
        $rangesParentidStmt->bindParam(':groupPdmid', $groupPdmid);
        $rangesParentidStmt->execute();
        $rangesParentid = $rangesParentidStmt->fetchColumn();




        if ($rangesParentid !== false) {

            $updateStmt = $pdo->prepare("UPDATE items SET productid = :rangesParentid WHERE pdmid = :pdmid");
            $updateStmt->bindParam(':rangesParentid', $rangesParentid);
            $updateStmt->bindParam(':pdmid', $pdmid);
            $updateStmt->execute();
        }

        // Set groups.exist to 'yes' if groupPdmid is not null and approve is 'yes'
        if ($approve == 'yes') {
            // Update 'productid'
            //  print_r($groupPdmid);
            // echo ' group ';

            // echo ' range';
            // print_r($rangesParentid);
            // echo '  <br>';
            $updateGroupsStmt = $pdo->prepare("UPDATE `groups` SET exist = :existValue WHERE pdmid = :groupPdmid");
            $updateGroupsStmt->bindValue(':existValue', 'yes', PDO::PARAM_STR);
            $updateGroupsStmt->bindParam(':groupPdmid', $parentid, PDO::PARAM_INT);

            $updateGroupsStmt->execute();

            // Set ranges.exist to 'yes' if rangesParentid is not null and approve is 'yes'

            $updateRangesStmt = $pdo->prepare("UPDATE ranges SET exist = 'yes' WHERE pdmid = :rangesParentid");
            $updateRangesStmt->bindParam(':rangesParentid', $groupPdmid);
            $updateRangesStmt->execute();
        }
    } elseif ($relevance == 'Not relevant') {
        // Update 'productid' using the 'ranges' table directly
        $rangesParentidStmt = $pdo->prepare("SELECT parentid FROM ranges WHERE ranges.pdmid = :parentid");
        $rangesParentidStmt->bindParam(':parentid', $parentid);
        $rangesParentidStmt->execute();
        $rangesParentid = $rangesParentidStmt->fetchColumn();

        if ($rangesParentid !== false) {
            // Update 'productid'
            $updateStmt = $pdo->prepare("UPDATE items SET productid = :rangesParentid WHERE pdmid = :pdmid");
            $updateStmt->bindParam(':rangesParentid', $rangesParentid);
            $updateStmt->bindParam(':pdmid', $pdmid);
            $updateStmt->execute();
        }

        // Set ranges.exist to 'yes' if rangesParentid is not null and approve is 'yes'
        if ($rangesParentid && $approve == 'yes') {
            $updateRangesStmt = $pdo->prepare("UPDATE ranges SET exist = 'yes' WHERE pdmid = :rangesParentid");
            $updateRangesStmt->bindParam(':rangesParentid', $parentid);
            $updateRangesStmt->execute();
        }
    }
}

// Close the database connection
$pdo = null;
