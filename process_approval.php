<?php
// Include your database connection file
// Assumes a db_connect.php file exists
include 'db_connect.php';

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get the application ID and new status from the form submission
    $application_id = filter_var($_POST['application_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_status = trim($_POST['new_status']);

    // --- DEBUGGING START ---
    // Use this to check the values being sent by your form.
    echo "Application ID: " . $application_id . "<br>";
    echo "New Status: " . $new_status . "<br>";
    echo "Lowercase Status: " . strtolower($new_status) . "<br>";
    // Use die() to stop the script here and view the output.
    // die("Debugging complete. Check the values above.");
    // --- DEBUGGING END ---

    // Check if the new status is 'approved'
    if (strtolower($new_status) === 'approved') {
        // Prepare the SQL statement to update both the status and the letter_content_id
        // The `letter_content_id` is automatically set to 1 for approved applications
        $sql = "UPDATE applications SET status = ?, letter_content_id = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $new_status, $application_id);
            if ($stmt->execute()) {
                // Redirect back to the admin dashboard with a success message
                header("Location: admin_dashboard.php?message=Application approved and linked successfully!");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }

    } else {
        // If the status is not 'approved', just update the status as normal
        $sql = "UPDATE applications SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("si", $new_status, $application_id);
            if ($stmt->execute()) {
                header("Location: admin_dashboard.php?message=Application status updated successfully!");
                exit();
            } else {
                echo "Error updating record: " . $conn->error;
            }
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}
$conn->close();
?>
