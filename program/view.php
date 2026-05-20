<?php
require_once '../includes/auth_check.php';
require_once '../db.php';

$program_id = intval($_GET['id'] ?? 1);

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$stmt = $pdo->prepare('SELECT * FROM program_specs WHERE program_id = ?');
$stmt->execute([$program_id]);
$program = $stmt->fetch();

if (!$program) {
    header('Location: ../index.php');
    exit();
}

$plos = $pdo->prepare('SELECT * FROM program_learning_outcomes WHERE program_id = ? ORDER BY category, plo_code');
$plos->execute([$program_id]);
$plos = $plos->fetchAll();

$kpis = $pdo->prepare('SELECT * FROM program_kpis WHERE program_id = ? ORDER BY kpi_code, id');
$kpis->execute([$program_id]);
$kpis = $kpis->fetchAll();

$courses = $pdo->prepare('SELECT course_code, course_title, credit_hours, course_level, status FROM course_specs WHERE program_id = ? ORDER BY course_level, course_code');
$courses->execute([$program_id]);
$courses = $courses->fetchAll();

$ploByCategory = [];
foreach ($plos as $plo) {
    $ploByCategory[$plo['category']][] = $plo;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Program Specification - <?php echo h($program['program_name']); ?></title>
<style>
body{font-family:"Times New Roman",serif;font-size:12pt;color:#000;background:#fff;margin:0}.no-print{font-family:Arial,sans-serif;background:#333;color:white;padding:12px 24px;display:flex;gap:12px}.no-print button,.no-print a{background:#d96f32;color:#fff;border:0;padding:8px 14px;font-weight:700;border-radius:4px;font-size:14px;text-decoration:none}.print-wrapper{max-width:920px;margin:0 auto;padding:30px 40px}h1{font-size:16pt;text-align:center;margin:0 0 4px}h2{font-size:12pt;background:#e9e9e9;border:1px solid #555;padding:6px 8px;margin:20px 0 8px}h3{font-size:11pt;margin:14px 0 6px}table{width:100%;border-collapse:collapse;margin-bottom:12px;font-size:10.5pt}th,td{border:1px solid #777;padding:5px 7px;vertical-align:top}th{background:#f1f1f1;text-align:left}.center{text-align:center}.muted{color:#555}@media print{.no-print{display:none!important}.print-wrapper{padding:0}h2,th{-webkit-print-color-adjust:exact;print-color-adjust:exact}}
</style>
</head>
<body>
<div class="no-print"><button onclick="window.print()">Print / Save as PDF</button><a href="../index.php">Back to Dashboard</a></div>
<div class="print-wrapper">
<h1>Program Specification</h1>
<p class="center muted">Al Yamamah University - Academic Quality Management System</p>

<h2>A. Program Identification and General Information</h2>
<table>
<tr><th>Program Name</th><td><?php echo h($program['program_name']); ?></td><th>Program Code</th><td><?php echo h($program['program_code']); ?></td></tr>
<tr><th>College</th><td><?php echo h($program['college']); ?></td><th>Department</th><td><?php echo h($program['department']); ?></td></tr>
<tr><th>Credit Hours</th><td><?php echo h($program['credit_hours']); ?></td><th>Qualification Level</th><td><?php echo h($program['qualification_level']); ?></td></tr>
</table>

<h2>B. Mission, Goals, and Program Aims</h2>
<table>
<tr><th>Mission</th><td><?php echo nl2br(h($program['mission'])); ?></td></tr>
<tr><th>Goals</th><td><?php echo nl2br(h($program['goals'])); ?></td></tr>
<tr><th>Program Aims</th><td><?php echo nl2br(h($program['program_aims'])); ?></td></tr>
<tr><th>Program Structure</th><td><?php echo nl2br(h($program['program_structure'])); ?></td></tr>
</table>

<h2>C. Program Learning Outcomes</h2>
<?php if (empty($plos)): ?>
<p>No PLOs recorded.</p>
<?php else: foreach ($ploByCategory as $category => $items): ?>
<h3><?php echo h($category); ?></h3>
<table><tr><th>Code</th><th>Description</th></tr>
<?php foreach ($items as $plo): ?><tr><td><?php echo h($plo['plo_code']); ?></td><td><?php echo h($plo['description']); ?></td></tr><?php endforeach; ?>
</table>
<?php endforeach; endif; ?>

<h2>D. Program KPIs</h2>
<table>
<tr><th>KPI Code</th><th>KPI</th><th>Target Level</th><th>Measurement Method</th><th>Measurement Time</th><th>Years to Achieve</th></tr>
<?php if (empty($kpis)): ?><tr><td colspan="6">No KPIs recorded.</td></tr><?php else: foreach ($kpis as $kpi): ?>
<tr><td><?php echo h($kpi['kpi_code']); ?></td><td><?php echo h($kpi['kpi_text']); ?></td><td><?php echo h($kpi['target_level']); ?></td><td><?php echo h($kpi['measurement_method']); ?></td><td><?php echo h($kpi['measurement_time']); ?></td><td><?php echo h($kpi['years_to_achieve']); ?></td></tr>
<?php endforeach; endif; ?>
</table>

<h2>E. Courses Under This Program</h2>
<table>
<tr><th>Course Code</th><th>Course Title</th><th>Level</th><th>Credit Hours</th><th>Status</th></tr>
<?php if (empty($courses)): ?><tr><td colspan="5">No courses recorded.</td></tr><?php else: foreach ($courses as $course): ?>
<tr><td><?php echo h($course['course_code']); ?></td><td><?php echo h($course['course_title']); ?></td><td><?php echo h($course['course_level']); ?></td><td><?php echo h($course['credit_hours']); ?></td><td><?php echo h(ucwords(str_replace('_', ' ', $course['status']))); ?></td></tr>
<?php endforeach; endif; ?>
</table>
</div>
</body>
</html>
