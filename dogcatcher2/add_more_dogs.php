<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $owner_id = $_POST['owner_id'];
    $dog_name = trim($_POST['dog_name']);
    $breed    = trim($_POST['breed']);
    $sex      = $_POST['sex'];
    
    // 1. Setup Folder & Upload Dog Photo
    $dog_dir = "uploads/dogs/";
    if (!is_dir($dog_dir)) {
        mkdir($dog_dir, 0777, true);
    }

    $photo_name = "dog_" . time() . "_" . preg_replace("/[^a-zA-Z0-9.]/", "_", $_FILES["dog_photo"]["name"]);
    $upload_path = $dog_dir . $photo_name;

    if (move_uploaded_file($_FILES["dog_photo"]["tmp_name"], $upload_path)) {
        try {
            // 2. SMART SERIAL GENERATION
            // Count current dogs for this owner to determine the next suffix (A, B, C...)
            $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM tbl_dogs WHERE owner_id = ?");
            $stmtCount->execute([$owner_id]);
            $existingCount = $stmtCount->fetchColumn();

            // Convert count to Letter (0 -> A, 1 -> B, etc.)
            $suffix = chr(65 + $existingCount); 
            
            // Format: BAS - YEAR - OWNER_ID (4 digits) - SUFFIX
            $tag_serial = "BAS-" . date('Y') . "-" . str_pad($owner_id, 4, '0', STR_PAD_LEFT) . "-" . $suffix;

            // 3. Insert into Database
            $stmt = $pdo->prepare("INSERT INTO tbl_dogs (owner_id, tag_serial, dog_name, breed, sex, dog_photo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $owner_id, 
                $tag_serial, 
                $dog_name, 
                $breed, 
                $sex, 
                $photo_name
            ]);

            // 4. Redirect back to the Owner's Profile to see the new addition
            header("Location: owner_profile.php?id=" . $owner_id . "&success=added");
            exit();

        } catch (Exception $e) {
            die("Database Error: " . $e->getMessage());
        }
    } else {
        die("Error: Failed to upload the dog's photo. Check folder permissions for 'uploads/dogs/'");
    }
} else {
    // Redirect if page is accessed directly without POST
    header("Location: owners.php");
    exit();
}
?>