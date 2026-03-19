<?php
// upload_logs.php
include('db_connect.php');

// Get the JSON data sent from the phone
$json = file_get_contents('php://input');
$logs = json_decode($json, true);

if (!empty($logs)) {
    try {
        $pdo->beginTransaction();
        
        foreach ($logs as $log) {
            // 1. Get dog_id from the serial
            $stmt = $pdo->prepare("SELECT dog_id FROM tbl_dogs WHERE tag_serial = ?");
            $stmt->execute([$log['tag_serial']]);
            $dog_id = $stmt->fetchColumn();

            if ($dog_id) {
                // 2. Insert into Impound Logs
                $insert = $pdo->prepare("INSERT INTO tbl_impound_logs (dog_id, log_timestamp, catcher_name, action_taken, is_synced) VALUES (?, ?, ?, ?, ?)");
                // We use the timestamp from the phone so the time is accurate to when it was caught
                $insert->execute([
                    $dog_id, 
                    date('Y-m-d H:i:s', strtotime($log['log_timestamp'])), 
                    'Mobile Catcher', 
                    'Caught in Field', 
                    1
                ]);
            }
        }
        
        $pdo->commit();
        echo "Success";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "No Data Received";
}
?>