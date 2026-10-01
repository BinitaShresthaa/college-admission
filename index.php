<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dream College</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="indexpage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>
<body>
    <header id="mainHeader">
        <div class="logo">
            <img src="logo.jpg" alt="Dream College Logo">
            DREAM COLLEGE
        </div>
        <nav>
            <ul>
                <li><a href="#" class="active">Home</a></li>
                <li><a href="#aboutCampusSection">About Us</a></li>
                <li><a href="course.php">Courses</a></li>
                <li><a href="#contactussection">Contact Us</a></li>
            </ul>
        </nav>
        <div class="auth-buttons">
                <button><a href="adminlogin.php">Admin login</a></button>           
                <button><a href="studentlogin.php">Student Signup/Login</a></button>
        </div>
    </header>

    <section class="line-section">
        <div class="line-content">
            <h1>SEARCH YOUR FUTURE</h1>
            <p>--college is your map, not your limit.--</p>
        </div>
    </section>

    <section class="about-campus" id="aboutCampusSection">
        <h1>About Our Campus</h1>
        <div class="campus-content">
            <div class="campus-card">
                <h2>About DC</h2>
                <p>Dream College rigorously prepares IT students through a project-based curriculum, ensuring hands-on experience in cutting-edge areas like AI and cybersecurity. Experienced faculty, many from the industry, guide students, while strong partnerships with tech companies offer invaluable internships and networking opportunities. This holistic approach, combining practical skills with career support, produces graduates who are not only technically proficient but also innovative and ready to lead in the evolving tech landscape.</p>
                <p>Students will receive the tools and information they need for professional success as well as support from industry-focused academics who are at the top of their field. They will also benefit from extensive career support for guidance and advice.</p>
            </div>
            <div class="campus-card">
                <h2>Our Campus</h2>
                <p>Dream College is a pioneering institution dedicated to the ever-evolving field of Information Technology. Nestled in a vibrant, forward-thinking campus environment, it offers a comprehensive range of IT programs, from cutting-edge software development and cybersecurity to data science and artificial intelligence. </p>
                <p>The college prides itself on its hands-on, project-based learning approach, equipping students with practical skills and real-world experience. State-of-the-art labs, industry-experienced faculty, and strong ties with leading tech companies ensure that Dream College graduates are not just job-ready, but innovators poised to shape the future of technology.</p>
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
                        <li><a href="#aboutussection" class="fas fa-graduation-cap">About</a></li>
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