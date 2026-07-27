<?php
/**
 * auth_process.php – Authentication Processing (No HTML Output)
 * 
 * Handles two POST actions:
 *   • register – validates input, hashes password, inserts user
 *   • login    – verifies credentials, sets session, redirects by role
 * 
 * All database operations use PDO prepared statements.
 * Passwords are hashed with PASSWORD_BCRYPT.
 */

session_start();

require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

require_once '../src/PHPMailer.php';
require_once '../src/SMTP.php';
require_once '../src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ── Only accept POST requests ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

// ── Determine the requested action ──
$action = $_POST['action'] ?? '';

// ============================================================
//  HELPER: Set a flash message and redirect
// ============================================================
function flashRedirect(string $message, string $type, string $url): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type']    = $type;   // 'success', 'danger', 'warning', 'info'
    header("Location: $url");
    exit();
}

// ============================================================
//  HELPER: Validate the CSRF token
// ============================================================
function validateAuthCsrfToken(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        flashRedirect('Invalid security token. Please try again.', 'danger', 'login.php');
    }
}

// ============================================================
//  ACTION: REGISTER
// ============================================================
if ($action === 'register') {

    // 1. Validate CSRF token
    validateAuthCsrfToken();

    // 2. Sanitize all inputs
    $name             = sanitizeInput($_POST['name'] ?? '');
    $email            = sanitizeInput($_POST['email'] ?? '');
    $mobile           = sanitizeInput($_POST['mobile'] ?? '');
    $password         = $_POST['password'] ?? '';          // Don't trim passwords
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role             = sanitizeInput($_POST['role'] ?? '');

    // Preserve form data for repopulation on error
    $_SESSION['form_data'] = [
        'name'   => $name,
        'email'  => $email,
        'mobile' => $mobile,
        'role'   => $role,
    ];

    // 3. Validate required fields
    if (empty($name) || empty($email) || empty($mobile) || empty($password) || empty($confirm_password) || empty($role)) {
        flashRedirect('All fields are required.', 'danger', 'register.php');
    }

    // 4. Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flashRedirect('Please enter a valid email address.', 'danger', 'register.php');
    }

    // 5. Validate mobile pattern (exactly 10 digits)
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        flashRedirect('Please enter a valid 10-digit mobile number.', 'danger', 'register.php');
    }

    // 6. Validate password strength (Google-style rules)

    // 6a. No leading or trailing spaces
    if ($password !== trim($password)) {
        flashRedirect('Password cannot start or end with a blank space.', 'danger', 'register.php');
    }

    // 6b. Minimum length: 8 characters
    if (strlen($password) < 8) {
        flashRedirect('Password must be at least 8 characters long.', 'danger', 'register.php');
    }

    // 6c. Require uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        flashRedirect('Password must contain at least one uppercase letter.', 'danger', 'register.php');
    }

    // 6d. Require lowercase letter
    if (!preg_match('/[a-z]/', $password)) {
        flashRedirect('Password must contain at least one lowercase letter.', 'danger', 'register.php');
    }

    // 6e. Require digit
    if (!preg_match('/[0-9]/', $password)) {
        flashRedirect('Password must contain at least one number.', 'danger', 'register.php');
    }

    // 6f. Require special character
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        flashRedirect('Password must contain at least one special character.', 'danger', 'register.php');
    }

    // 6g. Block commonly-breached / easily-guessable passwords
    $commonPasswords = [
        'password', 'password1', 'password123', 'password1234', '123456789012',
        'qwerty123456', 'letmein1234', 'welcome12345', 'admin12345', 'master12345',
        'iloveyou1234', 'trustno1234', 'sunshine1234', 'princess1234', 'football1234',
        'charlie12345', 'shadow123456', 'michael12345', 'donald123456', 'batman123456',
        'access123456', 'dragon123456', 'monkey123456', 'mustang12345', 'qwerty1234567',
        'abcdef123456', 'abc123456789', 'passw0rd1234', 'p@ssword1234', 'p@ssw0rd1234',
        '123456abcdef', 'qwertyuiop12', 'asdfghjkl123', 'zxcvbnm12345', '1234567890ab',
        'changeme1234', 'letmein12345', 'welcome1234!', 'password!234', 'test12345678',
    ];
    if (in_array(strtolower($password), $commonPasswords, true)) {
        flashRedirect('This password is too common and easily guessable. Please choose a stronger one.', 'danger', 'register.php');
    }

    // 6h. Block keyboard sequential patterns (qwerty, asdf, zxcvb, etc.)
    $keyboardPatterns = [
        'qwerty', 'qwertyui', 'qwertyuiop', 'asdfgh', 'asdfghjk', 'asdfghjkl',
        'zxcvbn', 'zxcvbnm', '1234567', '12345678', '123456789', '1234567890',
        '0987654', '09876543', '098765432', '0987654321',
        'abcdefg', 'abcdefgh', 'abcdefghi', 'abcdefghij',
    ];
    $passwordLower = strtolower($password);
    foreach ($keyboardPatterns as $pattern) {
        if (strpos($passwordLower, $pattern) !== false || strpos($passwordLower, strrev($pattern)) !== false) {
            flashRedirect('Password contains a keyboard or sequential pattern. Please avoid predictable sequences.', 'danger', 'register.php');
        }
    }

    // 6i. Block password containing user's name or email local part
    $nameLower = strtolower($name);
    $emailLocal = strtolower(explode('@', $email)[0]);
    if (strlen($nameLower) >= 3 && strpos($passwordLower, $nameLower) !== false) {
        flashRedirect('Password should not contain your name.', 'danger', 'register.php');
    }
    if (strlen($emailLocal) >= 3 && strpos($passwordLower, $emailLocal) !== false) {
        flashRedirect('Password should not contain your email address.', 'danger', 'register.php');
    }

    // 7. Passwords must match
    if ($password !== $confirm_password) {
        flashRedirect('Passwords do not match.', 'danger', 'register.php');
    }

    // 8. Only allow Participant or Organizer (block Admin self-registration)
    $allowedRoles = ['Participant', 'Organizer'];
    if (!in_array($role, $allowedRoles, true)) {
        flashRedirect('Invalid role selected.', 'danger', 'register.php');
    }

    // 9. Check if email already exists
    try {
        $stmt = $pdo->prepare('SELECT User_ID FROM users WHERE Email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);

        if ($stmt->fetch()) {
            flashRedirect('An account with this email already exists.', 'warning', 'register.php');
        }
    } catch (PDOException $e) {
        error_log('Registration email-check error: ' . $e->getMessage());
        flashRedirect('Something went wrong. Please try again later.', 'danger', 'register.php');
    }

    // 10. Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // 10a. Handle profile picture upload (if present and valid)
    $profile_pic_path = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileSize = $_FILES['profile_pic']['size'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            // Check size (2MB)
            if ($fileSize <= 2 * 1024 * 1024) {
                // Ensure upload directory exists
                $uploadDir = '../assets/images/uploads/profile_pics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $profile_pic_path = 'assets/images/uploads/profile_pics/' . $newFileName;
                }
            } else {
                flashRedirect('Profile picture size exceeds 2MB.', 'danger', 'register.php');
            }
        } else {
            flashRedirect('Invalid profile picture file type. Only JPG, PNG, and GIF allowed.', 'danger', 'register.php');
        }
    }

    // 11. Insert the new user
    try {
        $accountStatus = ($role === 'Organizer') ? 'Pending' : 'Approved';

        $stmt = $pdo->prepare(
            'INSERT INTO users (Name, Email, Mobile, Password, Role, Account_Status, Profile_Pic, created_at)
             VALUES (:name, :email, :mobile, :password, :role, :account_status, :profile_pic, NOW())'
        );
        $stmt->execute([
            ':name'           => $name,
            ':email'          => $email,
            ':mobile'         => $mobile,
            ':password'       => $hashedPassword,
            ':role'           => $role,
            ':account_status' => $accountStatus,
            ':profile_pic'    => $profile_pic_path,
        ]);

        // Clear preserved form data on success
        unset($_SESSION['form_data']);

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'eventoraganizers2026@gmail.com';
            $mail->Password = 'gdtfdzdcubqpenyq';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('eventoraganizers2026@gmail.com', 'Event Registration System');
            $mail->addAddress($email, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Event Registration System 🎉';

            $mail->Body = "
            <div style='font-family:Arial,sans-serif;line-height:1.7;color:#333;'>

                <h2 style='color:#0d6efd;'>Welcome, {$name}! 🎉</h2>

                <p>Your account has been created successfully.</p>

                <hr>

                <h3>Account Details</h3>

                <p><b>Name:</b> {$name}</p>
                <p><b>Email:</b> {$email}</p>
                <p><b>Role:</b> {$role}</p>

                <hr>

                <p>You can now log in and start using the Event Registration System.</p>

                <p style='color:green;font-weight:bold;'>
                ✅ Registration Successful
                </p>

                <br>

                <p>
                Regards,<br>
                <b>Event Registration System Team</b><br>
                eventoraganizers2026@gmail.com
                </p>

            </div>
            ";

            $mail->send();

        }catch (Exception $e) {
             die("Registration Mail Error: " . $mail->ErrorInfo);
        }

        flashRedirect('Registration successful! You can now log in.', 'success', 'login.php');

    } catch (PDOException $e) {
        die("Registration Query Error: " . $e->getMessage());
    }
}
// ============================================================
//  ACTION: LOGIN
// ============================================================
elseif ($action === 'login') {

    // 1. Validate CSRF token
    validateAuthCsrfToken();

    // 2. Sanitize inputs
    $email    = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';   // Don't trim passwords

    // 3. Basic validation
    if (empty($email) || empty($password)) {
        flashRedirect('Email and password are required.', 'danger', 'login.php');
    }

    // 4. Look up the user by email
    try {
        $stmt = $pdo->prepare(
            'SELECT User_ID, Name, Email, Password, Role, Account_Status FROM users WHERE Email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Login query error: ' . $e->getMessage());

        flashRedirect(
            'Something went wrong. Please try again later.',
            'danger',
            'login.php'
        );
    }

    // 5. Verify password
    if (!$user || !password_verify($password, $user['Password'])) {
        flashRedirect('Invalid email or password.', 'danger', 'login.php');
    }

    // 5a. Check Account Status
    if ($user['Account_Status'] === 'Pending') {
        flashRedirect('Your account is pending admin approval. Please wait for an admin to accept your request.', 'warning', 'login.php');
    }

    if ($user['Account_Status'] === 'Rejected') {
        flashRedirect('Your account request has been rejected by an admin.', 'danger', 'login.php');
    }

    // 6. Generate OTP and store temporary credentials
    $otp = rand(100000, 999999);
    $_SESSION['temp_user'] = [
        'User_ID'     => $user['User_ID'],
        'Name'        => $user['Name'],
        'Email'       => $user['Email'],
        'Role'        => $user['Role'],
        'Profile_Pic' => $user['Profile_Pic']
    ];
    $_SESSION['login_otp']        = $otp;
    $_SESSION['login_otp_expiry'] = time() + 300; // 5 minutes validity
    $_SESSION['otp_attempts']     = 0;
    $mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'eventoraganizers2026@gmail.com';
    $mail->Password = 'gdtfdzdcubqpenyq';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('eventoraganizers2026@gmail.com', 'Event Registration System');
    $mail->addAddress($user['Email']);

    $mail->isHTML(true);
    $mail->Subject = 'Login OTP';
    $mail->Body = "<h2>Your OTP is: <b>$otp</b></h2>";

    $mail->send();
    

} catch (Exception $e) {
    die("Mailer Error: " . $mail->ErrorInfo);
}


    // Redirect to OTP verification page
    header('Location: otp_verify.php');
    exit();
}

// ============================================================
//  UNKNOWN ACTION – Redirect to login
// ============================================================
else {
    header('Location: login.php');
    exit();
}
