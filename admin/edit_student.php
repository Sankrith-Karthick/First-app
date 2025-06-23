<?php 
// Start session 
session_start();  

// Check if admin is logged in 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {     
    header("Location: ../login.php");     
    exit(); 
}  

// Include database config 
include '../config.php';  

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_students.php");
    exit();
}

$student_id = intval($_GET['id']);
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $registered_email = mysqli_real_escape_string($conn, $_POST['registered_email']);
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : NULL;
    $section_id = !empty($_POST['section_id']) ? intval($_POST['section_id']) : NULL;
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $father_mobile = mysqli_real_escape_string($conn, $_POST['father_mobile']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $mother_mobile = mysqli_real_escape_string($conn, $_POST['mother_mobile']);
    $emergency_contact = mysqli_real_escape_string($conn, $_POST['emergency_contact']);

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($username)) {
        $error_message = "First name, last name, and username are required.";
    } else {
        // Check if username already exists for other students
        $check_username_sql = "SELECT id FROM students WHERE Username = '$username' AND id != $student_id";
        $check_result = mysqli_query($conn, $check_username_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            // Update student information
            $update_sql = "UPDATE students SET 
                          first_name = '$first_name',
                          last_name = '$last_name',
                          Username = '$username',
                          registered_email = '$registered_email',
                          class_id = " . ($class_id ? $class_id : 'NULL') . ",
                          section_id = " . ($section_id ? $section_id : 'NULL') . ",
                          father_name = '$father_name',
                          father_mobile = '$father_mobile',
                          mother_name = '$mother_name',
                          mother_mobile = '$mother_mobile',
                          emergency_contact = '$emergency_contact'
                          WHERE id = $student_id";
            
            if (mysqli_query($conn, $update_sql)) {
                $success_message = "Student information updated successfully!";
            } else {
                $error_message = "Error updating student information: " . mysqli_error($conn);
            }
        }
    }
}

// Fetch student data
$sql = "SELECT * FROM students WHERE id = $student_id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: manage_students.php");
    exit();
}

$student = mysqli_fetch_assoc($result);

// Fetch classes for dropdown
$classes_sql = "SELECT * FROM classes ORDER BY class_name";
$classes_result = mysqli_query($conn, $classes_sql);

// Fetch sections for dropdown
$sections_sql = "SELECT * FROM sections ORDER BY section_name";
$sections_result = mysqli_query($conn, $sections_sql);
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>     
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
            padding: 2.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Form Styling */
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
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
            color: var(--dark-color);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(102, 126, 234, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .form-control::placeholder {
            color: var(--text-muted);
            font-weight: 400;
        }

        /* Enhanced Button Styling */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
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

        .btn-success {
            background: var(--success-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .btn-success:hover {
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(17, 153, 142, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }

        .btn-secondary:hover {
            color: white;
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow-light);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(17, 153, 142, 0.1), rgba(56, 239, 125, 0.1));
            color: #0f5132;
            border-left: 4px solid #11998e;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(238, 90, 82, 0.1));
            color: #842029;
            border-left: 4px solid #ff6b6b;
        }

        /* Form sections */
        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h4 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
            display: flex;
            align-items: center;
        }

        .form-section h4 i {
            margin-right: 0.5rem;
            color: #667eea;
        }

        /* Button container */
        .button-container {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                padding: 2rem 0;
            }

            .form-container {
                margin: 0 -15px 2rem -15px;
                border-radius: 0;
                padding: 1.5rem;
            }

            h2 {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            .button-container {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
            }

            .navbar .btn {
                margin-left: 0.25rem;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                width: auto;
            }
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 1rem;
            }

            h2 {
                font-size: 1.6rem;
            }

            .form-section h4 {
                font-size: 1.1rem;
            }
        }

        /* Loading animation */
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
        <a class="navbar-brand" href="#"><i class="fas fa-users-cog me-2"></i>Admin Panel</a>     
        <div class="ms-auto">       
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>       
            <a href="manage_students.php" class="btn btn-light"><i class="fas fa-users me-1"></i>Students</a>       
            <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>     
        </div>   
    </div> 
</nav>  

<div class="container main-content">     
    <h2><i class="fas fa-user-edit me-2"></i>Edit Student</h2>      

    <div class="form-container">
        <?php if ($success_message) : ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message) : ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <!-- Personal Information Section -->
            <div class="form-section">
                <h4><i class="fas fa-user"></i>Personal Information</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" 
                               value="<?php echo htmlspecialchars($student['first_name']); ?>" 
                               placeholder="Enter first name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" 
                               value="<?php echo htmlspecialchars($student['last_name']); ?>" 
                               placeholder="Enter last name" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="username" class="form-label">Username *</label>
                        <input type="text" class="form-control" id="username" name="username" 
                               value="<?php echo htmlspecialchars($student['Username']); ?>" 
                               placeholder="Enter username" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="registered_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="registered_email" name="registered_email" 
                               value="<?php echo htmlspecialchars($student['registered_email']); ?>" 
                               placeholder="Enter email address">
                    </div>
                </div>
            </div>

            <!-- Academic Information Section -->
            <div class="form-section">
                <h4><i class="fas fa-graduation-cap"></i>Academic Information</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select class="form-select" id="class_id" name="class_id">
                            <option value="">Select Class</option>
                            <?php while ($class = mysqli_fetch_assoc($classes_result)) : ?>
                                <option value="<?php echo $class['id']; ?>" 
                                        <?php echo ($student['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($class['class_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="section_id" class="form-label">Section</label>
                        <select class="form-select" id="section_id" name="section_id">
                            <option value="">Select Section</option>
                            <?php while ($section = mysqli_fetch_assoc($sections_result)) : ?>
                                <option value="<?php echo $section['id']; ?>" 
                                        <?php echo ($student['section_id'] == $section['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($section['section_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Family Information Section -->
            <div class="form-section">
                <h4><i class="fas fa-home"></i>Family Information</h4>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="father_name" class="form-label">Father's Name</label>
                        <input type="text" class="form-control" id="father_name" name="father_name" 
                               value="<?php echo htmlspecialchars($student['father_name']); ?>" 
                               placeholder="Enter father's name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="father_mobile" class="form-label">Father's Mobile</label>
                        <input type="tel" class="form-control" id="father_mobile" name="father_mobile" 
                               value="<?php echo htmlspecialchars($student['father_mobile']); ?>" 
                               placeholder="Enter father's mobile number">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="mother_name" class="form-label">Mother's Name</label>
                        <input type="text" class="form-control" id="mother_name" name="mother_name" 
                               value="<?php echo htmlspecialchars($student['mother_name']); ?>" 
                               placeholder="Enter mother's name">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="mother_mobile" class="form-label">Mother's Mobile</label>
                        <input type="tel" class="form-control" id="mother_mobile" name="mother_mobile" 
                               value="<?php echo htmlspecialchars($student['mother_mobile']); ?>" 
                               placeholder="Enter mother's mobile number">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="emergency_contact" class="form-label">Emergency Contact</label>
                        <input type="tel" class="form-control" id="emergency_contact" name="emergency_contact" 
                               value="<?php echo htmlspecialchars($student['emergency_contact']); ?>" 
                               placeholder="Enter emergency contact number">
                    </div>
                </div>
            </div>

            <!-- Form Buttons -->
            <div class="button-container">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Update Student
                </button>
                <a href="manage_students.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body> 
</html>