<?php
// 1. TEMPLATE DOWNLOAD GENERATOR (Must be at the very top before any HTML)
if (isset($_GET['download_template'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="LGU_Basco_Template.csv"');
    
    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');
    
    // Output the exact column headers required
    fputcsv($output, array('Owner Name', 'Barangay', 'Contact Number', 'Dog Name', 'Breed', 'Sex'));
    
    // Output a sample row so they understand the format
    fputcsv($output, array('Juan Dela Cruz', 'Kayvaluganan', '0917-123-4567', 'Bantay', 'Aspin', 'Male'));
    
    fclose($output);
    exit();
}

include('db_connect.php'); 
include('includes/header.php'); 
?>

<style>
    /* Premium Import UI Styling */
    .upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 1.5rem;
        padding: 4rem 2rem;
        background-color: #f8fafc;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-zone:hover, .upload-zone.dragover {
        border-color: var(--basco-primary);
        background-color: #f1f5f9;
    }

    .file-icon {
        font-size: 4rem;
        color: #94a3b8;
        margin-bottom: 1.5rem;
        transition: color 0.3s ease;
    }
    
    .upload-zone:hover .file-icon { color: var(--basco-primary); }

    .instruction-card {
        border-radius: 1.25rem;
        background: #fff;
        border-left: 5px solid var(--basco-accent);
        box-shadow: var(--soft-shadow);
    }
</style>

<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 main-content">
    
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="fas fa-file-import text-primary me-2"></i>Bulk Import</h2>
            <p class="text-muted small mb-0">Upload existing barangay pet records via CSV.</p>
        </div>
        <a href="import.php?download_template=true" class="btn btn-warning fw-bold shadow-sm">
            <i class="fas fa-download me-2"></i>Download CSV Template
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            
            <div class="instruction-card p-4 mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-info-circle text-warning me-2"></i>How to import data:</h6>
                <ol class="text-muted small mb-0 ps-3" style="line-height: 1.8;">
                    <li>Click the <strong>Download CSV Template</strong> button above.</li>
                    <li>Open the file in Excel or Google Sheets.</li>
                    <li>Fill in the rows with the owners and dogs. <em>(Do not change the column headers!)</em></li>
                    <li>Save the file as a <strong>.CSV (Comma delimited)</strong> format.</li>
                    <li>Upload the saved file below.</li>
                </ol>
            </div>

            <div class="card border-0 shadow-lg" style="border-radius: 2rem;">
                <div class="card-body p-4 p-md-5 text-center">
                    
                    <form action="import_process.php" method="POST" enctype="multipart/form-data" id="importForm">
                        
                        <div class="upload-zone mb-4" onclick="document.getElementById('csv_file').click();">
                            <i class="fas fa-file-csv file-icon"></i>
                            <h4 class="fw-bold text-dark">Select CSV File</h4>
                            <p class="text-muted small mb-0" id="fileNameDisplay">Click here or drag and drop your .csv file</p>
                            
                            <input type="file" name="csv_file" id="csv_file" class="d-none" accept=".csv" required onchange="updateFileName(this)">
                        </div>

                        <button type="submit" name="import_btn" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" style="font-size: 1.1rem; border-radius: 1rem;">
                            <i class="fas fa-upload me-2"></i> START IMPORT PROCESS
                        </button>

                    </form>

                </div>
            </div>

        </div>
    </div>
</main>

<script>
    // Simple script to show the selected file name in the drag-and-drop zone
    function updateFileName(input) {
        const display = document.getElementById('fileNameDisplay');
        if (input.files && input.files.length > 0) {
            display.innerHTML = `<strong class="text-primary"><i class="fas fa-check-circle me-1"></i> ${input.files[0].name} selected.</strong> Ready to import.`;
        } else {
            display.innerText = "Click here or drag and drop your .csv file";
        }
    }
</script>

<?php include('includes/footer.php'); ?>