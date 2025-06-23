<?php
session_start();
require '../config.php';
require '../vendor/autoload.php'; // PhpSpreadsheet autoloader

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch teacher data
$teacher_query = "SELECT * FROM teachers WHERE user_id = $user_id";
$teacher_result = mysqli_query($conn, $teacher_query);
$teacher = mysqli_fetch_assoc($teacher_result);

if (!$teacher) {
    die("❌ Teacher not found.");
}

$class_id = $teacher['class_id'];
$section_id = $teacher['section_id'];
$teacher_id = $teacher['id'];

// Get students from the same class and section
$students_query = "SELECT * FROM students WHERE class_id = $class_id AND section_id = $section_id";
$students_result = mysqli_query($conn, $students_query);

// Handle form submission
if (isset($_POST['upload']) && isset($_FILES['report_file'])) {
    $term = mysqli_real_escape_string($conn, $_POST['term']);
    $file = $_FILES['report_file']['tmp_name'];

    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $highestRow = $sheet->getHighestRow();
    $highestCol = $sheet->getHighestColumn();

    for ($row = 2; $row <= $highestRow; $row++) {
        $student_id = $sheet->getCell("A$row")->getValue();

        // Validate student in same class
        $check_student = mysqli_query($conn, "SELECT id FROM students WHERE id = '$student_id' AND class_id = $class_id AND section_id = $section_id");
        if (mysqli_num_rows($check_student) == 0) {
            continue;
        }

        $col = 'B';
        while ($col <= $highestCol) {
            $subject = $sheet->getCell($col . '1')->getValue();
            $marks = $sheet->getCell($col . $row)->getValue();

            if ($subject && is_numeric($marks)) {
                $subject = mysqli_real_escape_string($conn, $subject);
                $marks = floatval($marks);

                $insert = "INSERT INTO report_cards (student_id, subject, marks, term, teacher_id)
                           VALUES ('$student_id', '$subject', '$marks', '$term', '$teacher_id')";
                mysqli_query($conn, $insert);
            }
            $col++;
        }
    }

    $success = "✅ Report cards uploaded successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Report Cards</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <div class="container">
    <div class="ml-auto">
      <a href="dashboard.php" class="btn btn-light">Back</a>
    </div>  
  </div>
</nav>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h3 class="mb-4">Upload Report Cards</h3>

    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" enctype="multipart/form-data" class="border p-4 bg-white shadow-sm">
        <div class="mb-3">
            <label for="term" class="form-label">Select Term</label>
            <select name="term" class="form-select" required>
                <option value="">-- Select Term --</option>
                <option value="Term 1">Term 1</option>
                <option value="Term 2">Term 2</option>
                <option value="Final">Final</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="report_file" class="form-label">Upload Excel File</label>
            <input type="file" name="report_file" class="form-control" accept=".xls,.xlsx" required>
            <div class="form-text">Format: Column A = Student ID, Row 1 = Subject Names, Cells = Marks</div>
        </div>

        <button type="submit" name="upload" class="btn btn-primary">Upload Report Cards</button>
    </form>

    <hr class="my-5">

    <h5>Students in Your Class (<?= $class_id ?> - Section <?= $section_id ?>)</h5>
    <table class="table table-bordered mt-3">
        <thead>
        <tr>
            <th>Student ID</th>
            <th>Name</th>
            <th>Parent Email</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($student = mysqli_fetch_assoc($students_result)) { ?>
            <tr>
                <td><?= $student['id'] ?></td>
                <td><?= $student['first_name'] . ' ' . $student['last_name'] ?></td>
                <td><?= $student['registered_email'] ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
</body>
</html>
