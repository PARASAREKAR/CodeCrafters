<?php
/**
 * Edit Event
 * 
 * Fetches an existing event (ownership-verified), pre-populates
 * the edit form, and processes the UPDATE on POST submission.
 *
 * @requires auth_check.php  – session bootstrap & role guard
 * @requires db_connect.php  – PDO $pdo connection
 * @requires helpers.php     – flash(), sanitize(), generateCsrfToken(), etc.
 */

require_once '../includes/auth_check.php';
requireRole(['Organizer']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$event_categories = [
    'Technology',
    'Business',
    'Education',
    'Health & Wellness',
    'Arts & Culture',
    'Sports',
    'Networking',
    'Workshop',
    'General'
];

$user_id  = $_SESSION['user_id'];
$event_id = isset($_GET['event_id']) ? (int) $_GET['event_id'] : 0;

// ── Fetch event (ownership check) ──────────────────────────────
$stmt = $pdo->prepare("SELECT * FROM events WHERE Event_ID = ? AND created_by = ?");
$stmt->execute([$event_id, $user_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    setFlashMessage('Event not found or access denied.', 'danger');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Handle POST submission ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid CSRF token. Please try again.', 'danger');
        header("Location: edit_event.php?event_id=$event_id");
        exit;
    }

    // Sanitise inputs
    $eventName   = trim(htmlspecialchars($_POST['Event_Name']   ?? '', ENT_QUOTES, 'UTF-8'));
    $description = trim(htmlspecialchars($_POST['Description']  ?? '', ENT_QUOTES, 'UTF-8'));
    $venue       = trim(htmlspecialchars($_POST['Venue']        ?? '', ENT_QUOTES, 'UTF-8'));
    $eventDate   = trim($_POST['Event_Date'] ?? '');
    $eventTime   = trim($_POST['Event_Time'] ?? '');
    $organizer   = trim(htmlspecialchars($_POST['Organizer']    ?? '', ENT_QUOTES, 'UTF-8'));
    $capacity    = (int) ($_POST['Capacity'] ?? 0);
    $category    = trim(htmlspecialchars($_POST['Event_Category'] ?? 'General', ENT_QUOTES, 'UTF-8'));

    // Validate category is in allowed list
    if (!in_array($category, $event_categories, true)) {
        $category = 'General';
    }

    // ── Validation ─────────────────────────────────────────────
    $errors = [];

    if (empty($eventName))  $errors[] = 'Event name is required.';
    if (empty($venue))      $errors[] = 'Venue is required.';
    if (empty($eventDate))  $errors[] = 'Event date is required.';
    if (empty($eventTime))  $errors[] = 'Event time is required.';
    if ($capacity < 1)      $errors[] = 'Capacity must be at least 1.';

    // ── Image Upload Handling ──────────────────────────────────
    $imagePath = $event['Image_Path']; // default to existing
    if (isset($_FILES['Event_Image']) && $_FILES['Event_Image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['Event_Image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload error occurred.';
        } elseif (!in_array($file['type'], $allowedTypes, true)) {
            $errors[] = 'Invalid file type. Only JPG, JPEG, PNG, and WEBP images are allowed.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'File size is too large. Maximum size allowed is 2MB.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid('event_', true) . '.' . $ext;
            $uploadDir = '../assets/images/uploads/';
            
            // Ensure folder exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                // Delete old image if exists
                if (!empty($event['Image_Path'])) {
                    $oldFile = '../' . $event['Image_Path'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                $imagePath = 'assets/images/uploads/' . $filename;
            } else {
                $errors[] = 'Failed to save the uploaded image.';
            }
        }
    }

    if (!empty($errors)) {
        setFlashMessage(implode('<br>', $errors), 'danger');
        header("Location: edit_event.php?event_id=$event_id");
        exit;
    }

    // ── UPDATE event ───────────────────────────────────────────
    $stmtUpdate = $pdo->prepare(
        "UPDATE events
         SET Event_Name = ?, Description = ?, Venue = ?, Event_Date = ?,
             Event_Time = ?, Organizer = ?, Capacity = ?, Event_Category = ?, Image_Path = ?
         WHERE Event_ID = ? AND created_by = ?"
    );
    $stmtUpdate->execute([
        $eventName, $description, $venue, $eventDate,
        $eventTime, $organizer, $capacity, $category, $imagePath,
        $event_id, $user_id
    ]);

    setFlashMessage('Event updated successfully!', 'success');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Generate CSRF token ────────────────────────────────────────
$csrfToken = generateCsrfToken();

$pageTitle = "Edit Event";
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Edit Event
                </h2>
                <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card card-custom glass-card">
                <div class="card-body p-4">
                    <form method="POST" action="edit_event.php?event_id=<?php echo (int) $event_id; ?>" enctype="multipart/form-data" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Event Name -->
                        <div class="mb-3">
                            <label for="Event_Name" class="form-label fw-semibold">
                                <i class="bi bi-bookmark me-1"></i>Event Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="Event_Name"
                                   name="Event_Name" required
                                   value="<?php echo htmlspecialchars($event['Event_Name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="Description" class="form-label fw-semibold">
                                <i class="bi bi-text-paragraph me-1"></i>Description
                            </label>
                            <textarea class="form-control form-control-custom" id="Description"
                                      name="Description" rows="4"><?php echo htmlspecialchars($event['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- Event Banner Image -->
                        <div class="mb-3">
                            <label for="Event_Image" class="form-label fw-semibold">
                                <i class="bi bi-image me-1"></i>Event Banner Image (Optional)
                            </label>
                            <?php if (!empty($event['Image_Path']) && file_exists('../' . $event['Image_Path'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">Current Image:</small>
                                    <img src="../<?php echo htmlspecialchars($event['Image_Path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Current Event Image" style="max-height: 120px; border-radius: 12px; border: 1px solid var(--border);">
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control form-control-custom" id="Event_Image" name="Event_Image" accept="image/*">
                            <small class="text-muted">Maximum file size: 2MB. Allowed formats: JPG, JPEG, PNG, WEBP.</small>
                        </div>

                        <!-- Event Category -->
                        <div class="mb-3">
                            <label for="Event_Category" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>Event Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-control-custom" id="Event_Category" name="Event_Category" required>
                                <?php
                                    $current_cat = $event['Event_Category'] ?? 'General';
                                    foreach ($event_categories as $cat):
                                ?>
                                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo ($current_cat === $cat) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Venue -->
                        <div class="mb-3">
                            <label for="Venue" class="form-label fw-semibold">
                                <i class="bi bi-geo-alt me-1"></i>Venue <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="Venue"
                                   name="Venue" required
                                   value="<?php echo htmlspecialchars($event['Venue'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="row">
                            <!-- Event Date -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date me-1"></i>Event Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-custom" id="Event_Date"
                                       name="Event_Date" required
                                       value="<?php echo htmlspecialchars($event['Event_Date'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Event Time -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Time" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Event Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control form-control-custom" id="Event_Time"
                                       name="Event_Time" required
                                       value="<?php echo htmlspecialchars($event['Event_Time'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <!-- Organizer Name -->
                            <div class="col-md-6 mb-3">
                                <label for="Organizer" class="form-label fw-semibold">
                                    <i class="bi bi-person-badge me-1"></i>Organizer
                                </label>
                                <input type="text" class="form-control form-control-custom" id="Organizer"
                                       name="Organizer"
                                       value="<?php echo htmlspecialchars($event['Organizer'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6 mb-3">
                                <label for="Capacity" class="form-label fw-semibold">
                                    <i class="bi bi-people me-1"></i>Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-custom" id="Capacity"
                                       name="Capacity" required min="1"
                                       value="<?php echo (int) $event['Capacity']; ?>">
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="organizer_dashboard.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-accent">
                                <i class="bi bi-check-circle me-1"></i>Update Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
