<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<link rel="manifest" href="manifest.json">

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-camera-retro me-2 text-primary"></i>Field Scanner</h2>
            <p class="text-muted small">Registry last updated: <span id="lastSyncDate">Never</span></p>
        </div>
        <div class="btn-group shadow-sm">
            <button id="syncBtn" class="btn btn-success fw-bold" onclick="syncRegistry()"><i class="fas fa-sync-alt me-1"></i> Sync Down</button>
            <button id="uploadBtn" class="btn btn-primary fw-bold" onclick="uploadLogs()"><i class="fas fa-cloud-upload-alt me-1"></i> Push to Office</button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-lg overflow-hidden mb-4" style="border-radius: 25px; background: #000;">
                <div id="reader" style="width: 100%;"></div>
                <div id="scannerPlaceholder" class="text-center py-5 text-white">
                    <i class="fas fa-qrcode fa-4x mb-3 opacity-50"></i>
                    <p>Camera is inactive</p>
                    <button class="btn btn-primary px-5 py-2 fw-bold" onclick="startScanner()" style="border-radius: 12px;">Open Scanner</button>
                </div>
            </div>
            <div id="resultCard" class="card border-0 shadow-lg d-none" style="border-radius: 25px; overflow: hidden;"></div>
        </div>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://unpkg.com/dexie/dist/dexie.js"></script>

<script>
// 1. OFFLINE DATABASE (Dexie)
const db = new Dexie("LGU_Basco_Registry");
db.version(1).stores({
    dogs: "tag_serial, dog_name, breed, sex, dog_photo, fullname, barangay, contact_number, owner_photo",
    logs: "++id, tag_serial, log_timestamp"
});

// 2. REGISTER SERVICE WORKER (Makes it an Offline App)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('sw.js');
}

document.getElementById('lastSyncDate').innerText = localStorage.getItem('lastSync') || 'Never';

// 3. DOWNLOAD DATA (Registry -> Phone)
async function syncRegistry() {
    const btn = document.getElementById('syncBtn');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';
    try {
        const response = await fetch('sync_data.php');
        const data = await response.json();
        await db.dogs.clear();
        await db.dogs.bulkAdd(data);
        const now = new Date().toLocaleString();
        localStorage.setItem('lastSync', now);
        document.getElementById('lastSyncDate').innerText = now;
        alert("Success! " + data.length + " pets saved for offline use.");
    } catch (err) { alert("Sync Down Failed! Ensure you have internet for this part."); }
    finally { btn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Sync Down'; }
}

// 4. UPLOAD LOGS (Phone -> Laptop)
async function uploadLogs() {
    const allLogs = await db.logs.toArray();
    if (allLogs.length === 0) { alert("No logs to upload."); return; }
    try {
        const response = await fetch('upload_logs.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(allLogs)
        });
        if (response.ok) {
            await db.logs.clear();
            alert("Upload Success! Dashboard Updated.");
        }
    } catch (err) { alert("Upload Failed! Is the office Wi-Fi on?"); }
}

// 5. SCANNER & DISPLAY
let html5QrCode;
function startScanner() {
    document.getElementById('scannerPlaceholder').classList.add('d-none');
    html5QrCode = new Html5Qrcode("reader");
    html5QrCode.start({ facingMode: "environment" }, { fps: 15, qrbox: 250 }, onScanSuccess);
}

async function onScanSuccess(decodedText) {
    const dog = await db.dogs.get(decodedText);
    const resultCard = document.getElementById('resultCard');
    if (dog) {
        if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
        resultCard.classList.remove('d-none');
        resultCard.innerHTML = `
            <div class="card-body p-4 bg-white text-center">
                <span class="badge bg-primary px-3 py-2 rounded-pill mb-2">${dog.tag_serial}</span>
                <h2 class="fw-bold text-dark">${dog.dog_name}</h2>
                <div class="row g-2 mb-3">
                    <div class="col-6"><img src="uploads/dogs/${dog.dog_photo}" class="img-fluid rounded border shadow-sm" style="height:130px;width:100%;object-fit:cover;"></div>
                    <div class="col-6"><img src="uploads/owners/${dog.owner_photo}" class="img-fluid rounded border shadow-sm" style="height:130px;width:100%;object-fit:cover;"></div>
                </div>
                <div class="p-3 bg-light rounded text-start border mb-3">
                    <small class="text-muted d-block">Owner: <strong>${dog.fullname}</strong></small>
                    <small class="text-muted d-block">Barangay: <strong>${dog.barangay}</strong></small>
                </div>
                <button class="btn btn-danger w-100 py-3 fw-bold" onclick="logCapture('${dog.tag_serial}')">LOG AS CAUGHT</button>
            </div>`;
    }
}

async function logCapture(tag) {
    await db.logs.add({ tag_serial: tag, log_timestamp: new Date().toISOString() });
    alert("Saved Offline! Logged in phone memory.");
    document.getElementById('resultCard').classList.add('d-none');
}
</script>

<?php include('includes/footer.php'); ?>