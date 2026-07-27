<?php
require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$user_id = $_SESSION['user_id'];

// Fetch current user details
try {
    $stmt = $pdo->prepare("SELECT Name, Email, Mobile, Profile_Pic FROM users WHERE User_ID = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        setFlashMessage('danger', 'User profile not found.');
        redirectTo('organizer_dashboard.php');
    }
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token. Please try again.";
    }

    $name = trim(htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL));
    $mobile = trim(htmlspecialchars($_POST['mobile'] ?? '', ENT_QUOTES, 'UTF-8'));
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $errors[] = "Name cannot be empty.";
    }
    if (!$email) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Check if email already in use by someone else
    if ($email && $email !== $user['Email']) {
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE Email = ? AND User_ID != ?");
        $stmtCheck->execute([$email, $user_id]);
        if ($stmtCheck->fetchColumn() > 0) {
            $errors[] = "This email address is already in use by another account.";
        }
    }

    // Handle Password Change
    $update_password = false;
    if (!empty($new_password)) {
        if (strlen($new_password) < 8) {
            $errors[] = "New password must be at least 8 characters.";
        }
        if (!preg_match('/[A-Z]/', $new_password)) {
            $errors[] = "New password must contain at least one uppercase letter.";
        }
        if (!preg_match('/[a-z]/', $new_password)) {
            $errors[] = "New password must contain at least one lowercase letter.";
        }
        if (!preg_match('/[0-9]/', $new_password)) {
            $errors[] = "New password must contain at least one number.";
        }
        if (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
            $errors[] = "New password must contain at least one special character.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        $update_password = true;
    }

    // Handle Profile Picture Upload
    $profile_pic_path = $user['Profile_Pic'];
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileSize = $_FILES['profile_pic']['size'];
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) {
                $uploadDir = '../assets/images/uploads/profile_pics/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                $dest_path = $uploadDir . $newFileName;
                
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Delete old profile picture if it exists
                    if (!empty($user['Profile_Pic']) && file_exists('../' . $user['Profile_Pic'])) {
                        @unlink('../' . $user['Profile_Pic']);
                    }
                    $profile_pic_path = 'assets/images/uploads/profile_pics/' . $newFileName;
                } else {
                    $errors[] = "Failed to save profile picture file.";
                }
            } else {
                $errors[] = "Profile picture size exceeds 2MB.";
            }
        } else {
            $errors[] = "Only JPG, PNG, and GIF images are allowed.";
        }
    }

    if (empty($errors)) {
        try {
            if ($update_password) {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmtUpdate = $pdo->prepare(
                    "UPDATE users SET Name = ?, Email = ?, Mobile = ?, Profile_Pic = ?, Password = ? WHERE User_ID = ?"
                );
                $stmtUpdate->execute([$name, $email, $mobile, $profile_pic_path, $hashed, $user_id]);
            } else {
                $stmtUpdate = $pdo->prepare(
                    "UPDATE users SET Name = ?, Email = ?, Mobile = ?, Profile_Pic = ? WHERE User_ID = ?"
                );
                $stmtUpdate->execute([$name, $email, $mobile, $profile_pic_path, $user_id]);
            }
            
            // Sync Session details
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_avatar'] = $profile_pic_path;
            
            $success = true;
            
            // Re-fetch current user details to update display
            $user['Name'] = $name;
            $user['Email'] = $email;
            $user['Mobile'] = $mobile;
            $user['Profile_Pic'] = $profile_pic_path;
            
            setFlashMessage('success', 'Profile updated successfully.');
            redirectTo('edit_profile.php');
            
        } catch (PDOException $e) {
            $errors[] = "Failed to update profile: " . $e->getMessage();
        }
    } else {
        setFlashMessage('danger', implode('<br>', $errors));
        redirectTo('edit_profile.php');
    }
}

// Generate CSRF token
$csrfToken = generateCsrfToken();

$pageTitle = "Edit Profile";
require_once '../includes/header.php';
?>

<div class="row justify-content-center" data-aos="fade-up">
    <div class="col-lg-8 col-xl-7">
        <div class="card glass-card shadow-lg mb-4" style="border-radius: 20px;">
            <div class="card-body p-4 p-md-5">
                <h3 class="fw-bold mb-1 text-center"><i class="bi bi-person-gear text-accent me-2"></i>Edit Profile</h3>
                <p class="text-muted text-center mb-4">Modify your profile picture, personal information, or change password.</p>

                <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                    <!-- Avatar Preview & Upload -->
                    <div class="text-center mb-4">
                        <?php if (!empty($user['Profile_Pic']) && file_exists('../' . $user['Profile_Pic'])): ?>
                            <img src="../<?php echo htmlspecialchars($user['Profile_Pic'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="Avatar Preview" class="rounded-circle mb-2" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid var(--accent); box-shadow: var(--shadow);">
                        <?php else: ?>
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 120px; height: 120px; background: rgba(255,255,255,0.03); border: 2px dashed var(--border);">
                                <i class="bi bi-person display-4 text-muted"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-2">
                            <label for="profile_pic" class="btn btn-sm btn-outline-accent px-3">
                                <i class="bi bi-upload me-1"></i>Upload Photo
                            </label>
                            <input type="file" id="profile_pic" name="profile_pic" accept="image/*" class="d-none" onchange="previewAvatar(event)">
                            <div class="form-text text-muted small mt-1">Accepts JPG, PNG, GIF up to 2MB.</div>
                        </div>
                    </div>

                    <!-- User Metadata -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label text-muted small fw-bold">Full Name</label>
                            <input type="text" class="form-control form-control-custom" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($user['Name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="mobile" class="form-label text-muted small fw-bold">Mobile Number</label>
                            <input type="text" class="form-control form-control-custom" id="mobile" name="mobile" 
                                   value="<?php echo htmlspecialchars($user['Mobile'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="col-12">
                            <label for="email" class="form-label text-muted small fw-bold">Email Address</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($user['Email'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <hr class="my-4" style="background-color: var(--border);">

                        <!-- Password Fields (Optional) -->
                        <h5 class="fw-bold mb-1"><i class="bi bi-key me-2 text-accent"></i>Change Password (Optional)</h5>
                        <p class="text-muted small mb-3">Leave blank if you do not wish to change your password.</p>

                        <div class="col-md-6">
                            <label for="new_password" class="form-label text-muted small fw-bold">New Password</label>
                            <input type="password" class="form-control form-control-custom" id="new_password" name="new_password" 
                                   placeholder="Enter new password">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label text-muted small fw-bold">Confirm New Password</label>
                            <input type="password" class="form-control form-control-custom" id="confirm_password" name="confirm_password" 
                                   placeholder="Re-enter new password">
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 text-center mt-5 d-flex gap-2 justify-content-center">
                            <a href="organizer_dashboard.php" class="btn btn-outline-secondary px-4 py-2">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-accent px-5 py-2">
                                <i class="bi bi-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            let preview = document.querySelector('img.rounded-circle.mb-2');
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Replace placeholder div with img element
                const placeholder = document.querySelector('div.rounded-circle.mb-2');
                if (placeholder) {
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.className = 'rounded-circle mb-2';
                    newImg.style.width = '120px';
                    newImg.style.height = '120px';
                    newImg.style.objectFit = 'cover';
                    newImg.style.border = '3px solid var(--accent)';
                    newImg.style.boxShadow = 'var(--shadow)';
                    newImg.alt = 'Avatar Preview';
                    placeholder.parentNode.replaceChild(newImg, placeholder);
                }
            }
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
