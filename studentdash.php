<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: studentlogin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="studentdash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <script>
        window.onunload = function() {
        };
    </script>
    <div class="dashboard-container">
        <div class="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-circle user-avatar"></i>
                <h2>Student Panel</h2>
            </div>
            
            <div class="sidebar-nav">
                <ul class="nav-links">
                    <li class="category"><a href="studentdash.php">DASHBOARD</a></li>
                    <li><a href="register.php"><i class="fas fa-file-alt"></i> Admission Form</a></li>
                    <li><a href="status.php"><i class="fas fa-list"></i>Check Admission Status </a></li>
                    <li><a href="viewform.php"><i class="fas fa-envelope"></i>Check Your Form </a></li>
                </ul>
            </div>
            
            <div class="logout-container">
                <a href="studentlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="main-content">
            <header class="main-header">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1>Student Dashboard</h1>
                </div>
                <div class="header-right-icons">
                    <a href="index.php" class="home-icon-link">
                    <i class="fas fa-home"></i>
                    </a>
                </div>
            </header>

            <section class="dashboard-content">
                <div class="dashboard-card welcome-card">
                    <div class="success-message">
                        Entered In Successfully
                    </div>
                    <div class="welcome-message">
                        <h2>Welcome to Student Dashboard</h2>
                        <p>Apply for your course - Admission Form</p>
                        <a href="register.php"> <button class="apply-now-btn">APPLY NOW</button></a>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <p>&copy; DREAM COLLEGE, Pokhara, UP-208002</p>
            </footer>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>
</html>