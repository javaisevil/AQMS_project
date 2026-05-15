<?php
require_once '../includes/auth_check.php';
requireRole('hod');
require_once '../db.php';

$course_id = intval($_GET['id'] ?? 0);
if (!$course_id) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare(
    'SELECT cs.*, ps.program_name, u.full_name as faculty_name
     FROM course_specs cs
     LEFT JOIN program_specs ps ON cs.program_id = ps.program_id
     LEFT JOIN user u ON cs.faculty_id = u.user_id
     WHERE cs.course_id = ?'
);
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: dashboard.php');
    exit();
}

$page_title = 'Review: ' . $course['course_title'];
$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $comment = trim($_POST['comment'] ?? '');

    if ($action === 'approve' && $course['status'] === 'pending_hod') {
        $pdo->prepare('UPDATE course_specs SET status = "pending_qa" WHERE course_id = ?')->execute([$course_id]);
        $pdo->prepare(
            'INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, "pending_hod", "pending_qa", ?)'
        )->execute([$course_id, $_SESSION['user_id'], $comment ?: 'Approved by HoD']);
        $msg = 'Course approved and forwarded to QA.';
        $course['status'] = 'pending_qa';

    } elseif ($action === 'reject' && $course['status'] === 'pending_hod') {
        if (!$comment) {
            $error = 'Please provide a comment explaining what needs to be revised.';
        } else {
            $pdo->prepare('UPDATE course_specs SET status = "draft" WHERE course_id = ?')->execute([$course_id]);
            $pdo->prepare(
                'INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, "pending_hod", "draft", ?)'
            )->execute([$course_id, $_SESSION['user_id'], $comment]);
            $msg = 'Course returned to faculty with your comments.';
            $course['status'] = 'draft';
        }
    }
}

$clos = $pdo->prepare(
    'SELECT cl.*, GROUP_CONCAT(p.plo_code ORDER BY p.plo_code SEPARATOR ", ") as plo_codes
     FROM course_learning_outcomes cl
     LEFT JOIN clo_plo_mapping m ON cl.clo_id = m.clo_id
     LEFT JOIN program_learning_outcomes p ON m.plo_id = p.plo_id
     WHERE cl.course_id = ?
     GROUP BY cl.clo_id
     ORDER BY cl.category, cl.clo_code'
);
$clos->execute([$course_id]);
$clos = $clos->fetchAll();

$assessments = $pdo->prepare('SELECT * FROM assessments WHERE course_id = ? ORDER BY timing_week');
$assessments->execute([$course_id]);
$assessments = $assessments->fetchAll();

$logs = $pdo->prepare(
    'SELECT al.*, u.full_name, u.role FROM approval_log al
     JOIN user u ON al.user_id = u.user_id
     WHERE al.course_id = ? ORDER BY al.created_at DESC'
);
$logs->execute([$course_id]);
$logs = $logs->fetchAll();

include '../includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap;">

    <div style="flex:1; min-width:0;">

        <div class="card">
            <div class="card-header">
                <div>
                    <h2><?php echo htmlspecialchars($course['course_title']); ?></h2>
                    <small class="text-muted">
                        <?php echo htmlspecialchars($course['course_code']); ?> ·
                        Faculty: <?php echo htmlspecialchars($course['faculty_name']); ?> ·
                        Program: <?php echo htmlspecialchars($course['program_name'] ?? '—'); ?>
                    </small>
                </div>
                <span class="status-badge status-<?php echo $course['status']; ?>" style="font-size:12px; padding:5px 12px;">
                    <?php echo ucwords(str_replace('_', ' ', $course['status'])); ?>
                </span>
            </div>

            <table style="margin-bottom:0;">
                <tr><td style="width:180px; color:var(--text-muted);">Level</td><td><?php echo htmlspecialchars($course['course_level']); ?></td></tr>
                <tr><td style="color:var(--text-muted);">Credit Hours</td><td><?php echo htmlspecialchars($course['credit_hours']); ?></td></tr>
                <tr><td style="color:var(--text-muted);">Course Type</td><td><?php echo htmlspecialchars($course['course_type']); ?></td></tr>
                <tr><td style="color:var(--text-muted);">Teaching Mode</td><td><?php echo htmlspecialchars($course['teaching_mode']); ?></td></tr>
                <tr><td style="color:var(--text-muted);">Pre-requisites</td><td><?php echo htmlspecialchars($course['prerequisites'] ?: '—'); ?></td></tr>
            </table>
        </div>

        <?php if ($course['course_description'] || $course['objectives']): ?>
        <div class="card">
            <div class="card-header"><h2>Description &amp; Objectives</h2></div>
            <?php if ($course['course_description']): ?>
                <p style="margin-bottom:12px;"><?php echo nl2br(htmlspecialchars($course['course_description'])); ?></p>
            <?php endif; ?>
            <?php if ($course['objectives']): ?>
                <p style="margin-top:8px;"><strong>Objectives:</strong><br><?php echo nl2br(htmlspecialchars($course['objectives'])); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h2>Course Learning Outcomes (CLOs)</h2></div>
            <?php if (empty($clos)): ?>
                <p class="empty-state">No CLOs entered.</p>
            <?php else: ?>
            <table>
                <thead>
                    <tr><th>Code</th><th>Description</th><th>Category</th><th>Mapped PLOs</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($clos as $clo): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($clo['clo_code']); ?></strong></td>
                        <td><?php echo htmlspecialchars($clo['description']); ?></td>
                        <td><?php echo htmlspecialchars($clo['category']); ?></td>
                        <td>
                            <?php if ($clo['plo_codes']): ?>
                                <span class="text-success" style="font-weight:600;"><?php echo htmlspecialchars($clo['plo_codes']); ?></span>
                            <?php else: ?>
                                <span class="text-danger">⚠ Not mapped</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header"><h2>Assessment Plan</h2></div>
            <?php if (empty($assessments)): ?>
                <p class="empty-state">No assessments entered.</p>
            <?php else: ?>
            <table>
                <thead><tr><th>Activity</th><th>Week</th><th>Weight (%)</th></tr></thead>
                <tbody>
                    <?php $total = 0; foreach ($assessments as $a): $total += $a['percentage']; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a['activity_name']); ?></td>
                        <td><?php echo htmlspecialchars($a['timing_week']); ?></td>
                        <td><?php echo htmlspecialchars($a['percentage']); ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight:600; background:#fafafa;">
                        <td colspan="2">Total</td>
                        <td style="color:<?php echo abs($total - 100) < 0.01 ? 'var(--success)' : 'var(--danger)'; ?>;">
                            <?php echo $total; ?>%
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div style="display:flex; gap:10px; margin-bottom:20px;">
            <a href="<?php echo BASE_URL; ?>/course/view.php?id=<?php echo $course_id; ?>" class="btn btn-outline" target="_blank">View Full Spec</a>
            <a href="dashboard.php" class="btn btn-ghost">← Back to Queue</a>
        </div>

    </div>

    <div style="width:320px; min-width:280px;">

        <?php if ($course['status'] === 'pending_hod'): ?>
        <div class="card">
            <div class="card-header"><h2>Decision</h2></div>
            <form method="POST">
                <div class="form-group">
                    <label for="comment">Comment / Feedback</label>
                    <textarea id="comment" name="comment" rows="5"
                              placeholder="Optional for approval. Required when returning to faculty."></textarea>
                </div>
                <div style="display:flex; flex-direction:column; gap:8px;">
                    <button type="submit" name="action" value="approve" class="btn btn-success btn-full"
                            onclick="return confirm('Approve and forward to QA?')">
                        ✓ Approve — Forward to QA
                    </button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-full"
                            onclick="return confirm('Return to faculty? Make sure you wrote a comment.')">
                        ↺ Return to Faculty
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <p class="text-muted text-center" style="padding:12px 0;">
                Status: <strong><?php echo ucwords(str_replace('_', ' ', $course['status'])); ?></strong><br>
                <span style="font-size:12px;">No HoD action required at this stage.</span>
            </p>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header"><h2>Activity Log</h2></div>
            <?php if (empty($logs)): ?>
                <p class="text-muted">No activity yet.</p>
            <?php else: ?>
            <ul class="timeline">
                <?php foreach ($logs as $log): ?>
                <li>
                    <div class="timeline-date">
                        <?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?>
                        <span class="role-badge role-<?php echo $log['role']; ?>"><?php echo strtoupper($log['role']); ?></span>
                    </div>
                    <div class="timeline-text">
                        <?php echo htmlspecialchars($log['full_name']); ?> ·
                        <span class="status-badge status-<?php echo $log['from_status']; ?>">
                            <?php echo $log['from_status'] ? ucwords(str_replace('_', ' ', $log['from_status'])) : 'New'; ?>
                        </span>
                        →
                        <span class="status-badge status-<?php echo $log['to_status']; ?>">
                            <?php echo ucwords(str_replace('_', ' ', $log['to_status'])); ?>
                        </span>
                    </div>
                    <?php if ($log['comment']): ?>
                        <div class="timeline-comment">"<?php echo htmlspecialchars($log['comment']); ?>"</div>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>