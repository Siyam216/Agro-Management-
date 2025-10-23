<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Health Records');

$animals = runQuery($pdo, "SELECT animal_id, tag_code FROM animals ORDER BY animal_id DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '')==='add') {
  runQuery($pdo, "INSERT INTO health_record (animal_id, visit_date, diagnosis, treatment, vet_name, cost)
                  VALUES (?,?,?,?,?,?)", [
    (int)($_POST['animal_id'] ?? 0),
    $_POST['visit_date'] ?: date('Y-m-d'),
    trim($_POST['diagnosis'] ?? ''),
    trim($_POST['treatment'] ?? ''),
    trim($_POST['vet_name'] ?? ''),
    (float)($_POST['cost'] ?? 0)
  ]);
  echo "<p>✅ Health record added.</p>";
}
?>
<div class="card">
  <h2>Add Health Record</h2>
  <form method="post" class="row">
    <input type="hidden" name="action" value="add">
    <div class="col">
      <label>Animal</label><br>
      <select name="animal_id" required>
        <option value="">--select--</option>
        <?php foreach($animals as $a): ?>
          <option value="<?= h($a['animal_id']) ?>">[<?= h($a['animal_id']) ?>] <?= h($a['tag_code']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col"><label>Visit Date</label><br><input type="date" name="visit_date" value="<?= h(date('Y-m-d')) ?>"></div>
    <div class="col"><label>Diagnosis</label><br><input name="diagnosis"></div>
    <div class="col"><label>Treatment</label><br><input name="treatment"></div>
    <div class="col"><label>Vet Name</label><br><input name="vet_name"></div>
    <div class="col"><label>Cost</label><br><input type="number" step="0.01" name="cost" value="0"></div>
    <div class="col" style="align-self:end"><button>Add</button></div>
  </form>
</div>

<div class="card">
  <h2>Recent Health Records</h2>
  <?php
    $rows = runQuery($pdo, "SELECT h.health_id, h.visit_date, h.diagnosis, h.treatment, h.vet_name, h.cost,
                                   a.tag_code, a.species
                            FROM health_record h
                            JOIN animals a ON a.animal_id = h.animal_id
                            ORDER BY h.visit_date DESC, h.health_id DESC
                            LIMIT 100")->fetchAll();
    renderTable($rows, "Health visits");
  ?>
</div>
<?php page_footer(); ?>
