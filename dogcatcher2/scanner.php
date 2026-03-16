<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-camera-retro me-2 text-primary"></i>Field Scanner</h2>
            <p id="syncStatus" class="text-muted small">Registry last updated: <span id="lastSyncDate">Never</span></p>
        </div>
        <button id="syncBtn" class="btn btn-success shadow-sm fw-bold" onclick="syncRegistry()" style="border-radius: 10px;">
            <i class="fas fa-sync-alt me-1"></i> Update Offline Data
        </button>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg overflow-hidden mb-4" style="border-radius: 25px; background: #000;">
                <div id="reader" style="width: 100%;"></div>
                <div id="scannerPlaceholder" class="text-center py-5 text-white">
                    <i class="fas fa-qrcode fa-4x mb-3 opacity-50"></i>
                    <p>Camera is inactive</p>
                    <button class="btn btn-primary px-5 py-2 fw-bold" onclick="startScanner()" style="border-radius: 12px;">
                        Open Scanner
                    </button>
                </div>
            </div>

            <div id="resultCard" class="card border-0 shadow-lg d-none" style="border-radius: 25px; overflow: hidden;">
                </div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://unpkg.com/dexie/dist/dexie.js"></script>

<script>
// 1. DATABASE SETUP (Dexie.js)
const db = new Dexie("LGU_Basco_Registry");
db.version(1).stores({
    dogs: "tag_serial, dog_name, fullname, barangay" 
});

// Update the timestamp on load
document.getElementById('lastSyncDate').innerText = localStorage.getItem('lastSync') || 'Never';

// 2. SYNC ENGINE: Pulls from sync_data.php
async function syncRegistry() {
    const btn = document.getElementById('syncBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Syncing...';
    
    try {
        const response = await fetch('sync_data.php');
        const data = await response.json();
        
        await db.dogs.clear();
        await db.dogs.bulkAdd(data);
        
        const now = new Date().toLocaleString();
        localStorage.setItem('lastSync', now);
        document.getElementById('lastSyncDate').innerText = now;
        
        alert("Sync Successful! " + data.length + " pets saved for offline use.");
    } catch (err) {
        alert("Sync Failed! Ensure you are connected to the Office Wi-Fi.");
    } finally {
        btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Update Offline Data';
    }
}

// 3. SCANNER ENGINE
let html5QrCode;

function startScanner() {
    document.getElementById('scannerPlaceholder').classList.add('d-none');
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };
    html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
}

// 4. THE MAGIC MOMENT: Verification Logic
async function onScanSuccess(decodedText) {
    // Search the phone's memory
    const dog = await db.dogs.get(decodedText);
    const resultCard = document.getElementById('resultCard');
    
    if (dog) {
        if (navigator.vibrate) navigator.vibrate([100, 50, 100]); // Haptic feedback
        
        resultCard.classList.remove('d-none');
        resultCard.innerHTML = `
            <div class="card-body p-4 bg-white">
                <div class="text-center mb-3">
                    <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">${dog.tag_serial}</span>
                    <h2 class="fw-bold text-dark mb-0">${dog.dog_name}</h2>
                    <p class="text-muted small">${dog.breed} • ${dog.sex || 'N/A'}</p>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="bg-light p-1 rounded shadow-sm border">
                            <img src="uploads/dogs/${dog.dog_photo}" class="img-fluid rounded" style="height:140px; width:100%; object-fit:cover;">
                            <div class="text-center mt-1 small fw-bold text-muted" style="font-size:0.65rem;">DOG PHOTO</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="bg-light p-1 rounded shadow-sm border">
                            <img src="uploads/owners/${dog.owner_photo}" class="img-fluid rounded" style="height:140px; width:100%; object-fit:cover;">
                            <div class="text-center mt-1 small fw-bold text-muted" style="font-size:0.65rem;">OWNER PHOTO</div>
                        </div>
                    </div>
                </div>

                <div class="p-3 rounded-3 bg-light border mb-4">
                    <div class="mb-2">
                        <small class="text-muted d-block">Owner Name</small>
                        <span class="fw-bold h5 text-dark">${dog.fullname}</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Barangay</small>
                        <span class="fw-bold text-primary"><i class="fas fa-map-marker-alt me-1"></i> ${dog.barangay}</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="tel:${dog.contact_number}" class="btn btn-outline-primary py-2 fw-bold">
                        <i class="fas fa-phone me-2"></i> Call Owner
                    </a>
                    <button class="btn btn-danger py-3 fw-bold shadow-sm" style="border-radius:12px;" onclick="logCapture('${dog.tag_serial}')">
                        <i class="fas fa-truck-pickup me-2"></i> Log as Caught
                    </button>
                </div>
            </div>
        `;
        resultCard.scrollIntoView({ behavior: 'smooth' });
    } else {
        alert("ID " + decodedText + " not found. Please sync your data.");
    }
}

function logCapture(tag) {
    alert("Captured: " + tag + ". Logging GPS location...");
    // Future: Save this to a separate Dexie table 'pending_logs' for office sync-back
}
</script>

<?php include('includes/footer.php'); ?>