<?php
include('../config.php');
$message = '';

// Fetch classes and sections
$classes = mysqli_query($conn, "SELECT * FROM classes");
$sections = mysqli_query($conn, "SELECT * FROM sections");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $father_name = $_POST['father_name'];
    $father_mobile = $_POST['father_mobile'];
    $mother_name = $_POST['mother_name'];
    $mother_mobile = $_POST['mother_mobile'];
    $emergency_contact = $_POST['emergency_contact'];
    $registered_email = $_POST['registered_email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $class_id = $_POST['class_id'];
    $section_id = $_POST['section_id'];

    // Check for duplicate username
    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($checkUser) > 0) {
        $message = "Username already exists!";
    } else {
        // Insert into users table
        $insertUser = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'student')");
        if ($insertUser) {
            $user_id = mysqli_insert_id($conn);

            // Insert into students table
            $insertStudent = mysqli_query($conn, "INSERT INTO students (
                user_id, first_name, last_name, father_name, father_mobile, mother_name, mother_mobile, emergency_contact, registered_email, class_id, section_id
            ) VALUES (
                '$user_id', '$first_name', '$last_name', '$father_name', '$father_mobile', '$mother_name', '$mother_mobile', '$emergency_contact', '$registered_email', '$class_id', '$section_id'
            )");

            $message = $insertStudent ? "Student added successfully!" : "Failed to insert into students table.";
        } else {
            $message = "Failed to insert into users table.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 1.5rem 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .header .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .header .subtitle {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: white;
        }

        .back-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .back-btn:active {
            transform: translateY(0);
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-section-title {
            color: #2c3e50;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.3rem;
            border-bottom: 2px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 6px;
            padding: 0.5rem;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .alert {
            border-radius: 8px;
            border: none;
            font-weight: 500;
            margin-bottom: 2rem;
        }

        .alert-info {
            background: linear-gradient(135deg, #17a2b8, #20c997);
            color: white;
        }

        .form-row {
            margin-bottom: 1rem;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e9ecef, transparent);
            margin: 1.5rem 0;
        }

        .compact-section {
            margin-bottom: 1.5rem;
        }

        .form-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }

        @media (max-width: 768px) {
            .header .container {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .header .subtitle {
                font-size: 1.2rem;
            }
            .form-card {
                padding: 1.5rem 1rem;
            }
            .main-container {
                padding: 0 0.5rem;
            }
            .form-columns {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <div class="subtitle">Add New Student Registration Form</div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>
    </div>

    <div class="main-container">
        <div class="form-card">
            <?php if (!empty($message)) echo "<div class='alert alert-info'><i class='fas fa-info-circle me-2'></i>$message</div>"; ?>

            <form method="POST">
                <div class="form-columns">
                    <div class="left-column">
                        <div class="compact-section">
                            <div class="form-section-title">
                                <i class="fas fa-user"></i>
                                Personal Information
                            </div>
                            
                            <div class="row form-row">
                                <div class="col-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="compact-section">
                            <div class="form-section-title">
                                <i class="fas fa-users"></i>
                                Parent Information
                            </div>

                            <div class="row form-row">
                                <div class="col-6">
                                    <label class="form-label">Father's Name</label>
                                    <input type="text" name="father_name" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Father's Mobile</label>
                                    <input type="text" name="father_mobile" class="form-control" required>
                                </div>
                            </div>

                            <div class="row form-row">
                                <div class="col-6">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Mother's Mobile</label>
                                    <input type="text" name="mother_mobile" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="right-column">
                        <div class="compact-section">
                            <div class="form-section-title">
                                <i class="fas fa-address-book"></i>
                                Contact Information
                            </div>

                            <div class="row form-row">
                                <div class="col-12">
                                    <label class="form-label">Emergency Contact</label>
                                    <input type="text" name="emergency_contact" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="row form-row">
                                <div class="col-12">
                                    <label class="form-label">Registered Email ID</label>
                                    <input type="email" name="registered_email" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="compact-section">
                            <div class="form-section-title">
                                <i class="fas fa-key"></i>
                                Account Information
                            </div>

                            <div class="row form-row">
                                <div class="col-6">
                                    <label class="form-label">Username</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="compact-section">
                            <div class="form-section-title">
                                <i class="fas fa-school"></i>
                                Academic Information
                            </div>

                            <div class="row form-row">
                                <div class="col-6">
                                    <label class="form-label">Class</label>
                                    <select name="class_id" class="form-select" required>
                                        <option value="">Select Class</option>
                                        <?php while ($row = mysqli_fetch_assoc($classes)) { ?>
                                            <option value="<?= $row['id'] ?>"><?= $row['class_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Section</label>
                                    <select name="section_id" class="form-select" required>
                                        <option value="">Select Section</option>
                                        <?php while ($row = mysqli_fetch_assoc($sections)) { ?>
                                            <option value="<?= $row['id'] ?>"><?= $row['section_name'] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>