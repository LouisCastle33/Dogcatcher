<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<style>
    .dog-card { margin-top: 40px; border-radius: 20px !important; }
    .dog-avatar { 
        width: 85px; height: 85px; object-fit: cover; border: 4px solid #fff; 
        border-radius: 50%; position: absolute; top: -42px; left: 50%; 
        transform: translateX(-50%); box-shadow: 0 4px 10px rgba(0,0,0,0.1); 
        background-color: #f8fafc;
    }
    .print-selector { position: absolute; top: 15px; right: 15px; transform: scale(1.3); }
    
    /* Modal Verification Styling */
    .verify-img {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 1rem;
        border: 3px solid #f1f5f9;
        box-shadow: var(--soft-shadow);
    }

    @media print { 
        .sidebar, .no-print, header, nav, .mobile-top-bar { display: none !important; } 
        main { margin: 0 !important; padding: 0 !important; width: 100% !important; } 
        .row { display: flex !important; flex-wrap: wrap !important; } 
        .col-print { width: 33.33% !important; float: left; padding: 10px; page-break-inside: avoid; } 
        body.qr-only .dog-avatar, body.qr-only .btn, body.qr-only hr { display: none !important; } 
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print border-bottom pb-3 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-dog text-primary me-2"></i>Registry & Tags</h2>
            <p class="text-muted small mb-0">Complete list of registered pets and official LGU tags.</p>
        </div>
        <div class="btn-group shadow-sm">
            <button class="btn btn-outline-primary fw-bold" onclick="printMode('full')"><i class="fas fa-id-card me-2"></i>Full IDs</button>
            <button class="btn btn-primary fw-bold" onclick="printMode('qr')"><i class="fas fa-qrcode me-2"></i>QR Only</button>
        </div>
    </div>

    <div class="row g-4 pt-3" id="printArea">
        <?php 
        // UPDATED SQL: Now pulling contact_number, barangay, and owner_photo for the Modal
        $query = "SELECT d.*, o.fullname, o.owner_photo, o.contact_number, o.barangay 
                  FROM tbl_dogs d 
                  JOIN tbl_owners o ON d.owner_id = o.owner_id 
                  ORDER BY d.dog_id DESC";
        $dogs = $pdo->query($query)->fetchAll();
        
        foreach($dogs as $d): 
            // Create a JSON package of data to send to the Quick Verify Modal
            $dogData = htmlspecialchars(json_encode([
                'tag' => $d['tag_serial'],
                'dogName' => $d['dog_name'],
                'breed' => $d['breed'],
                'dogImg' => 'uploads/dogs/' . $d['dog_photo'],
                'ownerName' => $d['fullname'],
                'ownerImg' => 'uploads/owners/' . $d['owner_photo'],
                'contact' => $d['contact_number'],
                'brgy' => $d['barangay'],
                'ownerId' => $d['owner_id']
            ]), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="col-md-6 col-lg-4 col-xl-3 col-print dog-item">
            <div class="card dog-card text-center pt-5 pb-3 px-3 shadow-sm h-100">
                <input class="form-check-input print-selector no-print" type="checkbox">
                <img src="uploads/dogs/<?php echo $d['dog_photo']; ?>" class="dog-avatar" onerror="this.src='uploads/dogs/default_dog.png';">
                
                <div class="card-body p-0 mt-3 d-flex flex-column">
                    <span class="badge bg-success px-3 py-1 mb-2 mx-auto rounded-pill w-auto shadow-sm"><?php echo $d['tag_serial']; ?></span>
                    <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($d['dog_name']); ?></h5>
                    <small class="text-muted d-block mb-3 fw-bold"><?php echo htmlspecialchars($d['fullname']); ?></small>
                    
                    <canvas class="qr-canvas d-none mt-2 mx-auto" data-serial="<?php echo $d['tag_serial']; ?>"></canvas>
                    
                    <div class="mt-auto no-print d-flex gap-2">
                        <button class="btn btn-primary btn-sm flex-grow-1 fw-bold shadow-sm" onclick="openVerifyModal(<?php echo $dogData; ?>)">
                            <i class="fas fa-check-double me-1"></i> Verify
                        </button>
                        <a href="owner_profile.php?id=<?php echo $d['owner_id']; ?>" class="btn btn-light btn-sm flex-grow-1 fw-bold text-muted border">
                            Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; overflow: hidden;">
      
      <div class="modal-header bg-primary text-white border-0 py-3">
        <h5 class="modal-title fw-bold text-uppercase" style="font-size: 0.9rem; letter-spacing: 1px;">
            <i class="fas fa-shield-alt me-2 text-warning"></i> Identity Verification
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4 bg-light">
          <div class="text-center mb-4">
              <span class="badge bg-success px-4 py-2 fs-5 font-monospace rounded-pill shadow-sm" id="vmTag"></span>
          </div>

          <div class="row g-3 mb-4">
              <div class="col-6 text-center">
                  <label class="small fw-bold text-muted mb-2 text-uppercase">Registered Pet</label>
                  <img id="vmDogImg" src="" class="verify-img" onerror="this.src='uploads/dogs/default_dog.png';">
                  <h5 class="fw-bold text-dark mt-2 mb-0" id="vmDogName"></h5>
                  <small class="text-muted fw-bold" id="vmBreed"></small>
              </div>
              <div class="col-6 text-center">
                  <label class="small fw-bold text-muted mb-2 text-uppercase">Registered Owner</label>
                  <img id="vmOwnerImg" src="" class="verify-img" onerror="this.src='uploads/owners/default_owner.png';">
                  <h5 class="fw-bold text-dark mt-2 mb-0" id="vmOwnerName"></h5>
                  <small class="text-muted fw-bold" id="vmBrgy"></small>
              </div>
          </div>

          <div class="p-3 bg-white rounded border text-center shadow-sm">
              <small class="text-muted fw-bold d-block mb-1">CONTACT NUMBER</small>
              <h5 class="text-dark fw-bold mb-0" id="vmContact"></h5>
          </div>
      </div>
      
      <div class="modal-footer border-0 bg-white p-3 d-flex gap-2">
          <button type="button" class="btn btn-light flex-grow-1 fw-bold text-muted" data-bs-dismiss="modal">Close</button>
          <a href="#" id="vmProfileBtn" class="btn btn-warning flex-grow-1 fw-bold"><i class="fas fa-folder-open me-2"></i>Full Record</a>
      </div>

    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    // 1. QR Canvas Generation
    document.querySelectorAll('.qr-canvas').forEach(c => new QRious({ element: c, value: c.getAttribute('data-serial'), size: 140, foreground: '#0c4a6e' }));
    
    // 2. Print Logic
    function printMode(mode) {
        const anyChecked = Array.from(document.querySelectorAll('.print-selector')).some(cb => cb.checked);
        document.querySelectorAll('.dog-item').forEach(item => {
            const cb = item.querySelector('.print-selector');
            if(anyChecked && !cb.checked) { item.classList.add('d-none'); }
        });
        document.querySelectorAll('.qr-canvas').forEach(c => c.classList.remove('d-none'));
        document.body.classList.add(mode === 'qr' ? 'qr-only' : 'full-card');
        setTimeout(() => { window.print(); location.reload(); }, 500);
    }

    // 3. Quick Verify Modal Logic
    let verifyModalInstance; // Declare outside so it's globally available

    document.addEventListener("DOMContentLoaded", function() {
        verifyModalInstance = new bootstrap.Modal(document.getElementById('verifyModal'));
    });
    
    function openVerifyModal(data) {
        // Feed the specific dog data into the modal fields
        document.getElementById('vmTag').innerText = data.tag;
        document.getElementById('vmDogName').innerText = data.dogName;
        document.getElementById('vmBreed').innerText = data.breed || 'Unknown Breed';
        
        // Handle images (add fallback logic directly to src assignment just in case)
        document.getElementById('vmDogImg').src = data.dogImg;
        document.getElementById('vmOwnerImg').src = data.ownerImg;
        
        document.getElementById('vmOwnerName').innerText = data.ownerName;
        document.getElementById('vmBrgy').innerText = data.brgy;
        document.getElementById('vmContact').innerText = data.contact;
        
        document.getElementById('vmProfileBtn').href = 'owner_profile.php?id=' + data.ownerId;

        // Open the modal
        verifyModalInstance.show();
    }
</script>

<?php include('includes/footer.php'); ?>