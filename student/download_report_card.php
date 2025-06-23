<?php
// Start output buffering at the very beginning of the script
ob_start();

// Check for whitespace or BOM at beginning of file (common cause of this error)
// Make sure this file is saved with no BOM and no whitespace before <?php

require_once '../config.php';
require_once '../vendor/autoload.php'; // mPDF

use Mpdf\Mpdf;

// Error handling
try {
    // Validate inputs
    if (!isset($_GET['student_id']) || !isset($_GET['term'])) {
        throw new Exception("❌ Invalid access. Required parameters missing.");
    }

    $student_id = intval($_GET['student_id']);
    $term = mysqli_real_escape_string($conn, $_GET['term']);

    // Fetch student info
    $student_query = mysqli_query($conn, "SELECT * FROM students WHERE id = $student_id");
    if (!$student_query) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }
    
    $student = mysqli_fetch_assoc($student_query);
    if (!$student) {
        throw new Exception("❌ Student not found.");
    }

    // Fetch report card data
    $query = "SELECT subject, marks FROM report_cards WHERE student_id = $student_id AND term = '$term'";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        throw new Exception("Database error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) === 0) {
        throw new Exception("❌ No report card data found for this term.");
    }

    // Create HTML content for PDF
    $html = "
        <h2 style='text-align:center;'>Report Card - {$term}</h2>
        <p><strong>Student:</strong> {$student['full_name']}</p>
        <p><strong>Class:</strong> {$student['class_id']} - Section {$student['section_id']}</p>
        <table border='1' cellpadding='8' cellspacing='0' width='100%' style='border-collapse: collapse;'>
            <thead>
                <tr style='background-color:#f2f2f2;'>
                    <th>Subject</th>
                    <th>Marks</th>
                </tr>
            </thead>
            <tbody>";

    while ($row = mysqli_fetch_assoc($result)) {
        $html .= "<tr><td>{$row['subject']}</td><td>{$row['marks']}</td></tr>";
    }

    $html .= "</tbody></table>";

    // Before PDF generation, clear any output that might be in buffer
    ob_clean();

    // Generate and output PDF
    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);
    $mpdf->Output("report_card_{$student_id}_{$term}.pdf", 'D'); // 'D' for download
    
    // End output buffering and discard any remaining content
    ob_end_clean();
    exit;
    
} catch (Exception $e) {
    // Clean the buffer
    ob_end_clean();
    // Output the error
    echo "Error: " . $e->getMessage();
    exit;
}