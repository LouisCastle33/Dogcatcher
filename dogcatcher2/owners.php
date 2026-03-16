<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-users me-2 text-primary"></i>Owners Registry</h2>
            <p class="text-muted small">Manage residents and their registered pet counts.</p>
        </div>
        <div class="search-box">
            <input type="text" id="ownerSearch" class="form-control shadow-sm" placeholder="Search by name or barangay..." style="border-radius: 10px;">
        </div>
    </div>

    <div class="row g-4">
        <?php
        $owners = $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM tbl_dogs WHERE owner_id = o.owner_id) as dog_count FROM tbl_owners o ORDER BY o.fullname ASC")->fetchAll();
        
        foreach($owners as $row): 
            $owner_img = !empty($row['owner_photo']) ? $row['owner_photo'] : 'default_owner.png';
        ?>
        <div class="col-md-4 owner-card" data-name="<?php echo strtolower($row['fullname'] . ' ' . $row['barangay']); ?>">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 15px;">
                <div class="d-flex align-items-center mb-3">
                    <img src="uploads/owners/<?php echo $owner_img; ?>" class="rounded-circle me-3 border border-2 border-primary border-opacity-25" style="width: 65px; height: 65px; object-fit: cover;">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?php echo $row['fullname']; ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $row['barangay']; ?></small>
                    </div>
                </div>
                
                <div class="bg-light rounded p-2 mb-3 d-flex justify-content-between align-items-center px-3">
                    <span class="small text-muted">Registered Pets</span>
                    <span class="badge bg-primary rounded-pill"><?php echo $row['dog_count']; ?></span>
                </div>

                <a href="owner_profile.php?id=<?php echo $row['owner_id']; ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold py-2" style="border-radius: 8px;">
                    View Full Profile
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
    // Real-time Search Logic
    document.getElementById('ownerSearch').addEventListener('keyup', function() {
        let val = this.value.toLowerCase();
        document.querySelectorAll('.owner-card').forEach(card => {
            let name = card.getAttribute('data-name');
            card.style.display = name.includes(val) ? 'block' : 'none';
        });
    });
</script>

<?php include('includes/footer.php'); ?>