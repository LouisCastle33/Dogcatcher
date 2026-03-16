<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LGU Basco | Pet Registry System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --basco-blue: #004a99; /* Professional Government Blue */
            --accent-blue: #007bff;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { background: var(--basco-blue); min-height: 100vh; color: white; transition: 0.3s; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); font-weight: 500; padding: 12px 20px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background: rgba(255,255,255,0.1); border-left: 4px solid #fff; }
        .card-stat { border: none; border-radius: 12px; transition: transform 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card-stat:hover { transform: translateY(-5px); }
        .btn-primary { background-color: var(--basco-blue); border: none; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-0 shadow">
            <div class="p-4 text-center">
                <h5 class="fw-bold mb-0">LGU BASCO</h5>
                <small class="text-white-50">Pet Registry System</small>
            </div>
            <ul class="nav flex-column mt-3">
                <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="owners.php"><i class="fas fa-users me-2"></i> Owners</a></li>
                <li class="nav-item"><a class="nav-link" href="dogs.php"><i class="fas fa-dog me-2"></i> Dog Registry</a></li>
                <li class="nav-item"><a class="nav-link" href="import.php"><i class="fas fa-file-import me-2"></i> CSV Import</a></li>
                <li class="nav-item"><a class="nav-link" href="logs.php"><i class="fas fa-history me-2"></i> Field Logs</a></li>
                <hr class="mx-3 opacity-25">
                <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </nav>