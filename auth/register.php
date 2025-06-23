<?php 
session_start(); 
require_once '../config.php';  

$error = ''; 
$success = '';  

if ($_SERVER['REQUEST_METHOD'] == 'POST') {     
    $username = trim($_POST['username']);     
    $email = trim($_POST['email']);     
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);     
    $role = $_POST['role'];      

    // Check if username already exists     
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");     
    $stmt->bind_param("s", $username);     
    $stmt->execute();     
    $stmt->store_result();      

    if ($stmt->num_rows > 0) {         
        $error = "Username already taken.";     
    } else {         
        // Insert new user         
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");         
        $stmt->bind_param("ssss", $username, $email, $password, $role);          

        if ($stmt->execute()) {             
            $success = "User registered successfully.";         
        } else {             
            $error = "Error: " . $stmt->error;         
        }     
    } 
} 
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - School System</title>     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .register-container {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 50%, #1e3c72 100%);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(52, 152, 219, 0.3);
            padding: 40px;
            margin-top: 50px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }

        .register-title {
            color: #ffffff;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            z-index: 2;
        }

        .register-title::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: linear-gradient(45deg, #ffffff, #e8f4fd);
            margin: 10px auto;
            border-radius: 2px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .form-control {
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
            background: #ffffff;
            color: #2c3e50;
        }

        .form-label {
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
            z-index: 2;
            position: relative;
        }

        .btn-register {
            background: linear-gradient(45deg, #ffffff, #e8f4fd);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2980b9;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
            background: linear-gradient(45deg, #f8fbff, #ffffff);
            color: #1e3c72;
        }

        .btn-home {
            background: linear-gradient(45deg, #ffffff, #e8f4fd);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            color: #2980b9;
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
        }

        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 255, 255, 0.4);
            background: linear-gradient(45deg, #f8fbff, #ffffff);
            color: #1e3c72;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .button-group > * {
            flex: 1;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .alert-success {
            background: linear-gradient(45deg, #27ae60, #2ecc71);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .role-select {
            background: rgba(255, 255, 255, 0.9);
            color: #2c3e50;
        }

        .school-icon {
            position: absolute;
            top: -10px;
            right: 20px;
            font-size: 60px;
            color: rgba(255, 255, 255, 0.1);
            z-index: 1;
        }

        @media (max-width: 768px) {
            .register-container {
                margin: 20px;
                padding: 30px 20px;
            }
            
            .button-group {
                flex-direction: column;
            }
        }

        .input-group-icon {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #3498db;
            z-index: 3;
        }

        .form-control.with-icon {
            padding-left: 45px;
        }
    </style>
</head> 
<body>     
    <div class="container">         
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="register-container position-relative">
                    <i class="fas fa-graduation-cap school-icon"></i>
                    <h2 class="register-title">
                        <i class="fas fa-user-plus"></i>
                        Register User
                    </h2>
                    
                    <form method="POST">             
                        <?php if ($error): ?>                 
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo $error; ?>
                            </div>             
                        <?php endif; ?>             
                        <?php if ($success): ?>                 
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo $success; ?>
                            </div>             
                        <?php endif; ?>             
                        
                        <div class="mb-3">                 
                            <label class="form-label">
                                <i class="fas fa-user"></i>
                                Username
                            </label>
                            <div class="input-group-icon">
                                <i class="fas fa-user input-icon"></i>
                                <input name="username" type="text" class="form-control with-icon" required placeholder="Enter your username" />
                            </div>             
                        </div>             
                        
                        <div class="mb-3">                 
                            <label class="form-label">
                                <i class="fas fa-envelope"></i>
                                Email
                            </label>
                            <div class="input-group-icon">
                                <i class="fas fa-envelope input-icon"></i>
                                <input name="email" type="email" class="form-control with-icon" required placeholder="Enter your email" />
                            </div>             
                        </div>             
                        
                        <div class="mb-3">                 
                            <label class="form-label">
                                <i class="fas fa-lock"></i>
                                Password
                            </label>
                            <div class="input-group-icon">
                                <i class="fas fa-lock input-icon"></i>
                                <input name="password" type="password" class="form-control with-icon" required placeholder="Enter your password" />
                            </div>             
                        </div>             
                        
                        <div class="mb-4">                 
                            <label class="form-label">
                                <i class="fas fa-users"></i>
                                Role
                            </label>
                            <select name="role" class="form-control role-select" required>                     
                                <option value="">Select your role</option>
                                <option value="student">👨‍🎓 Student</option>                     
                                <option value="teacher">👨‍🏫 Teacher</option>                     
                                <option value="admin">👨‍💼 Admin</option>                 
                            </select>             
                        </div>             
                        
                        <div class="button-group">
                            <button type="submit" class="btn btn-register">
                                <i class="fas fa-user-plus me-2"></i>
                                Register
                            </button>
                            <a href="../index.php" class="btn-home">
                                <i class="fas fa-home me-2"></i>
                                Home
                            </a>
                        </div>         
                    </form>     
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>