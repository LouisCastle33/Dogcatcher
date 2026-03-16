<?php 
include('db_connect.php'); 
include('includes/header.php'); 

if(!isset($_GET['id'])) { header("Location: owners.php"); exit; }

$id = $_GET['id'];
$owner = $pdo->prepare("SELECT * FROM tbl_owners WHERE owner_id = ?");
$owner->execute([$id]);
$o = $owner->fetch();

// Fetch all dogs belonging to this owner
$dogs = $pdo->prepare("SELECT * FROM tbl_dogs WHERE owner_id = ? ORDER BY dog_id DESC");
$dogs->execute([$id]);
$my_dogs = $dogs->fetchAll();
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <nav aria-label="breadcrumb" class="mb-4">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="owners.php" class="text-decoration-none text-primary">Owners</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?php echo $o['fullname']; ?></li>
      </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm text-center p-4 h-100" style="border-radius: 20px;">
                <img src="uploads/owners/<?php echo $o['owner_photo']; ?>" class="rounded-circle mx-auto mb-3 shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 5px solid #fff;">
                
                <h4 class="fw-bold mb-1 text-dark"><?php echo $o['fullname']; ?></h4>
                <p class="text-muted"><i class="fas fa-map-marker-alt text-primary me-1"></i> <?php echo $o['barangay']; ?></p>
                
                <div class="bg-light p-3 rounded text-start mt-3 mb-4">
                    <div class="mb-2">
                        <label class="small text-muted d-block">Contact Number</label>
                        <span class="fw-bold text-dark"><?php echo $o['contact_number']; ?></span>
                    </div>
                    <div>
                        <label class="small text-muted d-block">Address</label>
                        <span class="fw-bold text-dark small"><?php echo $o['address'] ?: 'N/A'; ?></span>
                    </div>
                </div>

                <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#addDogToOwnerModal">
                    <i class="fas fa-plus-circle me-2"></i>Register Another Dog
                </button>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background: white;">
                <h5 class="fw-bold mb-4 d-flex justify-content-between align-items-center">
                    The Pack <span class="badge bg-secondary rounded-pill small"><?php echo count($my_dogs); ?> Pets</span>
                </h5>
                
                <div class="row g-3">
                    <?php if(count($my_dogs) > 0): ?>
                        <?php foreach($my_dogs as $dog): ?>
                        <div class="col-md-6">
                            <div class="card border bg-light h-100 p-3" style="border-radius: 15px;">
                                <div class="d-flex align-items-center">
                                    <img src="uploads/dogs/<?php echo $dog['dog_photo']; ?>" class="rounded shadow-sm me-3" style="width: 90px; height: 90px; object-fit: cover;">
                                    <div>
                                        <h6 class="fw-bold mb-1 text-dark"><?php echo $dog['dog_name']; ?></h6>
                                        <span class="badge bg-white text-dark border mb-2 small"><?php echo $dog['tag_serial']; ?></span>
                                        <div class="small text-muted">
                                            <i class="fas fa-paw me-1"></i> <?php echo $dog['breed']; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center py-5 text-muted">
                            <i class="fas fa-ghost fa-3x mb-3 opacity-25"></i>
                            <p>This owner has no registered dogs yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="addDogToOwnerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
      <div class="modal-header border-0 pt-4 px-4">
        <h5 class="modal-title fw-bold">Add Dog to <?php echo $o['fullname']; ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="add_more_dogs.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="owner_id" value="<?php echo $o['owner_id']; ?>">
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-bold text-muted">Dog's Name</label>
            <input type="text" name="dog_name" class="form-control bg-light border-0" placeholder="e.g., Cooper" required>
          </div>
          <div class="row">
            <div class="col-6 mb-3">
              <label class="form-label small fw-bold text-muted">Breed</label>
              <input type="text" name="breed" class="form-control bg-light border-0" placeholder="Aspin">
            </div>
            <div class="col-6 mb-3">
              <label class="form-label small fw-bold text-muted">Sex</label>
              <select name="sex" class="form-select bg-light border-0">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
          </div>
          <div class="mb-3 text-center bg-light p-3 rounded" style="border: 2px dashed #ddd;">
            <label class="form-label small fw-bold text-muted d-block">Identification Photo</label>
            <input type="file" name="dog_photo" class="form-control form-control-sm border-0" accept="image/*" required>
          </div>
        </div>
        <div class="modal-footer border-0 p-4 pt-0">
          <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 12px;">Finalize Add-on</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include('includes/footer.php'); ?>