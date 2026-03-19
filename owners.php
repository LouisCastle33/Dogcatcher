<?php 
include('db_connect.php'); 
include('includes/header.php'); 
?>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-users me-2 text-primary"></i>Owners Registry</h2>
            <p class="text-muted small mb-0">Manage LGU Basco residents and their registered pet counts.</p>
        </div>
        <div class="d-flex gap-2 w-100" style="max-width: 450px;">
            <select id="entrySelect" class="form-select shadow-sm" style="width: 130px; border-radius: 10px;">
                <option value="6">Show 6</option>
                <option value="12" selected>Show 12</option>
                <option value="24">Show 24</option>
                <option value="all">Show All</option>
            </select>
            <input type="text" id="ownerSearch" class="form-control shadow-sm" placeholder="Search name or barangay..." style="border-radius: 10px;">
        </div>
    </div>

    <div class="row g-4" id="ownerContainer">
        <?php
        $owners = $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM tbl_dogs WHERE owner_id = o.owner_id) as dog_count FROM tbl_owners o ORDER BY o.fullname ASC")->fetchAll();
        
        foreach($owners as $row): 
            $photo_filename = !empty($row['owner_photo']) ? $row['owner_photo'] : 'default_owner.png';
            $photo_path = "uploads/owners/" . $photo_filename;
        ?>
        <div class="col-md-6 col-lg-4 owner-card" data-name="<?php echo strtolower($row['fullname'] . ' ' . $row['barangay']); ?>">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 15px;">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?php echo $photo_path; ?>" 
                         class="rounded-circle me-3 border border-2 border-primary border-opacity-25" 
                         style="width: 65px; height: 65px; object-fit: cover;"
                         onerror="this.src='uploads/owners/default_owner.png';">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($row['fullname']); ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($row['barangay']); ?></small>
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

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
        <span id="showingText" class="text-muted small mb-3 mb-md-0">Showing 0 to 0 of 0 entries</span>
        <nav>
            <ul class="pagination pagination-sm mb-0 shadow-sm" id="paginationControls">
                </ul>
        </nav>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.getElementById('ownerSearch');
    const entrySelect = document.getElementById('entrySelect');
    const cards = Array.from(document.querySelectorAll('.owner-card'));
    const paginationContainer = document.getElementById('paginationControls');
    const showingText = document.getElementById('showingText');

    let currentPage = 1;

    function updateView() {
        const searchTerm = searchInput.value.toLowerCase();
        const entries = entrySelect.value;
        const entriesPerPage = entries === 'all' ? cards.length : parseInt(entries);

        // Filter cards based on search bar
        const filteredCards = cards.filter(card => {
            return card.getAttribute('data-name').includes(searchTerm);
        });

        // Calculate pages
        const totalPages = Math.ceil(filteredCards.length / entriesPerPage) || 1;
        if (currentPage > totalPages) currentPage = 1;

        const startIndex = (currentPage - 1) * entriesPerPage;
        const endIndex = startIndex + entriesPerPage;

        // Hide all cards first, then show only the ones for the current page
        cards.forEach(card => card.style.display = 'none');
        
        const cardsToShow = filteredCards.slice(startIndex, endIndex);
        cardsToShow.forEach(card => card.style.display = '');

        // Update the "Showing X to Y of Z entries" text
        const endNum = Math.min(endIndex, filteredCards.length);
        const startNum = filteredCards.length === 0 ? 0 : startIndex + 1;
        showingText.innerHTML = `Showing <span class="fw-bold">${startNum}</span> to <span class="fw-bold">${endNum}</span> of <span class="fw-bold">${filteredCards.length}</span> entries`;

        // Build Pagination Buttons
        let pagHTML = '';
        
        pagHTML += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                      <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
                    </li>`;
                    
        for (let i = 1; i <= totalPages; i++) {
            pagHTML += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                          <a class="page-link" href="#" data-page="${i}">${i}</a>
                        </li>`;
        }

        pagHTML += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
                      <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
                    </li>`;

        paginationContainer.innerHTML = pagHTML;
    }

    // Listeners for typing or changing dropdown
    searchInput.addEventListener('input', () => { currentPage = 1; updateView(); });
    entrySelect.addEventListener('change', () => { currentPage = 1; updateView(); });

    // Listener for clicking page numbers
    paginationContainer.addEventListener('click', (e) => {
        e.preventDefault();
        if(e.target.tagName === 'A') {
            const page = parseInt(e.target.getAttribute('data-page'));
            if(!isNaN(page)) {
                currentPage = page;
                updateView();
            }
        }
    });

    // Run once on page load
    updateView();
});
</script>

<?php include('includes/footer.php'); ?>