<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<link rel="manifest" href="manifest.json">

<style>
    /* Viewfinder Styling (Modern Slate) */
    #reader {
        width: 100%;
        min-height: 400px;
        background: #0f172a;
        border-radius: 1.5rem;
        overflow: hidden;
        border: 4px solid #fff;
        box-shadow: var(--soft-shadow);
    }
    #reader video { object-fit: cover !important; }

    /* Result Card - EXACT Design from owner.php */
    .result-container { 
        margin-top: 60px; /* Space for the overlapping avatar */
        display: none; 
    }
    
    .res-card {
        border-radius: 1.5rem !important;
        background: #fff;
        position: relative;
        padding-top: 50px; /* Offset for avatar */
        box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important;
    }

    .res-avatar {
        width: 95px;
        height: 95px;
        object-fit: cover;
        border: 4px solid #fff;
        border-radius: 50%;
        position: absolute;
        top: -48px;
        left: 50%;
        transform: translateX(-50%);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        background: #f1f5f9;
        z-index: 10;
    }

    .stat-pill {
        background: var(--basco-background);
        border-radius: 12px;
        padding: 10px 15px;
        border: 1px dashed #cbd5e1;
    }

    .badge-serial {
        background-color: var(--basco-success);
        color: #fff;
        font-weight: 800;
        font-family: monospace;
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 no-mobile-padding">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 no-print gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-camera-retro me-2 text-primary"></i>Field Scanner</h2>
            <p class="text-muted small mb-0">Local Registry Status: <span id="lastSyncDate" class="fw-bold text-primary">Never</span></p>
        </div>
        <div class="btn-group shadow-sm">
            <button id="syncBtn" class="btn btn-success fw-bold px-3" onclick="syncRegistry()">
                <i class="fas fa-sync me-2"></i>Sync Down
            </button>
            <button id="uploadBtn" class="btn btn-primary fw-bold px-3" onclick="uploadLogs()">
                <i class="fas fa-cloud-upload-alt me-2"></i>Push to Office
            </button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            
            <div class="card bg-dark shadow-lg border-0 mb-4" style="border-radius: 2rem; overflow: hidden;">
                <div id="reader"></div>
                
                <div id="placeholder" class="text-center py-5 text-white w-100 d-flex flex-column align-items-center justify-content-center">
                    <div class="p-4 bg-white bg-opacity-10 rounded-circle mb-3">
                        <i class="fas fa-qrcode fa-4x opacity-25"></i>
                    </div>
                    <button class="btn btn-warning px-5 py-3 fw-bold shadow-lg" onclick="startScanner()" style="font-size: 1.1rem;">
                        <i class="fas fa-video me-2"></i> ACTIVATE CAMERA
                    </button>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4 p-3" style="border-radius: 1.25rem;">
                <label class="small fw-bold text-muted mb-2 text-uppercase tracking-wider">Manual Lookup</label>
                <div class="input-group">
                    <input type="text" id="manualInput" class="form-control fw-bold font-monospace text-uppercase" placeholder="BAS-2026-XXXX">
                    <button class="btn btn-dark fw-bold px-4" onclick="manualSearch()">LOOKUP</button>
                </div>
            </div>

            <div id="resultContainer" class="result-container mb-5">
                <div class="card res-card text-center px-4 pb-4">
                    
                    <img id="resDogImg" src="" class="res-avatar">
                    
                    <div class="card-body p-0 d-flex flex-column">
                        <div class="d-flex justify-content-center mb-3">
                            <span class="badge badge-serial px-3 py-2 rounded-pill shadow-sm" id="resTag"></span>
                        </div>
                        
                        <h2 class="fw-bold text-dark mb-0" id="resName"></h2>
                        <p class="text-muted fw-bold mb-4" id="resBreed"></p>

                        <div class="stat-pill text-start mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom border-light border-opacity-10">
                                <small class="text-muted fw-bold">OWNER NAME</small>
                                <strong class="text-dark" id="resOwner"></strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted fw-bold">BARANGAY</small>
                                <strong class="text-dark" id="resBrgy"></strong>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-12">
                                <button class="btn btn-danger w-100 py-3 fw-bold shadow" id="logBtn" style="font-size: 1.1rem;">
                                    <i class="fas fa-exclamation-triangle me-2"></i> LOG AS CAUGHT
                                </button>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-light w-100 fw-bold text-muted py-2 mt-2" onclick="closeResult()">
                                    CANCEL SCAN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://unpkg.com/dexie/dist/dexie.js"></script>

<script>
// 1. OFFLINE STORAGE
const db = new Dexie("LGU_Basco_Registry");
db.version(1).stores({ dogs: "tag_serial, dog_name, breed, dog_photo, fullname, barangay, owner_photo", logs: "++id, tag_serial, log_timestamp" });
document.getElementById('lastSyncDate').innerText = localStorage.getItem('lastSync') || 'Never';

let html5QrCode;

// 2. CAMERA ENGINE
async function startScanner() {
    if (html5QrCode) { try { await html5QrCode.stop(); } catch(e) {} }

    document.getElementById('placeholder').classList.add('d-none');
    document.getElementById('resultContainer').style.display = 'none';
    
    html5QrCode = new Html5Qrcode("reader");
    const config = { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

    try {
        await html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);
    } catch (err) {
        try {
            await html5QrCode.start({ facingMode: "user" }, config, onScanSuccess);
        } catch (err2) {
            alert("Camera Access Error. Verify localhost/HTTPS and permissions.");
            document.getElementById('placeholder').classList.remove('d-none');
        }
    }
}

// 3. SCAN SUCCESS
async function onScanSuccess(tagID) {
    if (html5QrCode) { await html5QrCode.stop(); }
    document.getElementById('placeholder').classList.remove('d-none');
    
    const dog = await db.dogs.get(tagID);
    if (dog) {
        if (navigator.vibrate) navigator.vibrate([100, 50, 100]); 

        // Populate card
        document.getElementById('resTag').innerText = dog.tag_serial;
        document.getElementById('resName').innerText = dog.dog_name;
        document.getElementById('resBreed').innerText = dog.breed;
        document.getElementById('resDogImg').src = 'uploads/dogs/' + dog.dog_photo;
        document.getElementById('resOwner').innerText = dog.fullname;
        document.getElementById('resBrgy').innerText = dog.barangay;
        
        document.getElementById('logBtn').onclick = () => logCapture(dog.tag_serial);
        
        document.getElementById('resultContainer').style.display = 'block';
    } else {
        alert("Tag not found! Please Sync Registry.");
        document.getElementById('placeholder').classList.remove('d-none');
    }
}

function manualSearch() {
    const val = document.getElementById('manualInput').value.trim().toUpperCase();
    if(val !== "") onScanSuccess(val);
}

// 4. SYNC & PUSH
async function syncRegistry() {
    try {
        const response = await fetch('sync_data.php');
        const data = await response.json();
        await db.dogs.clear();
        await db.dogs.bulkAdd(data);
        localStorage.setItem('lastSync', new Date().toLocaleString());
        location.reload();
    } catch (err) { alert("Sync Failed!"); }
}

async function logCapture(tag) {
    await db.logs.add({ tag_serial: tag, log_timestamp: new Date().toISOString() });
    alert("Saved Offline!");
    closeResult();
}

async function uploadLogs() {
    const logs = await db.logs.toArray();
    if (logs.length === 0) { alert("No logs to upload."); return; }
    try {
        const res = await fetch('upload_logs.php', { method: 'POST', body: JSON.stringify(logs) });
        if (res.ok) { await db.logs.clear(); alert("Push Success!"); }
    } catch (e) { alert("Push Failed!"); }
}

function closeResult() { document.getElementById('resultContainer').style.display = 'none'; }
</script>

<?php include('includes/footer.php'); ?>