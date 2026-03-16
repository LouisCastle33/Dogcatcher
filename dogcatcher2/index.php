<?php 
// 1. Database Connection & System Logic
include('db_connect.php'); 

try {
    $totalDogs = $pdo->query("SELECT COUNT(*) FROM tbl_dogs")->fetchColumn() ?: 0;
    $totalOwners = $pdo->query("SELECT COUNT(*) FROM tbl_owners")->fetchColumn() ?: 0;
    $vaccinated = $pdo->query("SELECT COUNT(*) FROM tbl_dogs WHERE last_rabies_vax >= DATE_SUB(NOW(), INTERVAL 1 YEAR)")->fetchColumn() ?: 0;
    $expired = ($totalDogs > 0) ? ($totalDogs - $vaccinated) : 0;
    $caughtToday = $pdo->query("SELECT COUNT(*) FROM tbl_impound_logs WHERE DATE(log_timestamp) = CURDATE()")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $totalDogs = $totalOwners = $vaccinated = $expired = $caughtToday = 0;
}

include('includes/header.php'); 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 text-dark fw-bold mb-0">Executive Summary</h1>
            <p class="text-muted small">LGU Basco Pet Registry Management Dashboard</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button class="btn btn-primary px-4 py-2 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addDogModal" style="border-radius: 10px;">
                <i class="fas fa-plus me-2"></i>Register New Pet
            </button>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom border-0 shadow-sm p-3 bg-white" style="border-radius: 15px;">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3 text-primary">
                        <i class="fas fa-dog fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-0">Total Dogs</h6>
                        <h3 class="fw-bold mb-0"><?php echo number_format($totalDogs); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom border-0 shadow-sm p-3 bg-white" style="border-radius: 15px;">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3 text-success">
                        <i class="fas fa-syringe fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-0">Vaccinated</h6>
                        <h3 class="fw-bold mb-0"><?php echo number_format($vaccinated); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom border-0 shadow-sm p-3 bg-white" style="border-radius: 15px;">
                <div class="d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-circle me-3 text-danger">
                        <i class="fas fa-shield-virus fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-0">Expired Vax</h6>
                        <h3 class="fw-bold mb-0"><?php echo number_format($expired); ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom border-0 shadow-sm p-3 bg-white" style="border-radius: 15px;">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3 text-info">
                        <i class="fas fa-truck-pickup fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted small mb-0">Caught Today</h6>
                        <h3 class="fw-bold mb-0"><?php echo number_format($caughtToday); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-custom border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-ul text-primary me-2"></i> Recent Field Activity</h5>
            <a href="logs.php" class="btn btn-sm btn-light text-primary fw-bold px-3">View All Logs</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small">
                    <tr>
                        <th class="ps-4">TAG ID</th>
                        <th>DOG NAME</th>
                        <th>CATCHER</th>
                        <th>ACTION</th>
                        <th>TIME</th>
                        <th class="text-end pe-4">STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentLogs = $pdo->query("SELECT l.*, d.dog_name, d.tag_serial FROM tbl_impound_logs l JOIN tbl_dogs d ON l.dog_id = d.dog_id ORDER BY log_timestamp DESC LIMIT 5")->fetchAll();
                    if(count($recentLogs) > 0):
                        foreach($recentLogs as $log): ?>
                        <tr>
                            <td class="ps-4"><span class="badge bg-secondary opacity-75"><?php echo $log['tag_serial']; ?></span></td>
                            <td class="fw-bold"><?php echo $log['dog_name']; ?></td>
                            <td><?php echo $log['catcher_name']; ?></td>
                            <td><?php echo $log['action_taken']; ?></td>
                            <td><?php echo date('h:i A', strtotime($log['log_timestamp'])); ?></td>
                            <td class="text-end pe-4">
                                <span class="badge rounded-pill <?php echo $log['is_synced'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                    <?php echo $log['is_synced'] ? 'Synced' : 'Pending Sync'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach;
                    else: ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted small italic">No recent activity detected.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="addDogModal" tabindex="-1" aria-labelledby="addDogModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <h5 class="modal-title fw-bold" id="addDogModalLabel"><i class="fas fa-id-card text-primary me-2"></i> Dual-Photo Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="register_process.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <div class="row g-4">
            <div class="col-md-6 border-end">
              <p class="text-primary small fw-bold text-uppercase mb-3">Owner Profile</p>
              <div class="mb-3 text-center">
                  <div class="bg-light rounded p-2 mb-2" style="height: 120px; border: 2px dashed #ddd;">
                      <i class="fas fa-user text-muted fa-3x mt-3"></i>
                  </div>
                  <label class="form-label small fw-bold">Owner's Photo</label>
                  <input type="file" name="owner_photo" class="form-control form-control-sm border-0 bg-light" accept="image/*" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Full Name</label>
                <input type="text" name="fullname" class="form-control bg-light border-0" placeholder="Juan Dela Cruz" required>
              </div>
              <div class="mb-3">
    <label class="form-label small fw-bold">Barangay</label>
    <select name="barangay" class="form-select bg-light border-0" required>
        <option value="" disabled selected>Select Barangay</option>
        <option value="Kayvaluganan">Kayvaluganan</option>
        <option value="Kayhuvokan">Kayhuvokan</option> <option value="Kaychanarianan">Kaychanarianan</option>
        <option value="San Joaquin">San Joaquin</option>
        <option value="San Antonio">San Antonio</option>
        <option value="Tukon">Tukon</option> <option value="Chanarian">Chanarian</option> </select>
</div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Contact Number</label>
                <input type="text" name="contact" class="form-control bg-light border-0" placeholder="0917-000-0000" required>
              </div>
            </div>

            <div class="col-md-6">
              <p class="text-primary small fw-bold text-uppercase mb-3">Dog Profile</p>
              <div class="mb-3 text-center">
                  <div class="bg-light rounded p-2 mb-2" style="height: 120px; border: 2px dashed #ddd;">
                      <i class="fas fa-paw text-muted fa-3x mt-3"></i>
                  </div>
                  <label class="form-label small fw-bold">Dog's Photo</label>
                  <input type="file" name="dog_photo" class="form-control form-control-sm border-0 bg-light" accept="image/*" required>
              </div>
              <div class="mb-3">
                <label class="form-label small fw-bold">Dog's Name</label>
                <input type="text" name="dog_name" class="form-control bg-light border-0" placeholder="e.g. Bantay" required>
              </div>
              <div class="row g-2">
                  <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Breed</label>
                    <input type="text" name="breed" class="form-control bg-light border-0" placeholder="Aspin">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="form-label small fw-bold">Sex</label>
                    <select name="sex" class="form-select bg-light border-0">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 p-4 pt-0 text-center d-block">
          <p class="text-muted small mb-3"><i class="fas fa-info-circle me-1"></i> Photos are required for accurate identification in the field.</p>
          <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 12px;">Complete Registration</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>