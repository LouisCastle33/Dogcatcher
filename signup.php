<?php
// 1. DATABASE CONNECTION & SYSTEM LOGIC
include('db_connect.php');

// Enable error reporting for debugging on phone (CP)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$msg = "";
$msg_type = "";

// 2. PROCESSING THE REGISTRATION
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];

    if (empty($fullname) || empty($username) || empty($password)) {
        $msg = "Please complete all fields.";
        $msg_type = "danger";
    } else {
        try {
            // Check for existing username
            $check = $pdo->prepare("SELECT username FROM tbl_users WHERE username = ?");
            $check->execute([$username]);
            
            if ($check->rowCount() > 0) {
                $msg = "Username already exists in the LGU database.";
                $msg_type = "warning";
            } else {
                // Secure encryption
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO tbl_users (fullname, username, password, role) VALUES (?, ?, ?, ?)");
                
                if ($stmt->execute([$fullname, $username, $hashed_password, $role])) {
                    // Success: Redirect to login with a success flag
                    header("Location: login.php?registered=success");
                    exit();
                }
            }
        } catch (PDOException $e) {
            $msg = "System Error: " . $e->getMessage();
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Signup | LGU Basco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(rgba(0, 74, 153, 0.9), rgba(0, 45, 93, 0.9)), url('assets/img/batanes-bg.jpg'); 
            background-size: cover;
            background-attachment: fixed;
            min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
        }
        .signup-card { 
            width: 100%; max-width: 480px; border-radius: 30px; border: none; overflow: hidden; 
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }
        .header-lgu { background: #004a99; color: white; padding: 35px 20px; text-align: center; }
        .lgu-seal { width: 100px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3)); }
        .btn-primary { background: #004a99; border: none; border-radius: 15px; padding: 14px; font-weight: 800; letter-spacing: 1px; }
        .form-control, .form-select { border-radius: 12px; padding: 12px; border: 1px solid #dee2e6; background: #fdfdfd; }
        .form-control:focus { box-shadow: 0 0 0 3px rgba(0, 74, 153, 0.15); border-color: #004a99; }
    </style>
</head>
<body>

    <div class="card signup-card">
        <div class="header-lgu">
            <img src="LGU.png" alt="LGU Logo" class="lgu-seal mb-3">
            <h3 class="fw-bold mb-0">STAFF REGISTRATION</h3>
            <p class="small mb-0 opacity-75 fw-bold text-uppercase" style="letter-spacing: 1px;">Basco Pet Registry System</p>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <?php if($msg): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show small mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">FULL NAME</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user text-primary"></i></span>
                        <input type="text" name="fullname" class="form-control border-start-0" placeholder="e.g. Juan A. Dela Cruz" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">SYSTEM USERNAME</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-at text-primary"></i></span>
                        <input type="text" name="username" class="form-control border-start-0" placeholder="e.g. j.delacruz" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">SECURE PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-key text-primary"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">DESIGNATED ROLE</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-user-tag text-primary"></i></span>
                        <select name="role" class="form-select border-start-0" required>
                            <option value="" selected disabled>Assign Role...</option>
                            <option value="Clerk">Office Clerk</option>
                            <option value="Catcher">Field Catcher</option>
                            <option value="Admin">System Admin</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 shadow mb-3">
                    REGISTER STAFF ACCOUNT
                </button>
                
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none small text-muted">
                        Already have an account? <span class="text-primary fw-bold">Sign In here</span>
                    </a>
                </div>
            </form>
        </div>
        
        <div class="card-footer bg-light border-0 py-3 text-center">
            <p class="text-muted mb-0" style="font-size: 0.75rem;">
                <i class="fas fa-lock me-1"></i> Authorized Personnel Only - LGU Basco IT Unit
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>