<?php
session_start();
include('../config.php');

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Initialize variables
$students = [];
$class_name = "";
$class_id = 0;

// For teacher role
if ($role == 'teacher') {
    // Get teacher ID
    if (isset($_SESSION['login_from']) && $_SESSION['login_from'] == 'teachers') {
        $teacher_id = $user_id;
    } else {
        $getTeacher = mysqli_query($conn, "SELECT id FROM teachers WHERE user_id = $user_id");
        if ($getTeacher && mysqli_num_rows($getTeacher) > 0) {
            $teacherRow = mysqli_fetch_assoc($getTeacher);
            $teacher_id = $teacherRow['id'];
        } else {
            die("Teacher record not found.");
        }
    }
    
    // Get the class assigned to this teacher (check both table names)
    $classResult = mysqli_query($conn, "SELECT tc.class_id, c.class_name 
                                        FROM teacher_class tc
                                        JOIN classes c ON tc.class_id = c.id
                                        WHERE tc.teacher_id = $teacher_id");
    
    if (!$classResult || mysqli_num_rows($classResult) === 0) {
        // Try alternate table name
        $classResult = mysqli_query($conn, "SELECT tc.class_id, c.class_name 
                                           FROM teacher_classes tc
                                           JOIN classes c ON tc.class_id = c.id
                                           WHERE tc.teacher_id = $teacher_id");
        
        if (!$classResult || mysqli_num_rows($classResult) === 0) {
            die("Assigned class not found for this teacher.");
        }
    }
    
    $classRow = mysqli_fetch_assoc($classResult);
    $class_id = $classRow['class_id'];
    $class_name = $classRow['class_name'];
    
    // Get students in this class
    $studentQuery = mysqli_query($conn, "SELECT s.id, s.name, u.username
                                        FROM students s
                                        JOIN users u ON s.user_id = u.id
                                        WHERE s.class_id = $class_id
                                        ORDER BY s.name");
    
    if ($studentQuery && mysqli_num_rows($studentQuery) > 0) {
        while ($row = mysqli_fetch_assoc($studentQuery)) {
            $student_id = $row['id'];
            
            // Get report card data for this student
            $reportQuery = mysqli_query($conn, "SELECT subject, marks
                                              FROM report_cards
                                              WHERE student_id = $student_id AND teacher_id = $teacher_id
                                              ORDER BY subject");
            
            $reports = [];
            $total_marks = 0;
            $subject_count = 0;
            
            if ($reportQuery && mysqli_num_rows($reportQuery) > 0) {
                while ($reportRow = mysqli_fetch_assoc($reportQuery)) {
                    $reports[] = $reportRow;
                    $total_marks += $reportRow['marks'];
                    $subject_count++;
                }
            }
            
            $average = $subject_count > 0 ? round($total_marks / $subject_count, 2) : 0;
            
            $row['reports'] = $reports;
            $row['average'] = $average;
            $row['subject_count'] = $subject_count;
            $students[] = $row;
        }
    }
}
// For student role
else if ($role == 'student') {
    // Get student information
    $studentQuery = mysqli_query($conn, "SELECT s.id, s.name, s.class_id, c.class_name
                                        FROM students s
                                        JOIN classes c ON s.class_id = c.id
                                        WHERE s.user_id = $user_id");
    
    if ($studentQuery && mysqli_num_rows($studentQuery) > 0) {
        $studentRow = mysqli_fetch_assoc($studentQuery);
        $student_id = $studentRow['id'];
        $class_id = $studentRow['class_id'];
        $class_name = $studentRow['class_name'];
        
        // Get report card data
        $reportQuery = mysqli_query($conn, "SELECT rc.subject, rc.marks, t.name as teacher_name
                                          FROM report_cards rc
                                          JOIN teachers t ON rc.teacher_id = t.id
                                          WHERE rc.student_id = $student_id
                                          ORDER BY rc.subject");
        
        $reports = [];
        $total_marks = 0;
        $subject_count = 0;
        
        if ($reportQuery && mysqli_num_rows($reportQuery) > 0) {
            while ($reportRow = mysqli_fetch_assoc($reportQuery)) {
                $reports[] = $reportRow;
                $total_marks += $reportRow['marks'];
                $subject_count++;
            }
        }
        
        $average = $subject_count > 0 ? round($total_marks / $subject_count, 2) : 0;
        
        $students[] = [
            'id' => $student_id,
            'name' => $studentRow['name'],
            'reports' => $reports,
            'average' => $average,
            'subject_count' => $subject_count
        ];
    } else {
        die("Student record not found.");
    }
}
// For admin role
else if ($role == 'admin') {
    $selected_class = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;
    
    // Get all classes
    $classesQuery = mysqli_query($conn, "SELECT id, class_name FROM classes ORDER BY class_name");
    $classes = [];
    
    if ($classesQuery && mysqli_num_rows($classesQuery) > 0) {
        while ($row = mysqli_fetch_assoc($classesQuery)) {
            $classes[] = $row;
            
            // Use the first class if none selected
            if ($selected_class == 0) {
                $selected_class = $row['id'];
            }
            
            // Set class name if this is the selected class
            if ($selected_class == $row['id']) {
                $class_name = $row['class_name'];
                $class_id = $row['id'];
            }
        }
    }
    
    // Get students in the selected class
    if ($selected_class > 0) {
        $studentQuery = mysqli_query($conn, "SELECT s.id, s.name, u.username
                                           FROM students s
                                           JOIN users u ON s.user_id = u.id
                                           WHERE s.class_id = $selected_class
                                           ORDER BY s.name");
        
        if ($studentQuery && mysqli_num_rows($studentQuery) > 0) {
            while ($row = mysqli_fetch_assoc($studentQuery)) {
                $student_id = $row['id'];
                
                // Get all report card data for this student
                $reportQuery = mysqli_query($conn, "SELECT rc.subject, rc.marks, t.name as teacher_name
                                                  FROM report_cards rc
                                                  JOIN teachers t ON rc.teacher_id = t.id
                                                  WHERE rc.student_id = $student_id
                                                  ORDER BY rc.subject");
                
                $reports = [];
                $total_marks = 0;
                $subject_count = 0;
                
                if ($reportQuery && mysqli_num_rows($reportQuery) > 0) {
                    while ($reportRow = mysqli_fetch_assoc($reportQuery)) {
                        $reports[] = $reportRow;
                        $total_marks += $reportRow['marks'];
                        $subject_count++;
                    }
                }
                
                $average = $subject_count > 0 ? round($total_marks / $subject_count, 2) : 0;
                
                $row['reports'] = $reports;
                $row['average'] = $average;
                $row['subject_count'] = $subject_count;
                $students[] = $row;
            }
        }
    }
}

// Get letter grade from numeric grade
function getLetterGrade($marks) {
    if ($marks >= 90) return 'A+';
    if ($marks >= 85) return 'A';
    if ($marks >= 80) return 'A-';
    if ($marks >= 75) return 'B+';
    if ($marks >= 70) return 'B';
    if ($marks >= 65) return 'B-';
    if ($marks >= 60) return 'C+';
    if ($marks >= 55) return 'C';
    if ($marks >= 50) return 'C-';
    if ($marks >= 40) return 'D';
    return 'F';
}

// Get CSS class for grade styling
function getGradeClass($marks) {
    if ($marks >= 90) return 'grade-a-plus';
    if ($marks >= 80) return 'grade-a';
    if ($marks >= 70) return 'grade-b';
    if ($marks >= 60) return 'grade-c';
    if ($marks >= 50) return 'grade-d';
    return 'grade-f';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Report Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .report-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .report-card-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
            border-radius: 8px 8px 0 0;
        }
        .report-card-body {
            padding: 15px;
        }
        .subject-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .subject-row:last-child {
            border-bottom: none;
        }
        .grade {
            font-weight: 600;
            width: 80px;
            text-align: right;
        }
        .subject {
            flex-grow: 1;
        }
        .teacher {
            font-size: 0.85em;
            color: #6c757d;
            width: 30%;
            text-align: center;
        }
        .average {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-top: 1px solid #ddd;
            text-align: right;
            font-weight: 600;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
        }
        .grade-a-plus { color: #198754; }
        .grade-a { color: #20c997; }
        .grade-b { color: #0d6efd; }
        .grade-c { color: #fd7e14; }
        .grade-d { color: #ffc107; }
        .grade-f { color: #dc3545; }
        .print-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            .container {
                width: 100%;
                max-width: 100%;
            }
            .card {
                border: none;
                box-shadow: none;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2>Report Cards - <?php echo htmlspecialchars($class_name); ?></h2>
        <div>
            <button onclick="window.print()" class="btn btn-outline-secondary me-2">
                <i class="fas fa-print"></i> Print
            </button>
            <?php if ($role == 'teacher'): ?>
            <a href="upload_report_cards.php" class="btn btn-primary">
                <i class="fas fa-upload"></i> Upload Marks
            </a>
            <?php else: ?>
            <a href="../dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($role == 'admin' && count($classes) > 0): ?>
    <div class="card mb-4 no-print">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label for="class_id" class="col-form-label">Select Class:</label>
                </div>
                <div class="col-auto">
                    <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo ($class['id'] == $class_id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (count($students) == 0): ?>
    <div class="alert alert-info">
        No report cards found for this class.
    </div>
    <?php else: ?>
    
    <div class="row">
        <?php foreach ($students as $student): ?>
        <div class="col-md-6 mb-4">
            <div class="report-card">
                <div class="report-card-header">
                    <h5 class="mb-0"><?php echo htmlspecialchars($student['name']); ?></h5>
                    <?php if ($role != 'student'): ?>
                    <small class="text-muted">Username: <?php echo htmlspecialchars($student['username']); ?></small>
                    <?php endif; ?>
                </div>
                <div class="report-card-body">
                    <?php if (empty($student['reports'])): ?>
                    <div class="no-data">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>No grades recorded yet.</p>
                    </div>
                    <?php else: ?>
                    <?php foreach ($student['reports'] as $report): ?>
                    <div class="subject-row">
                        <div class="subject"><?php echo htmlspecialchars($report['subject']); ?></div>
                        <?php if (isset($report['teacher_name']) && ($role == 'admin' || $role == 'student')): ?>
                        <div class="teacher"><?php echo htmlspecialchars($report['teacher_name']); ?></div>
                        <?php endif; ?>
                        <div class="grade <?php echo getGradeClass($report['marks']); ?>">
                            <?php echo $report['marks']; ?>% 
                            <small>(<?php echo getLetterGrade($report['marks']); ?>)</small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($student['reports'])): ?>
                <div class="average">
                    Overall Average: 
                    <span class="<?php echo getGradeClass($student['average']); ?>">
                        <?php echo $student['average']; ?>% 
                        <small>(<?php echo getLetterGrade($student['average']); ?>)</small>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
</body>
</html>