<?php 
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all classes for dropdown
$class_query = "SELECT * FROM classes";
$class_result = mysqli_query($conn, $class_query);

// Handle form submit
if (isset($_POST['add_section'])) {
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    $section_name = mysqli_real_escape_string($conn, $_POST['section_name']);
    
    $query = "INSERT INTO sections (section_name, class_id) VALUES ('$section_name', '$class_id')";
    if (mysqli_query($conn, $query)) {
        $success = "Section added successfully!";
    } else {
        $error = "Error adding section: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Section</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
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
            animation: slideInDown 0.5s ease-out;
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
            animation: slideInUp 0.6s ease-out;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
        }

        .form-label i {
            margin-right: 8px;
            color: var(--secondary-color);
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            font-size: 1rem;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: translateY(-1px);
            background: #fafbfc;
        }

        .form-control:hover, .form-select:hover {
            border-color: #bbb;
        }

        .form-control::placeholder {
            color: #adb5bd;
            font-style: italic;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        .form-select option {
            padding: 10px;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--secondary-color) 0%, #5dade2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow);
            min-width: 160px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            background: linear-gradient(135deg, #5dade2 0%, var(--secondary-color) 100%);
            color: white;
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .btn-submit i {
            margin-right: 8px;
        }

        .info-card {
            background: linear-gradient(135deg, #e8f4fd 0%, #d6eaf8 100%);
            border: 1px solid #aed6f1;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--secondary-color);
        }

        .info-card i {
            color: var(--secondary-color);
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .info-card h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .info-card p {
            color: #34495e;
            margin-bottom: 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr 1fr;
            }
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

            .info-card {
                padding: 1rem;
            }
        }

        /* Animations */
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

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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

        /* Form validation styles */
        .form-control.is-valid, .form-select.is-valid {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0.2rem rgba(39, 174, 96, 0.25);
        }

        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--danger-color);
            box-shadow: 0 0 0 0.2rem rgba(231, 76, 60, 0.25);
        }

        .no-classes-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            border: 1px solid #f39c12;
            border-radius: var(--border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid var(--warning-color);
            text-align: center;
        }

        .no-classes-warning i {
            color: var(--warning-color);
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .no-classes-warning h5 {
            color: #856404;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .no-classes-warning p {
            color: #856404;
            margin-bottom: 1rem;
        }

        .no-classes-warning .btn {
            background: var(--warning-color);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 20px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .no-classes-warning .btn:hover {
            background: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-graduation-cap"></i>
            Admin Panel
        </a>
        <div class="ms-auto">
            <a href="dashboard.php" class="btn btn-light">
                <i class="fas fa-tachometer-alt me-1"></i>
                Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="main-container">
        <h2 class="page-title">
            <i class="fas fa-layer-group"></i>
            Add New Section
        </h2>

        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="info-card">
            <h5>
                <i class="fas fa-info-circle"></i>
                Section Management
            </h5>
            <p>Create sections within classes to organize students more effectively. Each section belongs to a specific class and helps in better classroom management.</p>
        </div>

        <?php 
        // Reset the result pointer to check if there are classes
        mysqli_data_seek($class_result, 0);
        if (mysqli_num_rows($class_result) == 0): 
        ?>
            <div class="no-classes-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <h5>No Classes Available</h5>
                <p>You need to create at least one class before adding sections.</p>
                <a href="add_class.php" class="btn">
                    <i class="fas fa-plus me-1"></i>
                    Add Class First
                </a>
            </div>
        <?php else: ?>
            <div class="form-section">
                <form method="post" id="sectionForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label" for="class_id">
                                <i class="fas fa-chalkboard"></i>
                                Select Class
                            </label>
                            <select name="class_id" id="class_id" class="form-select" required>
                                <option value="">-- Select Class --</option>
                                <?php 
                                mysqli_data_seek($class_result, 0);
                                while ($row = mysqli_fetch_assoc($class_result)): 
                                ?>
                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['class_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-lightbulb me-1"></i>
                                Choose the class this section belongs to
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="section_name">
                                <i class="fas fa-tag"></i>
                                Section Name
                            </label>
                            <input type="text" 
                                   name="section_name" 
                                   id="section_name"
                                   class="form-control" 
                                   placeholder="e.g., A, B, Alpha, Beta"
                                   required
                                   maxlength="50">
                            <small class="text-muted mt-1 d-block">
                                <i class="fas fa-lightbulb me-1"></i>
                                Enter a unique section identifier (max 50 characters)
                            </small>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" name="add_section" class="btn-submit" id="submitBtn">
                            <i class="fas fa-plus"></i>
                            Add Section
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add loading animation to submit button
    document.getElementById('sectionForm')?.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const classSelect = document.getElementById('class_id');
        const sectionInput = document.getElementById('section_name');
        
        // Basic validation
        let isValid = true;
        
        if (classSelect.value === '') {
            e.preventDefault();
            classSelect.classList.add('is-invalid');
            isValid = false;
        } else {
            classSelect.classList.remove('is-invalid');
            classSelect.classList.add('is-valid');
        }
        
        if (sectionInput.value.trim() === '') {
            e.preventDefault();
            sectionInput.classList.add('is-invalid');
            isValid = false;
        } else {
            sectionInput.classList.remove('is-invalid');
            sectionInput.classList.add('is-valid');
        }
        
        if (isValid) {
            submitBtn.classList.add('loading');
            submitBtn.innerHTML = '<i class="fas fa-spinner"></i> Adding Section...';
        }
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

    // Real-time validation
    const classSelect = document.getElementById('class_id');
    const sectionInput = document.getElementById('section_name');

    if (classSelect) {
        classSelect.addEventListener('change', function() {
            if (this.value === '') {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
    }

    if (sectionInput) {
        sectionInput.addEventListener('input', function() {
            const value = this.value.trim();
            
            if (value.length === 0) {
                this.classList.remove('is-valid', 'is-invalid');
            } else if (value.length < 1) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });

        // Character counter
        const charCounter = document.createElement('small');
        charCounter.className = 'text-muted';
        charCounter.style.float = 'right';
        charCounter.style.marginTop = '5px';
        sectionInput.parentNode.appendChild(charCounter);

        sectionInput.addEventListener('input', function() {
            const currentLength = this.value.length;
            const maxLength = 50;
            charCounter.textContent = `${currentLength}/${maxLength} characters`;
            
            if (currentLength > maxLength * 0.8) {
                charCounter.style.color = '#e74c3c';
            } else {
                charCounter.style.color = '#6c757d';
            }
        });

        // Initial character count
        charCounter.textContent = '0/50 characters';
    }
</script>

</body>
</html>