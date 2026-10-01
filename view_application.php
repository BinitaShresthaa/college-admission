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

$courses = [];
$course_query = "SELECT id, name FROM courses ORDER BY name ASC";
$course_result = $conn->query($course_query);

if ($course_result && $course_result->num_rows > 0) {
    while($row = $course_result->fetch_assoc()) {
        $courses[] = $row;
    }
}

if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize input
    $delete_stmt = $conn->prepare("DELETE FROM applications WHERE id = ?");
    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $delete_id);
        if ($delete_stmt->execute()) {
            $_SESSION['form_message'] = "Student application with ID " . htmlspecialchars($delete_id) . " has been deleted successfully.";
        } else {
            $_SESSION['form_message'] = "Error deleting record: " . $delete_stmt->error;
        }
        $delete_stmt->close();
        header("Location: view_application.php");
        exit();
    } else {
        $_SESSION['form_message'] = "Error preparing delete statement: " . $conn->error;
    }
}

$student_query = "SELECT 
                    s.id, 
                    s.name, 
                    s.email, 
                    s.status, 
                    s.applied_date, 
                    c.name AS course_name 
                  FROM 
                    applications s
                  LEFT JOIN 
                    courses c ON s.course = c.name"; 
                  
$student_query = "SELECT 
                    s.id, 
                    s.name, 
                    s.email, 
                    s.status, 
                    s.applied_date, 
                    c.name AS course_name 
                  FROM 
                    applications s
                  LEFT JOIN 
                    courses c ON s.course_id = c.id"; 
$conditions = [];
$params = [];
$types = '';

if (isset($_GET['course_filter']) && $_GET['course_filter'] !== '') {
    $course_id_query = "SELECT id FROM courses WHERE name = ?";
    $stmt_course_id = $conn->prepare($course_id_query);
    $stmt_course_id->bind_param("s", $_GET['course_filter']);
    $stmt_course_id->execute();
    $result_course_id = $stmt_course_id->get_result()->fetch_assoc();
    $stmt_course_id->close();
    
    if ($result_course_id) {
        $conditions[] = "s.course_id = ?";
        $params[] = $result_course_id['id'];
        $types .= 'i';
    }
}


if (isset($_GET['status_filter']) && $_GET['status_filter'] !== '') {
    $conditions[] = "s.status = ?";
    $params[] = $_GET['status_filter'];
    $types .= 's'; 
}

if (isset($_GET['search']) && $_GET['search'] !== '') {
    $conditions[] = "(s.name LIKE ? OR s.email LIKE ?)";
    $searchTerm = "%" . $_GET['search'] . "%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ss'; 
}

if (!empty($conditions)) {
    $student_query .= " WHERE " . implode(" AND ", $conditions);
}

$student_query .= " ORDER BY s.id DESC";

$students = [];
$stmt = $conn->prepare($student_query);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $student_result = $stmt->get_result();

    if ($student_result && $student_result->num_rows > 0) {
        while($row = $student_result->fetch_assoc()) {
            $students[] = $row;
        }
    }
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

$conn->close();

$display_modal_message = '';
if (isset($_SESSION['form_message'])) {
    $display_modal_message = $_SESSION['form_message'];
    unset($_SESSION['form_message']); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Applications - Admin</title>
    <link rel="stylesheet" href="admindash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
.student-table {
    width: 100%; 
    margin: 20px 0; 
    border-collapse: collapse;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    background-color: var(--card-background); 
    border-radius: 8px; 
    overflow: hidden; 
}
.student-table th, .student-table td {
    border: 1px solid #ddd;
    padding: 12px 15px; 
    text-align: left;
    font-size: 0.95em; 
}
.student-table th {
    background-color:cornflowerblue; 
    color: var(--text-light); 
    font-weight: bold; 
    text-transform: uppercase; 
    font-size: 0.85em; 
    letter-spacing: 0.5px; 
}
.student-table thead tr:first-child th:first-child {
    border-top-left-radius: 8px;
}
.student-table thead tr:first-child th:last-child {
    border-top-right-radius: 8px;
}

.student-table tr:nth-child(even) {
    background-color: #f9f9f9;
}
.student-table tr:hover {
    background-color: #f1f1f1;
    transition: background-color 0.2s ease-in-out; 
}

.student-table td:last-child {
    display: flex;
    gap: 8px; 
    align-items: center; 
}
.view-btn{
    background-color: #4CAF50; 
    color: white;
    padding: 8px 15px; 
    border: none;
    border-radius: 5px; 
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9em;
    margin-right: 8px; 
    display: inline-block; 
    transition: opacity 0.2s ease, transform 0.2s ease; 
}
.view-btn:hover, .approve-btn:hover, .reject-btn:hover {
    opacity: 0.9; 
    transform: translateY(-1px); 
    box-shadow: 0 2px 5px rgba(0,0,0,0.1); 
}
.delete-btn {
    background-color: #dc3545;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none;
    font-size: 0.9em;
    margin-right: 8px;
    display: inline-block;
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.delete-btn:hover {
    background-color: #c82333;
    opacity: 0.9;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.4);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background-color: #fefefe;
    padding: 30px; 
    border-radius: 12px; 
    box-shadow: 0 8px 16px rgba(0,0,0,0.2); 
    max-width: 550px; 
    min-width: 300px;
    text-align: center;
    position: relative;
    animation: fadeIn 0.3s ease-out;
}

.modal-close-btn {
    position: absolute;
    top: 15px; 
    right: 20px; 
    color: #aaa;
    font-size: 30px; 
    font-weight: bold;
    cursor: pointer;
}

.modal-close-btn:hover,
.modal-close-btn:focus {
    color: #000;
    text-decoration: none;
    cursor: pointer;
}

.modal-message {
    white-space: pre-wrap;
    font-size: 1.1em; 
    line-height: 1.6; 
    margin-bottom: 15px; 
}

.modal-btn { 
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 12px 30px; 
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 15px;
}
.modal-btn:hover {
    background-color: #e69500;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px; 
    margin-bottom: 20px;
    padding: 20px;
    background-color: #f0f4f7; 
    border-radius: 8px; 
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
}

form label {
    font-weight: 600;
    color: #555;
    font-size: 0.9em;
    min-width: 80px; 
}

form select, form input[type="text"] {
    flex: 1; 
    padding: 10px 12px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1em;
    min-width: 150px; 
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

form select:focus, form input[type="text"]:focus {
    border-color: #5cb3fd; 
    box-shadow: 0 0 5px rgba(92, 179, 253, 0.5); 
    outline: none;
}

form button[type="submit"] {
    background-color: #4CAF50; 
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s ease, transform 0.2s ease;
    flex-shrink: 0; 
}

form button[type="submit"]:hover {
    background-color: #45a049;
    transform: translateY(-1px); 
}
@media (max-width: 768px) {
    .student-table th, .student-table td {
        padding: 10px 8px;
        font-size: 0.85em; 
    }
    .view-btn, .approve-btn, .reject-btn {
        padding: 6px 10px; 
        font-size: 0.8em;
        margin-right: 5px;
        margin-bottom: 5px; 
    }
    form {
        flex-direction: column;
        align-items: stretch;
    }
    form label, form select, form input, form button {
        width: 100%; 
        min-width: auto;
    }
    .modal-content {
        padding: 20px;
        max-width: 90%; 
    }
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
                    <li class="category"><a href="admin_dashboard.php">Dashboard</a></li>
                    <li><a href="view_application.php" class="active"><i class="fas fa-check-square"></i>Update Status</a></li>
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
                    <h1>Student Applications</h1>
                </div>
                <div class="header-right-icons">
                    <a href="index.php" class="home-icon-link">
                    <i class="fas fa-home"></i>
                    </a>
                </div>
            </header>

            <section class="dashboard-content">
                <?php if (!empty($display_modal_message)): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            showModal("<?php echo addslashes(htmlspecialchars($display_modal_message)); ?>", true, 3000);
                        });
                    </script>
                <?php endif; ?>

                <div class="dashboard-card">
                    <form action="view_application.php" method="GET" style="margin-bottom: 20px;">
                        <label for="course_filter">Filter by Course:</label>
                        <select name="course_filter" id="course_filter">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['name']); ?>" 
                                    <?php echo (isset($_GET['course_filter']) && $_GET['course_filter'] === $course['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="status_filter">Filter by Status:</label>
                        <select name="status_filter" id="status_filter">
                            <option value="">All Status</option>
                            <option value="Pending" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="Approved" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="Rejected" <?php echo (isset($_GET['status_filter']) && $_GET['status_filter'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
                        </select>
                        <label for="search">Search:</label>
                        <input type="text" id="search" name="search" placeholder="Name or Email" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <button type="submit" class="view-btn">Apply Filter</button>
                        <a href="view_application.php" class="view-btn">Back</a>
                    </form>

                    <?php if (!empty($students)): ?>
                        <table class="student-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Applied On</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($students as $student): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                        <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($student['applied_date']))); ?></td>
                                        <td><?php echo htmlspecialchars($student['status']); ?></td>
                                        <td>
                                            <a href="viewstudentdetail.php?id=<?php echo htmlspecialchars($student['id']); ?>" class="view-btn">View</a>
                                            <a href="view_application.php?delete_id=<?php echo htmlspecialchars($student['id']); ?>" class="delete-btn" onclick="return confirm('Are you sure you want to delete this application?');">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No student applications found matching the criteria.</p>
                    <?php endif; ?>
                </div>
            </section>

            <footer class="footer">
                <p>&copy; DREAM COLLEGE, Pokhara, UP-208002</p>
            </footer>
        </main>
    </div>

    <div id="customModal" class="modal">
        <div class="modal-content">
            <span class="modal-close-btn" onclick="closeModal()">&times;</span>
            <p class="modal-message"></p>
        </div>
    </div>

    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');

        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });

        let modalTimeout;

        function showModal(message, autoClose = false, duration = 3000) {
            const modal = document.getElementById('customModal');
            const messageEl = modal.querySelector('.modal-message');
            messageEl.innerText = message;
            modal.style.display = 'flex';

            if (modalTimeout) {
                clearTimeout(modalTimeout);
            }

            if (autoClose) {
                modalTimeout = setTimeout(() => {
                    closeModal();
                }, duration);
            }
        }

        function closeModal() {
            document.getElementById('customModal').style.display = 'none';
            if (modalTimeout) {
                clearTimeout(modalTimeout);
                modalTimeout = null;
            }
        }
    </script>
</body>
</html>