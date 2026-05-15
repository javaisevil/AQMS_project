<?php
require_once '../includes/auth_check.php';
requireRole('qa');
require_once '../db.php';

$page_title = 'Quality Assurance Dashboard';

$pending = $pdo->query(
    'SELECT cs.*, u.full_name as faculty_name, ps.program_name
     FROM course_specs cs
     JOIN user u ON cs.faculty_id = u.user_id
     LEFT JOIN program_specs ps ON cs.program_id = ps.program_id
     WHERE cs.status = "pending_qa"
     ORDER BY cs.updated_at ASC'
)->fetchAll();

$approved = $pdo->query(
    'SELECT cs.*, u.full_name as faculty_name, ps.program_name
     FROM course_specs cs
     JOIN user u ON cs.faculty_id = u.user_id
     LEFT JOIN program_specs ps ON cs.program_id = ps.program_id
     WHERE cs.status = "approved"
     ORDER BY cs.updated_at DESC
     LIMIT 10'
)->fetchAll();

include '../includes/header.php';
?>

<div class="welcome-banner">
    <h2><span class="accent">Quality Assurance</span> Dashboard</h2>
    <p>Final review and approval of course specifications. NCAAA &amp; Jahiziah 2026 compliance.</p>
</div>

<div class="stats-row">
    <div class="stat-card stat-card--warning">
        <div class="stat-num"><?php echo count($pending); ?></div>
        <div class="stat-label">Awaiting QA Review</div>
    </div>
    <div class="stat-card stat-card--success">
        <div class="stat-num"><?php echo count($approved); ?></div>
        <div class="stat-label">Recently Approved</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Pending QA Review <span class="text-muted" style="font-size:13px; font-weight:400;">(<?php echo count($pending); ?>)</span></h2>
    </div>

    <?php if (empty($pending)): ?>
        <p class="empty-state">No courses awaiting QA review. ✓</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Title</th>
                <th>Program</th>
                <th>Faculty</th>
                <th>Submitted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pending as $course): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                <td><?php echo htmlspecialchars($course['program_name'] ?? '—'); ?></td>
                <td><?php echo htmlspecialchars($course['faculty_name']); ?></td>
                <td><?php echo date('M d, Y', strtotime($course['updated_at'])); ?></td>
                <td class="table-actions">
                    <a href="course_review.php?id=<?php echo $course['course_id']; ?>" class="btn btn-sm btn-primary">Review</a>
                    <a href="<?php echo BASE_URL; ?>/course/view.php?id=<?php echo $course['course_id']; ?>" target="_blank" class="btn btn-sm btn-ghost">Preview</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php if (!empty($approved)): ?>
<div class="card">
    <div class="card-header">
        <h2>Recently Approved</h2>
    </div>
    <table>
        <thead>
            <tr><th>Code</th><th>Title</th><th>Faculty</th><th>Approved</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($approved as $course): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($course['course_code']); ?></strong></td>
                <td><?php echo htmlspecialchars($course['course_title']); ?></td>
                <td><?php echo htmlspecialchars($course['faculty_name']); ?></td>
                <td><?php echo date('M d, Y', strtotime($course['updated_at'])); ?></td>
                <td>
                    <a href="<?php echo BASE_URL; ?>/course/view.php?id=<?php echo $course['course_id']; ?>" target="_blank" class="btn btn-sm btn-outline">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>