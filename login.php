<?php
session_start();
include('db_connect.php');

$error = "";

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'Catcher' ? 'scanner.php' : 'index.php'));
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM tbl_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'Catcher') {
            header("Location: scanner.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "Invalid username or password credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Login | LGU Basco</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background: linear-gradient(rgba(0, 74, 153, 0.85), rgba(0, 45, 93, 0.95)), url('assets/img/basco-bg.jpg'); 
            background-size: cover;
            background-position: center;
            height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }
        .login-card { 
            width: 100%; max-width: 420px; border-radius: 35px; border: none; overflow: hidden; 
            background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .login-header { background: #fff; padding: 40px 20px 20px 20px; text-align: center; }
        .lgu-logo { width: 120px; filter: drop-shadow(0 5px 15px rgba(0,0,0,0.1)); transition: 0.3s; }
        .lgu-logo:hover { transform: scale(1.05); }
        
        .form-control { border-radius: 15px; padding: 14px 15px; border: 1px solid #dee2e6; background: #fdfdfd; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(0, 74, 153, 0.1); border-color: #004a99; }
        
        .btn-primary { 
            background: #004a99; border: none; border-radius: 15px; padding: 15px; 
            font-weight: 800; letter-spacing: 1px; transition: 0.3s;
        }
        .btn-primary:hover { background: #003366; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,74,153,0.3); }
        
        .input-group-text { background: #f8f9fa; border-radius: 15px 0 0 15px !important; border-right: none; color: #004a99; }
        .form-control { border-radius: 0 15px 15px 0 !important; }
    </style>
</head>
<body>

    <div class="card login-card">
        <div class="login-header">
            <img src="LGU.png" alt="LGU Logo" class="lgu-logo mb-3">
            <h2 class="fw-bold text-dark mb-0">LGU BASCO</h2>
            <p class="text-muted small fw-bold text-uppercase" style="letter-spacing: 2px;">Pet Registry System</p>
        </div>
        
        <div class="card-body p-4 p-md-5 pt-0">
            <?php if($error): ?>
                <div class="alert alert-danger py-2 small text-center border-0 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['registered'])): ?>
                <div class="alert alert-success py-2 small text-center border-0 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> Account ready. Please login.
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted ms-2">USERNAME</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user-circle"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted ms-2">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-shield-alt"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-4 shadow-sm">
                    LOG IN TO SYSTEM <i class="fas fa-arrow-right ms-2"></i>
                </button>
                
                <div class="text-center">
                    <p class="text-muted small mb-1">Unauthorized access is monitored.</p>
                    <a href="signup.php" class="text-decoration-none small fw-bold text-primary">Request Staff Registration</a>
                </div>
            </form>
        </div>
        
        <div class="card-footer bg-light border-0 py-3 text-center">
            <p class="text-muted mb-0" style="font-size: 0.7rem;">
                <i class="fas fa-code me-1"></i> Developed for LGU Basco - 2026
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>