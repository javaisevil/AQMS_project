<?php
require_once '../includes/auth_check.php';
requireRole('faculty');
require_once '../db.php';
require_once '../includes/course_validation.php';

$course_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM course_specs WHERE course_id = ? AND faculty_id = ? AND status IN ("draft", "returned_by_hod", "returned_by_qa")');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: dashboard.php');
    exit();
}

$errors = aqmsValidateCourseSpecification($pdo, $course, true);

if (!empty($errors)) {
    $_SESSION['submit_errors'] = $errors;
    header('Location: course_edit.php?id=' . $course_id . '&step=8');
    exit();
}

$from_status = $course['status'];
$deadline_status = 'not_due';
if (!empty($course['due_date'])) {
    $deadline_status = date('Y-m-d') <= $course['due_date'] ? 'on_time' : 'late';
}

$pdo->prepare('UPDATE course_specs SET status = "pending_hod", submitted_at = NOW(), deadline_status = ? WHERE course_id = ?')
    ->execute([$deadline_status, $course_id]);

$pdo->prepare(
    'INSERT INTO approval_log (course_id, user_id, from_status, to_status, comment) VALUES (?, ?, ?, "pending_hod", "Submitted by faculty for HoD review")'
)->execute([$course_id, $_SESSION['user_id'], $from_status]);

header('Location: dashboard.php?submitted=1');
exit();