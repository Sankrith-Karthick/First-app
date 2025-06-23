<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Bulk Upload</title>
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
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .header h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header p {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .upload-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 15px;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }

        .upload-section:hover {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-control:hover {
            border-color: #667eea;
        }

        .btn {
            display: inline-block;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 150px;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            background: linear-gradient(135deg, #218838, #1da88a);
        }

        .btn-success:active {
            transform: translateY(0);
        }

        .mt-3 {
            margin-top: 20px;
        }

        .alert {
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            border-left: 5px solid;
        }

        .alert-info {
            background-color: #e3f2fd;
            color: #0277bd;
            border-left-color: #03a9f4;
        }

        .alert-success {
            background-color: #e8f5e8;
            color: #2e7d32;
            border-left-color: #4caf50;
        }

        

        .upload-icon {
            text-align: center;
            margin-bottom: 20px;
        }

        .upload-icon svg {
            width: 60px;
            height: 60px;
            color: #667eea;
        }

        .file-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.9rem;
            color: #0277bd;
        }

        .file-info strong {
            display: block;
            margin-bottom: 5px;
        }

        .progress-bar {
            width: 100%;
            height: 6px;
            background-color: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 15px;
            display: none;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            width: 0%;
            transition: width 0.3s ease;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 10px;
            }

            .header h1 {
                font-size: 2rem;
            }

            .upload-section {
                padding: 20px;
            }
        }

        .loading {
            display: none;
            text-align: center;
            margin-top: 20px;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Bulk Upload</h1>
            <p>Upload an Excel file to add multiple students to the system</p>
        </div>

        <?php 
        require_once('../config.php');
        require '../vendor/autoload.php';
        
        use PhpOffice\PhpSpreadsheet\IOFactory;
        
        // Display available classes and sections for reference
        echo "<div class='alert alert-info'>";
        echo "<strong>Available Classes:</strong><br>";
        $classes_query = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY id");
        if ($classes_query && mysqli_num_rows($classes_query) > 0) {
            while ($class_row = mysqli_fetch_assoc($classes_query)) {
                echo "ID: " . $class_row['id'] . " - Name: " . htmlspecialchars($class_row['class_name']) . "<br>";
            }
        } else {
            echo "No classes found in database!<br>";
        }
        
        echo "<br><strong>Available Sections:</strong><br>";
        $sections_query = mysqli_query($conn, "SELECT id, section_name FROM sections ORDER BY id");
        if ($sections_query && mysqli_num_rows($sections_query) > 0) {
            while ($section_row = mysqli_fetch_assoc($sections_query)) {
                echo "ID: " . $section_row['id'] . " - Name: " . htmlspecialchars($section_row['section_name']) . "<br>";
            }
        } else {
            echo "No sections found in database!<br>";
        }
        echo "</div>";
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
            $file = $_FILES['excel_file']['tmp_name'];
            
            try {
                $spreadsheet = IOFactory::load($file);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
                
                $message = '';
                $success_count = 0;
                $error_count = 0;
                $errors = []; // Track specific errors
                
                echo "<div class='alert alert-info'><strong>Debug Info:</strong><br>";
                echo "Total rows found: " . count($rows) . "<br>";
                echo "Processing " . (count($rows) - 1) . " student records...<br>";
                
                // Show first few rows for debugging
               
                
                // Check if file is empty or only has header
                if (count($rows) <= 1) {
                    echo "<div class='alert alert-danger'>
                            <strong>❌ No Data Found!</strong><br>
                            Your Excel file appears to only contain a header row or is empty.<br>
                            Please ensure your Excel file has:<br>
                            • Row 1: Headers<br>
                            • Row 2+: Student data<br><br>
                            <strong>Expected format:</strong><br>
                            First Name | Last Name | Father Name | Father Mobile | Mother Name | Mother Mobile | Emergency Contact | Email | Username | Password | Class | Section
                          </div>";
                    return;
                }
                
                foreach ($rows as $index => $row) {
                    if ($index == 0) continue; // Skip header row
                    
                    // Check if row has enough columns
                    if (count($row) < 12) {
                        $errors[] = "Row " . ($index + 1) . ": Not enough columns (" . count($row) . " found, 12 required)";
                        $error_count++;
                        continue;
                    }
                    
                    // Extract data with null checks
                    $first_name = mysqli_real_escape_string($conn, trim($row[0] ?? ''));
                    $last_name = mysqli_real_escape_string($conn, trim($row[1] ?? ''));
                    $father_name = mysqli_real_escape_string($conn, trim($row[2] ?? ''));
                    $father_mobile = mysqli_real_escape_string($conn, trim($row[3] ?? ''));
                    $mother_name = mysqli_real_escape_string($conn, trim($row[4] ?? ''));
                    $mother_mobile = mysqli_real_escape_string($conn, trim($row[5] ?? ''));
                    $emergency_contact = mysqli_real_escape_string($conn, trim($row[6] ?? ''));
                    $registered_email = mysqli_real_escape_string($conn, trim($row[7] ?? ''));
                    $username = mysqli_real_escape_string($conn, trim($row[8] ?? ''));
                    $password_plain = trim($row[9] ?? '');
                    $class_identifier = trim($row[10] ?? ''); // This could be ID or name
                    $section_identifier = trim($row[11] ?? ''); // This could be ID or name
                    
                    // Validate required fields
                    if (empty($first_name) || empty($last_name) || empty($username) || empty($password_plain) || empty($class_identifier) || empty($section_identifier)) {
                        $errors[] = "Row " . ($index + 1) . ": Missing required fields";
                        $error_count++;
                        continue;
                    }
                    
                    $password = password_hash($password_plain, PASSWORD_DEFAULT);
                    
                    // Handle class lookup - try by ID first, then by name
                    $class_id = null;
                    if (is_numeric($class_identifier)) {
                        // If it's numeric, try to find by ID first
                        $class_res = mysqli_query($conn, "SELECT id FROM classes WHERE id = '$class_identifier'");
                        if ($class_res && mysqli_num_rows($class_res) > 0) {
                            $class = mysqli_fetch_assoc($class_res);
                            $class_id = $class['id'];
                        }
                    }
                    
                    // If not found by ID, try by name (case-insensitive)
                    if (!$class_id) {
                        $class_identifier_escaped = mysqli_real_escape_string($conn, $class_identifier);
                        $class_res = mysqli_query($conn, "SELECT id FROM classes WHERE LOWER(class_name) = LOWER('$class_identifier_escaped')");
                        if ($class_res && mysqli_num_rows($class_res) > 0) {
                            $class = mysqli_fetch_assoc($class_res);
                            $class_id = $class['id'];
                        }
                    }
                    
                    if (!$class_id) {
                        $errors[] = "Row " . ($index + 1) . ": Class '$class_identifier' not found (tried both ID and name)";
                        $error_count++;
                        continue;
                    }
                    
                    // Handle section lookup - try by ID first, then by name
                    $section_id = null;
                    if (is_numeric($section_identifier)) {
                        // If it's numeric, try to find by ID first
                        $section_res = mysqli_query($conn, "SELECT id FROM sections WHERE id = '$section_identifier'");
                        if ($section_res && mysqli_num_rows($section_res) > 0) {
                            $section = mysqli_fetch_assoc($section_res);
                            $section_id = $section['id'];
                        }
                    }
                    
                    // If not found by ID, try by name (case-insensitive)
                    if (!$section_id) {
                        $section_identifier_escaped = mysqli_real_escape_string($conn, $section_identifier);
                        $section_res = mysqli_query($conn, "SELECT id FROM sections WHERE LOWER(section_name) = LOWER('$section_identifier_escaped')");
                        if ($section_res && mysqli_num_rows($section_res) > 0) {
                            $section = mysqli_fetch_assoc($section_res);
                            $section_id = $section['id'];
                        }
                    }
                    
                    if (!$section_id) {
                        $errors[] = "Row " . ($index + 1) . ": Section '$section_identifier' not found (tried both ID and name)";
                        $error_count++;
                        continue;
                    }
                    
                    // Check if username already exists
                    $checkUser = mysqli_query($conn, "SELECT * FROM users WHERE username = '$username'");
                    if (!$checkUser) {
                        $errors[] = "Row " . ($index + 1) . ": Database error when checking username - " . mysqli_error($conn);
                        $error_count++;
                        continue;
                    }
                    
                    if (mysqli_num_rows($checkUser) > 0) {
                        $errors[] = "Row " . ($index + 1) . ": Username '$username' already exists";
                        $error_count++;
                        continue;
                    }
                    
                    // Begin transaction for this student
                    mysqli_autocommit($conn, FALSE);
                    
                    // Insert into users table
                    $insertUser = mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$username', '$password', 'student')");
                    if (!$insertUser) {
                        $errors[] = "Row " . ($index + 1) . ": Failed to create user - " . mysqli_error($conn);
                        mysqli_rollback($conn);
                        mysqli_autocommit($conn, TRUE);
                        $error_count++;
                        continue;
                    }
                    
                    $user_id = mysqli_insert_id($conn);
                    
                    // Insert into students table
                    $insertStudent = mysqli_query($conn, "INSERT INTO students (
                        user_id, Username, first_name, last_name, father_name, father_mobile, mother_name, mother_mobile, emergency_contact, registered_email, class_id, section_id
                    ) VALUES (
                        '$user_id', '$username', '$first_name', '$last_name', '$father_name', '$father_mobile', '$mother_name', '$mother_mobile', '$emergency_contact', '$registered_email', '$class_id', '$section_id'
                    )");
                    
                    if ($insertStudent) {
                        mysqli_commit($conn);
                        mysqli_autocommit($conn, TRUE);
                        $success_count++;
                    } else {
                        $errors[] = "Row " . ($index + 1) . ": Failed to create student record - " . mysqli_error($conn);
                        mysqli_rollback($conn);
                        mysqli_autocommit($conn, TRUE);
                        $error_count++;
                    }
                }
                
                // Show results
                if ($success_count > 0) {
                    echo "<div class='alert alert-success'>
                            <strong>✅ Success!</strong><br>
                            $success_count students added successfully
                          </div>";
                }
                
                
            } catch (Exception $e) {
                echo "<div class='alert alert-danger'>
                        <strong>File Error:</strong><br>
                        " . htmlspecialchars($e->getMessage()) . "
                      </div>";
            }
        }
        ?>

        <div class="upload-section">
            <div class="upload-icon">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="form-group">
                    <label for="excel_file">Select Excel File</label>
                    <input type="file" 
                           name="excel_file" 
                           id="excel_file"
                           accept=".xls,.xlsx" 
                           class="form-control" 
                           required>
                    <div class="file-info">
                        <strong>Supported formats:</strong> .xls, .xlsx<br>
                        <strong>Required columns:</strong> First Name, Last Name, Father Name, Father Mobile, Mother Name, Mother Mobile, Emergency Contact, Email, Username, Password, Class, Section
                    </div>
                </div>
                
                <div class="progress-bar" id="progressBar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                
                <button type="submit" class="btn btn-success mt-3" id="submitBtn">
                    📁 Upload Students
                </button>
                
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Processing your file...</p>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const progressBar = document.getElementById('progressBar');
            
            submitBtn.style.display = 'none';
            loading.style.display = 'block';
            progressBar.style.display = 'block';
            
            // Simulate progress (since we can't track actual upload progress easily with PHP)
            let progress = 0;
            const progressFill = document.getElementById('progressFill');
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressFill.style.width = progress + '%';
            }, 200);
            
            // Clear interval after form submission
            setTimeout(() => {
                clearInterval(interval);
                progressFill.style.width = '100%';
            }, 3000);
        });

        // File input change event
        document.getElementById('excel_file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                console.log(`Selected file: ${file.name} (${fileSize} MB)`);
            }
        });
    </script>
</body>
</html>