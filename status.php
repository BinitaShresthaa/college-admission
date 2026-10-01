<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: studentlogin.php");
    exit();
}
include 'db_connect.php';

$result_message = "";
$status_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['student_email'];
    
    $stmt = $conn->prepare("SELECT id, status FROM applications WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $student_id = $row['id'];
        $status = $row["status"];
        
        switch (strtolower($status)) {
            case 'approved':
                header("Location: generateletter.php?id=" . $student_id);
                exit();
                break;
            case 'pending':
                $result_message = "⏳ Your application is currently pending. Please check back later.";
                $status_class = "pending";
                break;
            case 'rejected':
                $result_message = "❌ We regret to inform you that your application was not accepted at this time.";
                $status_class = "rejected";
                break;
            default:
                $result_message = "Your application status is: " . htmlspecialchars($status);
                $status_class = "neutral";
                break;
        }
    } else {
        $result_message = "No application found for the provided email.";
        $status_class = "error";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Your Status</title>
    <link rel="stylesheet" href="studentdash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 500px;
            box-sizing: border-box;
        }
        .title {
            font-size: 2em;
            color: #1a202c;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="email"] {
            width: calc(100% - 24px);
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            color: #4a5568;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus {
            outline: none;
            border-color: #4299e1;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #4299e1;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        button:hover {
            background-color: #2b6cb0;
            transform: translateY(-2px);
        }
        .result {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1em;
        }
        .approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .pending {
            background-color: #fffbeb;
            color: #92400e;
        }
        .rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .error {
            background-color: #fecaca;
            color: #b91c1c;
        }
        .neutral {
            background-color: #e2e8f0;
            color: #4a5568;
        }
        .result p {
            margin: 0;
            line-height: 1.5;
        }
        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 100%;
            min-height: 100vh;
            padding: 20px;
            box-sizing: border-box;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #718096;
        }
    </style>
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
                    <li><a href="viewform.php"><i class="fas fa-envelope"></i>Check Your Form  </a></li>
                </ul>
            </div>
            
            <div class="logout-container">
                <a href="studentlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        <div class="main-content">
                <button class="menu-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>            
            <div class="container">
                <h3 class="title">Check Your Application Status</h3>
                <form method="POST" action="">
                    <div class="form-group">
                        <input type="email" name="student_email" placeholder="Enter your email address" required>
                    </div>
                    <button type="submit">Check Status</button>
                </form>
                
                <?php if ($result_message): ?>
                    <div class="result <?php echo $status_class; ?>">
                        <p><?php echo $result_message; ?></p>
                    </div>
                <?php endif; ?>

                <footer class="footer">
                    <p>&copy; DREAM COLLEGE, Pokhara, UP-208002</p>
                </footer>
            </div>
        </div>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
    </script>
</body>
</html>
