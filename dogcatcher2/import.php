<?php include('includes/header.php'); ?>
<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 text-center">
    <div class="card card-custom p-5 mx-auto" style="max-width: 600px;">
        <i class="fas fa-file-csv fa-4x text-primary mb-4"></i>
        <h3 class="fw-bold">Bulk Data Import</h3>
        <p class="text-muted">Upload a CSV file to register multiple owners and dogs at once.</p>
        
        <form action="process_import.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <input type="file" name="csv_file" class="form-control" accept=".csv" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">START IMPORT</button>
        </form>
        
        <div class="mt-4">
            <a href="sample.csv" class="text-decoration-none small text-muted"><i class="fas fa-download me-1"></i> Download CSV Template</a>
        </div>
    </div>
</main>