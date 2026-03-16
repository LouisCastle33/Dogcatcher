<?php include('includes/header.php'); include('db_connect.php'); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <h2 class="fw-bold mb-4">Field Activity Logs</h2>
    <div class="card card-custom border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th>Date/Time</th><th>Dog Name</th><th>Tag ID</th><th>Catcher</th><th>Action</th><th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $logs = $pdo->query("SELECT l.*, d.dog_name, d.tag_serial FROM tbl_impound_logs l JOIN tbl_dogs d ON l.dog_id = d.dog_id ORDER BY log_timestamp DESC")->fetchAll();
                    foreach($logs as $log): ?>
                    <tr>
                        <td><?php echo date('M d, Y h:i A', strtotime($log['log_timestamp'])); ?></td>
                        <td class="fw-bold"><?php echo $log['dog_name']; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $log['tag_serial']; ?></span></td>
                        <td><?php echo $log['catcher_name']; ?></td>
                        <td><span class="badge bg-warning text-dark"><?php echo $log['action_taken']; ?></span></td>
                        <td><i class="fas fa-map-marker-alt text-danger me-1"></i> <?php echo $log['gps_location']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>