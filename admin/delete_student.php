<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Check if student id is provided
if (isset($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Delete the student
    $delete_sql = "DELETE FROM users WHERE id = $student_id AND role = 'student'";
    
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: manage_students.php");
        exit();
    } else {
        echo "Error deleting student: " . mysqli_error($conn);
    }
} else {
    header("Location: manage_students.php");
    exit();
}
?>
