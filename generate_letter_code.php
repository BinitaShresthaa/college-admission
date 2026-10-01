<?php
session_start();

// Ensure the admin is logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate the inputs.
    $student_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $letter_id = filter_input(INPUT_POST, 'letter_id', FILTER_VALIDATE_INT);

    if ($student_id && $letter_id) {
        // SQL query to update the applications table.
        // We are using the new column name `letter_content_id`.
        $sql = "UPDATE applications SET letter_content_id = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $letter_id, $student_id);
            if ($stmt->execute()) {
                $_SESSION['form_message'] = "Approval letter successfully linked to Application ID " . $student_id . ".";
            } else {
                $_SESSION['form_message'] = "Error linking letter: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['form_message'] = "Database query preparation failed: " . $conn->error;
        }
    } else {
        $_SESSION['form_message'] = "Invalid student ID or letter ID provided.";
    }
} else {
    $_SESSION['form_message'] = "Invalid request method.";
}

$conn->close();

// Redirect back to the view application page with the student ID.
header("Location: view_application.php?id=" . $student_id);
exit();

?>
