<?php 
require '../vendor/autoload.php'; 
include('../config.php');  

use PhpOffice\PhpSpreadsheet\IOFactory;  

$message = '';  

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {     
    $file = $_FILES['excel_file']['tmp_name'];      

    $spreadsheet = IOFactory::load($file);     
    $sheet = $spreadsheet->getActiveSheet();     
    $rows = $sheet->toArray();      

    $errors = [];     
    $successCount = 0;      

    foreach ($rows as $index => $row) {         
        if ($index == 0) continue; // Skip header          

        list($username, $passwordRaw, $full_name, $subject, $class_id, $section_id, $email, $emergency_contact) = $row;          

        $password = password_hash($passwordRaw, PASSWORD_DEFAULT);          

        // Validate email         
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {             
            $errors[] = "Row " . ($index + 1) . ": Invalid email.";             
            continue;         
        }          

        // Validate phone         
        if (!preg_match('/^[6-9]\d{9}$/', $emergency_contact)) {             
            $errors[] = "Row " . ($index + 1) . ": Invalid emergency contact.";             
            continue;         
        }          

        // Check duplicate username         
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");         
        if (mysqli_num_rows($check) > 0) {             
            $errors[] = "Row " . ($index + 1) . ": Username already exists.";             
            continue;         
        }          

        // Insert into users         
        $insertUser = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'teacher')");         
        if ($insertUser) {             
            $user_id = mysqli_insert_id($conn);              

            // Insert into teachers             
            $insertTeacher = mysqli_query($conn, "INSERT INTO teachers (user_id, full_name, subject, class_id, section_id, email, emergency_contact)                                                   VALUES ('$user_id', '$full_name', '$subject', '$class_id', '$section_id', '$email', '$emergency_contact')");             
            if ($insertTeacher) {                 
                $successCount++;             
            } else {                 
                $errors[] = "Row " . ($index + 1) . ": Failed to insert teacher.";             
            }         
        }    
    }      

    $message = "$successCount teachers uploaded successfully.";     
    if (!empty($errors)) {         
        $message .= "<br><strong>Errors:</strong><ul><li>" . implode("</li><li>", $errors) . "</li></ul>";     
    } 
} 
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Teachers via Excel</title>     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
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
            max-width: 900px;
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

        /* File Upload Styling */
        .file-upload-container {
            position: relative;
            margin-bottom: 2rem;
        }

        .file-upload-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
        }

        .form-control[type="file"] {
            border: 2px dashed rgba(102, 126, 234, 0.3);
            border-radius: 12px;
            padding: 2rem;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(5px);
            text-align: center;
            cursor: pointer;
        }

        .form-control[type="file"]:hover {
            border-color: rgba(102, 126, 234, 0.5);
            background: rgba(255, 255, 255, 1);
            transform: translateY(-2px);
        }

        .form-control[type="file"]:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.7);
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
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
            padding: 1.5rem;
            font-weight: 500;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: var(--shadow-light);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(29, 78, 216, 0.1));
            color: var(--dark-color);
            border-left: 4px solid var(--info-gradient);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1), rgba(56, 239, 125, 0.1));
            color: var(--dark-color);
            border-left: 4px solid var(--success-gradient);
        }

        /* Info Section */
        .info-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-medium);
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.8s ease forwards;
        }

        .info-container h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-container li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .info-container li:last-child {
            border-bottom: none;
        }

        .info-container li i {
            color: var(--primary-gradient);
            width: 20px;
        }

        /* Divider */
        .custom-divider {
            height: 2px;
            background: var(--primary-gradient);
            border: none;
            border-radius: 1px;
            margin: 3rem 0;
            opacity: 0.3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 2rem 0;
            }

            .form-container,
            .info-container {
                padding: 2rem 1.5rem;
                margin: 0 -15px 2rem -15px;
                border-radius: 0;
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
            .form-container,
            .info-container {
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
        <a class="navbar-brand" href="#"><i class="fas fa-file-excel me-2"></i>Upload Teachers</a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
        </div>
    </div>
</nav>

<div class="container main-content">     
    <h2><i class="fas fa-upload me-2"></i>Upload Teachers via Excel</h2>     
    
    <?php if (!empty($message)) : ?>
        <div class="alert <?php echo ($successCount > 0) ? 'alert-success' : 'alert-info'; ?>">
            <i class="fas <?php echo ($successCount > 0) ? 'fa-check-circle' : 'fa-info-circle'; ?> me-2"></i>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data">         
            <div class="file-upload-container">             
                <label class="file-upload-label">
                    <i class="fas fa-file-excel me-1"></i>Select Excel File (.xlsx)
                </label>             
                <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>         
            </div>         
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-2"></i>Upload Teachers
                </button>     
            </div>
        </form>
    </div>

    <hr class="custom-divider">
    
    <div class="info-container">
        <h4><i class="fas fa-info-circle me-2"></i>Excel Format Requirements</h4>
        <p class="text-muted mb-3">Your Excel file should contain the following columns in this exact order:</p>
        <ul>         
            <li><i class="fas fa-user-circle"></i><strong>username</strong> - Unique username for login</li>         
            <li><i class="fas fa-lock"></i><strong>password</strong> - Plain text password (will be encrypted)</li>         
            <li><i class="fas fa-user"></i><strong>full_name</strong> - Teacher's full name</li>         
            <li><i class="fas fa-book"></i><strong>subject</strong> - Subject taught by teacher</li>         
            <li><i class="fas fa-chalkboard-teacher"></i><strong>class_id</strong> - Numeric ID of the class</li>         
            <li><i class="fas fa-layer-group"></i><strong>section_id</strong> - Numeric ID of the section</li>         
            <li><i class="fas fa-envelope"></i><strong>email</strong> - Valid email address</li>         
            <li><i class="fas fa-phone"></i><strong>emergency_contact</strong> - 10-digit mobile number (starting with 6-9)</li>     
        </ul>
        
        <div class="mt-3 p-3" style="background: rgba(59, 130, 246, 0.05); border-radius: 8px; border-left: 3px solid #3b82f6;">
            <small class="text-muted">
                <i class="fas fa-lightbulb me-1"></i>
                <strong>Tips:</strong> Make sure the first row contains headers, ensure all required fields are filled
            </small>
        </div>
    </div>
</div> 

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>