<?php
session_start();

if (!isset($_SESSION['student_logged_in']) || $_SESSION['student_logged_in'] !== true) {
    header("Location: studentlogin.php");
    exit();
}

include 'db_connect.php';

$sql_courses = "SELECT id, name FROM courses ORDER BY name ASC";
$result_courses = $conn->query($sql_courses);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Admissions</title>
    <link rel="stylesheet" href="regi_ster.css">
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            width: 90%;
            max-width: 450px;
            animation: fadeIn 0.3s ease-out;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .modal-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 1.8em;
            cursor: pointer;
            color: #aaa;
            transition: color 0.2s;
        }
        .close-btn:hover {
            color: #333;
        }
        .modal-body {
            font-size: 1em;
            line-height: 1.6;
            color: #555;
            white-space: pre-line;
            max-height: 300px;
            overflow-y: auto;
        }
        .modal-footer {
            padding-top: 15px;
            border-top: 1px solid #eee;
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }
        .modal-ok-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.2s;
        }
        .modal-ok-btn:hover {
            background-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header.scrolled {
            background-color: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .main-nav.scrolled {
            padding: 10px 0;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function showMessage(title, message, inputToFocus = null) {
                const modal = document.createElement('div');
                modal.className = 'modal-overlay';
                modal.innerHTML = `
                    <div class="modal-box">
                        <div class="modal-header">
                            <h3>${title}</h3>
                            <button class="close-btn">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>${message.replace(/\\n/g, '<br>')}</p>
                        </div>
                        <div class="modal-footer">
                            <button class="modal-ok-btn">OK</button>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);

                const closeModal = () => {
                    modal.remove();
                    if (inputToFocus) {
                        inputToFocus.focus();
                        if (inputToFocus.type === 'text' || inputToFocus.type === 'tel' || inputToFocus.type === 'email' || inputToFocus.type === 'number') {
                            inputToFocus.select();
                        }
                    }
                };
                modal.querySelector('.close-btn').addEventListener('click', closeModal);
                modal.querySelector('.modal-ok-btn').addEventListener('click', closeModal);
                modal.addEventListener('click', (e) => {
                    if (e.target.classList.contains('modal-overlay')) {
                        closeModal();
                    }
                });
            }

            const form = document.querySelector('.form-box');
            const nameInputs = form.querySelectorAll('input[name="name"], input[name="father_name"], input[name="mother_name"]');
            const phoneInput = form.querySelector('input[name="phone_number"]');
            const emailInput = form.querySelector('input[name="email"]');
            const dobInput = form.querySelector('#dob');
            const genderSelect = form.querySelector('#gender');
            const addressInput = form.querySelector('input[name="address"]');
            const courseSelect = form.querySelector('#course');
            const marks10Input = form.querySelector('#marks_10');
            const marks12Input = form.querySelector('#marks_12');
            const photoInput = form.querySelector('#photo');
            const marksheet10Input = form.querySelector('#marksheet_10');
            const marksheet12Input = form.querySelector('#marksheet_12');
            const idProofInput = form.querySelector('#id_proof');

            nameInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const originalValue = this.value;
                    const cleanedValue = originalValue.replace(/[^a-zA-Z\s.-]/g, '');

                    if (originalValue !== cleanedValue) {
                        this.value = cleanedValue;
                        showMessage('Input Error', 'Only alphabet characters, spaces, hyphens, and periods are allowed.', this);
                    }
                });
            });

            if (phoneInput) {
                phoneInput.addEventListener('input', function() {
                    const originalValue = this.value;
                    const cleanedValue = originalValue.replace(/\D/g, '');

                    if (originalValue !== cleanedValue) {
                        this.value = cleanedValue;
                        showMessage('Input Error', 'Only numbers are allowed in the phone number field.', this);
                    }
                    if (this.value.length > 10) {
                        this.value = this.value.slice(0, 10);
                        showMessage('Input Error', 'Phone number cannot exceed 10 digits.', this);
                    }
                });

                phoneInput.addEventListener('blur', function() {
                    if (this.value.length > 0 && this.value.length !== 10) {
                        showMessage('Validation Error', 'Phone number must be exactly 10 digits.', this);
                    }
                });
            }

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (this.value && !emailRegex.test(this.value)) {
                        showMessage('Validation Error', 'Please enter a valid email address (e.g., example@domain.com).', this);
                    }
                });
            }

            function setupMarksValidation(inputElement, type, isInteger = false) {
                if (inputElement) {
                    inputElement.addEventListener('input', function() {
                        let value = isInteger ? parseInt(this.value, 10) : parseFloat(this.value);
                        if (isNaN(value)) {
                            if (this.value !== '') {
                                showMessage('Input Error', `Please enter a valid ${isInteger ? 'integer' : 'number'} for ${type} Marks.`, this);
                            }
                        } else if (value < 0 || value > 100) {
                            showMessage('Input Error', `${type} Marks must be between 0 and 100.`, this);
                        }
                    });

                    inputElement.addEventListener('blur', function() {
                        let value = isInteger ? parseInt(this.value, 10) : parseFloat(this.value);
                        if (isNaN(value) || value < 0 || value > 100 || (isInteger && this.value.includes('.'))) {
                            if (this.value !== '') {
                                showMessage('Validation Error', `${type} Marks must be a ${isInteger ? 'whole number' : 'number'} between 0 and 100.`, this);
                            }
                        }
                    });
                }
            }
            setupMarksValidation(marks10Input, '10th');
            setupMarksValidation(marks12Input, '12th');

            form.addEventListener('submit', function(event) {
                const clientErrors = [];
                const requiredFields = [
                    { element: form.querySelector('input[name="name"]'), name: 'Name' },
                    { element: form.querySelector('input[name="father_name"]'), name: 'Father\'s Name' },
                    { element: form.querySelector('input[name="mother_name"]'), name: 'Mother\'s Name' },
                    { element: dobInput, name: 'Date of Birth' },
                    { element: genderSelect, name: 'Gender' },
                    { element: addressInput, name: 'Address' },
                    { element: emailInput, name: 'Email' },
                    { element: phoneInput, name: 'Phone Number' },
                    { element: courseSelect, name: 'Course' },
                    { element: marks10Input, name: '10th Marks' },
                    { element: marks12Input, name: '12th Marks' },
                    { element: photoInput, name: 'Passport-size Photo' },
                    { element: marksheet10Input, name: '10th Marksheet' },
                    { element: marksheet12Input, name: '12th Marksheet' },
                    { element: idProofInput, name: 'Identity Proof' }
                ];

                const firstEmptyField = requiredFields.find(field => {
                    if (field.element.type === 'file') {
                        return field.element.files.length === 0;
                    }
                    return !field.element.value;
                });

                if (firstEmptyField) {
                    event.preventDefault();
                    showMessage('Form Incomplete', `Please fill out the following required field: ${firstEmptyField.name}.`, firstEmptyField.element);
                    return;
                }

                nameInputs.forEach(input => {
                    if (!input.value.match(/^[a-zA-Z\s.-]*$/) && input.value !== '') {
                        clientErrors.push(`${input.placeholder.replace('Enter ', '').replace(/'s Name/, 's Name')} can only contain letters, spaces, hyphens, and periods.`);
                    }
                });

                if (phoneInput && (phoneInput.value.length !== 10 || !phoneInput.value.match(/^\d{10}$/))) {
                    clientErrors.push('Phone number must be exactly 10 digits and contain only numbers.');
                }

                if (emailInput) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailInput.value && !emailRegex.test(emailInput.value)) {
                        clientErrors.push('Please enter a valid email address.');
                    }
                }

                const validateMarksOnSubmit = (input, type, isInteger = false) => {
                    if (input) {
                        const value = isInteger ? parseInt(input.value, 10) : parseFloat(input.value);
                        if (isNaN(value) || value < 0 || value > 100 || (isInteger && input.value.includes('.'))) {
                            clientErrors.push(`${type} Marks must be a valid ${isInteger ? 'integer' : 'number'} between 0 and 100.`);
                        }
                    }
                };
                validateMarksOnSubmit(marks10Input, '10th');
                validateMarksOnSubmit(marks12Input, '12th');

                if (clientErrors.length > 0) {
                    event.preventDefault();
                    showMessage('Form Submission Errors', clientErrors.join('\n'));
                }
            });

            const header = document.getElementById('mainHeader');
            const nav = document.getElementById('mainNav');

            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) {
                    header.classList.add('scrolled');
                    nav.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                    nav.classList.remove('scrolled');
                }
            });
        });
    </script>
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
        <a href="studentdash.php">Dashboard</a>
        <a href="status.php">Check Admission Status</a>
        <a href="viewform.php">Check Your Form</a>
    </nav>

    <div class="form-container">
        <h2>Register for Admissions</h2>
        <form action="register_db.php" method="POST" enctype="multipart/form-data" class="form-box" novalidate>
            <fieldset>
                <legend>Personal Details</legend>
                <label for="name"> Enter Name:</label>
                <input type="text" name="name" placeholder="Enter Name" required aria-label="Enter Name"><br>
                <label for="father"> Enter Father's Name:</label>
                <input type="text" name="father_name" placeholder="Enter Father's Name" required aria-label="Enter Father's Name"><br>
                <label for="mother"> Enter Mother's Name:</label>
                <input type="text" name="mother_name" placeholder="Enter Mother's Name" required aria-label="Enter Mother's Name"><br>
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required aria-label="Date of Birth"><br>
                <label for="gender">Select Gender:</label>
                <select id="gender" name="gender" required aria-label="Select Gender">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select><br>
            </fieldset>

            <fieldset>
                <legend>Account & Contact</legend>
                <label for="address"> Enter Current Address:</label>
                <input type="text" name="address" placeholder="Enter Current Address" required aria-label="Enter Current Address"><br>
                <label for="email"> Enter Email:</label>
                <input type="email" name="email" placeholder="Enter Email" required aria-label="Enter Email"><br>
                <label for="phone"> Enter Phone no:</label>
                <input type="tel" name="phone_number" placeholder="Enter Phone Number (10 digits)" pattern="[0-9]{10}" title="Phone number must be 10 digits" required maxlength="10" aria-label="Enter Phone Number"><br>
            </fieldset>

            <fieldset>
                <legend>Academic Details</legend>
                <label for="course">Choose a Course:</label>
                <select name="course" id="course" required>
                    <option value="">-- Please select a course --</option>
                    <?php
                    if ($result_courses && $result_courses->num_rows > 0) {
                        while($course_row = $result_courses->fetch_assoc()) {
                            $course_id = htmlspecialchars($course_row['id']);
                            $course_name = htmlspecialchars($course_row['name']);
                            echo '<option value="' . $course_id . '">' . $course_name . '</option>';
                        }
                        $result_courses->free();
                    }
                    ?>
                </select><br>
                <label for="marks_10">Enter 10th Marks:</label>
                <input type="number" id="marks_10" name="marks_10" placeholder="10th marks " step="0.01" min="0" max="100" required aria-label="Enter 10th Marks"><br>
                <label for="marks_12">Enter 12th Marks:</label>
                <input type="number" id="marks_12" name="marks_12" placeholder="12th marks " step="0.01" min="0" max="100" required aria-label="Enter 12th Marks"><br>
            </fieldset>

            <fieldset>
                <legend>Upload Required Documents</legend>
                <label for="photo">Passport-size Photo:</label>
                <input type="file" id="photo" name="photo" accept="image/*" required aria-label="Upload Passport-size Photo"><br>
                <label for="marksheet_10">10th Marksheet:</label>
                <input type="file" id="marksheet_10" name="marksheet_10" accept="image/*,application/pdf" required aria-label="Upload 10th Marksheet"><br>
                <label for="marksheet_12">12th Marksheet (or equivalent):</label>
                <input type="file" id="marksheet_12" name="marksheet_12" accept="image/*,application/pdf" required aria-label="Upload 12th Marksheet"><br>
                <label for="id_proof">Identity Proof (Citizenship, License, etc.):</label>
                <input type="file" id="id_proof" name="id_proof" accept="image/*,application/pdf" required aria-label="Upload Identity Proof"><br>
            </fieldset>

            <small>By creating an account you agree to our <a href="#">Terms & Privacy</a>.</small><br>
            <div class="button-group">
                <button type="reset" class="btn btn-cancel">Cancel</button>
                <button type="submit" class="btn btn-register">Register</button>
            </div>
        </form>
    </div>

    <footer>
        <div class="footer-container">
            <p>DREAM COLLEGE, Pokhara, UP-208002</p>
            <p>Contact us: 9807021238</p>
        </div>
    </footer>
</body>
</html>
<?php
if (isset($conn) && $conn) {
    $conn->close();
}
?>