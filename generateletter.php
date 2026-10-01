<?php
session_start();

// Ensure the admin is logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Assume db_connect.php is in the same directory and contains the $conn variable.
include 'db_connect.php';

$student_details = null;
$letter_details = null;

// Check if an application ID is provided in the URL.
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $application_id = intval($_GET['id']);

    // Fetch the application details, including the linked letter_content_id.
    // The columns `name`, `course_id`, and `letter_content_id` exist in the `applications` table.
    $stmt = $conn->prepare("SELECT `name`, `course_id`, `letter_content_id` FROM `applications` WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student_details = $result->fetch_assoc();
        $stmt->close();
    }

    // Now, use the course_id to get the course name.
    if ($student_details && !empty($student_details['course_id'])) {
        $stmt_course = $conn->prepare("SELECT `name` FROM `courses` WHERE `id` = ?");
        if ($stmt_course) {
            $stmt_course->bind_param("i", $student_details['course_id']);
            $stmt_course->execute();
            $result_course = $stmt_course->get_result();
            if ($row_course = $result_course->fetch_assoc()) {
                $student_details['course_name'] = $row_course['name'];
            }
            $stmt_course->close();
        }
    }

    // Finally, fetch the letter content using the letter_content_id.
    // The columns `title` and `body` exist in the `letter_content` table.
    $stmt_letter = $conn->prepare("SELECT `title`, `body` FROM `letter_content` WHERE `id` = ?");
    if ($stmt_letter) {
        $stmt_letter->bind_param("i", $student_details['letter_content_id']);
        $stmt_letter->execute();
        $result_letter = $stmt_letter->get_result();
        $letter_details = $result_letter->fetch_assoc();
        $stmt_letter->close();
    }
}

// Close the database connection.
$conn->close();

// If the student or letter details are not found, redirect to a status page.
if (!$student_details || !$letter_details) {
    header("Location: status.php");
    exit();
}

// Replace the placeholders in the letter body with the actual student and course names.
$letter_body = str_replace(
    ['[[student_name]]', '[[course_name]]'],
    [htmlspecialchars($student_details['name']), htmlspecialchars($student_details['course_name'])],
    $letter_details['body']
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approval Letter</title>
    <style>
        /* General body styling */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            line-height: 1.6;
            margin: 0;
        }

        /* Header styling */
        .header {
            background-color: #f7a600;
            color: white;
            padding: 20px 30px;
            font-size: 2.2em;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            letter-spacing: 1.8px;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
        }

        /* Navigation styling */
        .main-nav {
            background-color: #f7d497;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .main-nav a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 12px;
            border-radius: 5px;
            transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease;
            font-weight: bold;
            position: relative;
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        .main-nav a:hover::after,
        .main-nav a.active::after {
            width: calc(100% - 24px);
            background-color: #f7a600;
        }

        .main-nav a:hover,
        .main-nav a.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #f0f2f5;
        }

        /* Sticky header and nav effects on scroll */
        .header.scrolled {
            background-color: rgba(255, 255, 255, 0.9);
            color: black;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .main-nav.scrolled {
            background-color: rgba(255, 255, 255, 0.85);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .main-nav.scrolled a {
            color: black;
        }

        .main-nav.scrolled a:hover::after,
        .main-nav.scrolled a.active::after {
            background-color: #f7a600;
        }

        .main-nav.scrolled a:hover,
        .main-nav.scrolled a.active {
            background-color: rgba(0, 0, 0, 0.1);
            color: #f7a600;
        }

        /* Letter container and content styling */
        .letter-container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #fff;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .sub-header {
            text-align: center;
            border-bottom: 2px solid #f7a600;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .logo {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            font-family: 'Times New Roman', Times, serif;
            font-weight: bolder;
            font-size: larger;
        }

        .logo img {
            max-height: 90px;
            width: auto;
            margin-right: 15px;
        }

        .sub-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 5px;
        }

        .sub-header p {
            color: #777;
            font-size: 1em;
            margin: 0;
        }

        .letter-content {
            font-size: 1.1em;
            font-family: 'Times New Roman', Times, serif;
        }

        .letter-content p {
            margin-bottom: 20px;
        }

        .signature {
            margin-top: 50px;
            text-align: right;
        }

        .signature h3 {
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .highlight {
            color: #f7a600;
            font-weight: 600;
        }

        .button-group {
            text-align: center;
            margin-top: 40px;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
            background-color: #2ecc71;
            box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);
        }

        .btn:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }

        /* Watermark image */
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 70%;
            opacity: 0.1;
            z-index: 0;
            pointer-events: none;
        }

        /* Footer styling */
        footer {
            text-align: center;
            background-color: #f7a600;
            color: #333;
            padding: 25px 20px;
            font-size: 0.95em;
            margin-top: 50px;
            border-top: 1px solid #e0e0e0;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        }

        .footer-container p {
            margin-bottom: 8px;
            line-height: 1.5;
        }

        /* Print styles to hide non-letter elements */
        @media print {
            body {
                background-color: #fff;
            }
            .header, .main-nav, footer, .button-group {
                display: none;
            }
            .letter-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: none;
            }
            .watermark {
                opacity: 0.15;
            }
        }

        /* Responsive styles for smaller screens */
        @media (max-width: 768px) {
            .header {
                font-size: 2em;
                padding: 20px 20px;
            }
            .main-nav {
                flex-direction: column;
                padding: 10px 0;
            }
            .main-nav a {
                display: block;
                margin: 8px auto;
                width: calc(100% - 40px);
                text-align: center;
                padding: 12px 0;
            }
            footer {
                padding: 20px 15px;
            }
            .logo {
                font-size: 24px;
                margin-bottom: 15px;
            }
            .logo img {
                max-height: 80px;
            }
        }

        @media (max-width: 480px) {
            .header {
                font-size: 1.7em;
                padding: 15px 10px;
            }
            footer {
                font-size: 0.85em;
            }
            .logo {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="header" id="mainHeader">
        DREAM COLLEGE
    </div>

    <nav class="main-nav" id="mainNav">
        <a href="index.php">Home</a>
        <a href="index.php#aboutCampusSection">About us</a>
        <a href="course.php">Courses</a>
        <a href="index.php#contactussection">Contact Us</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="updateletter.php">Approval Letter</a>
        <a href="updatecourse.php">Update Course</a>
        <a href="view_application.php">Application</a>
    </nav>
    <div class="letter-container">
        <img src="logo.jpg" alt="Dream College Logo" class="watermark">
        <div class="sub-header">
            <div class="logo">
                <img src="logo.jpg" alt="College Logo">
                <h1>DREAM COLLEGE</h1>
            </div>
            <p>123 College Lane, Pokhara, UP-208002</p>
            <p><?= htmlspecialchars($letter_details['title']) ?></p>
        </div>
        <div class="letter-content">
            <?= nl2br($letter_body) ?>
        </div>
        <div class="signature">
            <p>Sincerely,</p>
            <h3>The Admissions Team</h3>
            <p>Dream College</p>
        </div>
        <div class="button-group">
            <button class="btn" onclick="window.print()">Print This Letter</button>
        </div>
    </div>

    <footer>
        <div class="footer-container">
            <p>DREAM COLLEGE, Pokhara, UP-208002</p>
            <p>Contact us: 9807021238</p>
        </div>
    </footer>

    <script>
        // JavaScript for sticky header and nav bar
        const header = document.getElementById('mainHeader');
        const nav = document.getElementById('mainNav');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
                nav.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
                nav.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
