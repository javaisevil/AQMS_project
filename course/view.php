<?php
require_once '../includes/auth_check.php';
require_once '../db.php';

$course_id = intval($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT cs.*, ps.program_name, ps.program_code, ps.college, ps.department, u.full_name as faculty_name FROM course_specs cs LEFT JOIN program_specs ps ON cs.program_id = ps.program_id LEFT JOIN user u ON cs.faculty_id = u.user_id WHERE cs.course_id = ?');
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ../index.php');
    exit();
}

$clos = $pdo->prepare('SELECT * FROM course_learning_outcomes WHERE course_id = ? ORDER BY category, clo_code');
$clos->execute([$course_id]);
$clos = $clos->fetchAll();

$clo_ids = array_column($clos, 'clo_id');
$clo_maps = [];
if ($clo_ids) {
    $in  = implode(',', array_fill(0, count($clo_ids), '?'));
    $map = $pdo->prepare("SELECT m.*, p.plo_code FROM clo_plo_mapping m JOIN program_learning_outcomes p ON m.plo_id = p.plo_id WHERE m.clo_id IN ($in)");
    $map->execute($clo_ids);
    foreach ($map->fetchAll() as $row) {
        $clo_maps[$row['clo_id']][] = $row['plo_code'];
    }
}

$assessments = $pdo->prepare('SELECT a.*, GROUP_CONCAT(cl.clo_code ORDER BY cl.clo_code SEPARATOR ", ") as clo_codes FROM assessments a LEFT JOIN assessment_clo ac ON a.id = ac.assessment_id LEFT JOIN course_learning_outcomes cl ON ac.clo_id = cl.clo_id WHERE a.course_id = ? GROUP BY a.id ORDER BY a.timing_week');
$assessments->execute([$course_id]);
$assessments = $assessments->fetchAll();

$modes  = $pdo->prepare('SELECT * FROM teaching_modes WHERE course_id = ?');
$modes->execute([$course_id]);
$modes  = $modes->fetchAll();

$chours = $pdo->prepare('SELECT * FROM contact_hours WHERE course_id = ?');
$chours->execute([$course_id]);
$chours = $chours->fetchAll();

$topics = $pdo->prepare('SELECT * FROM course_topics WHERE course_id = ? ORDER BY sort_order');
$topics->execute([$course_id]);
$topics = $topics->fetchAll();

$res    = $pdo->prepare('SELECT * FROM resources WHERE course_id = ? ORDER BY category');
$res->execute([$course_id]);
$res    = $res->fetchAll();

$plos   = [];
if ($course['program_id']) {
    $ps = $pdo->prepare('SELECT * FROM program_learning_outcomes WHERE program_id = ? ORDER BY category, plo_code');
    $ps->execute([$course['program_id']]);
    $plos = $ps->fetchAll();
}

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Course Specification - <?php echo h($course['course_title']); ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; color: #000; background: #fff; margin: 0; padding: 0; }
        .print-wrapper { max-width: 900px; margin: 0 auto; padding: 30px 40px; }
        .no-print { font-family: Arial, sans-serif; background: #1B5E35; color: white; padding: 12px 24px; display: flex; gap: 12px; align-items: center; }
        .no-print button { background: #C8A415; color: #0F3D1E; border: none; padding: 8px 18px; font-weight: 700; cursor: pointer; border-radius: 4px; font-size: 14px; }
        .no-print a { color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
        h1 { font-size: 14pt; text-align: center; margin-bottom: 4px; }
        h2 { font-size: 12pt; background: #1B5E35; color: white; padding: 5px 10px; margin: 20px 0 8px; }
        h3 { font-size: 11pt; border-bottom: 1px solid #333; padding-bottom: 3px; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 11pt; }
        th { background: #e8f0eb; border: 1px solid #666; padding: 5px 8px; text-align: left; font-size: 10.5pt; }
        td { border: 1px solid #888; padding: 5px 8px; vertical-align: top; }
        .header-block { border: 1px solid #333; padding: 14px; margin-bottom: 20px; }
        .header-block table { margin: 0; }
        .header-block td { border: none; padding: 3px 8px; }
        .header-block td:first-child { font-weight: bold; width: 180px; }
        .center { text-align: center; }
        .approval-blank { height: 28px; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11pt; }
            h2 { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()">Print / Save as PDF</button>
    <a href="status.php?id=<?php echo $course_id; ?>">View Status</a>
    <a href="../index.php">Back to Dashboard</a>
</div>

<div class="print-wrapper">

    <h1>Course Specification</h1>
    <p class="center" style="font-size:11pt; margin-bottom:16px;">Al Yamamah University - Academic Quality Management System</p>

    <div class="header-block">
        <table>
            <tr><td>Course Title:</td><td><?php echo h($course['course_title']); ?></td><td>Course Code:</td><td><?php echo h($course['course_code']); ?></td></tr>
            <tr><td>Program:</td><td><?php echo h($course['program_name']); ?></td><td>Department:</td><td><?php echo h($course['department']); ?></td></tr>
            <tr><td>College:</td><td><?php echo h($course['college']); ?></td><td>Version:</td><td><?php echo h($course['version']); ?></td></tr>
            <tr><td>Prepared by:</td><td><?php echo h($course['faculty_name']); ?></td><td>Last Revised:</td><td><?php echo date('Y-m-d', strtotime($course['updated_at'])); ?></td></tr>
        </table>
    </div>

    <h2>A. General Information</h2>

    <h3>A.1 Course Identification</h3>
    <table>
        <tr><th style="width:200px;">Field</th><th>Details</th></tr>
        <tr><td>Credit Hours</td><td><?php echo h($course['credit_hours']); ?></td></tr>
        <tr><td>Course Type</td><td><?php echo h($course['course_type']); ?></td></tr>
        <tr><td>Level / Year</td><td><?php echo h($course['course_level']); ?></td></tr>
        <tr><td>Pre-requisites</td><td><?php echo h($course['prerequisites']); ?></td></tr>
        <tr><td>Co-requisites</td><td><?php echo h($course['corequisites']); ?></td></tr>
        <tr><td>General Description</td><td><?php echo nl2br(h($course['course_description'])); ?></td></tr>
        <tr><td>Main Objective(s)</td><td><?php echo nl2br(h($course['objectives'])); ?></td></tr>
    </table>

    <h3>A.2 Teaching Mode</h3>
    <table>
        <tr><th>#</th><th>Mode of Instruction</th><th>Contact Hours</th><th>Percentage</th></tr>
        <?php foreach ($modes as $i => $m): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo h($m['mode_type']); ?></td>
            <td><?php echo h($m['contact_hours']); ?></td>
            <td><?php echo h($m['percentage']); ?>%</td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>A.3 Contact Hours</h3>
    <table>
        <tr><th>#</th><th>Activity</th><th>Contact Hours</th></tr>
        <?php foreach ($chours as $i => $h): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo h($h['activity_type']); ?></td>
            <td><?php echo h($h['hours']); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="2"><strong>Total</strong></td><td><strong><?php echo array_sum(array_column($chours, 'hours')); ?></strong></td></tr>
    </table>

    <h2>B. Course Learning Outcomes (CLOs)</h2>
    <table>
        <tr><th>Code</th><th>CLO Description</th><th>Category</th><th>Aligned PLOs</th><th>Teaching Strategies</th><th>Assessment Methods</th></tr>
        <?php
        $categories = ['Knowledge' => '1.0', 'Skills' => '2.0', 'Values' => '3.0'];
        $last_cat = '';
        foreach ($clos as $clo):
            if ($clo['category'] !== $last_cat):
                $last_cat = $clo['category'];
        ?>
        <tr><td colspan="6" style="background:#f0f0f0; font-weight:bold;"><?php echo h($categories[$clo['category']] ?? ''); ?> - <?php echo h($clo['category']); ?> and Understanding</td></tr>
        <?php endif; ?>
        <tr>
            <td><?php echo h($clo['clo_code']); ?></td>
            <td><?php echo h($clo['description']); ?></td>
            <td><?php echo h($clo['category']); ?></td>
            <td><?php echo h(implode(', ', $clo_maps[$clo['clo_id']] ?? [])); ?></td>
            <td><?php echo h($clo['teaching_strategies']); ?></td>
            <td><?php echo h($clo['assessment_methods']); ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>C. Course Content</h2>
    <table>
        <tr><th>#</th><th>Topic</th><th>Contact Hours</th></tr>
        <?php foreach ($topics as $i => $t): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo h($t['topic_text']); ?></td>
            <td><?php echo h($t['contact_hours']); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="2"><strong>Total</strong></td><td><strong><?php echo array_sum(array_column($topics, 'contact_hours')); ?></strong></td></tr>
    </table>

    <h2>D. Student Assessment Activities</h2>
    <table>
        <tr><th>#</th><th>Assessment Activity</th><th>Week No.</th><th>% of Total</th><th>Linked CLOs</th></tr>
        <?php foreach ($assessments as $i => $a): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo h($a['activity_name']); ?></td>
            <td><?php echo h($a['timing_week']); ?></td>
            <td><?php echo h($a['percentage']); ?>%</td>
            <td><?php echo h($a['clo_codes']); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3"><strong>Total</strong></td><td><strong><?php echo array_sum(array_column($assessments, 'percentage')); ?>%</strong></td><td></td></tr>
    </table>

    <h2>E. Learning Resources & Facilities</h2>
    <?php
    $res_grouped = [];
    foreach ($res as $r) $res_grouped[$r['category']][] = $r['resource_text'];
    $res_order = ['Essential', 'Supportive', 'Electronic', 'Other'];
    foreach ($res_order as $cat):
        if (empty($res_grouped[$cat])) continue;
    ?>
    <h3>E.1 <?php echo $cat; ?> References</h3>
    <ul>
        <?php foreach ($res_grouped[$cat] as $item): ?>
            <li><?php echo h($item); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endforeach; ?>

    <h2>F. Assessment of Course Quality</h2>
    <table>
        <tr><th>Assessment Area</th><th>Assessor</th><th>Method</th></tr>
        <tr><td>Effectiveness of teaching</td><td>Students</td><td>Course evaluation survey</td></tr>
        <tr><td>Effectiveness of student assessment</td><td>Faculty, Peer Reviewer</td><td>Direct</td></tr>
        <tr><td>Quality of learning resources</td><td>Students, Faculty</td><td>Survey / Review</td></tr>
        <tr><td>Extent to which CLOs have been achieved</td><td>Faculty</td><td>Direct assessment data</td></tr>
    </table>

    <h2>G. Specification Approval</h2>
    <table>
        <tr><th>Council / Committee</th><td class="approval-blank"></td></tr>
        <tr><th>Reference No.</th><td class="approval-blank"></td></tr>
        <tr><th>Date</th><td class="approval-blank"></td></tr>
    </table>

</div>
</body>
</html>
