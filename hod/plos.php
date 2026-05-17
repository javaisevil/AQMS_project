<?php
require_once '../includes/auth_check.php';
requireRole('hod');
require_once '../db.php';

$page_title = 'Manage Program Learning Outcomes';

$programs = $pdo->query('SELECT * FROM program_specs ORDER BY program_name')->fetchAll();
$selected_program = intval($_GET['program_id'] ?? ($programs[0]['program_id'] ?? 0));

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $code     = trim($_POST['plo_code'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'Knowledge';
        $prog_id  = intval($_POST['program_id']);

        if (!$code || !$desc) {
            $error = 'PLO code and description are required.';
        } else {
            $pdo->prepare(
                'INSERT INTO program_learning_outcomes (program_id, plo_code, description, category) VALUES (?, ?, ?, ?)'
            )->execute([$prog_id, $code, $desc, $category]);
            $msg = 'PLO added.';
            $selected_program = $prog_id;
        }

    } elseif ($action === 'edit') {
        $plo_id   = intval($_POST['plo_id']);
        $code     = trim($_POST['plo_code'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? 'Knowledge';
        $prog_id  = intval($_POST['program_id']);

        if (!$code || !$desc) {
            $error = 'PLO code and description are required.';
        } else {
            $stmt = $pdo->prepare(
                'UPDATE program_learning_outcomes 
                 SET plo_code = ?, description = ?, category = ?, program_id = ? 
                 WHERE plo_id = ?'
            );
            $stmt->execute([$code, $desc, $category, $prog_id, $plo_id]);
            $msg = 'PLO updated successfully.';
            $selected_program = $prog_id;
        }

    } elseif ($action === 'delete') {
        $plo_id = intval($_POST['plo_id']);
        $pdo->prepare('DELETE FROM program_learning_outcomes WHERE plo_id = ?')->execute([$plo_id]);
        $msg = 'PLO removed.';
        $selected_program = intval($_POST['program_id']);
    }
}

$plos = [];
if ($selected_program) {
    $stmt = $pdo->prepare('SELECT * FROM program_learning_outcomes WHERE program_id = ? ORDER BY category, plo_code');
    $stmt->execute([$selected_program]);
    $plos = $stmt->fetchAll();
}

include '../includes/header.php';
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div style="display:flex; gap:20px; flex-wrap:wrap; align-items:flex-start;">

    <div style="flex:1; min-width:0;">
        <div class="card">
            <div class="card-header">
                <h2>Program Learning Outcomes (PLOs)</h2>
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

            <?php if (empty($plos)): ?>
                <p class="empty-state">No PLOs added yet for this program.</p>
            <?php else: ?>
                <?php
                $grouped = ['Knowledge' => [], 'Skills' => [], 'Values' => []];
                foreach ($plos as $plo) $grouped[$plo['category']][] = $plo;
                ?>
                <?php foreach ($grouped as $cat => $items): ?>
                    <?php if (empty($items)) continue; ?>
                    <h3 style="font-size:12px; color:var(--yu-orange); text-transform:uppercase; letter-spacing:1px; margin:18px 0 8px;"><?php echo $cat; ?></h3>
                    <table style="margin-bottom:14px;">
                        <thead>
                            <tr><th style="width:80px;">Code</th><th>Description</th><th style="width:80px;"></th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $plo): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($plo['plo_code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($plo['description']); ?></td>
                                <td> 
                                    <div style="display:flex; gap:5px;"> 
                                        <button type="button" class="btn btn-sm btn-edit"
                                        onclick='openEditModal(<?php echo htmlspecialchars(json_encode($plo)); ?>)'>
                                    Edit
                                </button>

                                    <form method="POST" onsubmit="return confirm('Remove this PLO? Any CLO mappings to it will also be removed.')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="plo_id" value="<?php echo $plo['plo_id']; ?>">
                                        <input type="hidden" name="program_id" value="<?php echo $selected_program; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                    </form>
                                </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div style="width:300px; min-width:260px;">
        <div class="card">
            <div class="card-header"><h2>Add a New PLO</h2></div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Program</label>
                    <select name="program_id">
                        <?php foreach ($programs as $p): ?>
                            <option value="<?php echo $p['program_id']; ?>"
                                <?php echo $p['program_id'] == $selected_program ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['program_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select name="category">
                        <option>Knowledge</option>
                        <option>Skills</option>
                        <option>Values</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>PLO Code</label>
                    <input type="text" name="plo_code" placeholder="K3, S2, V1..." required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" required placeholder="Describe this learning outcome..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Add PLO</button>
            </form>
        </div>
    </div>

</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="card-header modal-header">
            <h2>Edit PLO</h2>
            <button type="button" class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="plo_id" id="edit_plo_id">
            <input type="hidden" name="program_id" value="<?php echo $selected_program; ?>">
            <div class="form-group">
                <label>Category</label>
                <select name="category" id="edit_category">
                    <option>Knowledge</option>
                    <option>Skills</option>
                    <option>Values</option>
                </select>
            </div>
            <div class="form-group">
                <label>PLO Code</label>
                <input type="text" name="plo_code" id="edit_plo_code" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="edit_description" rows="4" required></textarea>
            </div>       
            <div style="display:flex; gap:10px; margin-top:10px;">
                <button type="submit" class="btn btn-primary btn-full">Save changes</button>
                <button type="button" onclick="closeModal()" class="btn btn-full btn-secondary">Cancel</button>
            </div>   
        </form>
    </div>
</div>

<script>
    function openEditModal(plo) {
        document.getElementById('edit_plo_id').value = plo.plo_id;
        document.getElementById('edit_plo_code').value = plo.plo_code;
        document.getElementById('edit_description').value = plo.description;
        document.getElementById('edit_category').value = plo.category;

        document.getElementById('editModal').classList.add('modal-open');
    }

    function closeModal() {
        document.getElementById('editModal').classList.remove('modal-open');
    }

    window.onclick = function (event) {
        let modal = document.getElementById('editModal');
        if (event.target == modal) {
            closeModal();
        }
    }

</script>

                        
                        

<?php include '../includes/footer.php'; ?>