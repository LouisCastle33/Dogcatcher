<?php 
include('db_connect.php'); 
include('includes/header.php'); 

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('Invalid Owner ID'); window.location='owners.php';</script>";
    exit;
}

$owner_id = $_GET['id'];

// Fetch Owner Details
$stmt = $pdo->prepare("SELECT * FROM tbl_owners WHERE owner_id = ?");
$stmt->execute([$owner_id]);
$owner = $stmt->fetch();

if (!$owner) {
    echo "<script>alert('Owner not found'); window.location='owners.php';</script>";
    exit;
}

// Fetch Dogs belonging to this owner
$stmtDogs = $pdo->prepare("SELECT * FROM tbl_dogs WHERE owner_id = ? ORDER BY dog_id DESC");
$stmtDogs->execute([$owner_id]);
$dogs = $stmtDogs->fetchAll();
?>

<style>
    /* Profile Header Styling */
    .profile-header {
        background: linear-gradient(135deg, var(--basco-primary) 0%, #075985 100%);
        border-radius: 1.5rem;
        padding: 3rem 2rem;
        color: white;
        position: relative;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(12, 74, 110, 0.2);
    }
    
    .profile-avatar {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid rgba(255,255,255,0.2);
        border-radius: 50%;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    /* Overlapping Dog Cards */
    .dog-card { margin-top: 45px; border-radius: 1.5rem !important; }
    
    .dog-img-wrapper {
        position: absolute;
        top: -42px;
        left: 50%;
        transform: translateX(-50%);
        width: 85px;
        height: 85px;
        z-index: 10;
    }

    .dog-avatar {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border: 4px solid #fff;
        border-radius: 50%;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        background: #f1f5f9;
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
    
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2">
        <a href="owners.php" class="btn btn-light shadow-sm text-muted fw-bold">
            <i class="fas fa-arrow-left me-2"></i>Back to Registry
        </a>
        <button class="btn btn-warning fw-bold shadow-sm no-print" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Print File
        </button>
    </div>

    <div class="profile-header mb-5 text-center text-md-start d-flex flex-column flex-md-row align-items-center gap-4">
        
        <div class="position-relative d-inline-block">
            <img src="uploads/owners/<?php echo $owner['owner_photo']; ?>" class="profile-avatar" onerror="this.src='uploads/owners/default_owner.png';">
            <button class="btn btn-warning btn-sm rounded-circle position-absolute bottom-0 end-0 shadow no-print d-flex justify-content-center align-items-center" 
                    style="width: 35px; height: 35px; right: 0px; bottom: 0px;" 
                    data-bs-toggle="modal" data-bs-target="#photoModal" 
                    onclick="setPhotoModal('owner', <?php echo $owner['owner_id']; ?>, <?php echo $owner['owner_id']; ?>)">
                <i class="fas fa-camera"></i>
            </button>
        </div>
        
        <div class="flex-grow-1">
            <h1 class="fw-bold mb-1"><?php echo htmlspecialchars($owner['fullname']); ?></h1>
            <p class="mb-3 opacity-75 fw-bold" style="letter-spacing: 1px;">
                <i class="fas fa-map-marker-alt text-warning me-2"></i><?php echo htmlspecialchars($owner['barangay']); ?>, Basco
            </p>
            <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-3">
                <span class="badge bg-white text-dark bg-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-phone-alt me-2 text-warning"></i><?php echo htmlspecialchars($owner['contact_number']); ?>
                </span>
                <span class="badge bg-white text-dark bg-opacity-25 px-3 py-2 rounded-pill shadow-sm">
                    <i class="fas fa-paw me-2 text-warning"></i><?php echo count($dogs); ?> Registered Pets
                </span>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="fas fa-dog text-primary me-2"></i>The Pack (Registered Pets)</h4>
    
    <div class="row g-4 mb-5">
        <?php if(empty($dogs)): ?>
            <div class="col-12 text-center py-5">
                <div class="p-4 bg-white rounded-circle d-inline-block shadow-sm mb-3">
                    <i class="fas fa-bone fa-3x text-muted opacity-25"></i>
                </div>
                <h5 class="fw-bold text-muted">No pets registered yet.</h5>
            </div>
        <?php else: ?>
            <?php foreach($dogs as $d): ?>
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card dog-card text-center pt-5 pb-3 px-3 shadow-sm h-100 position-relative">
                    
                    <div class="dog-img-wrapper">
                        <img src="uploads/dogs/<?php echo $d['dog_photo']; ?>" class="dog-avatar" onerror="this.src='uploads/dogs/default_dog.png';">
                        <button class="btn btn-warning btn-sm rounded-circle position-absolute bottom-0 end-0 shadow no-print d-flex justify-content-center align-items-center" 
                                style="width: 28px; height: 28px; transform: translate(10%, 10%);" 
                                data-bs-toggle="modal" data-bs-target="#photoModal" 
                                onclick="setPhotoModal('dog', <?php echo $d['dog_id']; ?>, <?php echo $owner['owner_id']; ?>)">
                            <i class="fas fa-camera" style="font-size: 0.75rem;"></i>
                        </button>
                    </div>
                    
                    <div class="card-body p-0 mt-3 d-flex flex-column">
                        <div class="mb-2">
                            <span class="badge bg-success px-3 py-1 rounded-pill font-monospace shadow-sm"><?php echo $d['tag_serial']; ?></span>
                        </div>
                        <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($d['dog_name']); ?></h4>
                        <div class="text-muted small fw-bold mb-3"><?php echo htmlspecialchars($d['breed']); ?> • <?php echo htmlspecialchars($d['sex']); ?></div>
                        
                        <div class="mt-auto p-2 bg-light rounded text-start">
                            <small class="text-muted d-block text-center fw-bold text-uppercase" style="font-size: 0.65rem;">Current Status</small>
                            <div class="text-center mt-1">
                                <?php if($d['status'] == 'Impounded'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-1 rounded-pill">Impounded</span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-1 rounded-pill">Active / Clear</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</main>

<div class="modal fade" id="photoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h6 class="modal-title fw-bold"><i class="fas fa-camera me-2 text-warning"></i> Update Photo</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="update_photo.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4 text-center">
            
            <input type="hidden" name="upload_type" id="modalUploadType">
            <input type="hidden" name="record_id" id="modalRecordId">
            <input type="hidden" name="owner_return_id" id="modalOwnerReturnId">
            
            <div class="mb-3">
                <label class="small text-muted fw-bold mb-2 d-block text-uppercase">Select New Image</label>
                <input type="file" name="new_photo" class="form-control bg-light border-0" accept="image/*" required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm" style="border-radius: 1rem;">
                <i class="fas fa-upload me-2"></i> Upload & Save
            </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Logic to pass data into the Modal when a camera icon is clicked
function setPhotoModal(type, recordId, returnId) {
    document.getElementById('modalUploadType').value = type;
    document.getElementById('modalRecordId').value = recordId;
    document.getElementById('modalOwnerReturnId').value = returnId;
}
</script>

<?php include('includes/footer.php'); ?>