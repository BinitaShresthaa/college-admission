<?php
session_start();

// Security check: Ensure admin is logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Include database connection file.
include 'db_connect.php';

// Check if the request method is POST and the content type is JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // Get the raw POST data.
    $json = file_get_contents('php://input');
    // Decode the JSON data.
    $data = json_decode($json, true);

    // Validate the received data.
    if (isset($data['id']) && isset($data['status'])) {
        $student_id = intval($data['id']);
        $status = htmlspecialchars($data['status']);

        // Check if the status is one of the allowed values.
        if (!in_array($status, ['Approved', 'Rejected'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid status provided.']);
            exit();
        }

        // Prepare the SQL statement to update the application status.
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $status, $student_id);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Status updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database update failed: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database query preparation failed: ' . $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data payload.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method or content type.']);
}

$conn->close();
?>
