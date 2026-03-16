<?php
include('db_connect.php');

// Fetch all dogs and join with owner info
$query = "SELECT d.tag_serial, d.dog_name, d.breed, d.color, d.last_rabies_vax, o.fullname, o.contact_number 
          FROM tbl_dogs d 
          JOIN tbl_owners o ON d.owner_id = o.owner_id";

$stmt = $pdo->query($query);
$data = $stmt->fetchAll();

header('Content-Type: application/json');
echo json_encode($data);
?>