<?php
session_start();
include('db_connect.php');

// Security Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['new_photo'])) {
    $upload_type = $_POST['upload_type']; // Will be 'owner' or 'dog'
    $record_id = $_POST['record_id'];
    $owner_return_id = $_POST['owner_return_id']; // To redirect back to the right profile

    $file = $_FILES['new_photo'];
    
    // Basic validation
    if ($file['error'] == 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'jfif'];
        
        if (in_array($ext, $allowed)) {
            // Generate a unique file name
            $new_file_name = $upload_type . '_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            
            // Determine folder and database table based on type
            if ($upload_type === 'owner') {
                $target_path = 'uploads/owners/' . $new_file_name;
                $sql = "UPDATE tbl_owners SET owner_photo = ? WHERE owner_id = ?";
            } else {
                $target_path = 'uploads/dogs/' . $new_file_name;
                $sql = "UPDATE tbl_dogs SET dog_photo = ? WHERE dog_id = ?";
            }

            // Upload the file and update DB
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$new_file_name, $record_id]);
                
                echo "<script>alert('Photo updated successfully!'); window.location.href='owner_profile.php?id=$owner_return_id';</script>";
                exit();
            } else {
                echo "<script>alert('Failed to move uploaded file.'); window.location.href='owner_profile.php?id=$owner_return_id';</script>";
                exit();
            }
        } else {
            echo "<script>alert('Invalid file format. Only JPG and PNG allowed.'); window.location.href='owner_profile.php?id=$owner_return_id';</script>";
            exit();
        }
    }
}
header("Location: owners.php");
exit();
?>