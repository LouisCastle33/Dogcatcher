<?php 
include('db_connect.php'); 
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// SECURITY: Only Admins can see the Dashboard. Catchers go to Scanner.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'Catcher') {
    header("Location: scanner.php");
    exit();
}

// Logic for Dashboard Stats
try {
    $totalDogs = $pdo->query("SELECT COUNT(*) FROM tbl_dogs")->fetchColumn() ?: 0;
    $totalImpounded = $pdo->query("SELECT COUNT(*) FROM tbl_dogs WHERE status = 'Impounded'")->fetchColumn() ?: 0;
    $totalOwners = $pdo->query("SELECT COUNT(*) FROM tbl_owners")->fetchColumn() ?: 0;
    $caughtToday = $pdo->query("SELECT COUNT(*) FROM tbl_impound_logs WHERE DATE(log_timestamp) = CURDATE()")->fetchColumn() ?: 0;
} catch (Exception $e) {
    $totalDogs = $totalImpounded = $totalOwners = $caughtToday = 0;
}

include('includes/header.php'); 
?>

<style>
    /* Premium Dashboard Stats Styling */
    .stat-card {
        border-radius: 1.5rem !important;
        background: #fff;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover { transform: translateY(-5px); }

    .icon-shape {
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 1.25rem;
        font-size: 1.5rem;
        margin-right: 1.25rem;
    }

    /* Activity Feed Table Style */
    .table-container {
        background: #fff;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: var(--soft-shadow);
    }
    .table thead th {
        background-color: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        color: #64748b;
        border: none;
        padding: 1.25rem 1.5rem;
    }
    .table tbody td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        color: #1e293b;
        font-weight: 500;
    }
    .tag-badge {
        font-family: monospace;
        font-weight: 700;
        background: #e2e8f0;
        color: #475569;
        padding: 5px 12px;
        border-radius: 8px;
    }

    /* Modal Styling */
    .modal-content { border-radius: 2rem !important; overflow: hidden; }
    .photo-upload-zone {
        background: #f8fafc;
        border-radius: 1.5rem;
        padding: 1.5rem;
        border: 2px dashed #e2e8f0;
        transition: all 0.3s;
    }
    .photo-upload-zone:hover { border-color: var(--basco-primary); background: #f1f5f9; }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap align-items-center pt-3 pb-2 mb-4 border-bottom gap-3">
        <div>
            <h1 class="h2 fw-bold text-dark mb-0">Executive Dashboard</h1>
            <p class="text-muted small mb-0">LGU Basco Pet Registry & Field Management</p>
        </div>
        <button class="btn btn-primary shadow-sm px-4 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#addDogModal">
            <i class="fas fa-plus-circle me-2"></i>Register New Pet
        </button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="icon-shape bg-primary bg-opacity-10 text-primary"><i class="fas fa-dog"></i></div>
                <div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Total Dogs</small>
                    <h3 class="fw-bold mb-0"><?php echo $totalDogs; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="icon-shape bg-success bg-opacity-10 text-success"><i class="fas fa-users"></i></div>
                <div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Owners</small>
                    <h3 class="fw-bold mb-0"><?php echo $totalOwners; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="icon-shape bg-danger bg-opacity-10 text-danger"><i class="fas fa-warehouse"></i></div>
                <div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Impounded</small>
                    <h3 class="fw-bold mb-0"><?php echo $totalImpounded; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm">
                <div class="icon-shape bg-warning bg-opacity-10 text-warning"><i class="fas fa-bolt"></i></div>
                <div>
                    <small class="text-muted fw-bold text-uppercase" style="font-size: 0.65rem;">Caught Today</small>
                    <h3 class="fw-bold mb-0"><?php echo $caughtToday; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container shadow-sm border-0">
        <div class="p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-stream text-primary me-2"></i>Recent Field Activity</h5>
            <div class="d-flex gap-2 align-items-center">
                <select id="logEntrySelect" class="form-select form-select-sm border-0 bg-light shadow-none" style="width: 100px;">
                    <option value="10" selected>Show 10</option>
                    <option value="25">Show 25</option>
                    <option value="all">All</option>
                </select>
                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="logSearch" class="form-control bg-light border-0 shadow-none" placeholder="Search logs...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">TAG ID</th>
                        <th>DOG NAME</th>
                        <th>CATCHER</th>
                        <th>ACTION</th>
                        <th>TIMESTAMP</th>
                    </tr>
                </thead>
                <tbody id="logTableBody">
                    <?php 
                    $logs = $pdo->query("SELECT l.*, d.dog_name, d.tag_serial FROM tbl_impound_logs l JOIN tbl_dogs d ON l.dog_id = d.dog_id ORDER BY log_timestamp DESC LIMIT 100")->fetchAll();
                    if(empty($logs)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted">No field activity reported yet.</td></tr>
                    <?php else:
                        foreach($logs as $l): ?>
                        <tr class="log-row" data-search="<?php echo strtolower($l['tag_serial'].' '.$l['dog_name'].' '.$l['catcher_name']); ?>">
                            <td class="ps-4"><span class="tag-badge"><?php echo $l['tag_serial']; ?></span></td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($l['dog_name']); ?></td>
                            <td><?php echo htmlspecialchars($l['catcher_name']); ?></td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                    <?php echo $l['action_taken']; ?>
                                </span>
                            </td>
                            <td class="small text-muted"><i class="far fa-clock me-1"></i><?php echo date('M d, h:i A', strtotime($l['log_timestamp'])); ?></td>
                        </tr>
                        <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="p-4 d-flex justify-content-between align-items-center bg-light border-top flex-wrap gap-2">
            <small id="logShowingText" class="text-muted fw-semibold"></small>
            <nav><ul class="pagination pagination-sm mb-0 shadow-none" id="logPagination"></ul></nav>
        </div>
    </div>
</main>

<div class="modal fade" id="addDogModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header border-0 pb-0 pt-4 px-4">
        <h5 class="modal-title fw-bold text-dark"><i class="fas fa-id-card text-primary me-2"></i> Dual-Photo Registration</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="register_process.php" method="POST" enctype="multipart/form-data">
        <div class="modal-body p-4">
          <div class="row g-4">
            <div class="col-md-6 border-end px-4">
              <p class="text-primary small fw-bold text-uppercase mb-3">Owner Profile</p>
              <div class="mb-3 text-center">
                  <div class="photo-upload-zone mb-2 mx-auto" style="height: 120px; width: 120px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-user-plus text-muted fa-2x"></i>
                  </div>
                  <input type="file" name="owner_photo" class="form-control form-control-sm border-0 bg-light" accept="image/*" required>
              </div>
              <div class="mb-3">
                <label class="small fw-bold text-muted mb-1 text-uppercase">Full Name</label>
                <input type="text" name="fullname" class="form-control bg-light border-0 shadow-none" placeholder="e.g. Juan A. Dela Cruz" required>
              </div>
              <div class="mb-3">
                <label class="small fw-bold text-muted mb-1 text-uppercase">Barangay</label>
                <select name="barangay" class="form-select bg-light border-0 shadow-none" required>
                    <option value="" disabled selected>Select Barangay</option>
                    <option value="Kayvaluganan">Kayvaluganan</option>
                    <option value="Kayhuvokan">Kayhuvokan</option>
                    <option value="Kaychanarianan">Kaychanarianan</option>
                    <option value="San Joaquin">San Joaquin</option>
                    <option value="San Antonio">San Antonio</option>
                    <option value="Tukon">Tukon</option>
                    <option value="Chanarian">Chanarian</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="small fw-bold text-muted mb-1 text-uppercase">Contact Number</label>
                <input type="text" name="contact" class="form-control bg-light border-0 shadow-none" placeholder="0917-000-0000" required>
              </div>
            </div>

            <div class="col-md-6 px-4">
              <p class="text-primary small fw-bold text-uppercase mb-3">Dog Profile</p>
              <div class="mb-3 text-center">
                  <div class="photo-upload-zone mb-2 mx-auto" style="height: 120px; width: 120px; display: flex; align-items: center; justify-content: center;">
                      <i class="fas fa-paw text-muted fa-2x"></i>
                  </div>
                  <input type="file" name="dog_photo" class="form-control form-control-sm border-0 bg-light" accept="image/*" required>
              </div>
              <div class="mb-3">
                <label class="small fw-bold text-muted mb-1 text-uppercase">Dog's Name</label>
                <input type="text" name="dog_name" class="form-control bg-light border-0 shadow-none" placeholder="e.g. Bantay" required>
              </div>
              <div class="row g-2">
                  <div class="col-md-6 mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">Breed</label>
                    <input type="text" name="breed" class="form-control bg-light border-0 shadow-none" placeholder="Aspin">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">Sex</label>
                    <select name="sex" class="form-select bg-light border-0 shadow-none">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                  </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 p-4 pt-0 text-center d-block">
          <p class="text-muted small mb-3"><i class="fas fa-shield-alt text-success me-1"></i> This data will be encoded for LGU Basco official field scanner use.</p>
          <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="border-radius: 1rem;">COMPLETE REGISTRATION</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('logSearch');
    const entrySelect = document.getElementById('logEntrySelect');
    const rows = Array.from(document.querySelectorAll('.log-row'));
    const paginationContainer = document.getElementById('logPagination');
    const showingText = document.getElementById('logShowingText');

    let currentPage = 1;

    function updateTable() {
        if(rows.length === 0) return;

        const searchTerm = searchInput.value.toLowerCase();
        const entries = entrySelect.value;
        const entriesPerPage = entries === 'all' ? rows.length : parseInt(entries);

        const filteredRows = rows.filter(row => {
            return row.getAttribute('data-search').includes(searchTerm);
        });

        const totalPages = Math.ceil(filteredRows.length / entriesPerPage) || 1;
        if (currentPage > totalPages) currentPage = 1;

        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        rows.forEach(row => row.style.display = 'none');
        
        const rowsToShow = filteredRows.slice(startIndex, endIndex);
        rowsToShow.forEach(row => row.style.display = '');

        const endNum = Math.min(endIndex, filteredRows.length);
        const startNum = filteredRows.length === 0 ? 0 : startIndex + 1;
        showingText.innerHTML = `Showing <span class="fw-bold">${startNum}</span> to <span class="fw-bold">${endNum}</span> of <span class="fw-bold">${filteredRows.length}</span> entries`;

        let pagHTML = '';
        pagHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}"><a class="page-link shadow-none border-0 bg-transparent text-muted" href="#" data-page="${currentPage - 1}">Previous</a></li>`;
        
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                pagHTML += `<li class="page-item ${currentPage === i ? 'active' : ''}"><a class="page-link shadow-none border-0 ${currentPage === i ? 'bg-primary rounded-pill text-white' : 'bg-transparent text-muted'}" href="#" data-page="${i}">${i}</a></li>`;
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                pagHTML += `<li class="page-item disabled"><span class="page-link bg-transparent border-0">...</span></li>`;
            }
        }
        
        pagHTML += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}"><a class="page-link shadow-none border-0 bg-transparent text-muted" href="#" data-page="${currentPage + 1}">Next</a></li>`;

        paginationContainer.innerHTML = pagHTML;
    }

    if(searchInput && entrySelect) {
        searchInput.addEventListener('input', () => { currentPage = 1; updateTable(); });
        entrySelect.addEventListener('change', () => { currentPage = 1; updateTable(); });
        paginationContainer.addEventListener('click', (e) => {
            e.preventDefault();
            const target = e.target.closest('a');
            if(target) {
                const page = parseInt(target.getAttribute('data-page'));
                if(!isNaN(page) && page >= 1) { 
                    currentPage = page; 
                    updateTable(); 
                    window.scrollTo({ top: document.querySelector('.table-container').offsetTop - 50, behavior: 'smooth' });
                }
            }
        });
        updateTable();
    }
});
</script>

<?php include('includes/footer.php'); ?>