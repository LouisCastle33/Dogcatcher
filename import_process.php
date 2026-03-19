<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db_connect.php');

// Security Check: Only admins can import
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'Catcher') {
    header("Location: scanner.php");
    exit();
}

if (isset($_POST['import_btn'])) {
    
    // Check if file was uploaded without errors
    if (isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != "") {
        
        $allowed_ext = ['csv'];
        $file_ext = strtolower(pathinfo($_FILES['csv_file']['name'], PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed_ext)) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, "r");
            
            // Skip the header row (Owner Name, Barangay, etc.)
            fgetcsv($handle);
            
            $successCount = 0;
            $currentYear = date('Y');

            try {
                // Begin Transaction for Data Integrity
                $pdo->beginTransaction();

                // Get the highest Tag ID for the current year to continue the sequence
                $stmtTag = $pdo->query("SELECT tag_serial FROM tbl_dogs WHERE tag_serial LIKE 'BAS-$currentYear-%' ORDER BY dog_id DESC LIMIT 1");
                $lastTag = $stmtTag->fetchColumn();
                
                if ($lastTag) {
                    // Extract the numerical part (e.g., "0045" from "BAS-2026-0045")
                    $parts = explode('-', $lastTag);
                    $counter = intval($parts[2]);
                } else {
                    $counter = 0;
                }

                // Loop through every row in the CSV
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    
                    // Ensure the row has the correct number of columns
                    if (count($data) < 6) continue;

                    // Clean the data
                    $owner_name = trim($data[0]);
                    $barangay   = trim($data[1]);
                    $contact    = trim($data[2]);
                    $dog_name   = trim($data[3]);
                    $breed      = trim($data[4]);
                    $sex        = trim($data[5]);

                    // Skip empty rows
                    if (empty($owner_name) || empty($dog_name)) continue;

                    // 1. HANDLE OWNER REGISTRATION
                    // Check if owner already exists to prevent duplicates
                    $stmtOwner = $pdo->prepare("SELECT owner_id FROM tbl_owners WHERE fullname = ? AND contact_number = ?");
                    $stmtOwner->execute([$owner_name, $contact]);
                    $owner = $stmtOwner->fetch();

                    if ($owner) {
                        $owner_id = $owner['owner_id']; // Use existing owner
                    } else {
                        // Create new owner with a default photo
                        $insertOwner = $pdo->prepare("INSERT INTO tbl_owners (fullname, barangay, contact_number, owner_photo) VALUES (?, ?, ?, ?)");
                        $insertOwner->execute([$owner_name, $barangay, $contact, 'default_owner.png']);
                        $owner_id = $pdo->lastInsertId();
                    }

                    // 2. HANDLE DOG REGISTRATION
                    $counter++;
                    // Format the tag: BAS-YYYY-XXXX (e.g., BAS-2026-0001)
                    $tag_serial = "BAS-" . $currentYear . "-" . str_pad($counter, 4, '0', STR_PAD_LEFT);

                    // Insert dog with 'Active' status and default photo
                    $insertDog = $pdo->prepare("INSERT INTO tbl_dogs (owner_id, dog_name, breed, sex, tag_serial, dog_photo, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $insertDog->execute([$owner_id, $dog_name, $breed, $sex, $tag_serial, 'default_dog.png', 'Active']);

                    $successCount++;
                }
                
                fclose($handle);
                $pdo->commit(); // Save all changes to the database
                
                // Success Alert & Redirect
                echo "<script>
                        alert('Import Successful! Successfully added $successCount new pet records to the registry.');
                        window.location.href='dogs.php';
                      </script>";
                exit();

            } catch (Exception $e) {
                $pdo->rollBack(); // Cancel everything if an error occurs
                $errorMsg = addslashes($e->getMessage());
                echo "<script>
                        alert('Database Error: $errorMsg');
                        window.location.href='import.php';
                      </script>";
                exit();
            }

        } else {
            echo "<script>
                    alert('Invalid file format. Please upload a .csv file.');
                    window.location.href='import.php';
                  </script>";
            exit();
        }
    } else {
        echo "<script>
                alert('No file selected. Please choose a file to import.');
                window.location.href='import.php';
              </script>";
        exit();
    }
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: import.php");
    exit();
}
?>