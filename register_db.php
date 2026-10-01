<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: studentlogin.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and trim all incoming POST data
    $student_id = $_SESSION['student_id'];
    $name = trim($conn->real_escape_string($_POST['name']));
    $father_name = trim($conn->real_escape_string($_POST['father_name']));
    $mother_name = trim($conn->real_escape_string($_POST['mother_name']));
    $dob = $conn->real_escape_string($_POST['dob']);
    $gender = $conn->real_escape_string($_POST['gender']);
    $address = trim($conn->real_escape_string($_POST['address']));
    $email = trim($conn->real_escape_string($_POST['email']));
    $phone_number = trim($conn->real_escape_string($_POST['phone_number']));
    
    // Ensure course_id exists and is an integer
    $course_id = isset($_POST['course']) ? intval($_POST['course']) : 0;
    
    // Ensure marks exist and are floats
    $marks_10 = isset($_POST['marks_10']) ? floatval($_POST['marks_10']) : -1.0;
    $marks_12 = isset($_POST['marks_12']) ? floatval($_POST['marks_12']) : -1.0;
    
    $errors = [];

    // Check if an application already exists for this student_id
    $check_sql = "SELECT id FROM applications WHERE student_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Change: Display an alert message using JavaScript and then redirect
        echo "<script>alert('A form has already been submitted from this account. Only one form can be filled per student.'); window.location.href='studentdash.php';</script>";
        $check_stmt->close();
        $conn->close();
        exit();
    }
    
    // Check if an application with this email already exists
    $check_email_sql = "SELECT id FROM applications WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    
    if ($check_email_stmt) {
        $check_email_stmt->bind_param("s", $email);
        $check_email_stmt->execute();
        $check_email_result = $check_email_stmt->get_result();

        if ($check_email_result->num_rows > 0) {
            echo "<script>alert('A form has already been submitted with this email address.'); window.history.back();</script>";
            $check_email_stmt->close();
            $conn->close();
            exit;
        }
        $check_email_stmt->close();
    } else {
        die("Email check prepare failed: " . $conn->error);
    }

    // --- Server-side validation ---
    // Validate Name fields
    if (empty($name)) { $errors[] = "Name is required."; }
    elseif (strlen($name) > 100) { $errors[] = "Name cannot exceed 100 characters."; }
    elseif (!preg_match("/^[a-zA-Z\s.-]*$/", $name)) { $errors[] = "Name can only contain letters, spaces, hyphens, and periods."; }

    if (empty($father_name)) { $errors[] = "Father's name is required."; }
    elseif (strlen($father_name) > 100) { $errors[] = "Father's name cannot exceed 100 characters."; }
    elseif (!preg_match("/^[a-zA-Z\s.-]*$/", $father_name)) { $errors[] = "Father's name can only contain letters, spaces, hyphens, and periods."; }

    if (empty($mother_name)) { $errors[] = "Mother's name is required."; }
    elseif (strlen($mother_name) > 100) { $errors[] = "Mother's name cannot exceed 100 characters."; }
    elseif (!preg_match("/^[a-zA-Z\s.-]*$/", $mother_name)) { $errors[] = "Mother's name can only contain letters, spaces, hyphens, and periods."; }

    // Validate Date of Birth
    if (empty($dob)) { $errors[] = "Date of Birth is required."; }
    else {
        $dob_check = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dob_check || $dob_check->format('Y-m-d') !== $dob) { $errors[] = "Invalid Date of Birth format. Please use YYYY-MM-DD."; }
        else {
            $today = new DateTime();
            if ($dob_check > $today) { $errors[] = "Date of Birth cannot be in the future."; }
        }
    }

    // Validate Gender
    $allowed_genders = ['Male', 'Female', 'Other'];
    if (empty($gender) || !in_array($gender, $allowed_genders)) { $errors[] = "Please select a valid gender."; }

    // Validate Address
    if (empty($address)) { $errors[] = "Address is required."; }
    elseif (strlen($address) > 255) { $errors[] = "Address cannot exceed 255 characters."; }

    // Validate Email
    if (empty($email)) { $errors[] = "Email is required."; }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "Invalid email format."; }
    elseif (strlen($email) > 100) { $errors[] = "Email cannot exceed 100 characters."; }

    // Validate Phone Number
    if (empty($phone_number)) { $errors[] = "Phone number is required."; }
    elseif (!preg_match("/^[0-9]{10}$/", $phone_number)) { $errors[] = "Phone number must be exactly 10 digits."; }
    
    // Validate Course
    if ($course_id === 0) {
        $errors[] = "Please select a valid course from the list.";
    }

    // Validate Marks
    if ($marks_10 < 0 || $marks_10 > 100) { 
        $errors[] = "10th Marks must be a number between 0 and 100."; 
    }
    if ($marks_12 < 0 || $marks_12 > 100) { 
        $errors[] = "12th Marks must be a number between 0 and 100."; 
    }

    // Validate file uploads
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "Passport-size Photo is required.";
    }
    if (!isset($_FILES['marksheet_10']) || $_FILES['marksheet_10']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "10th Marksheet is required.";
    }
    if (!isset($_FILES['marksheet_12']) || $_FILES['marksheet_12']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "12th Marksheet is required.";
    }
    if (!isset($_FILES['id_proof']) || $_FILES['id_proof']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = "Identity Proof is required.";
    }

    if (!empty($errors)) {
        $error_msg = implode("\\n", array_map('htmlspecialchars', $errors));
        echo "<script>alert('Please fix the following errors:\\n$error_msg'); window.history.back();</script>";
        exit;
    }

    // --- File Upload Logic ---
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_file_types = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    $max_file_size = 5 * 1024 * 1024; // 5 MB

    function uploadFile($file, $upload_dir, $prefix, $allowed_types, $max_size, &$errors) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Unknown upload error.";
            // The match expression here may not work on older PHP versions
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    $error_message = "File is too large (ini size).";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message = "File is too large (form size).";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message = "File was only partially uploaded.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message = "Missing a temporary folder.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message = "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message = "A PHP extension stopped the file upload.";
                    break;
            }
            $errors[] = "File upload error for " . $prefix . ": " . $error_message;
            return false;
        }

        $file_tmp = $file['tmp_name'];
        $file_name = basename($file['name']);
        $file_type = $file['type'];
        $file_size = $file['size'];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Use finfo_open to get the actual MIME type, which is more secure
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            $errors[] = $prefix . " upload failed: Invalid file type. Allowed types: JPEG, PNG, PDF.";
            return false;
        }
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'pdf'])) {
            $errors[] = $prefix . " upload failed: Invalid file extension. Allowed extensions: .jpg, .jpeg, .png, .pdf.";
            return false;
        }
        if ($file_size > $max_size) {
            $errors[] = $prefix . " upload failed: File size exceeds " . ($max_file_size / (1024 * 1024)) . "MB.";
            return false;
        }

        $new_filename = $prefix . '_' . uniqid() . '.' . $extension;
        $destination = $upload_dir . $new_filename;

        if (move_uploaded_file($file_tmp, $destination)) {
            return $new_filename;
        } else {
            $errors[] = $prefix . " upload failed: Could not move uploaded file.";
            return false;
        }
    }

    $photo = uploadFile($_FILES['photo'], $upload_dir, 'photo', $allowed_file_types, $max_file_size, $errors);
    $marksheet_10 = uploadFile($_FILES['marksheet_10'], $upload_dir, 'marksheet10', $allowed_file_types, $max_file_size, $errors);
    $marksheet_12 = uploadFile($_FILES['marksheet_12'], $upload_dir, 'marksheet12', $allowed_file_types, $max_file_size, $errors);
    $id_proof = uploadFile($_FILES['id_proof'], $upload_dir, 'idproof', $allowed_file_types, $max_file_size, $errors);

    if (!empty($errors)) {
        // Delete any partially uploaded files to clean up
        if ($photo && file_exists($upload_dir . $photo)) unlink($upload_dir . $photo);
        if ($marksheet_10 && file_exists($upload_dir . $marksheet_10)) unlink($upload_dir . $marksheet_10);
        if ($marksheet_12 && file_exists($upload_dir . $marksheet_12)) unlink($upload_dir . $marksheet_12);
        if ($id_proof && file_exists($upload_dir . $id_proof)) unlink($upload_dir . $id_proof);
        
        $error_msg = implode("\\n", array_map('htmlspecialchars', $errors));
        echo "<script>alert('Please fix the following errors:\\n$error_msg'); window.history.back();</script>";
        exit;
    }

    // --- Insert into Database ---
    $sql = "INSERT INTO applications
    (student_id, name, father_name, mother_name, dob, gender, address, 
    email, phone_number, course_id, marks_10, marks_12, photo, 
    marksheet_10, marksheet_12, id_proof) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    
    // Correct bind_param types
    $stmt->bind_param(
    "issssssssiddssss",
    $student_id,
    $name,
    $father_name,
    $mother_name,
    $dob,
    $gender,
    $address,
    $email,
    $phone_number,
    $course_id,
    $marks_10,
    $marks_12,
    $photo,
    $marksheet_10,
    $marksheet_12,
    $id_proof
);

    
    if ($stmt->execute()) {
        echo "<script>alert('Application submitted successfully!'); window.location.href='studentdash.php';</script>";
    } else {
        // Log the error but show a generic message to the user
        error_log("Database error during application submission: " . $stmt->error);
        echo "<script>alert('An unexpected error occurred. Please try again.'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Direct access to this page is not allowed.'); window.history.back();</script>";
    exit;
}
?>