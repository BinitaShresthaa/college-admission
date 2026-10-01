<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit(); 
}

// Security best practice: Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include 'db_connect.php';

$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = '';
    
    // Check which button was clicked
    if (isset($_POST['add_course'])) {
        $action = 'add';
    } elseif (isset($_POST['update_course'])) {
        $action = 'update';
    } elseif (isset($_POST['delete_course'])) {
        $action = 'delete';
    }

    switch ($action) {
        case 'add':
            // Check if required fields are set
            if (isset($_POST['name']) && isset($_POST['description']) && isset($_POST['fee'])) {
                $name = $_POST['name'];
                $description = $_POST['description'];
                $fee = floatval($_POST['fee']); // Convert fee to float for consistency
                
                $sql = "INSERT INTO courses (name, description, fee) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssd", $name, $description, $fee); 
                    if ($stmt->execute()) {
                        header("Location: updatecourse.php?status=add_success");
                        exit();
                    } else {
                        $message = "Error adding course: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing statement for add: " . $conn->error;
                }
            } else {
                $message = "Error: Missing required fields for adding a course.";
            }
            break;

        case 'update':
            // Check if required fields are set and ID is not empty
            if (isset($_POST['id']) && !empty($_POST['id']) && isset($_POST['name']) && isset($_POST['description']) && isset($_POST['fee'])) {
                $id = intval($_POST['id']); // Sanitize ID
                $name = $_POST['name'];
                $description = $_POST['description'];
                $fee = floatval($_POST['fee']); // Convert fee to float
                
                $sql = "UPDATE courses SET name=?, description=?, fee=? WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ssdi", $name, $description, $fee, $id); 
                    if ($stmt->execute()) {
                        header("Location: updatecourse.php?status=update_success");
                        exit();
                    } else {
                        $message = "Error updating course: " . $stmt->error . " (Course ID: " . $id . ")";
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing statement for update: " . $conn->error;
                }
            } else {
                $message = "Error: Missing required fields for updating a course.";
            }
            break;

        case 'delete':
            // Check if ID is set and not empty
            if (isset($_POST['id']) && !empty($_POST['id'])) {
                $id = intval($_POST['id']); // Sanitize ID
                $sql = "DELETE FROM courses WHERE id=?";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $id);
                    if ($stmt->execute()) {
                        header("Location: updatecourse.php?status=delete_success");
                        exit();
                    } else {
                        $message = "Error deleting course: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $message = "Error preparing statement for delete: " . $conn->error;
                }
            } else {
                $message = "Error: Missing required ID for deleting a course.";
            }
            break;
    }
}

if (isset($_GET['status'])) {
    if ($_GET['status'] == 'add_success') {
        $message = "New course added successfully!";
    } elseif ($_GET['status'] == 'update_success') {
        $message = "Course updated successfully!";
    } elseif ($_GET['status'] == 'delete_success') {
        $message = "Course deleted successfully!";
    }
}

$sql = "SELECT id, name, description, fee FROM courses"; 
$result = $conn->query($sql);

// Check if result is valid before trying to fetch
if ($result === false) {
    die("Error fetching courses: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="updatecourse.css">
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
                    <li><a href="view_application.php"><i class="fas fa-check-square"></i> Update Status</a></li>
                    <li><a href="updatecourse.php" class="active"><i class="fas fa-book-open"></i> Update Course</a></li>
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
                    <h1> Update Course</h1>
                </div>
                <div class="header-right-icons">
                    <a href="index.php" class="home-icon-link">
                    <i class="fas fa-home"></i>
                    </a>
                </div>
            </header>

            <section class="dashboard-content">
                <?php if (!empty($message)): ?>
                    <div class="success-message"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>

                <div class="form-card dashboard-card">
                    <h2>Add/Edit Course</h2>
                    <form action="updatecourse.php" method="post" id="courseForm">
                        <input type="hidden" name="id" id="course_id">
                        <label for="course_name">Course Name:</label>
                        <input type="text" name="name" placeholder="Course Name" id="course_name" required>
                        <label for="course_description">Course Description:</label>
                        <textarea name="description" placeholder="Course Description" id="course_description" rows="4" required></textarea>
                        <label for="course_fee">Course Fee:</label>
                        <input type="number" name="fee" placeholder="Course Fee" id="course_fee" step="0.01" required>
                        <div class="form-actions">
                            <button type="submit" name="add_course" class="submit-btn" id="submit_button">Add Course</button>
                            <button type="button" class="reset-btn" id="reset_button" style="display: none;">Cancel Edit / Add New</button>
                        </div>
                    </form>
                </div>

                <div class="list-card dashboard-card">
                    <h2>Existing Courses</h2>
                    <div class="course-list">
                        <?php
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<div class='course-list-item'>";
                                echo "<div><h3>" . htmlspecialchars($row['name']) . "</h3>";
                                echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                                echo "<p><strong>Fee: </strong>Rs" . htmlspecialchars(number_format($row['fee'], 2)) . "</p></div>";
                                echo "<div class='course-actions'>";
                                
                                echo "<button class='edit-btn' ";
                                echo "data-id='" . htmlspecialchars($row['id'], ENT_QUOTES) . "' ";
                                echo "data-name='" . htmlspecialchars($row['name'], ENT_QUOTES) . "' ";
                                echo "data-description='" . htmlspecialchars($row['description'], ENT_QUOTES) . "' ";
                                echo "data-fee='" . htmlspecialchars($row['fee'], ENT_QUOTES) . "'>Edit</button>";
                                
                                echo "<form action='updatecourse.php' method='post' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete this course?\");'>";
                                echo "<input type='hidden' name='id' value='" . htmlspecialchars($row['id'], ENT_QUOTES) . "'>";
                                echo "<button type='submit' name='delete_course' class='delete-btn'>Delete</button>";
                                echo "</form>";
                                
                                echo "</div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No courses found. Add a new one using the form above.</p>";
                        }
                        ?>
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
        const submitButton = document.getElementById('submit_button');
        const resetButton = document.getElementById('reset_button');
        const courseId = document.getElementById('course_id');
        const courseName = document.getElementById('course_name');
        const courseDescription = document.getElementById('course_description');
        const courseFee = document.getElementById('course_fee');
        const courseForm = document.getElementById('courseForm');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        document.querySelector('.course-list').addEventListener('click', (event) => {
            if (event.target.classList.contains('edit-btn')) {
                const button = event.target;
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const description = button.getAttribute('data-description');
                const fee = button.getAttribute('data-fee');
                
                courseId.value = id;
                courseName.value = name;
                courseDescription.value = description;
                courseFee.value = fee;
                submitButton.name = 'update_course';
                submitButton.textContent = 'Update Course';
                resetButton.style.display = 'inline-block';
                
                const formCard = document.querySelector('.form-card');
                if (formCard) {
                    formCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });

        resetButton.addEventListener('click', () => {
            courseForm.reset();
            courseId.value = '';
            submitButton.name = 'add_course';
            submitButton.textContent = 'Add Course';
            resetButton.style.display = 'none';
        });
    </script>
</body>
</html>