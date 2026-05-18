<?php
require_once '../includes/auth_check.php';
requireRole('faculty');
require_once '../db.php';

$yu_courses = require __DIR__ . '/../includes/yu_courses.php';

$course_id = intval($_GET['id'] ?? 0);
$step      = intval($_GET['step'] ?? 1);
if ($step < 1 || $step > 7) $step = 1;

$stmt = $pdo->prepare('SELECT * FROM course_specs WHERE course_id = ? AND faculty_id = ?');
$stmt->execute([$course_id, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: dashboard.php');
    exit();
}

$readonly = $course['status'] !== 'draft';
$page_title = ($readonly ? 'View' : 'Edit') . ': ' . $course['course_title'];
$msg = '';
$err = '';

function h($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$readonly) {

    if ($step === 1) {
        $pdo->prepare(
            'UPDATE course_specs SET course_title=?, course_code=?, course_level=?, credit_hours=?,
             course_type=?, teaching_mode=?, prerequisites=?, corequisites=?, course_description=?, objectives=?
             WHERE course_id=?'
        )->execute([
            trim($_POST['course_title']),
            trim($_POST['course_code']),
            intval($_POST['course_level']) ?: null,
            floatval($_POST['credit_hours']) ?: null,
            trim($_POST['course_type']),
            trim($_POST['teaching_mode']),
            trim($_POST['prerequisites']),
            trim($_POST['corequisites']),
            trim($_POST['course_description']),
            trim($_POST['objectives']),
            $course_id
        ]);
        $msg = 'Course information saved.';
        $stmt->execute([$course_id, $_SESSION['user_id']]);
        $course = $stmt->fetch();
    }

     elseif ($step === 2) {

        if (!empty($_POST['clo'])) {
    
            $pdo->prepare('DELETE FROM clo_plo_mapping 
                WHERE clo_id IN (
                    SELECT clo_id 
                    FROM course_learning_outcomes 
                    WHERE course_id = ?
                )')->execute([$course_id]);
    
            $pdo->prepare('DELETE FROM jahiziah_skills 
                WHERE course_id = ?')
                ->execute([$course_id]);
    
            $pdo->prepare('DELETE FROM course_learning_outcomes 
                WHERE course_id = ?')
                ->execute([$course_id]);
    
            foreach ($_POST['clo'] as $row) {
    
                $desc = trim($row['description'] ?? '');
                $code = trim($row['code'] ?? '');
                $cat  = $row['category'] ?? 'Knowledge';
                $ts   = trim($row['teaching_strategies'] ?? '');
                $am   = trim($row['assessment_methods'] ?? '');
    
                if (!$desc) continue;
    
                $ins = $pdo->prepare('
                    INSERT INTO course_learning_outcomes
                    (
                        course_id,
                        clo_code,
                        description,
                        category,
                        teaching_strategies,
                        assessment_methods
                    )
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
    
                $ins->execute([
                    $course_id,
                    $code,
                    $desc,
                    $cat,
                    $ts,
                    $am
                ]);
    
                $clo_id = $pdo->lastInsertId();
    
                if (!empty($row['jahiziah'])) {
    
                    foreach ($row['jahiziah'] as $skill) {
    
                        $pdo->prepare('
                            INSERT INTO jahiziah_skills
                            (course_id, clo_id, skill_type)
                            VALUES (?, ?, ?)
                        ')->execute([
                            $course_id,
                            $clo_id,
                            $skill
                        ]);
                    }
                }
    
                if (!empty($row['plos']) && is_array($row['plos'])) {
    
                    foreach ($row['plos'] as $plo_id) {
    
                        $pdo->prepare('
                            INSERT IGNORE INTO clo_plo_mapping
                            (clo_id, plo_id)
                            VALUES (?, ?)
                        ')->execute([
                            $clo_id,
                            intval($plo_id)
                        ]);
                    }
                }
            }
        }
    
        $msg = 'CLOs, PLO mappings, and Jahiziah skills saved.';
    }

    elseif ($step === 3) {
        $pdo->prepare('DELETE FROM assessment_clo WHERE assessment_id IN (SELECT id FROM assessments WHERE course_id = ?)')->execute([$course_id]);
        $pdo->prepare('DELETE FROM assessments WHERE course_id = ?')->execute([$course_id]);

        if (!empty($_POST['assessment'])) {
            foreach ($_POST['assessment'] as $row) {
                $name = trim($row['activity_name'] ?? '');
                if (!$name) continue;
                $ins = $pdo->prepare('INSERT INTO assessments (course_id, activity_name, timing_week, percentage) VALUES (?, ?, ?, ?)');
                $ins->execute([$course_id, $name, intval($row['timing_week']) ?: null, floatval($row['percentage']) ?: null]);
                $a_id = $pdo->lastInsertId();

                if (!empty($row['clos']) && is_array($row['clos'])) {
                    foreach ($row['clos'] as $clo_id) {
                        $pdo->prepare('INSERT INTO assessment_clo (assessment_id, clo_id) VALUES (?, ?)')
                            ->execute([$a_id, intval($clo_id)]);
                    }
                }
            }
        }
        $msg = 'Assessments saved.';
    }

    elseif ($step === 4) {
        $pdo->prepare('DELETE FROM teaching_modes WHERE course_id = ?')->execute([$course_id]);
        $pdo->prepare('DELETE FROM contact_hours WHERE course_id = ?')->execute([$course_id]);

        if (!empty($_POST['mode'])) {
            foreach ($_POST['mode'] as $row) {
                if (!trim($row['mode_type'] ?? '')) continue;
                $pdo->prepare('INSERT INTO teaching_modes (course_id, mode_type, contact_hours, percentage) VALUES (?, ?, ?, ?)')
                    ->execute([$course_id, trim($row['mode_type']), floatval($row['contact_hours']) ?: null, floatval($row['percentage']) ?: null]);
            }
        }

        if (!empty($_POST['hours'])) {
            foreach ($_POST['hours'] as $row) {
                if (!trim($row['activity_type'] ?? '')) continue;
                $pdo->prepare('INSERT INTO contact_hours (course_id, activity_type, hours) VALUES (?, ?, ?)')
                    ->execute([$course_id, trim($row['activity_type']), floatval($row['hours']) ?: null]);
            }
        }
        $msg = 'Teaching modes and contact hours saved.';
    }

    elseif ($step === 5) {
        $pdo->prepare('DELETE FROM course_topics WHERE course_id = ?')->execute([$course_id]);
        if (!empty($_POST['topic'])) {
            $order = 0;
            foreach ($_POST['topic'] as $row) {
                $txt = trim($row['topic_text'] ?? '');
                if (!$txt) continue;
                $pdo->prepare('INSERT INTO course_topics (course_id, topic_text, contact_hours, sort_order) VALUES (?, ?, ?, ?)')
                    ->execute([$course_id, $txt, floatval($row['contact_hours']) ?: null, $order++]);
            }
        }
        $msg = 'Topics saved.';
    }

    elseif ($step === 6) {
        $pdo->prepare('DELETE FROM resources WHERE course_id = ?')->execute([$course_id]);
        if (!empty($_POST['resource'])) {
            foreach ($_POST['resource'] as $row) {
                $txt = trim($row['resource_text'] ?? '');
                if (!$txt) continue;
                $pdo->prepare('INSERT INTO resources (course_id, category, resource_text) VALUES (?, ?, ?)')
                    ->execute([$course_id, $row['category'], $txt]);
            }
        }
        $msg = 'Resources saved.';
    }

    if (isset($_POST['next'])) {
        header('Location: course_edit.php?id=' . $course_id . '&step=' . ($step + 1));
        exit();
    }
}

elseif ($step === 7) {

        $pdo->prepare('DELETE FROM course_pdca WHERE course_id = ?')
            ->execute([$course_id]);
    
        if (!empty($_POST['pdca'])) {
            foreach ($_POST['pdca'] as $row) {
    
                $phase = trim($row['phase'] ?? '');
                $content = trim($row['content'] ?? '');
    
                if (!$phase && !$content) continue;
    
                $pdo->prepare('
                    INSERT INTO course_pdca (course_id, phase, content, created_at)
                    VALUES (?, ?, ?, NOW())
                ')->execute([
                    $course_id,
                    $phase,
                    $content
                ]);
            }
        }
        $msg = 'PDCA saved.';

    }
}

// get page data

$clos = $pdo->prepare('SELECT * FROM course_learning_outcomes WHERE course_id = ? ORDER BY category, clo_code');
$clos->execute([$course_id]);
$clos = $clos->fetchAll();

$clo_ids   = array_column($clos, 'clo_id');
$clo_maps  = [];
if ($clo_ids) {
    $in  = implode(',', array_fill(0, count($clo_ids), '?'));
    $map = $pdo->prepare("SELECT * FROM clo_plo_mapping WHERE clo_id IN ($in)");
    $map->execute($clo_ids);
    foreach ($map->fetchAll() as $row) {
        $clo_maps[$row['clo_id']][$row['plo_id']] = true;
    }
}

$program_id = $course['program_id'];
$plos = [];
if ($program_id) {
    $ps = $pdo->prepare('SELECT * FROM program_learning_outcomes WHERE program_id = ? ORDER BY category, plo_code');
    $ps->execute([$program_id]);
    $plos = $ps->fetchAll();
}

$assessments = $pdo->prepare('SELECT a.*, GROUP_CONCAT(ac.clo_id) as clo_ids FROM assessments a LEFT JOIN assessment_clo ac ON a.id = ac.assessment_id WHERE a.course_id = ? GROUP BY a.id');
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

$jahiziah = [];

$stmt = $pdo->prepare("
    SELECT clo_id, skill_type
    FROM jahiziah_skills
    WHERE course_id = ?
");
$stmt->execute([$course_id]);

foreach ($stmt->fetchAll() as $row) {
    $jahiziah[$row['clo_id']][] = $row['skill_type'];
}

$step_labels = [
    1 => 'Course Info',
    2 => 'CLOs & PLO Mapping',
    3 => 'Assessments',
    4 => 'Teaching Hours',
    5 => 'Topics',
    6 => 'Resources',
    7 => 'PDCA Quality Log',
    8 => 'Review & Submit'
];

include '../includes/header.php';
?>

<?php if ($readonly): ?>
<div class="alert alert-info">
    <div>
        <strong>Read-only mode.</strong> This course is currently
        <span class="status-badge status-<?php echo $course['status']; ?>"><?php echo ucwords(str_replace('_', ' ', $course['status'])); ?></span>
        and cannot be edited until it's returned to draft.
    </div>
</div>
<?php endif; ?>

<div class="card" style="padding:14px 22px;">
    <div class="tabs" style="margin-bottom:0;">
        <?php foreach ($step_labels as $i => $label): ?>
            <a href="course_edit.php?id=<?php echo $course_id; ?>&step=<?php echo $i; ?>"
               class="tab-link <?php echo $step === $i ? 'active' : ''; ?>">
                <?php echo $i . '. ' . $label; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo h($msg); ?></div>
<?php endif; ?>
<?php if ($err): ?>
    <div class="alert alert-danger"><?php echo h($err); ?></div>
<?php endif; ?>

<div class="card">
<form method="POST">
<fieldset <?php echo $readonly ? 'disabled' : ''; ?> style="border:none; padding:0; margin:0;">

<?php if ($step === 1): ?>

    <div class="card-header"><h2>Step 1 — Course Identification</h2></div>

    <div class="form-group">
        <label>YU study plan course</label>
        <select id="yu_course_select" onchange="fillYuCourse(this)">
            <option value="">-- Select course --</option>
            <?php foreach ($yu_courses as $c): ?>
                <option value="<?php echo h($c['code']); ?>"
                        data-code="<?php echo h($c['code']); ?>"
                        data-title="<?php echo h($c['title']); ?>"
                        data-level="<?php echo h($c['level']); ?>"
                        data-credits="<?php echo h($c['credits']); ?>"
                        data-type="<?php echo h($c['type']); ?>">
                    <?php echo h($c['code'] . ' - ' . $c['title'] . ' (' . $c['credits'] . ' cr)'); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label>Course Title *</label>
            <input type="text" id="course_title" name="course_title" required value="<?php echo h($course['course_title']); ?>">
        </div>
        <div class="form-group">
            <label>Course Code *</label>
            <input type="text" id="course_code" name="course_code" value="<?php echo h($course['course_code']); ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Level / Year (NQF)</label>
            <input type="number" id="course_level" name="course_level" min="1" max="8" value="<?php echo h($course['course_level']); ?>">
        </div>
        <div class="form-group">
            <label>Credit Hours</label>
            <input type="number" id="credit_hours" name="credit_hours" step="0.5" value="<?php echo h($course['credit_hours']); ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Course Type</label>
            <input type="text" id="course_type" name="course_type" value="<?php echo h($course['course_type']); ?>">
        </div>
        <div class="form-group">
            <label>Teaching Mode</label>
            <input type="text" name="teaching_mode"
                   value="<?php echo h($course['teaching_mode']); ?>">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label>Pre-requisites</label>
            <input type="text" name="prerequisites" value="<?php echo h($course['prerequisites']); ?>">
        </div>
        <div class="form-group">
            <label>Co-requisites</label>
            <input type="text" name="corequisites" value="<?php echo h($course['corequisites']); ?>">
        </div>
    </div>
    <div class="form-group">
        <label>Course General Description</label>
        <textarea name="course_description" rows="4"><?php echo h($course['course_description']); ?></textarea>
    </div>
    <div class="form-group">
        <label>Course Main Objective(s)</label>
        <textarea name="objectives" rows="3"><?php echo h($course['objectives']); ?></textarea>
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

<?php elseif ($step === 2): ?>

    <div class="card-header">
        <h2>Step 2 — Course Learning Outcomes (CLOs) & PLO Mapping</h2>
        <?php if (!$readonly): ?>
        <button type="button" class="btn btn-outline btn-sm" onclick="addCloRow()">+ Add CLO</button>
        <?php endif; ?>
    </div>

    <?php if (empty($plos)): ?>
        <div class="alert alert-warning">
            <div><strong>No PLOs found.</strong> Ask your HoD to add Program Learning Outcomes for this program first.</div>
        </div>
    <?php endif; ?>

    <p class="text-muted" style="margin-bottom:12px;">
        Each CLO must map to <strong>at least one PLO</strong>. Use the checkboxes to indicate alignment.
    </p>

    <div style="overflow-x:auto;">
    <table class="matrix-table" id="clo-table">
        <thead>
            <tr>
                <th style="width:60px;">Code</th>
                <th style="min-width:220px;">Description</th>
                <th style="width:110px;">Category</th>
                <th style="min-width:140px;">Teaching Strategies</th>
                <th style="min-width:140px;">Assessment Methods</th>
                <?php foreach ($plos as $plo): ?>
                    <th style="width:55px;" title="<?php echo h($plo['description']); ?>">
                        <?php echo h($plo['plo_code']); ?>
                    </th>
                <?php endforeach; ?>
                <?php if (!$readonly): ?><th style="width:30px;"></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($clos)): ?>
            <tr id="clo-row-0">
                <td><input type="text" name="clo[0][code]"></td>
                <td><input type="text" name="clo[0][description]"></td>
                <td>
                    <select name="clo[0][category]">
                        <option>Knowledge</option><option>Skills</option><option>Values</option>
                    </select>
                </td>
                <td><input type="text" name="clo[0][teaching_strategies]"></td>
                <td><input type="text" name="clo[0][assessment_methods]"></td>
                
                <div style="display:flex; gap:14px; flex-wrap:wrap;">
        <?php foreach (['Digital', 'Communication', 'Teamwork', 'Ethics'] as $skill): ?>
            <label style="display:flex; align-items:center; gap:5px; margin:0; font-weight:400;">
                <input type="checkbox"
                       name="clo[0][jahiziah][]"
                       value="<?php echo $skill; ?>">
                <span><?php echo $skill; ?></span>
            </label>
        <?php endforeach; ?>
    </div>
</td>
                <?php foreach ($plos as $plo): ?>
                    <td><input type="checkbox" name="clo[0][plos][]" value="<?php echo $plo['plo_id']; ?>"></td>
                <?php endforeach; ?>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php else: ?>
            <?php foreach ($clos as $i => $clo): ?>
            <tr id="clo-row-<?php echo $i; ?>">
                <td><input type="text" name="clo[<?php echo $i; ?>][code]" value="<?php echo h($clo['clo_code']); ?>"></td>
                <td><input type="text" name="clo[<?php echo $i; ?>][description]" value="<?php echo h($clo['description']); ?>"></td>
                <td>
                    <select name="clo[<?php echo $i; ?>][category]">
                        <?php foreach (['Knowledge', 'Skills', 'Values'] as $cat): ?>
                            <option <?php echo $clo['category'] === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="clo[<?php echo $i; ?>][teaching_strategies]" value="<?php echo h($clo['teaching_strategies']); ?>"></td>
                <td><input type="text" name="clo[<?php echo $i; ?>][assessment_methods]" value="<?php echo h($clo['assessment_methods']); ?>"></td>
                
    <?php foreach (['Digital', 'Communication', 'Teamwork', 'Ethics'] as $skill): ?>
        <label style="display:block; font-weight:400;">
            <input type="checkbox"
                   name="clo[<?php echo $i; ?>][jahiziah][]"
                   value="<?php echo $skill; ?>"
                   <?php echo (!empty($jahiziah[$clo['clo_id']]) && in_array($skill, $jahiziah[$clo['clo_id']])) ? 'checked' : ''; ?>>
            <?php echo $skill; ?>
        </label>
    <?php endforeach; ?>
</td>
        
                    <?php foreach ($plos as $plo): ?>
                    <td><input type="checkbox" name="clo[<?php echo $i; ?>][plos][]"
                               value="<?php echo $plo['plo_id']; ?>"
                               <?php echo isset($clo_maps[$clo['clo_id']][$plo['plo_id']]) ? 'checked' : ''; ?>></td>
                <?php endforeach; ?>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    </div>

    <script>
    var cloCount = <?php echo max(count($clos), 1); ?>;
    var ploList  = <?php echo json_encode(array_map(fn($p) => ['id' => $p['plo_id'], 'code' => $p['plo_code']], $plos)); ?>;

    function addCloRow() {
        var i = cloCount++;
        var tr = document.createElement('tr');
        var catOpts = ['Knowledge', 'Skills', 'Values'].map(c => '<option>' + c + '</option>').join('');
        var ploBoxes = ploList.map(p => '<td><input type="checkbox" name="clo['+i+'][plos][]" value="'+p.id+'"></td>').join('');
        tr.innerHTML =
            '<td><input type="text" name="clo['+i+'][code]"></td>' +
            '<td><input type="text" name="clo['+i+'][description]"></td>' +
            '<td><select name="clo['+i+'][category]">'+catOpts+'</select></td>' +
            '<td><input type="text" name="clo['+i+'][teaching_strategies]"></td>' +
            '<td><input type="text" name="clo['+i+'][assessment_methods]"></td>' +

'<td>' +
'<div style="display:flex; gap:14px; flex-wrap:wrap;">' +

['Digital','Communication','Teamwork','Ethics'].map(skill =>
    '<label style="display:flex; align-items:center; gap:5px; margin:0; font-weight:400;">' +
    '<input type="checkbox" name="clo['+i+'][jahiziah][]" value="'+skill+'">' +
    '<span>'+skill+'</span>' +
    '</label>'
).join('') +

'</div>' +
'</td>' +
            
            ploBoxes +
            '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()">✕</button></td>';
        document.querySelector('#clo-table tbody').appendChild(tr);
    }
    </script>

<?php elseif ($step === 3): ?>

    <div class="card-header">
        <h2>Step 3 — Assessment Activities</h2>
        <?php if (!$readonly): ?>
        <button type="button" class="btn btn-outline btn-sm" onclick="addAssessRow()">+ Add Activity</button>
        <?php endif; ?>
    </div>

    <p class="text-muted" style="margin-bottom:12px;">
        Total percentage must add up to <strong>100%</strong>. Each activity should map to one or more CLOs.
    </p>

    <table id="assess-table">
        <thead>
            <tr>
                <th>Activity</th>
                <th style="width:100px;">Week No.</th>
                <th style="width:100px;">% of Total</th>
                <th>Linked CLOs</th>
                <?php if (!$readonly): ?><th style="width:30px;"></th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($assessments)): ?>
            <tr>
                <td><input type="text" name="assessment[0][activity_name]"></td>
                <td><input type="number" name="assessment[0][timing_week]" min="1" max="16"></td>
                <td><input type="number" name="assessment[0][percentage]" step="0.5" min="0" max="100"></td>
                <td>
                    <?php foreach ($clos as $clo): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;margin-right:10px;font-weight:400;">
                            <input type="checkbox" name="assessment[0][clos][]" value="<?php echo $clo['clo_id']; ?>">
                            <?php echo h($clo['clo_code']); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php else: ?>
            <?php foreach ($assessments as $i => $a):
                $linked = $a['clo_ids'] ? explode(',', $a['clo_ids']) : [];
            ?>
            <tr>
                <td><input type="text" name="assessment[<?php echo $i; ?>][activity_name]" value="<?php echo h($a['activity_name']); ?>"></td>
                <td><input type="number" name="assessment[<?php echo $i; ?>][timing_week]" value="<?php echo h($a['timing_week']); ?>" min="1" max="16"></td>
                <td><input type="number" name="assessment[<?php echo $i; ?>][percentage]" value="<?php echo h($a['percentage']); ?>" step="0.5" min="0" max="100"></td>
                <td>
                    <?php foreach ($clos as $clo): ?>
                        <label style="display:inline-flex;align-items:center;gap:4px;margin-right:10px;font-weight:400;">
                            <input type="checkbox" name="assessment[<?php echo $i; ?>][clos][]"
                                   value="<?php echo $clo['clo_id']; ?>"
                                   <?php echo in_array($clo['clo_id'], $linked) ? 'checked' : ''; ?>>
                            <?php echo h($clo['clo_code']); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    var assessCount = <?php echo max(count($assessments), 1); ?>;
    var closForAssess = <?php echo json_encode(array_map(fn($c) => ['id' => $c['clo_id'], 'code' => $c['clo_code']], $clos)); ?>;

    function addAssessRow() {
        var i = assessCount++;
        var tr = document.createElement('tr');
        var cloBoxes = closForAssess.map(c =>
            '<label style="display:inline-flex;align-items:center;gap:4px;margin-right:10px;font-weight:400;">' +
            '<input type="checkbox" name="assessment['+i+'][clos][]" value="'+c.id+'">' + c.code + '</label>'
        ).join('');
        tr.innerHTML =
            '<td><input type="text" name="assessment['+i+'][activity_name]"></td>' +
            '<td><input type="number" name="assessment['+i+'][timing_week]" min="1" max="16"></td>' +
            '<td><input type="number" name="assessment['+i+'][percentage]" step="0.5" min="0" max="100"></td>' +
            '<td>' + cloBoxes + '</td>' +
            '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()">✕</button></td>';
        document.querySelector('#assess-table tbody').appendChild(tr);
    }
    </script>

<?php elseif ($step === 4): ?>

    <div class="card-header"><h2>Step 4 — Teaching Modes & Contact Hours</h2></div>

    <h3 style="margin-bottom:10px; color:var(--yu-orange); font-size:14px;">A. Mode of Instruction</h3>
    <table>
        <thead>
            <tr><th>Mode</th><th style="width:140px;">Contact Hours</th><th style="width:140px;">Percentage (%)</th></tr>
        </thead>
        <tbody>
            <?php
            $mode_rows = $modes ?: [['mode_type' => '', 'contact_hours' => '', 'percentage' => '']];
            foreach ($mode_rows as $i => $m):
            ?>
            <tr>
                <td>
                    <input type="text" name="mode[<?php echo $i; ?>][mode_type]"
                           value="<?php echo h($m['mode_type'] ?? ''); ?>">
                </td>
                <td><input type="number" name="mode[<?php echo $i; ?>][contact_hours]" step="0.5" value="<?php echo h($m['contact_hours'] ?? ''); ?>"></td>
                <td><input type="number" name="mode[<?php echo $i; ?>][percentage]" step="0.5" value="<?php echo h($m['percentage'] ?? ''); ?>"></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 style="margin:24px 0 10px; color:var(--yu-orange); font-size:14px;">B. Contact Hours Breakdown</h3>
    <table>
        <thead>
            <tr><th>Activity</th><th style="width:160px;">Hours</th></tr>
        </thead>
        <tbody>
            <?php
            $hour_options = ['Lectures', 'Laboratory/Studio', 'Field', 'Tutorial', 'Others'];
            $hour_data = [];
            foreach ($chours as $h) $hour_data[$h['activity_type']] = $h;
            foreach ($hour_options as $i => $opt):
                $h = $hour_data[$opt] ?? null;
            ?>
            <tr>
                <td>
                    <input type="hidden" name="hours[<?php echo $i; ?>][activity_type]" value="<?php echo h($opt); ?>">
                    <?php echo h($opt); ?>
                </td>
                <td><input type="number" name="hours[<?php echo $i; ?>][hours]" step="0.5" value="<?php echo h($h['hours'] ?? ''); ?>"></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif ($step === 5): ?>

    <div class="card-header">
        <h2>Step 5 — Course Topics & Content</h2>
        <?php if (!$readonly): ?>
        <button type="button" class="btn btn-outline btn-sm" onclick="addTopicRow()">+ Add Topic</button>
        <?php endif; ?>
    </div>

    <table id="topic-table">
        <thead>
            <tr><th style="width:50px;">#</th><th>Topic</th><th style="width:140px;">Contact Hours</th><?php if (!$readonly): ?><th style="width:30px;"></th><?php endif; ?></tr>
        </thead>
        <tbody>
            <?php if (empty($topics)): ?>
            <tr>
                <td class="text-muted">1</td>
                <td><input type="text" name="topic[0][topic_text]"></td>
                <td><input type="number" name="topic[0][contact_hours]" step="0.5"></td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php else: ?>
            <?php foreach ($topics as $i => $t): ?>
            <tr>
                <td class="text-muted"><?php echo $i + 1; ?></td>
                <td><input type="text" name="topic[<?php echo $i; ?>][topic_text]" value="<?php echo h($t['topic_text']); ?>"></td>
                <td><input type="number" name="topic[<?php echo $i; ?>][contact_hours]" step="0.5" value="<?php echo h($t['contact_hours']); ?>"></td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    var topicCount = <?php echo max(count($topics), 1); ?>;
    function addTopicRow() {
        var i = topicCount++;
        var tbody = document.querySelector('#topic-table tbody');
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td class="text-muted">'+(tbody.children.length+1)+'</td>' +
            '<td><input type="text" name="topic['+i+'][topic_text]"></td>' +
            '<td><input type="number" name="topic['+i+'][contact_hours]" step="0.5"></td>' +
            '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()">✕</button></td>';
        tbody.appendChild(tr);
    }
    </script>

<?php elseif ($step === 6): ?>

    <div class="card-header">
        <h2>Step 6 — Learning Resources</h2>
        <?php if (!$readonly): ?>
        <button type="button" class="btn btn-outline btn-sm" onclick="addResourceRow()">+ Add Resource</button>
        <?php endif; ?>
    </div>

    <h3 style="margin-bottom:10px; color:var(--yu-orange); font-size:14px;">A. Learning Resources</h3>
    <table id="res-table">
        <thead>
            <tr><th style="width:160px;">Category</th><th>Resource</th><?php if (!$readonly): ?><th style="width:30px;"></th><?php endif; ?></tr>
        </thead>
        <tbody>
            <?php if (empty($res)): ?>
            <?php foreach (['Essential', 'Supportive', 'Electronic', 'Other'] as $i => $cat): ?>
            <tr>
                <td>
                    <select name="resource[<?php echo $i; ?>][category]">
                        <?php foreach (['Essential', 'Supportive', 'Electronic', 'Other'] as $c): ?>
                            <option <?php echo $c === $cat ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="resource[<?php echo $i; ?>][resource_text]"></td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php else: ?>
            <?php foreach ($res as $i => $r): ?>
            <tr>
                <td>
                    <select name="resource[<?php echo $i; ?>][category]">
                        <?php foreach (['Essential', 'Supportive', 'Electronic', 'Other'] as $c): ?>
                            <option <?php echo $r['category'] === $c ? 'selected' : ''; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><input type="text" name="resource[<?php echo $i; ?>][resource_text]" value="<?php echo h($r['resource_text']); ?>"></td>
                <?php if (!$readonly): ?><td><button type="button" class="icon-btn" onclick="this.closest('tr').remove()">✕</button></td><?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
    var resCount = <?php echo max(count($res), 4); ?>;
    function addResourceRow() {
        var i = resCount++;
        var tr = document.createElement('tr');
        var opts = ['Essential','Supportive','Electronic','Other'].map(c => '<option>'+c+'</option>').join('');
        tr.innerHTML =
            '<td><select name="resource['+i+'][category]">'+opts+'</select></td>' +
            '<td><input type="text" name="resource['+i+'][resource_text]"></td>' +
            '<td><button type="button" class="icon-btn" onclick="this.closest(\'tr\').remove()">✕</button></td>';
        document.querySelector('#res-table tbody').appendChild(tr);
    }
    </script>

                <?php elseif ($step === 7): ?>

<div class="card-header">
    <h2>Step 7 — PDCA Quality Improvement Log</h2>
</div>


<table id="pdca-table">
    <thead>
        <tr>
            <th style="width:120px;">Phase</th>
            <th>Content</th>
            <?php if (!$readonly): ?>
            <th style="width:40px;"></th>
            <?php endif; ?>
        </tr>
    </thead>
    <?php
$pdca_items = $pdo->prepare('SELECT * FROM course_pdca WHERE course_id = ? ORDER BY id ASC');
$pdca_items->execute([$course_id]);
$pdca_items = $pdca_items->fetchAll();
?>

    <tbody>
        <?php foreach ($pdca_items as $i => $p): ?>
        <tr>
            <td>
                <select name="pdca[<?php echo $i; ?>][phase]">
                    <?php foreach (['Plan','Do','Check','Act'] as $ph): ?>
                        <option <?php echo $p['phase'] === $ph ? 'selected' : ''; ?>>
                            <?php echo $ph; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>

            <td>
                <textarea name="pdca[<?php echo $i; ?>][content]" rows="2"><?php echo h($p['content']); ?></textarea>
            </td>

            <?php if (!$readonly): ?>
            <td><button type="button" onclick="this.closest('tr').remove()">✕</button></td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($pdca_items)): ?>
        <tr>
            <td>
                <select name="pdca[0][phase]">
                    <option>Plan</option>
                    <option>Do</option>
                    <option>Check</option>
                    <option>Act</option>
                </select>
            </td>
            <td><textarea name="pdca[0][content]" rows="2"></textarea></td>
            <?php if (!$readonly): ?>
            <td><button type="button" onclick="this.closest('tr').remove()">✕</button></td>
            <?php endif; ?>
        </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php if (!$readonly): ?>
<button type="button" class="btn btn-outline btn-sm" onclick="addPDCA()">+ Add PDCA Entry</button>
<?php endif; ?>

<script>
var pdcaCount = <?php echo max(count($pdca_items), 1); ?>;

function addPDCA() {
    var i = pdcaCount++;

    var tr = document.createElement('tr');

    tr.innerHTML =
        '<td>' +
        '<select name="pdca['+i+'][phase]">' +
        '<option>Plan</option>' +
        '<option>Do</option>' +
        '<option>Check</option>' +
        '<option>Act</option>' +
        '</select>' +
        '</td>' +

        '<td><textarea name="pdca['+i+'][content]" rows="2"></textarea></td>' +

        '<td><button type="button" onclick="this.closest(\'tr\').remove()">✕</button></td>';

    document.querySelector('#pdca-table tbody').appendChild(tr);
}
</script>


<?php elseif ($step === 8): ?>

    <div class="card-header"><h2>Step 8 — Final Review & Submission</h2></div>

    <?php
    $has_clos    = !empty($clos);
    $all_mapped  = true;
    foreach ($clos as $clo) {
        if (empty($clo_maps[$clo['clo_id']])) { $all_mapped = false; break; }
    }
    $has_assess  = !empty($assessments);
    $total_pct   = array_sum(array_column($assessments, 'percentage'));
    $pct_ok      = abs($total_pct - 100) < 0.01;
    $has_basic   = !empty($course['course_description']) && !empty($course['objectives']);
    $can_submit  = $has_clos && $all_mapped && $has_assess && $has_basic;
    ?>

    <h3 style="margin-bottom:14px; color:var(--yu-black); font-size:14px;">Integrity Checks</h3>

    <table style="margin-bottom:24px;">
        <tbody>
            <tr>
                <td style="width:60%;">Course identification &amp; description complete</td>
                <td>
                    <?php if ($has_basic): ?>
                        <span class="text-success">✓ Complete</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Missing description or objectives (Step 1)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>At least one CLO defined</td>
                <td>
                    <?php if ($has_clos): ?>
                        <span class="text-success">✓ <?php echo count($clos); ?> CLO(s)</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Add CLOs in Step 2</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Every CLO mapped to at least one PLO</td>
                <td>
                    <?php if ($all_mapped && $has_clos): ?>
                        <span class="text-success">✓ All CLOs mapped</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Some CLOs have no PLO mapping (Step 2)</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Assessments defined</td>
                <td>
                    <?php if ($has_assess): ?>
                        <span class="text-success">✓ <?php echo count($assessments); ?> activity/activities</span>
                    <?php else: ?>
                        <span class="text-danger">✗ Add assessments in Step 3</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>Assessment weights sum to 100%</td>
                <td>
                    <?php echo number_format($total_pct, 1); ?>%
                    <?php if ($pct_ok): ?>
                        <span class="text-success"> ✓</span>
                    <?php else: ?>
                        <span style="color:var(--warning);"> (should equal 100%)</span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <?php if (!$readonly): ?>
        <?php if (!$can_submit): ?>
            <div class="alert alert-warning">
                <div>Fix the integrity checks above before submitting to the HoD.</div>
            </div>
        <?php endif; ?>

        <div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap; margin-top:8px;">
            <a href="<?php echo BASE_URL; ?>/course/view.php?id=<?php echo $course_id; ?>" class="btn btn-outline" target="_blank">Preview Full Spec</a>
            <?php if ($can_submit && $course['status'] === 'draft'): ?>
                <a href="course_submit.php?id=<?php echo $course_id; ?>"
                   class="btn btn-success"
                   onclick="return confirm('Submit this course specification to the HoD for review?\n\nOnce submitted, you cannot edit it until the HoD returns it.')">
                    ✓ Submit to HoD
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div style="margin-top:12px;">
            <span class="status-badge status-<?php echo $course['status']; ?>" style="font-size:13px; padding:6px 14px;">
                <?php echo ucwords(str_replace('_', ' ', $course['status'])); ?>
            </span>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php if ($step < 7 && !$readonly): ?>
    <div style="display:flex; gap:10px; margin-top:24px; padding-top:18px; border-top:1px solid var(--border);">
        <button type="submit" name="save" class="btn btn-outline">Save</button>
        <button type="submit" name="next" class="btn btn-primary">Save &amp; Next →</button>
        <a href="dashboard.php" class="btn btn-ghost" style="margin-left:auto;">Back to Dashboard</a>
    </div>
<?php endif; ?>

</fieldset>
</form>
</div>

<?php include '../includes/footer.php'; ?>
