<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// SECURITY: Redirect to login if not authenticated
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php' && basename($_SERVER['PHP_SELF']) != 'signup.php') {
    header("Location: login.php");
    exit();
}

// REDIRECTION: Keep Catchers out of Admin pages
$current_page = basename($_SERVER['PHP_SELF']);
$admin_only_pages = ['index.php', 'owners.php', 'dogs.php', 'import.php'];

if (isset($_SESSION['role']) && $_SESSION['role'] === 'Catcher' && in_array($current_page, $admin_only_pages)) {
    header("Location: scanner.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>LGU Basco | Pet Registry</title>
    
    <link rel="manifest" href="manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="#0c4a6e">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --basco-primary: #0c4a6e;      /* Deep Ocean Blue */
            --basco-primary-hover: #075985;
            --basco-accent: #eab308;       /* Golden Sun */
            --basco-success: #15803d;      /* Rolling Hills Green */
            --basco-background: #f8fafc;
            --basco-text: #1e293b;
            --soft-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--basco-background);
            color: var(--basco-text);
            padding-bottom: 0;
            overflow-x: hidden; /* Prevents horizontal scroll on mobile */
        }

        /* Sidebar Styling (Desktop) */
        .sidebar { background: var(--basco-primary); min-height: 100vh; color: white; transition: 0.3s; z-index: 1000; }
        .sidebar .nav-link { color: rgba(255,255,255,0.7); font-weight: 500; padding: 12px 20px; border-left: 4px solid transparent; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: var(--basco-accent); background: rgba(255,255,255,0.05); border-left: 4px solid var(--basco-accent); }

        /* Soft UI Card Design */
        .card { border: none !important; border-radius: 1.5rem !important; box-shadow: var(--soft-shadow) !important; transition: transform 0.2s ease; }
        .btn { border-radius: 12px; font-weight: 600; padding: 0.6rem 1.25rem; }
        .btn-primary { background-color: var(--basco-primary); border: none; }
        .btn-warning { background-color: var(--basco-accent); color: #000; border: none; }

        .form-control, .form-select { border-radius: 12px; border: 1px solid #e2e8f0; padding: 0.6rem 1rem; background-color: #f8fafc; }
        .form-control:focus { border-color: var(--basco-primary); box-shadow: 0 0 0 4px rgba(12, 74, 110, 0.1); }

        /* Mobile Top Bar Base (Hidden on Desktop) */
        .mobile-top-bar { 
            display: none; 
        }

        /* Mobile Responsive Logic */
        @media (max-width: 768px) {
            .sidebar { display: none !important; } /* Hide sidebar on mobile */
            body { padding-bottom: 90px; } /* Create space for the bottom nav */
            
            /* Show Mobile Top Bar */
            .mobile-top-bar { 
                display: flex !important; 
                background: var(--basco-primary); 
                color: white; 
                padding: 12px 20px; 
                position: sticky; 
                top: 0; 
                z-index: 1040;
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }
            
            /* Fix padding for main content on mobile */
            main {
                padding-left: 15px !important;
                padding-right: 15px !important;
            }
        }
    </style>
</head>
<body>

<div class="mobile-top-bar justify-content-between align-items-center w-100">
    <div class="d-flex align-items-center">
        <img src="LGU.png" alt="Logo" style="width: 32px;" class="me-2" onerror="this.style.display='none'">
        <div style="line-height: 1.1;">
            <div class="mb-0 fw-bold" style="font-size: 0.95rem;">LGU BASCO</div>
            <div class="fw-bold" style="color: var(--basco-accent); font-size: 0.6rem; letter-spacing: 1px;">BATANES</div>
        </div>
    </div>
    <a href="logout.php" class="text-white opacity-75"><i class="fas fa-sign-out-alt fa-lg"></i></a>
</div>

<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block sidebar p-0 shadow no-print collapse" id="sidebarMenu">
            <div class="p-4 text-center border-bottom border-white border-opacity-10">
                <img src="LGU.png" alt="Logo" style="width: 65px;" class="mb-2" onerror="this.style.display='none'">
                <h5 class="fw-bold mb-0 text-white">LGU BASCO</h5>
                <div class="fw-bold" style="color: var(--basco-accent); font-size: 0.7rem; letter-spacing: 2px;">BATANES</div>
                <div class="mt-3">
                    <span class="badge px-3 py-2 text-dark w-100" style="background-color: var(--basco-accent); border-radius: 8px;">
                        <?php echo htmlspecialchars($_SESSION['role'] ?? 'User'); ?>
                    </span>
                </div>
            </div>
            
            <ul class="nav flex-column mt-3 px-2">
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] !== 'Catcher'): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-chart-pie me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'owners.php') ? 'active' : ''; ?>" href="owners.php">
                        <i class="fas fa-users me-2"></i> Owners
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'dogs.php') ? 'active' : ''; ?>" href="dogs.php">
                        <i class="fas fa-dog me-2"></i> Registry & Tags
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo ($current_page == 'scanner.php') ? 'active' : ''; ?>" href="scanner.php">
                        <i class="fas fa-camera me-2"></i> Field Scanner
                    </a>
                </li>
                
                <li class="nav-item mt-5">
                    <a class="nav-link text-danger fw-bold" href="logout.php">
                        <i class="fas fa-power-off me-2"></i> Logout
                    </a>
                </li>
                <li class="nav-item">
    <a class="nav-link <?php echo ($current_page == 'import.php') ? 'active' : ''; ?>" href="import.php">
        <i class="fas fa-file-import me-2"></i> Import Data
    </a>
</li>
            </ul>
        </nav>