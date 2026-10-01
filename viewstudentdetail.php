<?php
session_start();

// Ensure the admin is logged in.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: adminlogin.php");
    exit();
}

// Security best practice: Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Database connection setup - assuming 'db_connect.php' exists
include 'db_connect.php'; 

$student_details = null;
$error_message = '';
$letters = [];

// Fetch all available letter templates for the letter generation form
$sql_letters = "SELECT id, title FROM letter_content";
$result_letters = $conn->query($sql_letters);
if ($result_letters->num_rows > 0) {
    while($row = $result_letters->fetch_assoc()) {
        $letters[] = $row;
    }
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $student_id = intval($_GET['id']);

    // Fetch student application details along with course information
    // The letter content and title are not fetched here as per the user's request.
    $sql = "
        SELECT 
            a.*, 
            c.name AS course_name, 
            c.description AS course_description,
            c.fee AS course_fee
        FROM 
            applications a
        LEFT JOIN 
            courses c ON a.course_id = c.id
        WHERE 
            a.id = ?
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $student_details = $result->fetch_assoc();
        } else {
            $error_message = "Student not found.";
        }
        $stmt->close();
    } else {
        $error_message = "Database query preparation failed: " . $conn->error;
    }
} else {
    $error_message = "No student ID provided.";
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
    <title>View Student Application</title>
    <!-- Combined CSS styles embedded here for a single, self-contained file -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 0;
        }
        .header {
            background-color: #f7a600;
            color: white;
            padding: 20px 30px;
            font-size: 2.2em;
            font-weight: bold;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            letter-spacing: 1.8px;
            text-transform: uppercase;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, color 0.3s ease, box-shadow 0.3s ease; 
        }
        .main-nav {
            background-color: #f7d497;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, box-shadow 0.3s ease; 
        }
        .main-nav a {
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 12px;
            border-radius: 5px;
            transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease; 
            font-weight: bold;
            position: relative;
        }
        .main-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            background-color: #333;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .main-nav a:hover::after,
        .main-nav a.active::after {
            width: calc(100% - 24px);
            background-color: #f7a600;
        }
        .main-nav a:hover,
        .main-nav a.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: #f7a600;
        }
        .header.scrolled {
            background-color: rgba(255, 255, 255, 0.9);
            color: black;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .main-nav.scrolled {
            background-color: rgba(255, 255, 255, 0.85);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }
        .main-nav.scrolled a {
            color: black;
        }
        .main-nav.scrolled a::after {
            background-color: #333;
            transition: width 0.3s ease, background-color 0.3s ease;
        }
        .main-nav.scrolled a:hover::after,
        .main-nav.scrolled a.active::after {
            background-color: #f7a600;
        }
        .main-nav.scrolled a:hover,
        .main-nav.scrolled a.active {
            background-color: rgba(0, 0, 0, 0.1);
            color: #f7a600;
        }
        .view-details-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-size: 1.8em;
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
        }
        .detail-group {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 20px;
            gap: 20px;
        }
        .detail-item {
            flex: 1 1 48%;
            display: flex;
            flex-direction: column;
        }
        .detail-item label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            color: #34495e;
        }
        .detail-item span,
        .detail-item select,
        .detail-item textarea {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background-color: #fafafa;
            box-sizing: border-box;
            color: #555;
            transition: border-color 0.3s;
        }
        .document-link {
            display: inline-block;
            margin-top: 8px;
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }
        .document-link:hover {
            text-decoration: underline;
        }
        .section-title {
            background-color: #ecf0f1;
            padding: 15px;
            margin-top: 30px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            color: #2c3e50;
        }
        .error-message {
            color: #c0392b;
            text-align: center;
            margin-top: 20px;
            font-weight: 500;
        }
        .button-group {
            text-align: center;
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s, transform 0.2s;
        }
        .btn-register {
            background-color: #2ecc71;
            box-shadow: 0 4px 6px rgba(46, 204, 113, 0.3);
        }
        .btn-register:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        .approve-btn, .reject-btn {
            border: none;
        }
        .approve-btn {
            background-color: #5eb573ff;
        }
        .reject-btn {
            background-color: #dc3545;
        }
        .approve-btn:hover, .reject-btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .generate-btn {
            background-color: #2196F3;
        }
        .generate-btn:hover {
            background-color: #0b7dda;
        }
        /* --- Modal Styles --- */
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
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        /* --- Footer --- */
        footer {
            text-align: center;
            background-color: #f7a600;
            color: #333;
            padding: 25px 20px;
            font-size: 0.95em;
            margin-top: 50px;
            border-top: 1px solid #e0e0e0;
            box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.05);
        }
        .footer-container p {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .message-box {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
            text-align: center;
            border: 1px solid #c3e6cb;
        }
        .message-box.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .letter-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .header {
                font-size: 2em;
                padding: 20px 20px;
            }
            .main-nav {
                flex-direction: column;
                padding: 10px 0;
            }
            .main-nav a {
                display: block;
                margin: 8px auto;
                width: calc(100% - 40px);
                text-align: center;
                padding: 12px 0;
            }
            footer {
                padding: 20px 15px;
            }
        }
        @media (max-width: 480px) {
            .header {
                font-size: 1.7em;
                padding: 15px 10px;
            }
            footer {
                font-size: 0.85em;
            }
        }
    </style>
</head>
<body>
    <div class="header" id="mainHeader">
        DREAM COLLEGE
    </div>

    <nav class="main-nav" id="mainNav">
        <a href="index.php">Home</a>
        <a href="index.php#aboutCampusSection">About us</a>
        <a href="course.php">Courses</a>
        <a href="index.php#contactussection">Contact Us</a>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="updateletter.php">Approval Letter</a>
        <a href="updatecourse.php">Update Course</a>
        <a href="view_application.php">Application</a>
    </nav>

    <div class="view-details-container">
        <h2>View Application Form</h2>
        <?php if ($student_details): ?>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Applicant Name:</label>
                    <span><?php echo htmlspecialchars($student_details['name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Course Applied:</label>
                    <span><?php echo htmlspecialchars($student_details['course_name']); ?></span>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Application Status:</label>
                    <span id="application-status"><?php echo htmlspecialchars($student_details['status']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($student_details['email']); ?></span>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Date of Birth:</label>
                    <span><?php echo htmlspecialchars($student_details['dob']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Gender:</label>
                    <span><?php echo htmlspecialchars($student_details['gender']); ?></span>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Mobile Number:</label>
                    <span><?php echo htmlspecialchars($student_details['phone_number']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Father's Name:</label>
                    <span><?php echo htmlspecialchars($student_details['father_name']); ?></span>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Mother's Name:</label>
                    <span><?php echo htmlspecialchars($student_details['mother_name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Address:</label>
                    <span><?php echo htmlspecialchars($student_details['address']); ?></span>
                </div>
            </div>
            <div class="section-title">Academic Details</div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>10th Marks:</label>
                    <span><?php echo htmlspecialchars($student_details['marks_10']); ?></span>
                </div>
                <div class="detail-item">
                    <label>12th Marks:</label>
                    <span><?php echo htmlspecialchars($student_details['marks_12']); ?></span>
                </div>
            </div>
            <div class="section-title">Uploaded Documents</div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>Passport-size Photo:</label>
                    <?php if ($student_details['photo']): ?>
                        <a href="uploads/<?php echo htmlspecialchars($student_details['photo']); ?>" target="_blank" class="document-link">View Photo</a>
                    <?php else: ?>
                        <span>Not Available</span>
                    <?php endif; ?>
                </div>
                <div class="detail-item">
                    <label>10th Marksheet:</label>
                    <?php if ($student_details['marksheet_10']): ?>
                        <a href="uploads/<?php echo htmlspecialchars($student_details['marksheet_10']); ?>" target="_blank" class="document-link">View Marksheet</a>
                    <?php else: ?>
                        <span>Not Available</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="detail-group">
                <div class="detail-item">
                    <label>12th Marksheet:</label>
                    <?php if ($student_details['marksheet_12']): ?>
                        <a href="uploads/<?php echo htmlspecialchars($student_details['marksheet_12']); ?>" target="_blank" class="document-link">View Marksheet</a>
                    <?php else: ?>
                        <span>Not Available</span>
                    <?php endif; ?>
                </div>
                <div class="detail-item">
                    <label>Identity Proof:</label>
                    <?php if ($student_details['id_proof']): ?>
                        <a href="uploads/<?php echo htmlspecialchars($student_details['id_proof']); ?>" target="_blank" class="document-link">View ID Proof</a>
                    <?php else: ?>
                        <span>Not Available</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="message-box" id="messageBox"></div>

            <div class="section-title">Update Application Status</div>
            <div class="button-group" id="buttonGroup">
                <!-- Buttons will be dynamically added here by JavaScript -->
            </div>

            <div id="letterGeneratorForm" class="letter-form" style="display:none;">
                <form action="generate_letter_code.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($student_details['id']); ?>">
                    <div class="detail-item">
                        <label for="letter_id">Select Letter Template:</label>
                        <select name="letter_id" id="letter_id" required>
                            <?php foreach ($letters as $letter): ?>
                                <option value="<?php echo htmlspecialchars($letter['id']); ?>">
                                    <?php echo htmlspecialchars($letter['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-register">Generate Approval Letter</button>
                </form>
            </div>
            
        <?php else: ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
    </div>

    <footer>
        <div class="footer-container">
            <p>DREAM COLLEGE, Pokhara, UP-208002</p>
            <p>Contact us: 9807021238</p>
        </div>
    </footer>

    <script>
        const header = document.getElementById('mainHeader');
        const nav = document.getElementById('mainNav');
        const applicationStatus = document.getElementById('application-status');
        const buttonGroup = document.getElementById('buttonGroup');
        const letterGeneratorForm = document.getElementById('letterGeneratorForm');
        const messageBox = document.getElementById('messageBox');
        
        // Initial setup for buttons based on current status
        window.addEventListener('load', () => {
            updateUI(applicationStatus.textContent);
        });

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
                nav.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
                nav.classList.remove('scrolled');
            }
        });

        function showMessage(message, isError = false) {
            messageBox.textContent = message;
            messageBox.style.display = 'block';
            if (isError) {
                messageBox.classList.add('error');
            } else {
                messageBox.classList.remove('error');
            }
        }

        function updateUI(status) {
            applicationStatus.textContent = status;
            buttonGroup.innerHTML = ''; // Clear existing buttons
            letterGeneratorForm.style.display = 'none';

            if (status === 'Pending') {
                const approveBtn = document.createElement('button');
                approveBtn.className = 'btn approve-btn';
                approveBtn.textContent = 'Approve';
                approveBtn.addEventListener('click', () => updateStatus('Approved'));
                buttonGroup.appendChild(approveBtn);

                const rejectBtn = document.createElement('button');
                rejectBtn.className = 'btn reject-btn';
                rejectBtn.textContent = 'Reject';
                rejectBtn.addEventListener('click', () => updateStatus('Rejected'));
                buttonGroup.appendChild(rejectBtn);

            } else if (status === 'Approved') {
                const rejectBtn = document.createElement('button');
                rejectBtn.className = 'btn reject-btn';
                rejectBtn.textContent = 'Reject';
                rejectBtn.addEventListener('click', () => updateStatus('Rejected'));
                buttonGroup.appendChild(rejectBtn);

                const letterLinked = "<?php echo $student_details['letter_content_id']; ?>";
                if (!letterLinked) {
                    letterGeneratorForm.style.display = 'flex';
                }
            } else if (status === 'Rejected') {
                const approveBtn = document.createElement('button');
                approveBtn.className = 'btn approve-btn';
                approveBtn.textContent = 'Approve';
                approveBtn.addEventListener('click', () => updateStatus('Approved'));
                buttonGroup.appendChild(approveBtn);
            }
        }

        function updateStatus(status) {
            fetch('update_application_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: <?php echo htmlspecialchars($student_id); ?>, status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Application status updated successfully!', false);
                    updateUI(status);
                } else {
                    showMessage('Error: ' + data.message, true);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('An unexpected error occurred. Please try again.', true);
            });
        }
    </script>
</body>
</html>
