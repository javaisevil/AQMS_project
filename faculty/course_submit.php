<?php
require_once '../includes/auth_check.php';
requireRole('faculty');
require_once '../db.php';

$course_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM course_specs WHERE course_id = ? AND faculty_id = ? AND status = "draft"');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: dashboard.php');
    exit();
}

// Integrity gatekeeping
$errors = [];

if (empty($course['course_description']) || empty($course['objectives'])) {
    $errors[] = 'Course description and objectives are required (Step 1).';
}

$clos = $pdo->prepare('SELECT clo_id FROM course_learning_outcomes WHERE course_id = ?');
$clos->execute([$course_id]);
$clo_ids = $clos->fetchAll(PDO::FETCH_COLUMN);

if (empty($clo_ids)) {
    $errors[] = 'At least one CLO is required (Step 2).';
} else {
    $map_check = $pdo->prepare('SELECT COUNT(*) FROM clo_plo_mapping WHERE clo_id = ?');
    foreach ($clo_ids as $clo_id) {
        $map_check->execute([$clo_id]);
        if ($map_check->fetchColumn() == 0) {
            $errors[] = 'Every CLO must map to at least one PLO (Step 2).';
            break;
        }
    }
}

$has_assess = $pdo->prepare('SELECT COUNT(*) FROM assessments WHERE course_id = ?');
$has_assess->execute([$course_id]);
if ($has_assess->fetchColumn() == 0) {
    $errors[] = 'At least one assessment activity is required (Step 3).';
}

if (!empty($errors)) {
    $_SESSION['submit_errors'] = $errors;
    header('Location: course_edit.php?id=' . $course_id . '&step=7');
    exit();
}

// All checks passed — transition state
$pdo->prepare('UPDATE course_specs SET status = "pending_hod" WHERE course_id = ?')->execute([$course_id]);

$pdo->prepare(
    'INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, "draft", "pending_hod", "Submitted by faculty for HoD review")'
)->execute([$course_id, $_SESSION['user_id']]);

header('Location: dashboard.php?submitted=1');
exit();