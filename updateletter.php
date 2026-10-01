<?php
session_start();

// Ensure the admin is logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit(); 
}

include 'db_connect.php';

// Initialize variables.
$message = '';
$letter_content = ['title' => '', 'body' => ''];

// Get the letter ID from the URL, or default to 1.
$letter_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch the existing letter content for the specified ID.
$stmt = $conn->prepare("SELECT title, body FROM letter_content WHERE id = ?"); 
$stmt->bind_param("i", $letter_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $letter_content = $result->fetch_assoc();
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_title = $_POST['title'];
    $new_body = $_POST['body'];
    $new_letter_id = $_POST['letter_id']; // Get the ID from a hidden or input field.

    // Check if the record already exists.
    $check_stmt = $conn->prepare("SELECT id FROM letter_content WHERE id = ?");
    $check_stmt->bind_param("i", $new_letter_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Record exists, so update it.
        $stmt = $conn->prepare("UPDATE letter_content SET title = ?, body = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssi", $new_title, $new_body, $new_letter_id);
            if ($stmt->execute()) {
                $message = "Letter content updated successfully! ✅";
                $letter_content['title'] = $new_title;
                $letter_content['body'] = $new_body;
            } else {
                $message = "Error updating letter content. ❌";
            }
            $stmt->close();
        }
    } else {
        // Record does not exist, so insert a new one.
        $stmt = $conn->prepare("INSERT INTO letter_content (id, title, body) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("iss", $new_letter_id, $new_title, $new_body);
            if ($stmt->execute()) {
                $message = "Letter content saved successfully! ✅";
                $letter_content['title'] = $new_title;
                $letter_content['body'] = $new_body;
            } else {
                $message = "Error saving new letter content. ❌";
            }
            $stmt->close();
        }
    }
    
    $check_stmt->close();
    $letter_id = $new_letter_id; // Update the current ID to the one that was just saved/updated.
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Letter</title>
    <!-- Assuming updatecourse.css and font-awesome are in place -->
    <link rel="stylesheet" href="updatecourse.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General form and container styling */
        .form-card { max-width: 800px; margin: 0 auto; }
        .info {
            background-color: #f9f9f9; 
            border-left: 4px solid #f7a600; 
            padding: 15px;
            margin-top: 20px;
            font-size: 0.9em;
            color: #555;
            border-radius: 4px; 
            }
        .highlight { font-weight: bold; color: #f7a600; }
        #preview-box { 
            margin-top: 20px; 
            padding: 20px; 
            border: 1px dashed #ccc; 
            border-radius: 6px; 
            background: #fafafa; 
            display: none; 
            white-space: pre-line; 
            font-family: Georgia, serif; 
            line-height: 1.6; 
        }
        .preview-title { 
            font-weight: bold; 
            font-size: 1.2em; 
            margin-bottom: 15px; 
            color: #333; 
        }
        form .preview-btn {
            background-color: #6edee8ff;
            color: #ffffffff;
            border: none;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        form .preview-btn:hover {
            background-color: #7fd6deff;
            transform: translateY(-2px);
        }
    </style>
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
                    <li class="category"><a href="admin_dashboard.php"> Dashboard</a></li>
                    <li><a href="view_application.php"><i class="fas fa-check-square"></i> Update Status</a></li>
                    <li><a href="updatecourse.php"><i class="fas fa-book-open"></i> Update Course</a></li>
                    <li><a href="updateletter.php" class="active"><i class="fas fa-file-alt"></i> Approval Letter</a></li>
                </ul>
            </nav>
            <div class="logout-container">
                <a href="adminlogout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </aside>
        <div class="main-content">
            <header class="main-header">
                <button class="menu-toggle" id="menu-toggle"><i class="fas fa-bars"></i></button>
                <h1 class="header-title">Update Admission Letter</h1>
                <div class="header-right-icons">
                    <a href="index.php" class="home-icon-link">
                    <i class="fas fa-home"></i>
                    </a>
                </div>
            </header>

            <div class="dashboard-content">
                <?php if ($message): ?>
                    <div class="success-message" style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; margin-bottom: 20px; border-radius: 4px;"><?= $message ?></div>
                <?php endif; ?>
                <div class="form-card">
                    <h2>Edit Approval Letter (ID: <?= htmlspecialchars($letter_id) ?>)</h2>
                    <form action="updateletter.php" method="POST">
                        <input type="hidden" name="letter_id" value="<?= htmlspecialchars($letter_id) ?>">
                        <label for="title">Letter Title:</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($letter_content['title']) ?>" required>
                        <label for="body">Letter Body:</label>
                        <textarea id="body" name="body" rows="15" required><?= htmlspecialchars($letter_content['body']) ?></textarea>
                        <p class="info">
                            Use placeholders to insert dynamic content: 
                            <br>
                            <span class="highlight">[[student_name]]</span> for the student's name.
                            <br>
                            <span class="highlight">[[course_name]]</span> for the course name.
                        </p>
                        <button type="button" onclick="previewLetter()" class="preview-btn">👁️ Preview Letter</button>
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </form>
                    <div id="preview-box">
                        <div class="preview-title" id="preview-title"><?= htmlspecialchars($letter_content['title']) ?></div>
                        <div id="preview-body"></div>
                    </div>
                </div>
            </div>
            <footer class="footer">
                <p>&copy; <?php echo date("Y"); ?> DREAM COLLEGE. All rights reserved.</p>
            </footer>
        </div>
    </div>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const previewBox = document.getElementById('preview-box');
        menuToggle.addEventListener('click', () => { sidebar.classList.toggle('active'); });
        function previewLetter() {
            const title = document.getElementById('title').value;
            const body = document.getElementById('body').value;
            let previewText = body
                .replace(/\[\[student_name\]\]/g, "Student Name")
                .replace(/\[\[course_name\]\]/g, "Course Name");
            previewText = previewText.replace(/\n/g, '<br>');
            document.getElementById('preview-title').textContent = title;
            document.getElementById('preview-body').innerHTML = previewText;
            previewBox.style.display = 'block';
            previewBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    </script>
</body>
</html>
