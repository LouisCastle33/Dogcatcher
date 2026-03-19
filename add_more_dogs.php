<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $owner_id = $_POST['owner_id'];
    $dog_name = htmlspecialchars(trim($_POST['dog_name']));
    $breed    = htmlspecialchars(trim($_POST['breed']));
    $sex      = $_POST['sex'];
    
    // 1. Setup Folder & Secure Upload
    $dog_dir = "uploads/dogs/";
    if (!is_dir($dog_dir)) {
        mkdir($dog_dir, 0777, true);
    }

    $photo_name = "dog_" . time() . "_" . rand(100,999) . "." . pathinfo($_FILES["dog_photo"]["name"], PATHINFO_EXTENSION);
    $upload_path = $dog_dir . $photo_name;

    if (move_uploaded_file($_FILES["dog_photo"]["tmp_name"], $upload_path)) {
        try {
            // 2. Specialized Tag Serial Generation
            // Check how many dogs this owner already has
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_dogs WHERE owner_id = ?");
            $stmtCount->execute([$owner_id]);
            $existingCount = $stmtCount->fetchColumn();

            // Suffix: 0=A, 1=B, 2=C...
            $suffix = chr(65 + $existingCount); 
            $tag_serial = "BAS-" . date('Y') . "-" . str_pad($owner_id, 4, '0', STR_PAD_LEFT) . "-" . $suffix;

            // 3. Insert into Database
            $stmt = $pdo->prepare("INSERT INTO tbl_dogs (owner_id, tag_serial, dog_name, breed, sex, dog_photo, status) VALUES (?, ?, ?, ?, ?, ?, 'Home')");
            $stmt->execute([
                $owner_id, 
                $tag_serial, 
                $dog_name, 
                $breed, 
                $sex, 
                $photo_name
            ]);

            header("Location: owner_profile.php?id=" . $owner_id . "&success=added");
            exit();

        } catch (Exception $e) {
            error_log($e->getMessage());
            die("Database Error. Please contact IT.");
        }
    } else {
        die("Error: Failed to upload photo. Check permissions for 'uploads/dogs/'");
    }
}