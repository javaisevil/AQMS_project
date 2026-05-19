<?php
require_once '../includes/auth_check.php';
requireRole('hod');
require_once '../db.php';

$page_title = 'Assign Course Specification';
$msg = '';
$error = '';

$programs = $pdo->query('SELECT * FROM program_specs ORDER BY program_name')->fetchAll();
$faculty = $pdo->query('SELECT * FROM user WHERE role = "faculty" ORDER BY full_name, username')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $program_id = intval($_POST['program_id'] ?? 0);
    $faculty_id = intval($_POST['faculty_id'] ?? 0);
    $title = trim($_POST['course_title'] ?? '');
    $code = trim($_POST['course_code'] ?? '');
    $level = intval($_POST['course_level'] ?? 0);
    $credits = floatval($_POST['credit_hours'] ?? 0);
    $required = $_POST['required_elective'] ?? null;
    $due_date = $_POST['due_date'] ?: null;

    $types = $_POST['course_type'] ?? [];
    $course_type = is_array($types) ? implode(', ', $types) : trim($types);

    $program = null;
    if ($program_id) {
        $p = $pdo->prepare('SELECT * FROM program_specs WHERE program_id = ?');
        $p->execute([$program_id]);
        $program = $p->fetch();
    }

    if (!$program_id || !$faculty_id || !$title || !$code) {
        $error = 'Program, faculty, course title, and course code are required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO course_specs (program_id, faculty_id, course_title, course_code, department, college, institution, credit_hours, course_type, required_elective, course_level, due_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "draft")');
        $stmt->execute([
            $program_id,
            $faculty_id,
            $title,
            $code,
            $program['department'] ?? '',
            $program['college'] ?? '',
            $program['institution'] ?? 'Al Yamamah University',
            $credits ?: null,
            $course_type,
            $required,
            $level ?: null,
            $due_date
        ]);
        $new_id = $pdo->lastInsertId();
        $pdo->prepare('INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, NULL, "draft", "Course specification assigned to faculty")')
            ->execute([$new_id, $_SESSION['user_id']]);
        $msg = 'Course assigned to faculty.';
    }
}

include '../includes/header.php';
?>

<?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

<div style="max-width:760px;">
<div class="card">
    <div class="card-header"><h2>Assign a Course Specification</h2></div>
    <form method="POST">
        <div class="form-row">
            <div class="form-group">
                <label>Program</label>
                <select name="program_id" required>
                    <option value="">Select program</option>
                    <?php foreach ($programs as $p): ?>
                    <option value="<?php echo $p['program_id']; ?>"><?php echo htmlspecialchars($p['program_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Faculty / Coordinator</label>
                <select name="faculty_id" required>
                    <option value="">Select faculty</option>
                    <?php foreach ($faculty as $f): ?>
                    <option value="<?php echo $f['user_id']; ?>"><?php echo htmlspecialchars($f['full_name'] ?: $f['username']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group"><label>Course Title</label><input type="text" name="course_title" required></div>
            <div class="form-group"><label>Course Code</label><input type="text" name="course_code" required></div>
        </div>

        <div class="form-row">
            <div class="form-group"><label>Credit hours</label><input type="number" name="credit_hours" step="0.5"></div>
            <div class="form-group"><label>Level/year at which this course is offered</label><input type="number" name="course_level" min="1" max="8"></div>
        </div>

        <div class="form-group">
            <label>Course type - A</label>
            <?php foreach (['University','College','Department','Track','Others'] as $type): ?>
            <label style="display:inline-flex;gap:5px;margin-right:14px;font-weight:400;"><input type="checkbox" name="course_type[]" value="<?php echo $type; ?>"> <?php echo $type; ?></label>
            <?php endforeach; ?>
        </div>

        <div class="form-group">
            <label>Course type - B</label>
            <?php foreach (['Required','Elective'] as $type): ?>
            <label style="display:inline-flex;gap:5px;margin-right:14px;font-weight:400;"><input type="radio" name="required_elective" value="<?php echo $type; ?>"> <?php echo $type; ?></label>
            <?php endforeach; ?>
        </div>

        <div class="form-group"><label>Due date</label><input type="date" name="due_date"></div>

        <button type="submit" class="btn btn-primary">Assign Course</button>
        <a href="dashboard.php" class="btn btn-ghost">Cancel</a>
    </form>
</div>
</div>

<?php include '../includes/footer.php'; ?>