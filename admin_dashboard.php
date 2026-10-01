<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    echo '<script>';
    echo 'history.replaceState(null, null, "admin_dashboard.php");';
    echo 'history.pushState(null, null, "admin_dashboard.php");';
    echo '</script>';
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="admindash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
   
</head>
<body>
    <script>
        window.onunload = function() {
        };
    </script>

    <div class="dashboard-container">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <i class="fas fa-user-circle user-avatar"></i>
                <h2>Admin Panel</h2>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav-links">
                    <li class="category"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="view_application.php"><i class="fas fa-check-square"></i>Update Status</a></li>
                    <li><a href="updatecourse.php"><i class="fas fa-book-open"></i> Update Course</a></li>
                    <li><a href="updateletter.php"><i class="fas fa-file-alt"></i> Approval Letter</a></li>
                </ul>
            </nav>
            
            <div class="logout-container">
                <a href="adminlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>

        <main class="main-content" id="mainContent">
            <header class="main-header">
                <button id="menu-toggle" class="menu-toggle"><i class="fas fa-bars"></i></button>
                <div class="header-title">
                    <h1>Admin Dashboard</h1>
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
                        Logged In Successfully!
                    </div>
                    <div class="welcome-message">
                        <h2>Welcome to Admin Dashboard</h2>
                        <p>This is where you can manage all college admissions and student applications.</p>
                        <a href="view_application.php" class="update-btn">Update Application Status <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </section>

            <footer class="footer">
                <p>&copy; DREAM COLLEGE, Pokhara, UP-208002</p>
            </footer>
        </main>
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