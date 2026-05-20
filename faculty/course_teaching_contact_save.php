<?php
require_once '../includes/auth_check.php';
requireRole('faculty');
require_once '../db.php';

header('Content-Type: application/json');

$course_id = intval($_POST['course_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM course_specs WHERE course_id = ? AND faculty_id = ? AND status IN ("draft", "returned_by_hod", "returned_by_qa")');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    echo json_encode(['ok' => false, 'message' => 'Course not found or not editable']);
    exit();
}

$pdo->prepare('DELETE FROM teaching_modes WHERE course_id = ?')->execute([$course_id]);
$pdo->prepare('DELETE FROM contact_hours WHERE course_id = ?')->execute([$course_id]);

foreach (($_POST['mode'] ?? []) as $row) {
    if (empty($row['selected'])) continue;
    $pdo->prepare('INSERT INTO teaching_modes (course_id, mode_type, contact_hours, percentage) VALUES (?, ?, ?, ?)')
        ->execute([
            $course_id,
            $row['mode_type'],
            floatval($row['contact_hours'] ?? 0) ?: null,
            floatval($row['percentage'] ?? 0) ?: null
        ]);
}

foreach (($_POST['hours'] ?? []) as $row) {
    $pdo->prepare('INSERT INTO contact_hours (course_id, activity_type, hours) VALUES (?, ?, ?)')
        ->execute([
            $course_id,
            $row['activity_type'],
            floatval($row['hours'] ?? 0) ?: null
        ]);
}

echo json_encode(['ok' => true]);
