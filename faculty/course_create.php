<?php
require_once '../includes/auth_check.php';
requireRole('faculty');
require_once '../db.php';

$page_title = 'New Course Specification';

$yu_courses = require __DIR__ . '/../includes/yu_courses.php';
$programs = $pdo->query('SELECT * FROM program_specs ORDER BY program_name')->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title      = trim($_POST['course_title'] ?? '');
    $code       = trim($_POST['course_code'] ?? '');
    $program_id = intval($_POST['program_id'] ?? 0);
    $level      = intval($_POST['course_level'] ?? 0);
    $credits    = floatval($_POST['credit_hours'] ?? 0);
    $type       = trim($_POST['course_type'] ?? '');

    if (!$title || !$code) {
        $error = 'Course title and code are required.';
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO course_specs (program_id, faculty_id, course_title, course_code, course_level, credit_hours, course_type, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, "draft")'
        );
        $stmt->execute([$program_id ?: null, $_SESSION['user_id'], $title, $code, $level ?: null, $credits ?: null, $type ?: null]);
        $new_id = $pdo->lastInsertId();

        $pdo->prepare(
            'INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, NULL, "draft", "Course specification created")'
        )->execute([$new_id, $_SESSION['user_id']]);

        header('Location: course_edit.php?id=' . $new_id . '&step=1');
        exit();
    }
}

include '../includes/header.php';
?>

<div style="max-width:680px;">
    <div class="card">
        <div class="card-header">
            <h2>Start a New Course Specification</h2>
        </div>

        <p class="text-muted" style="margin-bottom:20px;">
            Enter the basic identification details to begin the course specification.
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>YU study plan course</label>
                <select id="yu_course_select" onchange="fillYuCourse(this)">
                    <option value="">-- Select course --</option>
                    <?php foreach ($yu_courses as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['code']); ?>"
                                data-code="<?php echo htmlspecialchars($c['code']); ?>"
                                data-title="<?php echo htmlspecialchars($c['title']); ?>"
                                data-level="<?php echo htmlspecialchars($c['level']); ?>"
                                data-credits="<?php echo htmlspecialchars($c['credits']); ?>"
                                data-type="<?php echo htmlspecialchars($c['type']); ?>">
                            <?php echo htmlspecialchars($c['code'] . ' - ' . $c['title'] . ' (' . $c['credits'] . ' cr)'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Course Title *</label>
                    <input type="text" id="course_title" name="course_title" required
                           value="<?php echo htmlspecialchars($_POST['course_title'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Course Code *</label>
                    <input type="text" id="course_code" name="course_code" required
                           value="<?php echo htmlspecialchars($_POST['course_code'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Program</label>
                <select name="program_id">
                    <option value="">— Select Program —</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?php echo $p['program_id']; ?>"
                            <?php echo (($_POST['program_id'] ?? '') == $p['program_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['program_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Level (Year)</label>
                    <input type="number" id="course_level" name="course_level" min="1" max="8"
                           value="<?php echo htmlspecialchars($_POST['course_level'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label>Credit Hours</label>
                    <input type="number" id="credit_hours" name="credit_hours" step="0.5" min="0"
                           value="<?php echo htmlspecialchars($_POST['credit_hours'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Course Type</label>
                <input type="text" id="course_type" name="course_type"
                       value="<?php echo htmlspecialchars($_POST['course_type'] ?? ''); ?>">
            </div>

            <div style="display:flex; gap:10px; margin-top:18px;">
                <button type="submit" class="btn btn-primary">Create & Continue →</button>
                <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function fillYuCourse(select) {
    var item = select.options[select.selectedIndex];
    if (!item || !item.dataset.code) return;

    document.getElementById('course_title').value = item.dataset.title;
    document.getElementById('course_code').value = item.dataset.code;
    document.getElementById('course_level').value = item.dataset.level;
    document.getElementById('credit_hours').value = item.dataset.credits;
    document.getElementById('course_type').value = item.dataset.type;
}
</script>

<?php include '../includes/footer.php'; ?>
