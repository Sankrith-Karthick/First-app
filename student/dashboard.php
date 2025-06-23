<?php  
session_start(); 
require_once '../config.php';  

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
$student_id = null;

if ($user_role == 'student') {
    $student_query = "SELECT s.id AS student_id, CONCAT(s.first_name, ' ', s.last_name) AS name, c.class_name, sec.section_name
                      FROM students s
                      JOIN classes c ON s.class_id = c.id
                      JOIN sections sec ON s.section_id = sec.id
                      WHERE s.user_id = $user_id";
} elseif ($user_role == 'parent') {
    $parent_email_query = "SELECT email FROM users WHERE id = $user_id";
    $parent_result = mysqli_query($conn, $parent_email_query);
    $parent_email = '';

    if ($parent_result && mysqli_num_rows($parent_result) > 0) {
        $parent_data = mysqli_fetch_assoc($parent_result);
        $parent_email = $parent_data['email'];

        $student_query = "SELECT s.id AS student_id, CONCAT(s.first_name, ' ', s.last_name) AS name, c.class_name, sec.section_name
                          FROM students s
                          JOIN classes c ON s.class_id = c.id
                          JOIN sections sec ON s.section_id = sec.id
                          WHERE s.registered_email = '$parent_email'";
    } else {
        $student_query = "SELECT s.id AS student_id, CONCAT(s.first_name, ' ', s.last_name) AS name, c.class_name, sec.section_name
                          FROM students s
                          JOIN classes c ON s.class_id = c.id
                          JOIN sections sec ON s.section_id = sec.id
                          WHERE s.user_id = $user_id";
    }
} else {
    header("Location: ../login.php");
    exit();
}

$student_result = mysqli_query($conn, $student_query);
if (!$student_result) {
    $error_message = "Database error: " . mysqli_error($conn);
}
$student = ($student_result && mysqli_num_rows($student_result) > 0) ? mysqli_fetch_assoc($student_result) : null;

$report_cards = []; 
if ($student) {
    $student_id = $student['student_id'];
    $report_query = "SELECT subject, marks, term FROM report_cards WHERE student_id = $student_id";
    $report_result = mysqli_query($conn, $report_query);

    if ($report_result) {
        while ($row = mysqli_fetch_assoc($report_result)) {
            $report_cards[] = $row;
        }
    }
}

// Handle PDF download BEFORE any HTML output
if (isset($_GET['download_pdf']) && $student && !empty($report_cards)) {
    require_once '../tcpdf/tcpdf.php'; // Include TCPDF only when needed
    
    // Clear any output buffer
    if (ob_get_length()) {
        ob_clean();
    }
    
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('School System');
    $pdf->SetTitle('Report Card');
    $pdf->AddPage();

    $html = "<h2>Report Card</h2>";
    $html .= "<p><strong>Name:</strong> {$student['name']}</p>";
    $html .= "<p><strong>Class:</strong> {$student['class_name']}</p>";
    $html .= "<p><strong>Section:</strong> {$student['section_name']}</p>";
    $html .= "<br><table border='1' cellpadding='5'>
                <thead>
                    <tr><th>Term</th><th>Subject</th><th>Marks</th></tr>
                </thead><tbody>";

    foreach ($report_cards as $report) {
        $html .= "<tr><td>{$report['term']}</td><td>{$report['subject']}</td><td>{$report['marks']}</td></tr>";
    }

    $html .= "</tbody></table>";

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('report_card.pdf', 'D');
    exit();
}

$page_title = ($user_role == 'parent') ? 'Parent Dashboard' : 'Student Dashboard';
$welcome_message = ($user_role == 'parent') ? 'Welcome, Parent' : 'Welcome, Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-light: #6366f1;
            --primary-dark: #3730a3;
            --secondary-color: #06b6d4;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: var(--gray-800);
            line-height: 1.6;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }

        .header-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .welcome-subtitle {
            font-size: 1.1rem;
            color: var(--gray-600);
            font-weight: 400;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .card-title i {
            color: var(--primary-color);
            font-size: 1.25rem;
        }

        .student-info-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
            background: var(--gray-50);
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s ease;
        }

        .student-info-item:hover {
            background: var(--gray-100);
            transform: translateX(5px);
        }

        .student-info-item i {
            color: var(--primary-color);
            margin-right: 1rem;
            font-size: 1.1rem;
            width: 20px;
        }

        .student-info-label {
            font-weight: 600;
            color: var(--gray-700);
            margin-right: 0.5rem;
        }

        .student-info-value {
            color: var(--gray-600);
            font-weight: 500;
        }

        .report-card-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .table-modern {
            margin: 0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table-modern thead {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
        }

        .table-modern thead th {
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border: none;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr {
            transition: all 0.3s ease;
            border: none;
        }

        .table-modern tbody tr:hover {
            background: var(--gray-50);
            transform: scale(1.01);
        }

        .table-modern tbody td {
            padding: 1rem 1.5rem;
            border: none;
            border-bottom: 1px solid var(--gray-200);
            font-weight: 500;
        }

        .table-modern tbody tr:last-child td {
            border-bottom: none;
        }

        .marks-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .marks-excellent {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }

        .marks-good {
            background: linear-gradient(135deg, var(--secondary-color), #0891b2);
            color: white;
        }

        .marks-average {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
            color: white;
        }

        .marks-poor {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
        }

        .download-section {
            margin-top: 2rem;
            text-align: center;
        }

        .btn-download {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-md);
        }

        .btn-download:hover {
            background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .btn-download:active {
            transform: translateY(-1px);
        }

        .alert-modern {
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-md);
            font-weight: 500;
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }

        .alert-warning {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            color: var(--warning-color);
            border-left: 4px solid var(--warning-color);
        }

        .no-data-message {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--gray-500);
        }

        .no-data-message i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .no-data-message h4 {
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }

            .welcome-title {
                font-size: 2rem;
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .header-section,
            .info-card,
            .report-card-table {
                padding: 1.5rem;
            }

            .table-modern thead th,
            .table-modern tbody td {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            .btn-download {
                padding: 0.75rem 1.5rem;
                font-size: 0.9rem;
            }
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header-section fade-in">
            <h1 class="welcome-title">
                <i class="fas fa-graduation-cap"></i>
                <?= $welcome_message ?>
            </h1>
            <p class="welcome-subtitle">
                <?= isset($_SESSION['name']) ? $_SESSION['name'] : 'Dashboard' ?>
            </p>
        </div>

        <!-- Error Message -->
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-modern fade-in">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?= $error_message ?>
            </div>
        <?php endif; ?>

        <?php if ($student): ?>
            <!-- Dashboard Grid -->
            <div class="dashboard-grid fade-in">
                <!-- Student Information Card -->
                <div class="info-card">
                    <h4 class="card-title">
                        <i class="fas fa-user-graduate"></i>
                        Student Information
                    </h4>
                    
                    <div class="student-info-item">
                        <i class="fas fa-user"></i>
                        <span class="student-info-label">Name:</span>
                        <span class="student-info-value"><?= $student['name'] ?></span>
                    </div>
                    
                    <div class="student-info-item">
                        <i class="fas fa-school"></i>
                        <span class="student-info-label">Class:</span>
                        <span class="student-info-value"><?= $student['class_name'] ?></span>
                    </div>
                    
                    <div class="student-info-item">
                        <i class="fas fa-users"></i>
                        <span class="student-info-label">Section:</span>
                        <span class="student-info-value"><?= $student['section_name'] ?></span>
                    </div>
                </div>

                <!-- Quick Stats or Additional Info -->
                <div class="info-card">
                    <h4 class="card-title">
                        <i class="fas fa-chart-line"></i>
                        Academic Overview
                    </h4>
                    <?php if (!empty($report_cards)): ?>
                        <?php 
                        $total_subjects = count($report_cards);
                        $total_marks = array_sum(array_column($report_cards, 'marks'));
                        $average_marks = $total_marks / $total_subjects;
                        ?>
                        
                        <div class="student-info-item">
                            <i class="fas fa-book"></i>
                            <span class="student-info-label">Total Subjects:</span>
                            <span class="student-info-value"><?= $total_subjects ?></span>
                        </div>
                        
                        <div class="student-info-item">
                            <i class="fas fa-calculator"></i>
                            <span class="student-info-label">Average Score:</span>
                            <span class="student-info-value"><?= number_format($average_marks, 1) ?>%</span>
                        </div>
                        
                        <div class="student-info-item">
                            <i class="fas fa-trophy"></i>
                            <span class="student-info-label">Performance:</span>
                            <span class="student-info-value">
                                <?php 
                                if ($average_marks >= 90) echo "Excellent";
                                elseif ($average_marks >= 80) echo "Good";
                                elseif ($average_marks >= 70) echo "Average";
                                else echo "Needs Improvement";
                                ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="no-data-message">
                            <i class="fas fa-chart-line"></i>
                            <h5>No data available yet</h5>
                            <p>Academic overview will appear once report cards are added.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Report Cards Section -->
            <div class="report-card-table fade-in">
                <h4 class="card-title">
                    <i class="fas fa-file-alt"></i>
                    Report Cards
                </h4>
                
                <?php if (!empty($report_cards)): ?>
                    <div class="table-responsive">
                        <table class="table table-modern">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-calendar-alt me-2"></i>Term</th>
                                    <th><i class="fas fa-book me-2"></i>Subject</th>
                                    <th><i class="fas fa-percent me-2"></i>Marks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_cards as $report): ?>
                                    <tr>
                                        <td><?= $report['term'] ?></td>
                                        <td><?= $report['subject'] ?></td>
                                        <td>
                                            <?php 
                                            $marks = $report['marks'];
                                            $class = '';
                                            if ($marks >= 90) $class = 'marks-excellent';
                                            elseif ($marks >= 80) $class = 'marks-good';
                                            elseif ($marks >= 70) $class = 'marks-average';
                                            else $class = 'marks-poor';
                                            ?>
                                            <span class="marks-badge <?= $class ?>"><?= $marks ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="download-section">
                        <a href="?download_pdf=1" class="btn-download pulse-animation">
                            <i class="fas fa-download"></i>
                            Download PDF Report
                        </a>
                    </div>
                <?php else: ?>
                    <div class="no-data-message">
                        <i class="fas fa-file-alt"></i>
                        <h4>No Report Cards Available</h4>
                        <p>Your report cards will appear here once they are uploaded by your teachers.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning alert-modern fade-in">
                <i class="fas fa-exclamation-circle me-2"></i>
                No student records found. Please contact the administrator.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling and enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to cards
            const cards = document.querySelectorAll('.info-card, .report-card-table');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Add click animation to download button
            const downloadBtn = document.querySelector('.btn-download');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            }
        });
    </script>
</body>
</html>