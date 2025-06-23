<?php
include('../config.php');
$message = '';

// Fetch available classes and sections from the database
$classes = mysqli_query($conn, "SELECT * FROM classes");
$sections = mysqli_query($conn, "SELECT * FROM sections");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $subject = $_POST['subject'];
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];
    $email = $_POST['email'];
    $emergency_contact = $_POST['emergency_contact'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format!";
    } 
    // Validate phone number (10-digit, India)
    elseif (!preg_match('/^[6-9]\d{9}$/', $emergency_contact)) {
        $message = "Invalid phone number!";
    } 
    else {
        $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
        if (mysqli_num_rows($checkUser) > 0) {
            $message = "Username already exists!";
        } else {
            $insertUser = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'teacher')");
            if ($insertUser) {
                $user_id = mysqli_insert_id($conn);
                $insertTeacher = mysqli_query($conn, "INSERT INTO teachers (user_id, full_name, subject, class_id, section_id, email, emergency_contact)
                                                      VALUES ('$user_id', '$full_name', '$subject', '$class_id', '$section_id', '$email', '$emergency_contact')");
                if ($insertTeacher) {
                    $message = "Teacher added successfully!";
                } else {
                    $message = "Failed to insert into teachers table.";
                }
            } else {
                $message = "Failed to insert into users table.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            --light-gradient: linear-gradient(135deg, #ffeef8 0%, #f0f4ff 100%);
            --dark-color: #2c3e50;
            --text-muted: #64748b;
            --border-radius: 16px;
            --shadow-light: 0 4px 25px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 8px 35px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 15px 45px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            line-height: 1.7;
            color: var(--dark-color);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Enhanced Navbar */
        .navbar {
            background: var(--primary-gradient) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: var(--shadow-medium);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar .btn {
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: 0.02em;
            margin-left: 0.5rem;
        }

        .navbar .btn-light {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--dark-color);
            box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
        }

        .navbar .btn-light:hover {
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }

        .navbar .btn-danger {
            background: var(--danger-gradient);
            border: none;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
        }

        .navbar .btn-danger:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        /* Container and Content */
        .container {
            max-width: 1000px;
        }

        .main-content {
            padding: 3rem 0;
        }

        h2 {
            font-weight: 800;
            font-size: 2.2rem;
            color: var(--dark-color);
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: -0.03em;
            position: relative;
            padding-bottom: 1rem;
        }

        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            height: 4px;
            width: 80px;
            background: var(--primary-gradient);
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        /* Form Container */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            padding: 3rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Layout */
        .form-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        /* Form Groups */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
        }

        .form-control,
        .form-select {
            border: 2px solid rgba(102, 126, 234, 0.1);
            border-radius: 12px;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Enhanced Button Styling */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.8rem 2rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
            border: none;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            color: var(--dark-color);
            border-left: 4px solid var(--primary-gradient);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1), rgba(56, 239, 125, 0.1));
            color: var(--dark-color);
            border-left: 4px solid var(--success-gradient);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 2rem 0;
            }

            .form-container {
                padding: 2rem 1.5rem;
                margin: 0 -15px 2rem -15px;
                border-radius: 0;
            }

            .form-section {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            h2 {
                font-size: 2rem;
                margin-bottom: 1.5rem;
            }

            .navbar .btn {
                margin-left: 0.25rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 1.5rem 1rem;
            }

            h2 {
                font-size: 1.8rem;
            }

            .btn-primary {
                width: 100%;
                margin-top: 1rem;
            }
        }

        /* Input Icons */
        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            z-index: 1;
        }

        .input-icon .form-control,
        .input-icon .form-select {
            padding-left: 3rem;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-gradient);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-chalkboard-teacher me-2"></i>Add Teacher</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container main-content">
    <h2><i class="fas fa-user-plus me-2"></i>Add New Teacher</h2>
    
    <?php if (!empty($message)) : ?>
        <div class="alert <?php echo (strpos($message, 'successfully') !== false) ? 'alert-success' : 'alert-info'; ?>">
            <i class="fas <?php echo (strpos($message, 'successfully') !== false) ? 'fa-check-circle' : 'fa-info-circle'; ?> me-2"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <div class="form-section">
                <div class="form-group">
                    <label><i class="fas fa-user me-1"></i>Full Name</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-book me-1"></i>Subject</label>
                    <div class="input-icon">
                        <i class="fas fa-book"></i>
                        <input type="text" name="subject" class="form-control" placeholder="Enter subject" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-chalkboard-teacher me-1"></i>Class</label>
                    <div class="input-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <select name="class_id" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php while ($row = mysqli_fetch_assoc($classes)) { ?>
                                <option value="<?= $row['id'] ?>"><?= $row['class_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-layer-group me-1"></i>Section</label>
                    <div class="input-icon">
                        <i class="fas fa-layer-group"></i>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select Section</option>
                            <?php while ($row = mysqli_fetch_assoc($sections)) { ?>
                                <option value="<?= $row['id'] ?>"><?= $row['section_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope me-1"></i>Email ID</label>
                    <div class="input-icon">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Enter email address" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone me-1"></i>Emergency Contact</label>
                    <div class="input-icon">
                        <i class="fas fa-phone"></i>
                        <input type="text" name="emergency_contact" class="form-control" placeholder="Enter 10-digit mobile number" maxlength="10" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-user-circle me-1"></i>Username</label>
                    <div class="input-icon">
                        <i class="fas fa-user-circle"></i>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock me-1"></i>Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Teacher
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>