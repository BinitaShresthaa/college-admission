<?php
session_start();
include 'db_connect.php';

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- LOGIN LOGIC ---
    if (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // SQL query to find the user
        $sql = "SELECT id, password_hash FROM student WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Verify the password
            if (password_verify($password, $user['password_hash'])) {
                // Success! Set the session variables
                $_SESSION['student_id'] = $user['id'];
                $_SESSION['student_logged_in'] = true;
                
                // Redirect to the dashboard
                header("Location: studentdash.php");
                exit();
            }
        }
        // If login fails
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: studentlogin.php");
        exit();

    // --- SIGN UP LOGIC ---
    } elseif (isset($_POST['signup'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Server-side validation
        if (empty($email) || empty($password) || empty($confirm_password)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: studentlogin.php");
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Invalid email format.";
            header("Location: studentlogin.php");
            exit();
        }

        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Passwords do not match.";
            header("Location: studentlogin.php");
            exit();
        }

        // Check if email already exists
        $check_sql = "SELECT id FROM student WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $_SESSION['error'] = "An account with this email already exists.";
            header("Location: studentlogin.php");
            exit();
        }
        $check_stmt->close();

        // Hash the password before storing
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new student into the database
        $insert_sql = "INSERT INTO student (email, password_hash) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ss", $email, $password_hash);

        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Account created successfully. You can now log in.";
            // No automatic login for security, force user to the login form
            header("Location: studentlogin.php");
            exit();
        } else {
            $_SESSION['error'] = "An error occurred during sign-up. Please try again.";
            error_log("Sign-up error: " . $insert_stmt->error);
            header("Location: studentlogin.php");
            exit();
        }
        $insert_stmt->close();
    }
} else {
    // If the file is accessed directly, redirect them away
    header("Location: studentlogin.php");
    exit();
}
$conn->close();
?>