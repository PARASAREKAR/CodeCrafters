<?php
/**
 * Admin — View All Payments (Full Details)
 */
require_once '../includes/auth_check.php';
requireRole(['Admin']);
require_once '../config/db_connect.php';
require_once '../includes/helpers.php';

$stmt = $pdo->query(
    "SELECT p.payment_id, p.qr_token, p.qr_viewed_count, p.amount, p.status AS pay_status,
            p.paid_at, p.created_at AS qr_sent_at,
            r.Registration_ID, r.Registration_Date, r.Status AS reg_status, r.organizer_approved,
            u.Name AS participant_name, u.Email AS participant_email, u.Mobile,
            e.Event_Name, e.Event_Date, e.Venue, e.Event_Fee,
            org.Name AS organizer_name
     FROM payments p
     JOIN registrations r ON p.registration_id = r.Registration_ID
     JOIN users u          ON r.User_ID  = u.User_ID
     JOIN events e         ON r.Event_ID = e.Event_ID
     LEFT JOIN users org   ON e.created_by = org.User_ID
     ORDER BY p.created_at DESC"
);
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalPaid    = array_sum(array_map(fn($p) => ($p['pay_status'] === 'Paid' ? $p['amount'] : 0), $payments));
$totalPending = count(array_filter($payments, fn($p) => $p['pay_status'] === 'Pending'));

$pageTitle = "Payment Details";
require_once '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-down">
    <div>
        <h2 class="fw-bold mb-1"><i class="bi bi-qr-code-scan me-2 text-accent"></i>Payment Records</h2>
        <p class="text-muted mb-0">Full payment details for all registrations with QR payment flow.</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4" data-aos="fade-up">
        <div class="card glass-card p-4 text-center">
            <div class="fs-1 mb-1">💰</div>
            <div class="fw-bold fs-3" style="color:var(--accent);">₹<?php echo number_format($totalPaid, 2); ?></div>
            <div class="text-muted small">Total Revenue Collected</div>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card glass-card p-4 text-center">
            <div class="fs-1 mb-1">✅</div>
            <div class="fw-bold fs-3 text-success"><?php echo count(array_filter($payments, fn($p) => $p['pay_status'] === 'Paid')); ?></div>
            <div class="text-muted small">Payments Confirmed</div>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card glass-card p-4 text-center">
            <div class="fs-1 mb-1">⏳</div>
            <div class="fw-bold fs-3 text-warning"><?php echo $totalPending; ?></div>
            <div class="text-muted small">Payments Pending</div>
        </div>
    </div>
</div>

<div class="card glass-card" data-aos="fade-up">
    <div class="card-header bg-transparent border-0 pt-4 px-4">
        <h5 class="fw-bold mb-0">📋 All Payment Records</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($payments)): ?>
            <div class="p-5 text-center text-muted">
                <i class="bi bi-qr-code fs-1 mb-2 d-block"></i>
                <p>No payment records yet. They appear when organizers accept registrations.</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-custom align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Participant</th>
                        <th>Event</th>
                        <th>Amount</th>
                        <th>Pay Status</th>
                        <th>QR Views</th>
                        <th>Organizer Approved</th>
                        <th>Paid At</th>
                        <th class="pe-4">QR Token</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): ?>
                    <tr class="table-custom-row">
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($p['participant_name']); ?></div>
                            <div class="text-muted small"><?php echo htmlspecialchars($p['participant_email']); ?></div>
                            <div class="text-muted small"><i class="bi bi-phone me-1"></i><?php echo htmlspecialchars($p['Mobile'] ?? '—'); ?></div>
                        </td>
                        <td>
                            <div class="fw-semibold" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($p['Event_Name']); ?></div>
                            <div class="text-muted small"><?php echo date('d M Y', strtotime($p['Event_Date'])); ?></div>
                            <div class="text-muted small">Org: <?php echo htmlspecialchars($p['organizer_name'] ?? '—'); ?></div>
                        </td>
                        <td><span class="fw-bold" style="color:var(--accent);">₹<?php echo number_format($p['amount'], 2); ?></span></td>
                        <td>
                            <?php if ($p['pay_status'] === 'Paid'): ?>
                                <span class="badge bg-success">✅ Paid</span>
                            <?php elseif ($p['pay_status'] === 'Pending'): ?>
                                <span class="badge bg-warning text-dark">⏳ Pending</span>
                            <?php else: ?>
                                <span class="badge bg-danger"><?php echo htmlspecialchars($p['pay_status']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?php echo (int)$p['qr_viewed_count']; ?> / 2</span>
                        </td>
                        <td>
                            <?php if ($p['organizer_approved']): ?>
                                <span class="badge bg-success">✅ Yes</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">No</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?php echo $p['paid_at'] ? date('d M Y H:i', strtotime($p['paid_at'])) : '—'; ?>
                        </td>
                        <td class="pe-4">
                            <code style="font-size:10px;word-break:break-all;"><?php echo htmlspecialchars(substr($p['qr_token'], 0, 20) . '...'); ?></code>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
