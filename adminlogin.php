<?php
session_start();

$correct_username = "admin";
$correct_password = "admin#123";

$logout_message = '';
if (isset($_SESSION['form_message'])) {
    $logout_message = $_SESSION['form_message'];
    unset($_SESSION['form_message']);
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $correct_username && $password === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_success'] = true;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: adminlogin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Dream College</title>
    <link rel="stylesheet" href="admindashlogin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .header {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .header .home-link {
            position: absolute;
            left: 20px;
            color: white;
            font-size: 1.5em;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .header .home-link:hover {
            color: #f0f0f0;
        }
        .logout-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="index.php" class="home-link" aria-label="Go to Home Page">
            <i class="fas fa-home"></i>
        </a>
        DREAM <span>COLLEGE</span>
    </div>
    <div class="login-container">
        <div class="login-box">
            <h2>Admin Login</h2>
            <?php
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            ?>
            <form action="adminlogin.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
    <footer>
        <div class="footer-content">
            <p>DREAM COLLEGE, Pokhara, UP-208002</p>
            <p>Contact us: 9807021238</p>
        </div>
    </footer>
    <script>
        window.addEventListener('popstate', function() {
            window.location.href = 'index.php';
        });
    </script>
</body>
</html>