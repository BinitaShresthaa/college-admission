<?php
// Start the session at the very beginning
session_start();

// --- Security and Session Management ---
// Check if the student is logged in and the session is valid
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

// Check for a successful database connection
if ($conn->connect_error) {
    error_log("Database Connection Failed: " . $conn->connect_error);
    header("Location: check_your_form.php?error=db_error");
    exit();
}

// Check if a POST request was made and the action is set
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    header("Location: check_your_form.php");
    exit();
}

// Define the directory for file uploads
$target_dir = "uploads/";

// --- Function to handle file uploads ---
function uploadFile($file_name, $original_file_path, $target_dir, $conn) {
    // Check if a new file has been selected for upload
    if (isset($_FILES[$file_name]) && $_FILES[$file_name]['error'] == 0) {
        $file = $_FILES[$file_name];
        // Generate a unique filename to prevent overwrites
        $unique_filename = uniqid() . '_' . basename($file['name']);
        $target_file = $target_dir . $unique_filename;

        // Check if file is an actual file or a fake file
        $check = getimagesize($file['tmp_name']);
        $is_image = ($check !== false);
        $file_type = mime_content_type($file['tmp_name']);
        $is_pdf = ($file_type == 'application/pdf');

        if (!$is_image && !$is_pdf) {
            return ['success' => false, 'error' => "Only JPG, JPEG, PNG, GIF, and PDF files are allowed."];
        }

        // Check file size (e.g., limit to 5MB)
        if ($file['size'] > 5000000) {
            return ['success' => false, 'error' => "Sorry, your file is too large."];
        }

        // Move the file to the target directory
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Delete the old file if it exists and is not the default
            if (!empty($original_file_path) && file_exists($target_dir . $original_file_path)) {
                unlink($target_dir . $original_file_path);
            }
            return ['success' => true, 'filename' => $unique_filename];
        } else {
            return ['success' => false, 'error' => "Sorry, there was an error uploading your file."];
        }
    } else {
        // No new file uploaded, return the original file path
        return ['success' => true, 'filename' => $original_file_path];
    }
}


// --- Main Action Logic ---
$action = $_POST['action'];
$student_id = $_POST['id'];
$student_email = $_SESSION['email'];

// --- UPDATE action ---
if ($action === 'update') {
    // Collect and sanitize all form data
    $name = $conn->real_escape_string($_POST['name']);
    $father_name = $conn->real_escape_string($_POST['father_name']);
    $mother_name = $conn->real_escape_string($_POST['mother_name']);
    $dob = $_POST['dob'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $address = $conn->real_escape_string($_POST['address']);
    $phone_number = $conn->real_escape_string($_POST['phone_number']);
    $course_id = $_POST['course'];
    $marks_10 = $_POST['marks_10'];
    $marks_12 = $_POST['marks_12'];

    // Handle file uploads for each document
    $photo_upload = uploadFile('photo', $_POST['original_photo'], $target_dir, $conn);
    $marksheet_10_upload = uploadFile('marksheet_10', $_POST['original_marksheet_10'], $target_dir, $conn);
    $marksheet_12_upload = uploadFile('marksheet_12', $_POST['original_marksheet_12'], $target_dir, $conn);
    $id_proof_upload = uploadFile('id_proof', $_POST['original_id_proof'], $target_dir, $conn);

    // Check if any file upload failed
    if (!$photo_upload['success'] || !$marksheet_10_upload['success'] || !$marksheet_12_upload['success'] || !$id_proof_upload['success']) {
        $conn->close();
        header("Location: check_your_form.php?error=photo_upload_failed");
        exit();
    }

    $photo_path = $photo_upload['filename'];
    $marksheet_10_path = $marksheet_10_upload['filename'];
    $marksheet_12_path = $marksheet_12_upload['filename'];
    $id_proof_path = $id_proof_upload['filename'];

    // Check if the provided course ID is valid (optional but recommended)
    $stmt_check_course = $conn->prepare("SELECT id FROM courses WHERE id = ?");
    $stmt_check_course->bind_param("i", $course_id);
    $stmt_check_course->execute();
    $result_check_course = $stmt_check_course->get_result();

    if ($result_check_course->num_rows === 0) {
        $conn->close();
        header("Location: check_your_form.php?error=course_not_found");
        exit();
    }
    $stmt_check_course->close();

   // Prepare and execute the UPDATE statement
$sql = "UPDATE applications SET name=?, father_name=?, mother_name=?, dob=?, gender=?, address=?, phone_number=?, course_id=?, marks_10=?, marks_12=?, photo=?, marksheet_10=?, marksheet_12=?, id_proof=? WHERE id=? AND email=?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    // Corrected bind_param call
    // Added 's' to the type string and $student_email to the parameters
    $stmt->bind_param("sssssssidsssssis", $name, $father_name, $mother_name, $dob, $gender, $address, $phone_number, $course_id, $marks_10, $marks_12, $photo_path, $marksheet_10_path, $marksheet_12_path, $id_proof_path, $student_id, $student_email);
    
    if ($stmt->execute()) {
        // Success: Redirect back to the form with a success message
        $stmt->close();
        $conn->close();
        header("Location: check_your_form.php?success=update_success");
        exit();
    } else {
        // Failure: Redirect with an error message
        error_log("Update Failed: " . $stmt->error);
        $stmt->close();
        $conn->close();
        header("Location: check_your_form.php?error=update_failed");
        exit();
    }
} else {
    // Statement preparation failed
    error_log("SQL Prepare Failed: " . $conn->error);
    $conn->close();
    header("Location: check_your_form.php?error=db_error");
    exit();
}
}
// --- DELETE action ---
if ($action === 'delete') {
    // First, fetch the file paths to delete the files from the server
    $sql_fetch_files = "SELECT photo, marksheet_10, marksheet_12, id_proof FROM applications WHERE id=? AND email=?";
    $stmt_fetch_files = $conn->prepare($sql_fetch_files);
    $stmt_fetch_files->bind_param("is", $student_id, $student_email);
    $stmt_fetch_files->execute();
    $result_fetch_files = $stmt_fetch_files->get_result();
    $files_to_delete = $result_fetch_files->fetch_assoc();
    $stmt_fetch_files->close();

    // If files were found, delete them
    if ($files_to_delete) {
        foreach ($files_to_delete as $file) {
            if (!empty($file) && file_exists($target_dir . $file)) {
                unlink($target_dir . $file);
            }
        }
    }

    // Now, delete the record from the database
    $sql_delete = "DELETE FROM applications WHERE id=? AND email=?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("is", $student_id, $student_email);

    if ($stmt_delete->execute()) {
       
        $stmt_delete->close();
        $conn->close();
        header("Location: viewform.php?message=application_deleted");
        exit();
    } else {
        error_log("Delete Failed: " . $stmt_delete->error);
        $stmt_delete->close();
        $conn->close();
        header("Location: check_your_form.php?error=delete_failed");
        exit();
    }
}

$conn->close();
?>