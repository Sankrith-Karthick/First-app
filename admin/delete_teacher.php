<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Check if teacher id is provided
if (isset($_GET['id'])) {
    $teacher_id = intval($_GET['id']);

    // Delete the teacher
    $delete_sql = "DELETE FROM users WHERE id = $teacher_id AND role = 'teacher'";
    
    if (mysqli_query($conn, $delete_sql)) {
        header("Location: manage_teachers.php");
        exit();
    } else {
        echo "Error deleting teacher: " . mysqli_error($conn);
    }
} else {
    header("Location: manage_teachers.php");
    exit();
}
?>
