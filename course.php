<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses</title>
    <link rel="stylesheet" href="course.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    <header id="mainHeader">
        <div class="logo">
            <img src="logo.jpg" alt="Dream College Logo">
            Dream College
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="index.php#aboutCampusSection">About Us</a></li>
                <li><a href="#coursesection">Courses</a></li>
                <li><a href="#contactussection">Contact Us</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
            <button><a href="adminlogin.php">Admin Login</a></button>
            <button><a href="studentlogin.php">Student Signup/Login</a></button>
        </div>
    </header>


    <section class="line-section">
        <div class="line-content">
        <h1>Unlock Your Future</h1>
        <p>Explore our various IT courses and start your journey towards a successful career today.</p>
        </div>
    </section>

    <section class="coursesection" id="coursesection">
        <div class="container">
            <h2>Our Courses</h2>
            <lable>Choose the best course and apply for it</lable>
            <div class="courses-grid">
                <?php
                $sql = "SELECT id, name, description, fee FROM courses";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "<div class='course-card'>";
        echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
        echo "<p>" . htmlspecialchars($row['description']) . "</p>";
        echo "<p class='course-fee'><strong>Fee:</strong> Rs" . htmlspecialchars(number_format($row['fee'], 2)) . "</p>";
        echo "<a href='studentlogin.php'><button class='apply-btn'>Apply Now</button></a>";
        echo "</div>";
    }
} else {
                    echo "<p style='text-align:center; grid-column: 1 / -1; color: #555;'>No courses available at this time. Please check back later.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </section>

    <footer id="contactussection">
        <div class="container">
            <div class="footer-columns">
                <div class="footer-column">
                    <h3>DREAM COLLEGE</h3>
                    <p>College admission management system project source code</p>
                </div>

                <div class="footer-column">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php" class="fas fa-home">Home</a></li>
                        <li><a href="index.php#aboutussection" class="fas fa-graduation-cap">About</a></li>
                        <li><a href="contact.php" class="fas fa-phone">Contact</a></li>
                        <li><a href="#contactussection" class="fas fa-book">Courses</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Follow Us</h3>
                    <ul>
                        <li><a href="#" class="fab fa-twitter"> Twitter</a></li>
                        <li><a href="#" class="fab fa-instagram"> Instagram</a></li>
                        <li><a href="#" class="fab fa-facebook"> Facebook</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h3>Contact Info</h3>
                    <p><strong>Address:</strong> pokhara</p>
                    <p><strong>Email:</strong> contact@dream.com</p>
                    <p><strong>Phone:</strong> 9876549878</p>
                </div>
            </div>
        </div>
    </footer>


    <script>
        const header = document.getElementById("mainHeader");
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
                header.classList.add("sticky");
            } else {
                header.classList.remove("sticky");
            }
        });
    </script>
</body>
</html>