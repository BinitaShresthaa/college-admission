<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: studentlogin.php");
    exit();
}
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {

    header("Location: studentlogin.php");

    exit();

}

// --- Configuration and Database Connection ---
include 'db_connect.php';

// Check for successful database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Data Retrieval and Error Handling ---
// Handle success and error messages from URL parameters
if (isset($_GET['success'])) {
    $message = ($_GET['success'] === 'update_success') ? 'Form updated successfully!' : '';
    if (!empty($message)) {
        echo "<script>alert('$message');</script>";
    }
}

// --- IMPORTANT: Handle errors passed via the URL and exit immediately ---
if (isset($_GET['error'])) {
    $error_message_alert = '';
    switch ($_GET['error']) {
        case 'course_not_found':
            $error_message_alert = 'The selected course could not be found. Please try again.';
            break;
        case 'update_failed':
            $error_message_alert = 'Update failed. Please try again.';
            break;
        case 'db_error':
            $error_message_alert = 'Database error. Please try again later.';
            break;
        case 'photo_upload_failed':
            $error_message_alert = 'Failed to upload your photo. Please check the file and try again.';
            break;
        default:
            $error_message_alert = 'An unknown error occurred.';
            break;
    }
    if (!empty($error_message_alert)) {
        // We use window.history.back() to go back to the form page to fix the issue
        echo "<script>alert('$error_message_alert'); window.history.back();</script>";
        exit(); // Exit to prevent the rest of the page from loading
    }
}

// Fetch student application data
$student_email = $_SESSION['email'];
$student_data = null;
$error_message = '';
$courses_array = [];

$sql = "SELECT a.*, c.id AS course_id, c.name AS course_name FROM applications a
        LEFT JOIN courses c ON a.course_id = c.id
        WHERE a.email = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("s", $student_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $student_data = $result->fetch_assoc();
    } else {
        $error_message = "No application form found for your email address. Please apply first.";
    }
    $stmt->close();
} else {
    $error_message = "Database query failed: " . $conn->error;
}

// Fetch all available courses
$sql_courses = "SELECT id, name FROM courses ORDER BY name ASC";
$result_courses = $conn->query($sql_courses);

if ($result_courses && $result_courses->num_rows > 0) {
    while ($row = $result_courses->fetch_assoc()) {
        // Fetch both the ID and name for the dropdown
        $courses_array[] = ['id' => $row['id'], 'name' => htmlspecialchars($row['name'])];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Application Form</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #eef2f5; margin: 0; padding: 0; }
        .header { background-color: #f7a600; color: #ffffff; padding: 20px 30px; font-size: 2.2em; font-weight: bold; text-align: center; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); letter-spacing: 1.8px; text-transform: uppercase; position: sticky; top: 0; z-index: 1000; transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease; }
        .main-nav { background-color: #f7d497; padding: 15px 20px; text-align: center; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); transition: background-color 0.3s ease, box-shadow 0.3s ease; }
        .main-nav a { color: rgb(255, 255, 255); text-decoration: none; padding: 10px 20px; margin: 0 12px; border-radius: 5px; transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease; font-weight: bold; position: relative; }
        .main-nav a::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 50%; transform: translateX(-50%); background-color: #333; transition: width 0.3s ease, background-color 0.3s ease; }
        .main-nav a:hover::after, .main-nav a.active::after { width: calc(100% - 24px); background-color: #333; }
        .main-nav a:hover, .main-nav a.active { background-color: rgba(255, 255, 255, 0.15); color: #fff; }
        .header.scrolled { background-color: rgba(255, 255, 255, 0.9); color: black; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); }
        .main-nav.scrolled { background-color: rgba(255, 255, 255, 0.85); box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15); }
        .main-nav.scrolled a { color: black; }
        .main-nav.scrolled a::after { background-color: #333; transition: width 0.3s ease, background-color 0.3s ease; }
        .main-nav.scrolled a:hover::after, .main-nav.scrolled a.active::after { background-color: #f7a600; }
        .main-nav.scrolled a:hover, .main-nav.scrolled a.active { background-color: rgba(0, 0, 0, 0.1); color: #f7a600; }
        .form-container { max-width: 900px; margin: 40px auto; padding: 30px; background-color: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); }
        .form-container h2 { font-size: 1.8em; color: #2c3e50; margin-bottom: 25px; text-align: center; font-weight: 600; }
        .form-box fieldset { border: 1px solid #e0e0e0; border-radius: 8px; padding: 20px; margin-bottom: 25px; background-color: #fafafa; }
        .form-box legend { font-size: 1.2em; font-weight: 600; color: #2c3e50; padding: 0 10px; }
        .form-box label { font-weight: bold; display: block; margin-bottom: 8px; color: #34495e; margin-top: 15px; }
        .form-box input, .form-box select { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 8px; box-sizing: border-box; background-color: #fefefe; transition: border-color 0.3s; }
        .form-box input:focus, .form-box select:focus { outline: none; border-color: #f7a600; }
        .file-upload-info { font-size: 0.9em; color: #555; margin-top: 5px; }
        .file-upload-info a { color: #3498db; text-decoration: none; }
        .file-upload-info a:hover { text-decoration: underline; }
        .button-group { display: flex; justify-content: center; gap: 15px; margin-top: 30px; }
        .btn-update, .btn-delete { padding: 12px 25px; border: none; border-radius: 8px; cursor: pointer; font-size: 1em; font-weight: 600; color: white; transition: background-color 0.3s, transform 0.2s; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .btn-update { background-color: #2ecc71; }
        .btn-update:hover { background-color: #27ae60; transform: translateY(-2px); }
        .btn-delete { background-color: #e74c3c; }
        .btn-delete:hover { background-color: #c0392b; transform: translateY(-2px); }
        .alert { padding: 20px; margin-bottom: 20px; border-radius: 8px; font-weight: 600; font-size: 1.1em; color: #c0392b; background-color: #fef0f0; border: 1px solid #e74c3c; text-align: center; }
        footer { text-align: center; background-color: #f7a600; color: #333; padding: 25px 20px; font-size: 0.95em; margin-top: 50px; border-top: 1px solid #e0e0e0; box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05); }
        .footer-container p { margin-bottom: 8px; line-height: 1.5; }
        @media (max-width: 768px) {
            .header { font-size: 2em; padding: 20px 20px; }
            .main-nav { flex-direction: column; padding: 10px 0; }
            .main-nav a { display: block; margin: 8px auto; width: calc(100% - 40px); text-align: center; padding: 12px 0; }
            .form-container { margin: 20px auto; padding: 15px; }
            footer { padding: 20px 15px; }
        }
        @media (max-width: 480px) {
            .header { font-size: 1.7em; padding: 15px 10px; }
            footer { font-size: 0.85em; }
        }
    </style>
</head>
<body>
    <script>
        // Use a more robust check for confirming page leave
        window.addEventListener('beforeunload', function (e) {
            // Check if the form has been modified
            const form = document.querySelector('form');
            if (form && form.dataset.dirty === 'true') {
                e.preventDefault();
                e.returnValue = ''; // Required for some browsers
            }
        });
        
        // Add a listener to set the dirty state
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('change', function() {
                    form.dataset.dirty = 'true';
                });
            }
        });
    </script>
    <div class="header" id="mainHeader">
        DREAM COLLEGE
    </div>

    <nav class="main-nav" id="mainNav">
        <a href="index.php">Home</a>
        <a href="index.php#aboutCampusSection">About us</a>
        <a href="course.php">Courses</a>
        <a href="index.php#contactussection">Contact Us</a>
        <a href="studentdash.php">Dashboard</a>
        <a href="status.php">Check Admission Status</a>
        <a href="viewform.php" class="active">Check Your Form</a>
    </nav>

    <div class="form-container">
        <h2>My Application Form</h2>
        <?php if ($student_data): ?>
            <form action="update_delete_db.php" method="POST" enctype="multipart/form-data" class="form-box" novalidate>
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($student_data['id']); ?>">
                <input type="hidden" name="original_photo" value="<?php echo htmlspecialchars($student_data['photo']); ?>">
                <input type="hidden" name="original_marksheet_10" value="<?php echo htmlspecialchars($student_data['marksheet_10']); ?>">
                <input type="hidden" name="original_marksheet_12" value="<?php echo htmlspecialchars($student_data['marksheet_12']); ?>">
                <input type="hidden" name="original_id_proof" value="<?php echo htmlspecialchars($student_data['id_proof']); ?>">

                <fieldset>
                    <legend>Personal Details</legend>
                    <label for="name">Name:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($student_data['name']); ?>" required><br>
                    <label for="father_name">Father's Name:</label>
                    <input type="text" name="father_name" value="<?php echo htmlspecialchars($student_data['father_name']); ?>" required><br>
                    <label for="mother_name">Mother's Name:</label>
                    <input type="text" name="mother_name" value="<?php echo htmlspecialchars($student_data['mother_name']); ?>" required><br>
                    <label for="dob">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($student_data['dob']); ?>" required><br>
                    <label for="gender">Gender:</label>
                    <select id="gender" name="gender" required>
                        <option value="Male" <?php if ($student_data['gender'] === 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($student_data['gender'] === 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($student_data['gender'] === 'Other') echo 'selected'; ?>>Other</option>
                    </select><br>
                </fieldset>

                <fieldset>
                    <legend>Contact Details</legend>
                    <label for="address">Address:</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($student_data['address']); ?>" required><br>
                    <label for="email">Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($student_data['email']); ?>" required readonly><br>
                    <label for="phone_number">Phone no:</label>
                    <input type="tel" name="phone_number" value="<?php echo htmlspecialchars($student_data['phone_number']); ?>" pattern="[0-9]{10}" maxlength="10" required><br>
                </fieldset>

                <fieldset>
                    <legend>Academic Details</legend>
                    <label for="current_course">Course Applied:</label>
                    <input type="text" id="current_course" value="<?php echo htmlspecialchars($student_data['course_name']); ?>" readonly><br>
                    <label for="course">Update Course:</label>
                    <select name="course" id="course" required>
                        <option value="">-- Please select a course --</option>
                        <?php foreach ($courses_array as $course): ?>
                            <!-- Use the course ID as the value to send to the server -->
                            <option value="<?php echo $course['id']; ?>" <?php if ($student_data['course_id'] === $course['id']) echo 'selected'; ?>>
                                <?php echo $course['name']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select><br>
                    <label for="marks_10">10th Marks:</label>
                    <input type="number" id="marks_10" name="marks_10" value="<?php echo htmlspecialchars($student_data['marks_10']); ?>" step="0.01" min="0" max="100" required><br>
                    <label for="marks_12">12th Marks:</label>
                    <input type="number" id="marks_12" name="marks_12" value="<?php echo htmlspecialchars($student_data['marks_12']); ?>" step="0.01" min="0" max="100" required><br>
                </fieldset>

                <fieldset>
                    <legend>Uploaded Documents</legend>
                    <label for="photo">Passport-size Photo:</label>
                    <input type="file" id="photo" name="photo" accept="image/*"><br>
                    <div class="file-upload-info">Current file: <a href="uploads/<?php echo htmlspecialchars($student_data['photo']); ?>" target="_blank"><?php echo htmlspecialchars($student_data['photo']); ?></a></div><br>

                    <label for="marksheet_10">10th Marksheet:</label>
                    <input type="file" id="marksheet_10" name="marksheet_10" accept="image/*,application/pdf"><br>
                    <div class="file-upload-info">Current file: <a href="uploads/<?php echo htmlspecialchars($student_data['marksheet_10']); ?>" target="_blank"><?php echo htmlspecialchars($student_data['marksheet_10']); ?></a></div><br>

                    <label for="marksheet_12">12th Marksheet:</label>
                    <input type="file" id="marksheet_12" name="marksheet_12" accept="image/*,application/pdf"><br>
                    <div class="file-upload-info">Current file: <a href="uploads/<?php echo htmlspecialchars($student_data['marksheet_12']); ?>" target="_blank"><?php echo htmlspecialchars($student_data['marksheet_12']); ?></a></div><br>

                    <label for="id_proof">Identity Proof:</label>
                    <input type="file" id="id_proof" name="id_proof" accept="image/*,application/pdf"><br>
                    <div class="file-upload-info">Current file: <a href="uploads/<?php echo htmlspecialchars($student_data['id_proof']); ?>" target="_blank"><?php echo htmlspecialchars($student_data['id_proof']); ?></a></div><br>
                </fieldset>

                <div class="button-group">
                    <button type="submit" name="action" value="update" class="btn-update">Update Form</button>
                    <button type="submit" name="action" value="delete" class="btn-delete" onclick="return confirm('Are you sure you want to delete your application? This action cannot be undone.');">Delete Form</button>
                </div>
            </form>
        <?php else: ?>
            <div class="alert"><?php echo $error_message; ?></div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="footer-container">
            <p>DREAM COLLEGE, Pokhara, UP-208002</p>
            <p>Contact us: 9807021238</p>
        </div>
    </footer>
</body>
</html>
