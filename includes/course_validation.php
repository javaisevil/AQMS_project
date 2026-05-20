<?php

function aqmsTableExists(PDO $pdo, string $tableName): bool {
    try {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$tableName]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function aqmsColumnExists(PDO $pdo, string $tableName, string $columnName): bool {
    try {
        $stmt = $pdo->prepare("SHOW COLUMNS FROM `$tableName` LIKE ?");
        $stmt->execute([$columnName]);
        return (bool)$stmt->fetchColumn();
    } catch (Exception $e) {
        return false;
    }
}

function aqmsHasMeasurableVerb(string $text): bool {
    $text = strtolower(trim($text));
    if ($text === '') return false;

    $weakVerbs = ['understand', 'know', 'learn', 'be familiar with', 'be aware of'];
    foreach ($weakVerbs as $verb) {
        if (preg_match('/^' . preg_quote($verb, '/') . '\b/', $text)) return false;
    }

    $measurableVerbs = [
        'analyze', 'apply', 'assess', 'calculate', 'classify', 'compare', 'configure',
        'construct', 'create', 'critique', 'define', 'demonstrate', 'describe', 'design',
        'develop', 'differentiate', 'evaluate', 'explain', 'identify', 'implement',
        'interpret', 'justify', 'list', 'measure', 'prepare', 'present', 'produce',
        'propose', 'recognize', 'solve', 'test', 'use', 'write'
    ];

    foreach ($measurableVerbs as $verb) {
        if (preg_match('/^' . preg_quote($verb, '/') . '\b/', $text)) return true;
    }
    return false;
}

function aqmsValidateCourseSpecification(PDO $pdo, array $course, bool $strict = true): array {
    $errors = [];
    $courseId = intval($course['course_id'] ?? 0);

    $requiredCourseFields = [
        'course_title' => 'Course title is required.',
        'course_code' => 'Course code is required.',
        'program_id' => 'Program is required.',
        'credit_hours' => 'Credit hours are required.',
        'course_level' => 'Course level/year is required.',
        'course_type' => 'Course type A is required.',
        'required_elective' => 'Course type B is required.',
        'course_description' => 'Course general description is required.',
        'objectives' => 'Course main objectives are required.'
    ];

    foreach ($requiredCourseFields as $field => $message) {
        if (!isset($course[$field]) || trim((string)$course[$field]) === '') $errors[] = $message;
    }

    $teachingModes = $pdo->prepare('SELECT COUNT(*) FROM teaching_modes WHERE course_id = ?');
    $teachingModes->execute([$courseId]);
    if ((int)$teachingModes->fetchColumn() === 0) $errors[] = 'At least one teaching mode is required.';

    $contactHours = $pdo->prepare('SELECT COUNT(*) FROM contact_hours WHERE course_id = ? AND hours IS NOT NULL AND hours > 0');
    $contactHours->execute([$courseId]);
    if ((int)$contactHours->fetchColumn() === 0) $errors[] = 'Contact hours must be entered.';

    $topics = $pdo->prepare('SELECT COUNT(*) FROM course_topics WHERE course_id = ? AND TRIM(topic_text) != ""');
    $topics->execute([$courseId]);
    if ((int)$topics->fetchColumn() === 0) $errors[] = 'At least one course content topic is required.';

    $clos = $pdo->prepare('SELECT clo_id, clo_code, description FROM course_learning_outcomes WHERE course_id = ?');
    $clos->execute([$courseId]);
    $cloRows = $clos->fetchAll(PDO::FETCH_ASSOC);

    if (empty($cloRows)) {
        $errors[] = 'At least one CLO is required.';
    } else {
        $mapCheck = $pdo->prepare('SELECT COUNT(*) FROM clo_plo_mapping WHERE clo_id = ?');
        foreach ($cloRows as $clo) {
            $label = trim((string)($clo['clo_code'] ?? '')) ?: 'A CLO';
            if (!aqmsHasMeasurableVerb((string)$clo['description'])) {
                $errors[] = $label . ' must start with a measurable verb, not a vague verb such as understand/know/learn.';
            }
            $mapCheck->execute([$clo['clo_id']]);
            if ((int)$mapCheck->fetchColumn() === 0) $errors[] = $label . ' must map to at least one PLO.';
        }
    }

    $assessmentFields = 'id, activity_name, timing_week, percentage';
    if (aqmsColumnExists($pdo, 'assessments', 'rubric')) $assessmentFields .= ', rubric';
    if (aqmsColumnExists($pdo, 'assessments', 'performance_task')) $assessmentFields .= ', performance_task';

    $assessments = $pdo->prepare("SELECT $assessmentFields FROM assessments WHERE course_id = ?");
    $assessments->execute([$courseId]);
    $assessmentRows = $assessments->fetchAll(PDO::FETCH_ASSOC);

    if (empty($assessmentRows)) {
        $errors[] = 'At least one assessment activity is required.';
    } else {
        $total = 0;
        $linkCheck = $pdo->prepare('SELECT COUNT(*) FROM assessment_clo WHERE assessment_id = ?');
        foreach ($assessmentRows as $assessment) {
            $name = trim((string)$assessment['activity_name']) ?: 'An assessment activity';
            $total += (float)$assessment['percentage'];
            if (empty($assessment['timing_week'])) $errors[] = $name . ' must include assessment timing.';
            if ($assessment['percentage'] === null || $assessment['percentage'] === '') $errors[] = $name . ' must include percentage of total assessment.';
            $linkCheck->execute([$assessment['id']]);
            if ((int)$linkCheck->fetchColumn() === 0) $errors[] = $name . ' must be linked to at least one CLO.';
        }
        if (abs($total - 100) >= 0.01) $errors[] = 'Assessment percentages must total 100%.';
    }

    $resources = $pdo->prepare('SELECT COUNT(*) FROM resources WHERE course_id = ? AND TRIM(resource_text) != ""');
    $resources->execute([$courseId]);
    if ((int)$resources->fetchColumn() === 0) $errors[] = 'At least one learning resource is required.';

    if (aqmsTableExists($pdo, 'course_facilities')) {
        $facilities = $pdo->prepare('SELECT COUNT(*) FROM course_facilities WHERE course_id = ? AND TRIM(resources) != ""');
        $facilities->execute([$courseId]);
        if ((int)$facilities->fetchColumn() === 0) $errors[] = 'Required facilities and equipment must be entered.';
    }

    if (aqmsTableExists($pdo, 'course_quality')) {
        $quality = $pdo->prepare('SELECT COUNT(*) FROM course_quality WHERE course_id = ? AND TRIM(assessor) != "" AND TRIM(assessment_method) != ""');
        $quality->execute([$courseId]);
        if ((int)$quality->fetchColumn() === 0) $errors[] = 'Assessment of course quality must be completed.';
    }

    if ($strict && aqmsTableExists($pdo, 'course_approval')) {
        $approval = $pdo->prepare('SELECT COUNT(*) FROM course_approval WHERE course_id = ? AND TRIM(council_committee) != "" AND TRIM(reference_no) != "" AND approval_date IS NOT NULL');
        $approval->execute([$courseId]);
        if ((int)$approval->fetchColumn() === 0) $errors[] = 'Specification approval data must be completed.';
    }

    return array_values(array_unique($errors));
}
