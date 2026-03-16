<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<style>
    /* UI Styles */
    .dog-card {
        border: none;
        border-radius: 20px;
        transition: all 0.3s ease;
        background: #fff;
    }
    .dog-card:hover { transform: translateY(-5px); }
    
    .qr-container {
        background: #fff;
        border-radius: 12px;
        padding: 10px;
        display: inline-block;
        border: 1px solid #f0f0f0;
    }

    .owner-pill {
        background: rgba(0, 74, 153, 0.05);
        color: #004a99;
        font-weight: 600;
        font-size: 0.8rem;
        padding: 5px 12px;
        border-radius: 50px;
        display: inline-flex;
        align-items: center;
    }

    /* PRINT LOGIC: THE "KILLER" FEATURE FOR LGU BASCO */
    @media print {
        /* Hide everything by default */
        .sidebar, .btn, .no-print, header, nav, .breadcrumb { display: none !important; }
        main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        
        /* Layout for printing cards */
        .row { display: flex !important; flex-wrap: wrap !important; }
        .col-print { width: 33.33% !important; padding: 10px !important; float: left !important; }

        /* MODE: QR ONLY */
        body.qr-only-mode .dog-card {
            border: 1px dashed #ccc !important; /* Cut line for lamination */
            box-shadow: none !important;
            padding: 10px !important;
            height: auto !important;
        }
        body.qr-only-mode .dog-info-block, 
        body.qr-only-mode .owner-pill, 
        body.qr-only-mode hr, 
        body.qr-only-mode .view-profile-btn { 
            display: none !important; 
        }
        body.qr-only-mode .qr-container { border: none !important; }
        body.qr-only-mode .qr-serial { font-size: 14pt !important; color: #000 !important; margin-top: 5px; }

        /* MODE: FULL ID CARD */
        body.full-card-mode .dog-card {
            border: 1px solid #004a99 !important;
            border-radius: 10px !important;
        }
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-print me-2 text-primary"></i>Tag Generator</h2>
            <p class="text-muted small">Prepare QR codes for lamination or office records.</p>
        </div>
        <div class="btn-group shadow-sm">
            <button class="btn btn-outline-primary fw-bold px-3" onclick="printMode('full')">
                <i class="fas fa-id-card me-2"></i>Print Full IDs
            </button>
            <button class="btn btn-primary fw-bold px-3" onclick="printMode('qr')">
                <i class="fas fa-qrcode me-2"></i>Print QR Tags Only
            </button>
        </div>
    </div>

    <div class="row g-4" id="printableArea">
        <?php
        $query = "SELECT d.*, o.fullname, o.owner_photo FROM tbl_dogs d 
                  JOIN tbl_owners o ON d.owner_id = o.owner_id 
                  ORDER BY d.dog_id DESC";
        $dogs = $pdo->query($query)->fetchAll();

        foreach($dogs as $dog): 
            $owner_img = !empty($dog['owner_photo']) ? $dog['owner_photo'] : 'default_owner.png';
        ?>
        <div class="col-md-6 col-lg-4 col-xl-3 col-print">
            <div class="card dog-card shadow-sm h-100 text-center">
                <div class="card-body p-3">
                    <div class="qr-container shadow-sm mb-2">
                        <canvas class="qr-canvas" data-serial="<?php echo $dog['tag_serial']; ?>"></canvas>
                        <div class="qr-serial fw-bold text-primary mt-1" style="font-size: 0.8rem;">
                            <?php echo $dog['tag_serial']; ?>
                        </div>
                    </div>

                    <div class="dog-info-block">
                        <h6 class="fw-bold mb-0 mt-2"><?php echo $dog['dog_name']; ?></h6>
                        <small class="text-muted d-block mb-2"><?php echo $dog['breed']; ?></small>
                        <hr class="my-2 opacity-10">
                        <div class="owner-pill mb-2">
                            <img src="uploads/owners/<?php echo $owner_img; ?>" class="rounded-circle me-2" style="width: 20px; height: 20px; object-fit: cover;">
                            <?php echo $dog['fullname']; ?>
                        </div>
                    </div>
                    
                    <div class="no-print mt-2">
                        <a href="owner_profile.php?id=<?php echo $dog['owner_id']; ?>" class="btn btn-sm btn-link text-decoration-none view-profile-btn">View File</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
<script>
    // Initialize QR Codes
    document.querySelectorAll('.qr-canvas').forEach(canvas => {
        new QRious({
            element: canvas,
            value: canvas.getAttribute('data-serial'),
            size: 160,
            level: 'H',
            foreground: '#004a99'
        });
    });

    // Smart Print Function
    function printMode(type) {
        if (type === 'qr') {
            document.body.classList.add('qr-only-mode');
            document.body.classList.remove('full-card-mode');
        } else {
            document.body.classList.add('full-card-mode');
            document.body.classList.remove('qr-only-mode');
        }

        // Slight delay to allow CSS to apply before print dialog
        setTimeout(() => {
            window.print();
            // Clean up after print
            document.body.classList.remove('qr-only-mode', 'full-card-mode');
        }, 500);
    }
</script>

<?php include('includes/footer.php'); ?>