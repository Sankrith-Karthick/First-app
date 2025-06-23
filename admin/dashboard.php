<?php session_start();  

// Check if user is logged in and is admin 
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {     
    header('Location: /auth/login.php');     
    exit(); 
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --success-color: #11998e;
            --info-color: #38ef7d;
            --warning-color: #f093fb;
            --danger-color: #f093fb;
            --dark-color: #2c3e50;
            --light-color: #f8fafc;
            --text-muted: #64748b;
            --border-radius: 16px;
            --shadow-light: 0 4px 25px rgba(0, 0, 0, 0.06);
            --shadow-medium: 0 8px 35px rgba(0, 0, 0, 0.1);
            --shadow-heavy: 0 15px 45px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            line-height: 1.7;
            color: var(--dark-color);
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.1) 0%, transparent 50%);
            pointer-events: none;
            z-index: -1;
        }

        /* Navbar Enhanced Styling */
        .navbar {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.95) 0%, rgba(118, 75, 162, 0.95) 100%) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: var(--shadow-medium);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: scale(1.02);
        }

        .navbar-brand i {
            margin-right: 0.7rem;
            font-size: 1.3rem;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .navbar .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            border: none;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);
            border-radius: 12px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            letter-spacing: 0.02em;
        }

        .navbar .btn-danger:hover {
            background: linear-gradient(135deg, #ff5252, #d32f2f);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.4);
        }

        .navbar .btn-danger:active {
            transform: translateY(-1px) scale(0.98);
        }

        /* Content Area */
        .dashboard-container {
            padding: 3rem 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-header {
            font-weight: 800;
            font-size: 2.5rem;
            color: var(--dark-color);
            margin-bottom: 2.5rem;
            position: relative;
            padding-bottom: 1.5rem;
            letter-spacing: -0.03em;
            text-align: center;
        }

        .welcome-header:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            height: 4px;
            width: 100px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            border-radius: 2px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        /* Enhanced Card Styling */
        .dashboard-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            height: 100%;
            overflow: hidden;
            position: relative;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: inherit;
            opacity: 0.9;
            z-index: 1;
        }

        .dashboard-card * {
            position: relative;
            z-index: 2;
        }

        .dashboard-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-heavy);
        }

        .dashboard-card .card-body {
            padding: 2.5rem;
            position: relative;
        }

        .card-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1.2rem;
            letter-spacing: -0.02em;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .card-text {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-size: 1.05rem;
            line-height: 1.6;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Enhanced Card Colors with Gradients */
        .bg-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
        }

        .bg-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        }

        /* Enhanced Button Styling */
        .dashboard-btn {
            border-radius: 12px;
            padding: 0.8rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            letter-spacing: 0.02em;
            position: relative;
            overflow: hidden;
        }

        .dashboard-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .dashboard-btn:hover::before {
            left: 100%;
        }

        .dashboard-btn i {
            margin-right: 0.7rem;
            font-size: 1.1rem;
        }

        .btn-light {
            background: rgba(255, 255, 255, 0.95);
            color: var(--dark-color);
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .btn-light:hover {
            background: rgba(255, 255, 255, 1);
            color: var(--dark-color);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .btn-light:active {
            transform: translateY(-1px) scale(0.98);
        }

        /* Enhanced Card Icon */
        .card-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            display: block;
            opacity: 0.9;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
            transition: all 0.3s ease;
        }

        .dashboard-card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
            opacity: 1;
        }

        /* Enhanced Footer */
        .dashboard-footer {
            text-align: center;
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-top: 4rem;
            font-weight: 500;
            letter-spacing: 0.02em;
        }

        /* Responsive Enhancements */
        @media (max-width: 1200px) {
            .dashboard-container {
                padding: 2rem 1.5rem;
            }
        }

        @media (max-width: 992px) {
            .navbar {
                padding: 0.8rem 1.5rem;
            }
            
            .dashboard-container {
                padding: 2rem 1rem;
            }
            
            .welcome-header {
                font-size: 2.2rem;
            }
            
            .card-title {
                font-size: 1.4rem;
            }

            .dashboard-card .card-body {
                padding: 2rem;
            }
        }
        
        @media (max-width: 768px) {
            .welcome-header {
                font-size: 1.9rem;
                margin-bottom: 2rem;
            }

            .dashboard-card {
                margin-bottom: 2rem;
            }

            .dashboard-card .card-body {
                padding: 1.8rem;
            }

            .card-icon {
                font-size: 2.5rem;
            }

            .navbar {
                padding: 0.6rem 1rem;
            }

            .navbar-brand {
                font-size: 1.3rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-container {
                padding: 1.5rem 0.8rem;
            }

            .welcome-header {
                font-size: 1.7rem;
            }

            .dashboard-card .card-body {
                padding: 1.5rem;
            }

            .dashboard-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
        }

        /* Subtle animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .dashboard-card:nth-child(1) { animation-delay: 0.1s; }
        .dashboard-card:nth-child(2) { animation-delay: 0.2s; }
        .dashboard-card:nth-child(3) { animation-delay: 0.3s; }
        .dashboard-card:nth-child(4) { animation-delay: 0.4s; }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-school me-2"></i>
                School Admin Panel
            </a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container container">
        <h1 class="welcome-header">Welcome, <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin'; ?>!</h1>
        
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-success dashboard-card">
                    <div class="card-body">
                        <i class="fas fa-user-graduate card-icon"></i>
                        <h5 class="card-title">Manage Students</h5>
                        <p class="card-text">Add, edit or delete student information and records.</p>
                        <a href="add_student.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-plus"></i> Add Student
                        </a>
                        <a href="manage_students.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-clipboard-list"></i> Manage Students
                        </a>
                        <a href="upload_students.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-file-upload"></i> Bulk Upload
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-info dashboard-card">
                    <div class="card-body">
                        <i class="fas fa-chalkboard-teacher card-icon"></i>
                        <h5 class="card-title">Manage Teachers</h5>
                        <p class="card-text">Add, edit or delete teacher profiles and assignments.</p>
                        <a href="add_teacher.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-plus"></i> Add Teacher
                        </a>
                        <a href="manage_teachers.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-clipboard-list"></i> Manage Teachers
                        </a>
                        <a href="upload_teachers.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-file-upload"></i> Bulk Upload
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-warning dashboard-card">
                    <div class="card-body">
                        <i class="fas fa-bullhorn card-icon"></i>
                        <h5 class="card-title">Send Announcements</h5>
                        <p class="card-text">Create and send notifications to teachers and students easily.</p>
                        <a href="send_announcement.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-paper-plane"></i> Create Announcement
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card text-white bg-primary dashboard-card">
                    <div class="card-body">
                        <i class="fas fa-school card-icon"></i>
                        <h5 class="card-title">Manage Classes</h5>
                        <p class="card-text">Manage classes for teacher and students easily.</p>
                        <a href="add_class.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-plus"></i> Add Classes
                        </a>
                        <a href="add_section.php" class="btn btn-light dashboard-btn">
                            <i class="fas fa-plus"></i> Add Section
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="dashboard-footer">
            <p>School Management System &copy; <?php echo date('Y'); ?> | All Rights Reserved</p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>