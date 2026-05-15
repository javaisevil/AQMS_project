<?php
require_once '../includes/auth_check.php';
requireRole('hod');
require_once '../db.php';

$page_title = 'Program KPIs (Section G.6)';

$programs = $pdo->query('SELECT * FROM program_specs ORDER BY program_name')->fetchAll();
$selected_program = intval($_GET['program_id'] ?? ($programs[0]['program_id'] ?? 0));

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $prog_id = intval($_POST['program_id']);
        $code    = trim($_POST['kpi_code'] ?? '');
        $text    = trim($_POST['kpi_text'] ?? '');
        $target  = trim($_POST['target_level'] ?? '');
        $method  = trim($_POST['measurement_method'] ?? '');
        $time    = trim($_POST['measurement_time'] ?? '');
        $years   = intval($_POST['years_to_achieve'] ?? 1);

        if (!$text) {
            $error = 'KPI description is required.';
        } else {
            $pdo->prepare(
                'INSERT INTO program_kpis (program_id, kpi_code, kpi_text, target_level, measurement_method, measurement_time, years_to_achieve)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([$prog_id, $code, $text, $target, $method, $time, $years]);
            $msg = 'KPI added.';
            $selected_program = $prog_id;
        }

    } elseif ($action === 'delete') {
        $kpi_id = intval($_POST['kpi_id']);
        $pdo->prepare('DELETE FROM program_kpis WHERE id = ?')->execute([$kpi_id]);
        $msg = 'KPI removed.';
        $selected_program = intval($_POST['program_id']);
    }
}

$kpis = [];
if ($selected_program) {
    $stmt = $pdo->prepare('SELECT * FROM program_kpis WHERE program_id = ? ORDER BY kpi_code');
    $stmt->execute([$selected_program]);
    $kpis = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Program KPIs — NCAAA Section G.6</h2>
        <form method="GET" style="display:flex; gap:8px; align-items:center;">
            <select name="program_id" onchange="this.form.submit()" style="width:auto;">
                <?php foreach ($programs as $p): ?>
                    <option value="<?php echo $p['program_id']; ?>"
                        <?php echo $p['program_id'] == $selected_program ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['program_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th style="width:40px;">#</th>
                <th style="width:90px;">Code</th>
                <th>KPI Description</th>
                <th>Target</th>
                <th>Method</th>
                <th>Timing</th>
                <th style="width:60px;">Years</th>
                <th style="width:60px;"></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($kpis)): ?>
                <tr><td colspan="8" class="empty-state">No KPIs added yet for this program.</td></tr>
            <?php else: ?>
                <?php foreach ($kpis as $i => $kpi): ?>
                <tr>
                    <td><?php echo $i + 1; ?></td>
                    <td><strong><?php echo htmlspecialchars($kpi['kpi_code']); ?></strong></td>
                    <td><?php echo htmlspecialchars($kpi['kpi_text']); ?></td>
                    <td><?php echo htmlspecialchars($kpi['target_level']); ?></td>
                    <td><?php echo htmlspecialchars($kpi['measurement_method']); ?></td>
                    <td><?php echo htmlspecialchars($kpi['measurement_time']); ?></td>
                    <td><?php echo htmlspecialchars($kpi['years_to_achieve']); ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Remove this KPI?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="kpi_id" value="<?php echo $kpi['id']; ?>">
                            <input type="hidden" name="program_id" value="<?php echo $selected_program; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Del</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h2>Add a KPI</h2></div>
    <form method="POST">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="program_id" value="<?php echo $selected_program; ?>">
        <div class="form-row">
            <div class="form-group" style="max-width:140px;">
                <label>KPI Code</label>
                <input type="text" name="kpi_code" placeholder="KPI-01">
            </div>
            <div class="form-group">
                <label>KPI Description *</label>
                <input type="text" name="kpi_text" required placeholder="e.g. Graduate employment rate within 6 months">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Target Level</label>
                <input type="text" name="target_level" placeholder="e.g. 80%">
            </div>
            <div class="form-group">
                <label>Measurement Method</label>
                <input type="text" name="measurement_method" placeholder="e.g. Alumni survey">
            </div>
            <div class="form-group">
                <label>Measurement Time</label>
                <input type="text" name="measurement_time" placeholder="e.g. End of academic year">
            </div>
            <div class="form-group" style="max-width:120px;">
                <label>Years</label>
                <input type="number" name="years_to_achieve" value="1" min="1" max="10">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Add KPI</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>