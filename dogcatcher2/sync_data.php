<?php
// sync_data.php
include('db_connect.php');

// Tell the browser we are sending JSON data
header('Content-Type: application/json');

try {
    /** * SENIOR DEV TIP: 
     * We use a JOIN here to link the Dog table and the Owner table.
     * This ensures the phone gets the Barangay and the Owner Photo 
     * linked to the correct Dog ID in one single trip.
     */
    $query = "SELECT 
                d.tag_serial, 
                d.dog_name, 
                d.breed, 
                d.sex, 
                d.dog_photo, 
                o.fullname, 
                o.barangay, 
                o.contact_number, 
                o.owner_photo 
              FROM tbl_dogs d 
              JOIN tbl_owners o ON d.owner_id = o.owner_id";

    $stmt = $pdo->query($query);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send the complete list to the phone
    echo json_encode($data);

} catch (Exception $e) {
    // If the database fails, send the error message so we can debug
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>