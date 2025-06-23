<?php session_start(); if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {     header("Location: /auth/login.php");     exit(); } ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3a6ea5;
            --secondary-color: #ff9e2c;
            --dark-color: #344055;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background-color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .welcome-text {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            padding: 1rem;
            border: none;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .action-btn {
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2d5a8b;
            border-color: #2d5a8b;
        }
        
        .btn-warning {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e68d1f;
            border-color: #e68d1f;
            color: white;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
        }
        
        .footer {
            text-align: center;
            padding: 1rem 0;
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 2rem;
        }
        
        .icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .logout-container {
            text-align: center;
            margin-top: 1rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 0.75rem;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
            
            .header {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }
            
            .user-info {
                position: static;
                justify-content: flex-end;
                margin-bottom: 1rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <span><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Teacher'; ?></span>
            </div>
            <h1 class="welcome-text">Welcome to Your Teacher Dashboard</h1>
            <p class="subtitle">Manage your classroom resources and communications</p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Report Cards
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-file-excel icon"></i>
                        <p>Upload student report cards in Excel format</p>
                        <a href="upload_report_cards.php" class="btn btn-primary action-btn">
                            <i class="fas fa-upload"></i> Upload Report Cards
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Announcements
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-bullhorn icon"></i>
                        <p>Check important announcements for your class</p>
                        <a href="announcements.php" class="btn btn-warning action-btn">
                            <i class="fas fa-bell"></i> View Announcements
                        </a>
                    </div>
                   
                </div>
            </div>
        </div>
        <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Send Email
                    </div>
         <div class="card-body text-center">
                        <i class="fas fa-bullhorn icon"></i>
                        <p>Send important Email to students!</p>
                        <a href="send_email.php" class="btn btn-warning action-btn">
                            <i class="fas fa-bell"></i> Send Email
                        </a>
                    </div>
        </div>
     <div class="logout-container">
            <a href="logout.php" class="btn btn-danger action-btn" style="max-width: 200px; margin: 0 auto;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>