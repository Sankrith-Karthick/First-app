<?php 
// Start session 
session_start();  

// Include PHPMailer
require '../PHPMailer/src/PHPMailer.php'; 
require '../PHPMailer/src/SMTP.php'; 
require '../PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if admin is logged in 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {     
    header("Location: ../login.php");     
    exit(); 
}  

// Include database config 
include '../config.php';  

// Email sending logic
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_email'])) {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $selected_emails = $_POST['emails'] ?? [];

    if (!empty($selected_emails)) {
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

            $mail->setFrom('sankrithkarthick7@gmail.com', 'School Admin');

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
    } else {
        $error = "No email addresses selected.";
    }
}

// Fetch all students with class information
$sql = "SELECT s.*, c.class_name, sec.section_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.id 
        LEFT JOIN sections sec ON s.section_id = sec.id 
        ORDER BY s.id ASC"; 
$result = mysqli_query($conn, $sql);  
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>     
    <meta charset="UTF-8">     
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #17a2b8;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
            --border-color: #dee2e6;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Navigation Styles */
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            box-shadow: var(--shadow);
            padding: 1rem 0;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-2px);
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .navbar .btn-light {
            background: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .navbar .btn-light:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
            background: #f8f9fa;
        }

        /* Main Content */
        .main-content {
            background: white;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: var(--hover-shadow);
            max-width: 1400px;
            position: relative;
            overflow: hidden;
        }

        .main-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color), var(--warning-color));
        }

        .main-content h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            text-align: center;
            position: relative;
        }

        .main-content h2::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
            border-radius: 2px;
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow);
            animation: slideInDown 0.5s ease;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 5px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-left: 5px solid var(--danger-color);
        }

        /* Button Styles */
        .btn {
            border-radius: 25px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--hover-shadow);
        }

        .btn-info {
            background: linear-gradient(135deg, var(--info-color) 0%, #138496 100%);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success-color) 0%, #1e7e34 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #d39e00 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger-color) 0%, #c82333 100%);
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            color: white;
        }

        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
        }

        /* Form Controls */
        .form-check-input:checked {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .form-check-label {
            font-weight: 500;
            color: var(--primary-color);
        }

        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: var(--shadow);
            margin: 2rem 0;
        }

        .table {
            margin: 0;
            background: white;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color) 0%, #34495e 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
        }

        .student-name {
            font-weight: 600;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .student-class {
            display: inline-block;
            background: linear-gradient(135deg, var(--secondary-color) 0%, #3498db 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            min-width: 80px;
        }

        /* Email Checkbox Styles */
        .email-checkbox {
            transform: scale(1.2);
            margin-right: 0.5rem;
        }

        /* Modal Styles */
        .email-modal .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--hover-shadow);
        }

        .email-modal .modal-header {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #2980b9 100%);
            border: none;
            padding: 1.5rem;
        }

        .email-modal .modal-title {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .email-modal .modal-body {
            padding: 2rem;
        }

        .email-modal .form-label {
            color: var(--primary-color);
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
        }

        .email-modal .form-control {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }

        .email-modal .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        #selectedEmailsList {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            min-height: 100px;
        }

        #selectedEmailsList .bg-white {
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        #selectedEmailsList .bg-white:hover {
            box-shadow: var(--shadow);
            transform: translateY(-1px);
        }

        /* Add Student Button */
        .add-student-container {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px dashed var(--border-color);
        }

        .add-student-container .btn {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
        }

        /* Empty State */
        .table tbody tr td.text-center {
            padding: 4rem 2rem;
        }

        .table tbody tr td.text-center .fa-users {
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin: 1rem;
                padding: 1rem;
                border-radius: 15px;
            }

            .main-content h2 {
                font-size: 2rem;
            }

            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.875rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                min-width: unset;
                width: 100%;
            }

            .table-responsive {
                border-radius: 10px;
            }
        }

        @media (max-width: 576px) {
            .navbar .container {
                flex-direction: column;
                gap: 1rem;
            }

            .main-content h2 {
                font-size: 1.75rem;
            }

            .email-modal .modal-body {
                padding: 1rem;
            }
        }

        /* Animations */
        @keyframes slideInDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .table tbody tr {
            animation: fadeIn 0.5s ease;
        }

        /* Loading States */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn:disabled::before {
            display: none;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--light-bg);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #2980b9;
        }
    </style>
</head> 
<body>  

<nav class="navbar navbar-expand-lg navbar-dark">   
    <div class="container">     
        <a class="navbar-brand" href="#"><i class="fas fa-users-cog me-2"></i>Admin Panel</a>     
        <div class="ms-auto">       
            <a href="dashboard.php" class="btn btn-light"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>       
        </div>   
    </div> 
</nav>  

<div class="container main-content">     
    <h2><i class="fas fa-user-graduate me-2"></i>Manage Students</h2>      

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>Emails sent successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#emailModal">
            <i class="fas fa-envelope me-2"></i>Send Email to Selected
        </button>
        
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="selectAllCheckbox">
            <label class="form-check-label" for="selectAllCheckbox">Select All</label>
        </div>
    </div>

    <div class="table-container">
        <div class="table-responsive">
            <form id="emailForm" method="post">
                <table class="table table-hover">         
                    <thead>             
                        <tr>                 
                            <th><i class="fas fa-hashtag me-1"></i>ID</th>                 
                            <th><i class="fas fa-user me-1"></i>Name</th>                 
                            <th><i class="fas fa-at me-1"></i>Username</th>                 
                            <th class="d-none d-md-table-cell"><i class="fas fa-envelope me-1"></i>Email</th>                 
                            <th class="d-none d-lg-table-cell"><i class="fas fa-chalkboard-teacher me-1"></i>Class</th>                 
                            <th class="d-none d-lg-table-cell"><i class="fas fa-male me-1"></i>Father</th>                 
                            <th class="d-none d-xl-table-cell"><i class="fas fa-phone me-1"></i>Father Mobile</th>                 
                            <th class="d-none d-lg-table-cell"><i class="fas fa-female me-1"></i>Mother</th>                 
                            <th class="d-none d-xl-table-cell"><i class="fas fa-phone me-1"></i>Mother Mobile</th>                 
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>             
                        </tr>         
                    </thead>         
                    <tbody>             
                        <?php if (mysqli_num_rows($result) > 0) : ?>
                            <?php while ($student = mysqli_fetch_assoc($result)) : ?>                 
                                <tr>                     
                                    <td><?php echo $student['id']; ?></td>                     
                                    <td>
                                        <div class="student-name">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                        </div>
                                    </td>                     
                                    <td><?php echo htmlspecialchars($student['Username']); ?></td>                     
                                    <td class="d-none d-md-table-cell">
                                        <?php if (!empty($student['registered_email'])): ?>
                                            <div class="form-check">
                                                <input class="form-check-input email-checkbox" type="checkbox" 
                                                       name="emails[]" value="<?php echo htmlspecialchars($student['registered_email']); ?>" 
                                                       id="email_<?php echo $student['id']; ?>">
                                                <label class="form-check-label" for="email_<?php echo $student['id']; ?>">
                                                    <?php echo htmlspecialchars($student['registered_email']); ?>
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No email</span>
                                        <?php endif; ?>
                                    </td>                     
                                    <td class="d-none d-lg-table-cell">
                                        <?php if ($student['class_name'] || $student['section_name']) : ?>
                                            <span class="student-class">
                                                <?php echo htmlspecialchars($student['class_name'] . ' - ' . $student['section_name']); ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="text-muted">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>                     
                                    <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($student['father_name'] ?: 'N/A'); ?></td>                     
                                    <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($student['father_mobile'] ?: 'N/A'); ?></td>                     
                                    <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($student['mother_name'] ?: 'N/A'); ?></td>                     
                                    <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($student['mother_mobile'] ?: 'N/A'); ?></td>                     
                                    <td>
                                        <div class="action-buttons">                         
                                            <a href="edit_student.php?id=<?php echo $student['id']; ?>" class="btn btn-warning btn-sm" title="Edit Student">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>                         
                                            <a href="delete_student.php?id=<?php echo $student['id']; ?>" class="btn btn-danger btn-sm" title="Delete Student" onclick="return confirm('Are you sure you want to delete this student? This action cannot be undone.');">
                                                <i class="fas fa-trash me-1"></i>Delete
                                            </a>
                                        </div>                     
                                    </td>                 
                                </tr>             
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Students Found</h5>
                                    <p class="text-muted">No students are currently registered in the system.</p>
                                </td>
                            </tr>
                        <?php endif; ?>         
                    </tbody>     
                </table>
                
                <!-- Email Modal -->
                <div class="modal fade email-modal" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="emailModalLabel">
                                    <i class="fas fa-envelope me-2"></i>Send Email to Selected Parents
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-4">
                                    <label for="subject" class="form-label fw-bold">Subject:</label>
                                    <input type="text" class="form-control form-control-lg" id="subject" name="subject" required 
                                           placeholder="Enter email subject" style="font-size: 1.1rem;">
                                </div>
                                <div class="mb-4">
                                    <label for="message" class="form-label fw-bold">Message:</label>
                                    <textarea class="form-control" id="message" name="message" rows="10" required
                                              placeholder="Type your message here..." style="font-size: 1.1rem;"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Selected Email Addresses:</label>
                                    <div id="selectedEmailsList" class="p-3 bg-light rounded" style="max-height: 200px; overflow-y: auto;">
                                        <small class="text-muted">No email addresses selected</small>
                                    </div>
                                    <div class="mt-2 text-end">
                                        <small class="text-muted" id="selectedCount">0 emails selected</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i> Close
                                </button>
                                <button type="submit" name="send_email" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Email
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>      

    <div class="add-student-container">
        <a href="add_student.php" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Add New Student
        </a> 
    </div>
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Select all checkbox functionality
    document.getElementById('selectAllCheckbox').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.email-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateSelectedEmailsList();
    });

    // Update selected emails list when checkboxes change
    document.querySelectorAll('.email-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedEmailsList);
    });

    // Update the selected emails list in the modal
    function updateSelectedEmailsList() {
        const selectedEmails = Array.from(document.querySelectorAll('.email-checkbox:checked'))
            .map(checkbox => checkbox.value);
        
        const selectedEmailsList = document.getElementById('selectedEmailsList');
        const selectedCount = document.getElementById('selectedCount');
        
        if (selectedEmails.length > 0) {
            selectedEmailsList.innerHTML = selectedEmails
                .map(email => `<div class="mb-2 p-2 bg-white rounded"><i class="fas fa-envelope me-2 text-primary"></i>${email}</div>`)
                .join('');
            selectedCount.textContent = `${selectedEmails.length} email${selectedEmails.length !== 1 ? 's' : ''} selected`;
            selectedCount.className = 'text-success fw-bold';
        } else {
            selectedEmailsList.innerHTML = '<small class="text-muted">No email addresses selected</small>';
            selectedCount.textContent = '0 emails selected';
            selectedCount.className = 'text-muted';
        }
    }

    // Update the list when modal is shown
    document.getElementById('emailModal').addEventListener('show.bs.modal', updateSelectedEmailsList);

    // Focus on subject field when modal opens
    document.getElementById('emailModal').addEventListener('shown.bs.modal', function() {
        document.getElementById('subject').focus();
    });
</script>
</body> 
</html>