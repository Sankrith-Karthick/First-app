<?php 
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Fetch all teachers from the teachers table
$sql = "SELECT * FROM teachers ORDER BY full_name";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #f8f9fa;
        }

        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)) !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .container {
            max-width: 1200px;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            border-left: 5px solid var(--secondary-color);
        }

        .page-header h2 {
            color: var(--primary-color);
            margin: 0;
            font-weight: 600;
        }

        .teachers-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .table {
            margin: 0;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.9rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #e9ecef;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
            transition: background-color 0.3s ease;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-warning:hover {
            background-color: #e67e22;
            border-color: #e67e22;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: #c0392b;
            border-color: #c0392b;
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
        }

        .btn-success:hover {
            background-color: #229954;
            border-color: #229954;
            transform: translateY(-2px);
        }

        .action-buttons {
            white-space: nowrap;
        }

        .teacher-id {
            font-weight: 600;
            color: var(--primary-color);
        }

        .teacher-name {
            font-weight: 500;
            color: var(--primary-color);
        }

        .teacher-email {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .add-teacher-section {
            text-align: center;
            margin-top: 2rem;
        }

        .no-teachers {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }

        .no-teachers i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-user-shield me-2"></i>Admin Panel
        </a>
        <div class="navbar-nav ms-auto">
            <a href="dashboard.php" class="btn btn-light me-2">
                <i class="fas fa-dashboard me-1"></i>Dashboard
            </a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="page-header">
        <h2><i class="fas fa-chalkboard-teacher me-2"></i>Manage Teachers</h2>
    </div>

    <div class="teachers-table">
        <?php if (mysqli_num_rows($result) > 0) : ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-1"></i>ID</th>
                            <th><i class="fas fa-user me-1"></i>Full Name</th>
                            <th><i class="fas fa-envelope me-1"></i>Email</th>
                            <th><i class="fas fa-id-card me-1"></i>Username</th>
                            <th><i class="fas fa-book me-1"></i>Subject</th>
                            <th><i class="fas fa-phone me-1"></i>Emergency Contact</th>
                            <th><i class="fas fa-cogs me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($teacher = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td class="teacher-id">#<?php echo htmlspecialchars($teacher['id']); ?></td>
                                <td class="teacher-name"><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                                <td class="teacher-email"><?php echo htmlspecialchars($teacher['email']); ?></td>
                                <td><?php echo htmlspecialchars($teacher['Username']); ?></td>
                                <td>
                                    <?php if (!empty($teacher['subject'])) : ?>
                                        <span class="badge bg-info"><?php echo htmlspecialchars($teacher['subject']); ?></span>
                                    <?php else : ?>
                                        <span class="text-muted">Not assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($teacher['emergency_contact']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_teacher.php?id=<?php echo $teacher['id']; ?>" 
                                       class="btn btn-warning btn-sm me-1" 
                                       title="Edit Teacher">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_teacher.php?id=<?php echo $teacher['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');"
                                       title="Delete Teacher">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="no-teachers">
                <i class="fas fa-user-times"></i>
                <h4>No Teachers Found</h4>
                <p>There are currently no teachers in the system. Add a new teacher to get started.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="add-teacher-section">
        <a href="add_teacher.php" class="btn btn-success">
            <i class="fas fa-plus me-2"></i>Add New Teacher
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>