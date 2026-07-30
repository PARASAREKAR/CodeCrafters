<?php
/**
 * Standalone Contact Us Page
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/helpers.php';
require_once 'config/db_connect.php';

require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$success_submitted = false;
$sender_name = '';
$sender_email = '';
$sender_subject = '';
$errors = [];

$csrfToken = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    } else {
    $sender_name    = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $sender_email   = trim(filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL));
    $sender_subject = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
    $sender_message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

    if (empty($sender_name)) {
        $errors[] = "Please enter your name.";
    }
    if (!$sender_email) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($sender_subject)) {
        $errors[] = "Please specify a subject.";
    }
    if (empty($sender_message)) {
        $errors[] = "Please enter your message.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$sender_name, $_POST['email'], $sender_subject, $sender_message]);
            
            // ── Send Email to Sender using PHPMailer ─────────────
            $mailSender = new PHPMailer(true);
            try {
                $mailSender->isSMTP();
                $mailSender->Host       = 'smtp.gmail.com';
                $mailSender->SMTPAuth   = true;
                $mailSender->Username   = 'eventoraganizers2026@gmail.com';
                $mailSender->Password   = 'gdtfdzdcubqpenyq';
                $mailSender->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mailSender->Port       = 587;

                $mailSender->setFrom('eventoraganizers2026@gmail.com', 'EventHub Support');
                $mailSender->addAddress($sender_email, $sender_name);

                $mailSender->isHTML(true);
                $mailSender->Subject = "Your Message Has Been Received | Event Registration System";
                $mailSender->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.7; color: #333;'>

                     <h2 style='color:#0d6efd;'>Hello {$sender_name},</h2>

                     <p>Thank you for contacting the <b>Event Registration System</b>.</p>

                     <p>✅ <b>Your message has been sent successfully.</b></p>

                     <p>We have received your inquiry and our support team will review it shortly.</p>

                     <hr style='border:0; border-top:1px solid #ddd;'>

                     <h3>Your Submitted Details</h3>

                     <p><b>Subject:</b> {$sender_subject}</p>

                     <p><b>Message:</b><br>" . nl2br($sender_message) . "</p>

                     <hr style='border:0; border-top:1px solid #ddd;'>

                     <p>If you have any additional questions, simply reply to this email.</p>

                     <p>We appreciate your interest in our Event Registration System.</p>

                     <br>

                     <p>
                         Regards,<br>
                         <b>Event Registration System Team</b><br>
                         📧 eventoraganizers2026@gmail.com
                     </p>

                </div>
                ";
                $mailSender->send();
            } catch (Exception $e) {
                error_log('Contact Sender Mail Error: ' . $mailSender->ErrorInfo);
            }

            // ── Send Email to Organizer/Admin using PHPMailer ────
            $mailAdmin = new PHPMailer(true);
            try {
                $mailAdmin->isSMTP();
                $mailAdmin->Host       = 'smtp.gmail.com';
                $mailAdmin->SMTPAuth   = true;
                $mailAdmin->Username   = 'eventoraganizers2026@gmail.com';
                $mailAdmin->Password   = 'gdtfdzdcubqpenyq';
                $mailAdmin->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mailAdmin->Port       = 587;

                $mailAdmin->setFrom('eventoraganizers2026@gmail.com', 'EventHub System');
                $mailAdmin->addAddress('eventoraganizers2026@gmail.com', 'EventHub Support');
                $mailAdmin->addReplyTo($sender_email, $sender_name);

                $mailAdmin->isHTML(true);
                $mailAdmin->Subject = "[EventHub Support] New Message: " . $sender_subject;
                $mailAdmin->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                        <h2 style='color: #0d6efd;'>New Support Message Received</h2>
                        <p>You have received a new support message via the EventHub Contact Us page.</p>
                        <hr style='border: 0; border-top: 1px solid #eee;'>
                        <p><b>Sender Name:</b> {$sender_name}</p>
                        <p><b>Sender Email:</b> {$sender_email}</p>
                        <p><b>Subject:</b> {$sender_subject}</p>
                        <p><b>Message:</b><br>" . nl2br($sender_message) . "</p>
                        <hr style='border: 0; border-top: 1px solid #eee;'>
                        <p>Log in to the Admin Dashboard to view/manage all contact messages.</p>
                    </div>
                ";
                $mailAdmin->send();
            } catch (Exception $e) {
                error_log('Contact Admin Mail Error: ' . $mailAdmin->ErrorInfo);
            }

            $success_submitted = true;
        } catch (PDOException $e) {
            $errors[] = "Failed to submit your message: " . $e->getMessage();
        }
    }
    } // end else for CSRF
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | EventHub</title>
    
    <!-- Bootstrap 5.3.2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom Stylesheets -->
    <link rel="stylesheet" href="assets/css/themes.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
    <!-- Theme Initialization -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'midnight-dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom" data-aos="fade-down" data-aos-delay="100">
        <div class="container">
            <!-- Brand -->
            <a class="navbar-brand fw-bold" href="index.php">
                <img src="assets/images/logo.png" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);"> EventHub
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarLanding" aria-controls="navbarLanding"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Nav Links -->
            <div class="collapse navbar-collapse" id="navbarLanding">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-house me-1"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse_events.php">
                            <i class="bi bi-calendar-event me-1"></i>Browse Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">
                            <i class="bi bi-info-circle me-1"></i>About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact.php">
                            <i class="bi bi-envelope me-1"></i>Contact Us
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item ms-lg-2">
                            <?php $role = getUserRole(); ?>
                            <?php if ($role === 'Admin'): ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="admin/admin_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php elseif ($role === 'Organizer'): ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="organizer/organizer_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php else: ?>
                                <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="participant/participant_dashboard.php">
                                    <i class="bi bi-speedometer2 me-1"></i>Dashboard
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php else: ?>
                        <li class="nav-item ms-lg-2">
                            <a class="nav-link" href="auth/login.php">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </a>
                        </li>
                        <li class="nav-item ms-lg-1">
                            <a class="nav-link btn btn-accent btn-sm px-3 text-white" href="auth/register.php">
                                <i class="bi bi-person-plus me-1"></i>Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header Hero -->
    <header class="py-5 text-center bg-glass" style="margin-top: 120px; border-radius: 24px; margin-left: 15px; margin-right: 15px;">
        <div class="container py-4" data-aos="zoom-in">
            <h1 class="display-4 fw-bold">Get In <span style="color: var(--accent);">Touch</span></h1>
            <p class="lead text-muted max-width-600 mx-auto">Have questions? We would love to hear from you. Get in touch with our team.</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container py-5">
        <div class="row justify-content-center" data-aos="fade-up">
            <div class="col-lg-8">
                <div class="glass-card p-5" style="border-radius: 24px;">
                    <?php if ($success_submitted): ?>
                        <div class="text-center py-4">
                            <i class="bi bi-patch-check-fill display-1 text-success mb-4 d-block" data-aos="zoom-in"></i>
                            <h3 class="fw-bold mb-3" data-aos="fade-up">Message Recorded!</h3>
                            <p class="text-muted mb-4 px-lg-5" style="line-height: 1.8; font-size: 1.05rem;" data-aos="fade-up" data-aos-delay="100">
                                Thank you, <strong><?php echo htmlspecialchars($sender_name); ?></strong>. Your inquiry regarding <em>"<?php echo htmlspecialchars($sender_subject); ?>"</em> has been securely recorded in our database. 
                            </p>
                            <div class="p-4 mb-4 mx-lg-5 text-start" style="background: rgba(255,255,255,0.02); border: 1px solid var(--border); border-radius: 16px;" data-aos="fade-up" data-aos-delay="200">
                                <h6 class="fw-bold mb-2 text-accent"><i class="bi bi-info-circle me-2"></i>Official Support Receipt</h6>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">
                                    Our administrative and organizing support team has received your query. A response has been queued for your email: <strong><?php echo htmlspecialchars($_POST['email']); ?></strong>. We will review your message details and provide a formal reply within 24 to 48 business hours. Thank you for using EventHub!
                                </p>
                            </div>
                            <a href="contact.php" class="btn btn-accent px-4 py-2" data-aos="fade-up" data-aos-delay="300">
                                <i class="bi bi-arrow-left me-2"></i>Send Another Message
                            </a>
                        </div>
                    <?php else: ?>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php echo implode('<br>', $errors); ?>
                            </div>
                        <?php endif; ?>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div class="p-4 text-center" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid var(--border);">
                                    <i class="bi bi-envelope-paper display-4 mb-3" style="color: var(--accent);"></i>
                                    <h5 class="fw-bold">Email Us</h5>
                                    <p class="text-muted mb-0">
                                        <a href="mailto:eventoraganizers2026@gmail.com" style="color: var(--text-secondary); text-decoration: none;">
                                            eventoraganizers2026@gmail.com
                                        </a>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-4 text-center" style="background: rgba(255,255,255,0.02); border-radius: 16px; border: 1px solid var(--border);">
                                    <i class="bi bi-telephone display-4 mb-3" style="color: var(--accent);"></i>
                                    <h5 class="fw-bold">Call Us</h5>
                                    <p class="text-muted mb-0">Mobile number will be provided later.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Form -->
                        <h3 class="fw-bold text-center mb-4">Send Us a Message</h3>
                        <form action="contact.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label text-muted">Your Name</label>
                                    <input type="text" class="form-control form-control-custom" id="name" name="name" required placeholder="Enter name"
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label text-muted">Your Email</label>
                                    <input type="email" class="form-control form-control-custom" id="email" name="email" required placeholder="Enter email"
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label text-muted">Subject</label>
                                    <input type="text" class="form-control form-control-custom" id="subject" name="subject" required placeholder="Enter subject"
                                           value="<?php echo htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label text-muted">Message</label>
                                    <textarea class="form-control form-control-custom" id="message" name="message" rows="6" required placeholder="Type your message here..." maxlength="3000" style="word-break: break-word; overflow-wrap: break-word; word-wrap: break-word; resize: vertical; white-space: pre-wrap;"><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                    <div class="text-end mt-1"><small class="text-muted" id="msgCharCount">0 / 3000 characters</small></div>
                                </div>
                                <div class="col-12 text-center mt-4">
                                    <button type="submit" name="submit_message" class="btn btn-accent px-5 py-2">
                                        <i class="bi bi-send me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="modern-footer mt-auto">
        <div class="container">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-6">
                    <span class="footer-brand" style="color: var(--accent);"><img src="assets/images/logo.png" alt="EventHub Logo" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid var(--accent);"> EventHub</span>
                    <p class="footer-desc">
                        Discover and register for world-class tech, business, and creative events. Elevate your potential today.
                    </p>
                    <div class="footer-socials">
                        <a href="#"><i class="bi bi-twitter-x"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                        <a href="#"><i class="bi bi-github"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h5 class="footer-title">Quick Actions</h5>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="bi bi-house me-2"></i>Home</a></li>
                        <li><a href="about.php"><i class="bi bi-info-circle me-2"></i>About Us</a></li>
                        <li><a href="contact.php"><i class="bi bi-envelope me-2"></i>Contact Us</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="auth/login.php"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="auth/login.php"><i class="bi bi-box-arrow-in-right me-2"></i>Login</a></li>
                            <li><a href="auth/register.php"><i class="bi bi-person-plus me-2"></i>Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-title">Contact Support</h5>
                    <p class="footer-desc">Need help? Get in touch with our team.</p>
                    <div class="footer-links mt-3">
                        <li><a href="javascript:void(0);" onclick="showSupportUnderProcess(event);"><i class="bi bi-chat-dots me-2"></i>Support Center</a></li>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; 2026 EventHub. All rights reserved.</span>
                <div>
                    <a href="#" class="text-muted text-decoration-none me-3">Privacy Policy</a>
                    <a href="#" class="text-muted text-decoration-none">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.2 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
            crossorigin="anonymous"></script>

    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });
    </script>
    <script>
        // Live character counter for message textarea
        (function () {
            var msgBox = document.getElementById('message');
            var counter = document.getElementById('msgCharCount');
            if (msgBox && counter) {
                function updateCount() {
                    var len = msgBox.value.length;
                    var max = parseInt(msgBox.getAttribute('maxlength')) || 3000;
                    counter.textContent = len + ' / ' + max + ' characters';
                    counter.style.color = (len > max * 0.9) ? '#ef4444' : '';
                }
                updateCount();
                msgBox.addEventListener('input', updateCount);
            }
        })();
    </script>
</body>
</html>
