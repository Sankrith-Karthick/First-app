<?php 
session_start(); 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Handle form submission
if (isset($_POST['send_announcement'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    $sql = "INSERT INTO announcements (title, message) VALUES ('$title', '$message')";
    
    if (mysqli_query($conn, $sql)) {
        $success = "Announcement sent successfully!";
    } else {
        $error = "Error sending announcement: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Announcement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important;
            box-shadow: var(--shadow);
            border-bottom: 3px solid rgba(255, 255, 255, 0.1);
        }

        .navbar-brand {
            font-weight: 600;
            font-size: 1.4rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .navbar .btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-left: 10px;
        }

        .navbar .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .main-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            margin-top: 2rem;
            position: relative;
            overflow: hidden;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
        }

        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 15px;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--secondary-color), var(--success-color));
            border-radius: 2px;
        }

        .page-title i {
            margin-right: 10px;
            color: var(--secondary-color);
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            box-shadow: var(--shadow);
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f1b0b7 100%);
            color: #721c24;
            border-left: 4px solid var(--danger-color);
        }

        .form-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid #e9ecef;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 8px;
            color: var(--secondary-color);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: translateY(-1px);
        }

        .form-control:hover {
            border-color: #bbb;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--success-color) 0%, #2ecc71 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
            background: linear-gradient(135deg, #2ecc71 0%, var(--success-color) 100%);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            
            .navbar .btn {
                margin-left: 5px;
                padding: 6px 15px;
                font-size: 0.9rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-section {
                padding: 1.5rem;
            }
        }

        /* Animation for form appearance */
        .form-section {
            animation: slideInUp 0.6s ease-out;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Loading animation for submit button */
        .btn-submit.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-submit.loading::after {
            content: '';
            display: inline-block;
            width: 16px;
            height: 16px;
            margin-left: 10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-shield-alt"></i>
            Admin Panel
        </a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="main-container">
        <h2 class="page-title">
            <i class="fas fa-bullhorn"></i>
            Send Announcement to Teachers
        </h2>
        
        <?php if (isset($success)) : ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-section">
            <form method="POST" action="" id="announcementForm">
                <div class="form-group">
                    <label class="form-label" for="title">
                        <i class="fas fa-heading"></i>
                        Announcement Title
                    </label>
                    <input type="text" 
                           name="title" 
                           id="title"
                           class="form-control" 
                           placeholder="Enter announcement title..."
                           required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="message">
                        <i class="fas fa-message"></i>
                        Message Content
                    </label>
                    <textarea name="message" 
                              id="message"
                              class="form-control" 
                              rows="6" 
                              placeholder="Write your announcement message here..."
                              required></textarea>
                </div>
                
                <button type="submit" name="send_announcement" class="btn-submit" id="submitBtn">
                    <i class="fas fa-paper-plane"></i>
                    Send Announcement
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add loading animation to submit button
    document.getElementById('announcementForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.classList.add('loading');
        submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Sending...';
    });

    // Auto-hide alerts after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.remove();
                }, 300);
            }, 5000);
        });
    });

    // Character counter for message
    const messageTextarea = document.getElementById('message');
    const charCounter = document.createElement('small');
    charCounter.className = 'text-muted';
    charCounter.style.float = 'right';
    messageTextarea.parentNode.appendChild(charCounter);

    messageTextarea.addEventListener('input', function() {
        const currentLength = this.value.length;
        charCounter.textContent = `${currentLength} characters`;
        
        if (currentLength > 500) {
            charCounter.style.color = '#e74c3c';
        } else {
            charCounter.style.color = '#6c757d';
        }
    });
</script>

</body>
</html>