<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Get teacher ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_teachers.php");
    exit();
}

$teacher_id = mysqli_real_escape_string($conn, $_GET['id']);

// Handle form submission
if ($_POST) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_id = mysqli_real_escape_string($conn, $_POST['section_id']);
    
    // Update password only if provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE teachers SET 
                full_name = '$full_name', 
                email = '$email', 
                Username = '$username', 
                password = '$password', 
                subject = '$subject', 
                emergency_contact = '$emergency_contact',
                class_id = '$class_id',
                section_id = '$section_id'
                WHERE id = '$teacher_id'";
    } else {
        $sql = "UPDATE teachers SET 
                full_name = '$full_name', 
                email = '$email', 
                Username = '$username', 
                subject = '$subject', 
                emergency_contact = '$emergency_contact',
                class_id = '$class_id',
                section_id = '$section_id'
                WHERE id = '$teacher_id'";
    }
    
    if (mysqli_query($conn, $sql)) {
        $success_message = "Teacher updated successfully!";
    } else {
        $error_message = "Error updating teacher: " . mysqli_error($conn);
    }
}

// Fetch teacher data
$sql = "SELECT * FROM teachers WHERE id = '$teacher_id'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: manage_teachers.php");
    exit();
}

$teacher = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Teacher</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .container {
            max-width: 800px;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border-left: 5px solid var(--secondary-color);
        }

        .page-header h2 {
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }

        .edit-form {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
            padding: 0.75rem 2rem;
            transition: all 0.3s ease;
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }

        .btn-success:hover {
            background-color: #229954;
            border-color: #229954;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #545b62;
            transform: translateY(-2px);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem 1.5rem;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .password-note {
            font-size: 0.875rem;
            color: #6c757d;
            font-style: italic;
        }

        .form-buttons {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e9ecef;
        }

        .form-row {
            display: flex;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .edit-form {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-shield me-2"></i>Admin Panel
        </a>
        <div class="navbar-nav ms-auto">
            <a href="dashboard.php" class="btn btn-light me-2">
                <i class="fas fa-dashboard me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="page-header">
        <h2><i class="fas fa-user-edit me-2"></i>Edit Teacher</h2>
    </div>

    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <div class="edit-form">
        <form method="POST" action="">
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user me-2"></i>Personal Information
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="full_name" class="form-label">
                            <i class="fas fa-user me-1"></i>Full Name
                        </label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($teacher['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-1"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($teacher['email']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username" class="form-label">
                            <i class="fas fa-id-card me-1"></i>Username
                        </label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($teacher['Username']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="emergency_contact" class="form-label">
                            <i class="fas fa-phone me-1"></i>Emergency Contact
                        </label>
                        <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact" 
                               value="<?php echo htmlspecialchars($teacher['emergency_contact']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-chalkboard-teacher me-2"></i>Professional Information
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="subject" class="form-label">
                            <i class="fas fa-book me-1"></i>Subject
                        </label>
                        <input type="text" class="form-control" id="subject" name="subject" 
                               value="<?php echo htmlspecialchars($teacher['subject']); ?>" 
                               placeholder="e.g., Mathematics, English, Science">
                    </div>
                    
                    <div class="form-group">
                        <label for="class_id" class="form-label">
                            <i class="fas fa-users me-1"></i>Class ID
                        </label>
                        <input type="number" class="form-control" id="class_id" name="class_id" 
                               value="<?php echo htmlspecialchars($teacher['class_id']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="section_id" class="form-label">
                        <i class="fas fa-list me-1"></i>Section ID
                    </label>
                    <input type="number" class="form-control" id="section_id" name="section_id" 
                           value="<?php echo htmlspecialchars($teacher['section_id']); ?>" required>
                </div>
            </div>

        

            <div class="form-buttons">
                <button type="submit" class="btn btn-success me-3">
                    <i class="fas fa-save me-2"></i>Update Teacher
                </button>
                <a href="manage_teachers.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>