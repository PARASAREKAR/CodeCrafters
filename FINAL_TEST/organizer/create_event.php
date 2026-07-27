<?php
/**
 * Create Event
 * 
 * Renders a creation form and processes POST submissions.
 * Validates CSRF token, sanitises all inputs, checks business
 * rules (future date, capacity > 0), then INSERTs via PDO.
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

// ── Current organizer info ─────────────────────────────────────
$user_id   = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// ── Handle POST submission ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('Invalid CSRF token. Please try again.', 'danger');
        header('Location: create_event.php');
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
    $eventFee    = max(0, (float) ($_POST['Event_Fee'] ?? 0));

    // Validate category is in allowed list
    if (!in_array($category, $event_categories, true)) {
        $category = 'General';
    }

    // ── Validation ─────────────────────────────────────────────
    $errors = [];

    if (empty($eventName))                          $errors[] = 'Event name is required.';
    if (empty($venue))                              $errors[] = 'Venue is required.';
    if (empty($eventDate))                          $errors[] = 'Event date is required.';
    if ($eventDate < date('Y-m-d'))                 $errors[] = 'Event date cannot be in the past.';
    if (empty($eventTime))                          $errors[] = 'Event time is required.';
    if ($capacity < 1)                              $errors[] = 'Capacity must be at least 1.';

    // ── Image Upload Handling ──────────────────────────────────
    $imagePath = null;
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
                $imagePath = 'assets/images/uploads/' . $filename;
            } else {
                $errors[] = 'Failed to save the uploaded image.';
            }
        }
    }

    if (!empty($errors)) {
        setFlashMessage(implode('<br>', $errors), 'danger');
        header('Location: create_event.php');
        exit;
    }

    // ── INSERT event ───────────────────────────────────────────
    $stmt = $pdo->prepare(
        "INSERT INTO events (Event_Name, Description, Venue, Event_Date, Event_Time, Organizer, Capacity, Event_Fee, Event_Category, Image_Path, created_by, Status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')"
    );
    $stmt->execute([$eventName, $description, $venue, $eventDate, $eventTime, $organizer, $capacity, $eventFee, $category, $imagePath, $user_id]);

    setFlashMessage('Event created successfully! It is now pending admin approval.', 'success');
    header('Location: organizer_dashboard.php');
    exit;
}

// ── Generate CSRF token for form ───────────────────────────────
$csrfToken = generateCsrfToken();

$pageTitle = "Create Event";
require_once '../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Create New Event
                </h2>
                <a href="organizer_dashboard.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>

            <?php displayFlashMessage(); ?>

            <div class="card card-custom glass-card">
                <div class="card-body p-4">
                    <form method="POST" action="create_event.php" enctype="multipart/form-data" novalidate>
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>">

                        <!-- Event Name -->
                        <div class="mb-3">
                            <label for="Event_Name" class="form-label fw-semibold">
                                <i class="bi bi-bookmark me-1"></i>Event Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-custom" id="Event_Name"
                                   name="Event_Name" required placeholder="Enter event name"
                                   value="<?php echo htmlspecialchars($_POST['Event_Name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="Description" class="form-label fw-semibold">
                                <i class="bi bi-text-paragraph me-1"></i>Description
                            </label>
                            <textarea class="form-control form-control-custom" id="Description"
                                      name="Description" rows="4"
                                      placeholder="Describe your event"><?php echo htmlspecialchars($_POST['Description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <!-- Event Banner Image -->
                        <div class="mb-3">
                            <label for="Event_Image" class="form-label fw-semibold">
                                <i class="bi bi-image me-1"></i>Event Banner Image (Optional)
                            </label>
                            <input type="file" class="form-control form-control-custom" id="Event_Image" name="Event_Image" accept="image/*">
                            <small class="text-muted">Maximum file size: 2MB. Allowed formats: JPG, JPEG, PNG, WEBP.</small>
                        </div>

                        <!-- Event Category -->
                        <div class="mb-3">
                            <label for="Event_Category" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>Event Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select form-control-custom" id="Event_Category" name="Event_Category" required>
                                <?php foreach ($event_categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"
                                        <?php echo (isset($_POST['Event_Category']) && $_POST['Event_Category'] === $cat) ? 'selected' : ''; ?>>
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
                                   name="Venue" required placeholder="Event venue / location"
                                   value="<?php echo htmlspecialchars($_POST['Venue'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="row">
                            <!-- Event Date -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar-date me-1"></i>Event Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control form-control-custom" id="Event_Date"
                                       name="Event_Date" required min="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo htmlspecialchars($_POST['Event_Date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Event Time -->
                            <div class="col-md-6 mb-3">
                                <label for="Event_Time" class="form-label fw-semibold">
                                    <i class="bi bi-clock me-1"></i>Event Time <span class="text-danger">*</span>
                                </label>
                                <input type="time" class="form-control form-control-custom" id="Event_Time"
                                       name="Event_Time" required
                                       value="<?php echo htmlspecialchars($_POST['Event_Time'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
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
                                       value="<?php echo htmlspecialchars($_POST['Organizer'] ?? $user_name, ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <!-- Capacity -->
                            <div class="col-md-6 mb-3">
                                <label for="Capacity" class="form-label fw-semibold">
                                    <i class="bi bi-people me-1"></i>Capacity <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control form-control-custom" id="Capacity"
                                       name="Capacity" required min="1" placeholder="Max participants"
                                       value="<?php echo htmlspecialchars($_POST['Capacity'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                        </div>

                        <!-- Event Fee -->
                        <div class="mb-3">
                            <label for="Event_Fee" class="form-label fw-semibold">
                                <i class="bi bi-currency-rupee me-1"></i>Registration Fee (₹)
                                <small class="text-muted fw-normal ms-1">(Enter 0 for free event)</small>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-currency-rupee"></i></span>
                                <input type="number" class="form-control form-control-custom" id="Event_Fee"
                                       name="Event_Fee" min="0" step="0.01" placeholder="0.00"
                                       value="<?php echo htmlspecialchars($_POST['Event_Fee'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                            </div>
                            <div class="form-text text-muted">This fee will be shown to participants and used for payment QR generation.</div>
                        </div>

                        <!-- Submit -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="organizer_dashboard.php" class="btn btn-outline-secondary me-md-2">
                                <i class="bi bi-x-circle me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-accent">
                                <i class="bi bi-check-circle me-1"></i>Create Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
