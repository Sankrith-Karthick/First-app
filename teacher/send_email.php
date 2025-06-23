<?php
session_start();
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';
include '../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$teacher_user_id = $_SESSION['user_id'];

// Get class_id and section_id
$teacher_query = "SELECT class_id, section_id FROM teachers WHERE user_id = $teacher_user_id";
$teacher_result = mysqli_query($conn, $teacher_query);
$teacher_data = mysqli_fetch_assoc($teacher_result);

if (!$teacher_data) {
    die("Teacher not found.");
}

$class_id = $teacher_data['class_id'];
$section_id = $teacher_data['section_id'];

$success = false;
$error = '';
$selected_emails = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_email'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $student_names = $_POST['student_names'] ?? '';
    $select_all = isset($_POST['select_all']);
    
    if ($select_all) {
        // Get all registered emails from the database
        $all_emails_query = "SELECT registered_email FROM students WHERE class_id = $class_id AND section_id = $section_id AND registered_email IS NOT NULL AND registered_email != ''";
        $all_emails_result = mysqli_query($conn, $all_emails_query);
        
        while ($row = mysqli_fetch_assoc($all_emails_result)) {
            $selected_emails[] = $row['registered_email'];
        }
    } elseif (!empty($student_names)) {
        // Process comma-separated student names
        $names_array = array_map('trim', explode(',', $student_names));
        
        foreach ($names_array as $name) {
            if (!empty($name)) {
                // Search for student by name (case-insensitive)
                $name_parts = explode(' ', trim($name));
                if (count($name_parts) >= 2) {
                    $first_name = mysqli_real_escape_string($conn, $name_parts[0]);
                    $last_name = mysqli_real_escape_string($conn, end($name_parts));
                    $email_query = "SELECT registered_email FROM students WHERE class_id = $class_id AND section_id = $section_id AND LOWER(first_name) = LOWER('$first_name') AND LOWER(last_name) = LOWER('$last_name') AND registered_email IS NOT NULL AND registered_email != ''";
                } else {
                    // Single name search - check both first and last name
                    $single_name = mysqli_real_escape_string($conn, $name);
                    $email_query = "SELECT registered_email FROM students WHERE class_id = $class_id AND section_id = $section_id AND (LOWER(first_name) = LOWER('$single_name') OR LOWER(last_name) = LOWER('$single_name')) AND registered_email IS NOT NULL AND registered_email != ''";
                }
                
                $email_result = mysqli_query($conn, $email_query);
                if ($email_row = mysqli_fetch_assoc($email_result)) {
                    $selected_emails[] = $email_row['registered_email'];
                } else {
                    $error .= "Student '$name' not found or no email registered. ";
                }
            }
        }
    }
    
    // Remove duplicates
    $selected_emails = array_unique($selected_emails);
    
    if (!empty($selected_emails) && empty($error)) {
        $mail = new PHPMailer(true);
        
        try {
            // SMTP settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sankrithkarthick7@gmail.com'; // replace with your Gmail
            $mail->Password = 'iqar pela cvqy rdvf'; // replace with App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom('sankrithkarthick7@gmail.com', 'Testers');
            
            foreach ($selected_emails as $email) {
                $mail->addAddress($email);
            }
            
            $mail->Subject = $subject;
            $mail->Body = $message;
            
            $mail->send();
            $success = true;
        } catch (Exception $e) {
            $error = "Mailer Error: " . $mail->ErrorInfo;
        }
    } elseif (empty($selected_emails)) {
        $error = "No valid students found or selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email to Parents</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Teacher Panel</a>
    <div class="ml-auto">
      <a href="dashboard.php" class="btn btn-light">Back</a>
    </div>
  </div>
</nav>
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 1000px;
        }
        
        .email-card {
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        
        .card-header h2 {
            margin: 0;
            font-weight: 600;
            color: #343a40;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-control, .form-select {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-primary {
            background-color: #0d6efd;
            border-color: #0d6efd;
            border-radius: 4px;
            padding: 8px 16px;
        }
        
        .btn-primary:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 4px;
            padding: 6px 12px;
        }
        
        .btn-secondary:hover {
            background-color: #5c636a;
            border-color: #565e64;
        }
        
        .btn-info {
            background-color: #0dcaf0;
            border-color: #0dcaf0;
            border-radius: 4px;
            padding: 6px 12px;
            margin-bottom: 20px;
        }
        
        .btn-info:hover {
            background-color: #0bb5d0;
            border-color: #0aa2c0;
        }
        
        .alert {
            border-radius: 4px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 8px;
        }
        
        .student-input-section {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group .form-control {
            padding-right: 120px;
        }
        
        .select-all-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
        }
        
        .email-preview {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .email-tag {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.85em;
            margin: 2px;
        }
        
        .help-text {
            font-size: 0.9em;
            color: #6c757d;
            font-style: italic;
        }
        
        .student-list {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            max-height: 400px;
            overflow-y: auto;
            margin-top: 15px;
        }
        
        .student-item {
            padding: 12px 15px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        
        .student-item:last-child {
            border-bottom: none;
        }
        
        .student-item:hover {
            background-color: #f8f9fa;
        }
        
        .student-info {
            flex-grow: 1;
        }
        
        .student-name {
            font-weight: 600;
            color: #343a40;
        }
        
        .student-email {
            font-size: 0.9em;
            color: #6c757d;
        }
        
        .no-students {
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <div class="email-card">
        <div class="card-header">
            <h2><i class="fas fa-envelope"></i> Send Email to Parents</h2>
        </div>
        <div class="card-body p-4">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Emails sent successfully to <?php echo count($selected_emails); ?> recipients!
                    <div class="email-preview">
                        <?php foreach ($selected_emails as $email): ?>
                            <span class="email-tag"><?php echo htmlspecialchars($email); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-tag"></i> Subject:</label>
                    <input type="text" name="subject" class="form-control" placeholder="Enter email subject" required>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="fas fa-edit"></i> Message:</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Type your message here..." required></textarea>
                </div>

                <div class="student-input-section">
                    <h5 class="mb-3"><i class="fas fa-users"></i> Select Recipients</h5>
                    
                    <button type="button" class="btn btn-info" onclick="toggleStudentList()">
                        <i class="fas fa-list"></i> <span id="toggleText">Show Student List</span>
                    </button>
                    
                    <div id="studentListContainer" style="display: none;">
                        <div class="student-list">
                            <?php
                            // Get all students for this teacher's class and section
                            $students_query = "SELECT CONCAT(first_name, ' ', last_name) AS full_name, registered_email, first_name, last_name 
                                             FROM students 
                                             WHERE class_id = $class_id AND section_id = $section_id 
                                             ORDER BY first_name, last_name";
                            $students_result = mysqli_query($conn, $students_query);
                            
                            if (mysqli_num_rows($students_result) > 0) {
                                while ($student = mysqli_fetch_assoc($students_result)) {
                                    echo "<div class='student-item'>";
                                    echo "<div class='student-info'>";
                                    echo "<div class='student-name'>" . htmlspecialchars($student['full_name']) . "</div>";
                                    if (!empty($student['registered_email'])) {
                                        echo "<div class='student-email'>" . htmlspecialchars($student['registered_email']) . "</div>";
                                    } else {
                                        echo "<div class='student-email text-muted'>No email registered</div>";
                                    }
                                    echo "</div>";
                                    if (!empty($student['registered_email'])) {
                                        echo "<button type='button' class='btn btn-sm btn-outline-primary' onclick=\"addStudentName('" . htmlspecialchars($student['full_name']) . "')\">Add</button>";
                                    }
                                    echo "</div>";
                                }
                            } else {
                                echo "<div class='no-students'>No students found in your class.</div>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label class="form-label">Student Names:</label>
                        <div class="input-group">
                            <input type="text" name="student_names" class="form-control" 
                                   placeholder="Enter student names separated by commas (e.g., John Doe, Jane Smith)" 
                                   id="studentNamesInput">
                            <button type="button" class="btn btn-secondary select-all-btn" onclick="selectAllStudents()">
                                <i class="fas fa-users"></i> Select All
                            </button>
                        </div>
                        <div class="help-text mt-2">
                            <i class="fas fa-info-circle"></i> You can enter multiple student names separated by commas, or click "Select All" to include all students.
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="select_all" id="selectAll" onchange="toggleSelectAll()">
                        <label class="form-check-label" for="selectAll">
                            <strong>Select All Students</strong> (This will override individual names and send to all registered emails)
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" name="send_email" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let studentListVisible = false;

function toggleStudentList() {
    const container = document.getElementById('studentListContainer');
    const toggleText = document.getElementById('toggleText');
    
    if (studentListVisible) {
        container.style.display = 'none';
        toggleText.textContent = 'Show Student List';
        studentListVisible = false;
    } else {
        container.style.display = 'block';
        toggleText.textContent = 'Hide Student List';
        studentListVisible = true;
    }
}

function addStudentName(name) {
    const input = document.getElementById('studentNamesInput');
    let currentValue = input.value.trim();
    
    // Check if name already exists
    if (currentValue.includes(name)) {
        return;
    }
    
    if (currentValue === '') {
        input.value = name;
    } else {
        input.value = currentValue + ', ' + name;
    }
    
    // Clear select all if individual names are being added
    document.getElementById('selectAll').checked = false;
    input.disabled = false;
}

function selectAllStudents() {
    document.getElementById('selectAll').checked = true;
    document.getElementById('studentNamesInput').value = '';
    document.getElementById('studentNamesInput').disabled = true;
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const studentNamesInput = document.getElementById('studentNamesInput');
    
    if (selectAllCheckbox.checked) {
        studentNamesInput.value = '';
        studentNamesInput.disabled = true;
        studentNamesInput.placeholder = 'Select All is enabled - all students will be included';
    } else {
        studentNamesInput.disabled = false;
        studentNamesInput.placeholder = 'Enter student names separated by commas (e.g., John Doe, Jane Smith)';
    }
}
</script>

</body>
</html>