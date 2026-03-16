<?php
include('db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Handle File Upload (The Dog's Picture)
    $target_dir = "uploads/dogs/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_ext = pathinfo($_FILES["dog_photo"]["name"], PATHINFO_EXTENSION);
    $photo_name = "dog_" . time() . "." . $file_ext;
    $target_file = $target_dir . $photo_name;

    if (move_uploaded_file($_FILES["dog_photo"]["tmp_name"], $target_file)) {
        try {
            $pdo->beginTransaction();

            // 2. Insert Owner
            $stmt = $pdo->prepare("INSERT INTO tbl_owners (fullname, barangay, contact_number) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['fullname'], $_POST['barangay'], $_POST['contact']]);
            $owner_id = $pdo->lastInsertId();

            // 3. Generate Tag Serial (e.g., BAS-2026-0001)
            $tag_serial = "BAS-" . date('Y') . "-" . str_pad($owner_id, 4, '0', STR_PAD_LEFT);

            // 4. Insert Dog with Photo
            $stmt = $pdo->prepare("INSERT INTO tbl_dogs (owner_id, tag_serial, dog_name, breed, sex, dog_photo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$owner_id, $tag_serial, $_POST['dog_name'], $_POST['breed'], $_POST['sex'], $photo_name]);

            $pdo->commit();
            header("Location: index.php?success=Registered");
        } catch (Exception $e) {
            $pdo->rollBack();
            die("Error: " . $e->getMessage());
        }
    } else {
        die("Photo upload failed. Check folder permissions.");
    }
}
?>