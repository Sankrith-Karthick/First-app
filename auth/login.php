<?php
session_start();
require_once '../config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // First check in users table (students, admin, parents)
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['login_from'] = 'users';

            // Check and redirect by role
           if (in_array($user['role'], ['student', 'admin', 'teacher'])) {
    header("Location: ../{$user['role']}/dashboard.php");
    exit();
} else {
    $error = "Unsupported user role.";
}

        } else {
            $error = "Invalid password.";
        }
    } else {
        // Then check teachers table
        $stmt = $conn->prepare("SELECT * FROM teachers WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $teacher = $res->fetch_assoc();
            if (password_verify($password, $teacher['password'])) {
                $_SESSION['user_id'] = $teacher['id'];
                $_SESSION['role'] = 'teacher';
                $_SESSION['name'] = $teacher['name'];
                $_SESSION['login_from'] = 'teachers';

                header("Location: ../teacher/dashboard.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .login-card {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 50%, #1e3c72 100%);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(52, 152, 219, 0.3);
            padding: 40px;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }
        
        .login-header h2 {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        
        .login-header p {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        
        .form-control {
            height: 50px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 12px 45px 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
        }
        
        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.25);
            background: #ffffff;
            color: #2c3e50;
        }
        
        .form-control::placeholder {
            color: #7f8c8d;
        }
        
        .btn-login {
            background: linear-gradient(45deg, #ffffff, #e8f4fd);
            border: none;
            height: 50px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2980b9;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
            background: linear-gradient(45deg, #f8fbff, #ffffff);
            color: #1e3c72;
        }
        
        .btn-register {
            background: linear-gradient(45deg, #ffffff, #e8f4fd);
            border: none;
            height: 50px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #2980b9;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
            background: linear-gradient(45deg, #f8fbff, #ffffff);
            color: #1e3c72;
        }
        
        .form-label {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: 500;
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1rem;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .input-group-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #3498db;
            z-index: 3;
        }
        
        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 1.5rem;
        }
        
        .button-group > * {
            flex: 1;
        }

        .divider {
            text-align: center;
            margin: 20px 0;
            position: relative;
            z-index: 2;
        }

        .divider span {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 50%, #1e3c72 100%);
            color: rgba(255, 255, 255, 0.8);
            padding: 0 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(255, 255, 255, 0.3);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 576px) {
            .login-card {
                padding: 30px 20px;
                margin: 20px;
            }
            
            .school-logo {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <div class="school-logo">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h2>
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Welcome Back
                    </h2>
                    <p>Sign in to access your account</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            Username
                        </label>
                        <div class="input-group-icon">
                            <input name="username" type="text" class="form-control" required placeholder="Enter your username" />
                            <i class="fas fa-user input-icon"></i>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <div class="input-group-icon">
                            <input name="password" type="password" class="form-control" required placeholder="Enter your password" />
                            <i class="fas fa-lock input-icon"></i>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn w-100 btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        Login
                    </button>
                </form>
                
                <div class="divider">
                    <span>Don't have an account?</span>
                </div>
                
                <div class="button-group">
                    <a href="register.php" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i>
                        Create Account
                    </a>
                    <a href="../index.php" class="btn-register">
                        <i class="fas fa-home me-2"></i>
                        Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>