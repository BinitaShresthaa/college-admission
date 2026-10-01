<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true) {
    header("Location: studentdash.php");
    exit();
}

$error = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Dream College</title>
    <link rel="stylesheet" href="studentlogin.css">
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
        .message {
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
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
        <div class="login-box" id="login-box">
            <h2>Student Login</h2>
            <?php
            if (!empty($error)) {
                echo '<p class="error-message">' . htmlspecialchars($error) . '</p>';
            }
            if (isset($_SESSION['error'])) {
                echo '<p class="error-message">' . htmlspecialchars($_SESSION['error']) . '</p>';
                unset($_SESSION['error']);
            }
            if (isset($_SESSION['success'])) {
                echo '<p class="success-message">' . htmlspecialchars($_SESSION['success']) . '</p>';
                unset($_SESSION['success']);
            }
            ?>
            <form action="student_account.php" method="POST">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn" name="login">Login</button>
            </form>
            <p class="toggle-form-message">Don't have an account? <a href="#" id="show-signup">Sign Up</a></p>
        </div>

        <div class="login-box" id="signup-box" style="display:none;">
            <h2>Student Sign Up</h2>
            <form action="student_account.php" method="POST">
                <div class="input-group">
                    <label for="signup-email">Email</label>
                    <input type="email" id="signup-email" name="email" required>
                </div>
                <div class="input-group">
                    <label for="signup-password">Password</label>
                    <input type="password" id="signup-password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="confirm-password">Confirm Password</label>
                    <input type="password" id="confirm-password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn" name="signup">Sign Up</button>
            </form>
            <p class="toggle-form-message">Already have an account? <a href="#" id="show-login">Login</a></p>
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

        document.getElementById('show-signup').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('login-box').style.display = 'none';
            document.getElementById('signup-box').style.display = 'block';
        });

        document.getElementById('show-login').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('signup-box').style.display = 'none';
            document.getElementById('login-box').style.display = 'block';
        });
    </script>
</body>
</html>