<?php
include('db_connect.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. COLLECT & CLEAN DATA
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $barangay = htmlspecialchars($_POST['barangay']);
    $contact  = htmlspecialchars(trim($_POST['contact']));
    $dog_name = htmlspecialchars(trim($_POST['dog_name']));
    $breed    = htmlspecialchars(trim($_POST['breed']));
    $sex      = $_POST['sex'];

    // Secure Upload Helper
    function secureUpload($file, $targetDir, $prefix) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        $maxSize = 5 * 1024 * 1024; // 5MB Limit

        if ($file['error'] !== 0) return "default_" . $prefix . ".png";
        if (!in_array($file['type'], $allowedTypes)) return "default_" . $prefix . ".png";
        if ($file['size'] > $maxSize) return "default_" . $prefix . ".png";

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newFilename = $prefix . "_" . time() . "_" . rand(1000, 9999) . "." . $ext;
        
        if (move_uploaded_file($file['tmp_name'], $targetDir . $newFilename)) {
            return $newFilename;
        }
        return "default_" . $prefix . ".png";
    }

    try {
        $pdo->beginTransaction();

        // 2. HANDLE OWNER PHOTO
        $owner_photo = secureUpload($_FILES['owner_photo'], 'uploads/owners/', 'owner');

        // INSERT OWNER
        $stmt_owner = $pdo->prepare("INSERT INTO tbl_owners (fullname, barangay, contact_number, owner_photo) VALUES (?, ?, ?, ?)");
        $stmt_owner->execute([$fullname, $barangay, $contact, $owner_photo]);
        $owner_id = $pdo->lastInsertId();

        // 3. HANDLE DOG PHOTO
        $dog_photo = secureUpload($_FILES['dog_photo'], 'uploads/dogs/', 'dog');

        // GENERATE TAG SERIAL
        $tag_serial = "BAS-" . date('Y') . "-" . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // INSERT DOG
        $stmt_dog = $pdo->prepare("INSERT INTO tbl_dogs (owner_id, tag_serial, dog_name, breed, sex, dog_photo, status) VALUES (?, ?, ?, ?, ?, ?, 'Home')");
        $stmt_dog->execute([$owner_id, $tag_serial, $dog_name, $breed, $sex, $dog_photo]);

        $pdo->commit();
        header("Location: index.php?registration=success");
        exit();

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        die("Critical Registration Error: " . $e->getMessage());
    }
}
?>