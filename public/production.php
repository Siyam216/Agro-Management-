<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Production');

$animals = runQuery($pdo, "SELECT animal_id, tag_code FROM animals ORDER BY animal_id DESC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '')==='add') {
  $animal_id = (int)($_POST['animal_id'] ?? 0);
  $prod_date = $_POST['prod_date'] ?: date('Y-m-d');
  runQuery($pdo, "INSERT INTO production (animal_id, prod_date, milk_liters, eggs_count, weight_gain_kg, notes)
                  VALUES (?,?,?,?,?,?)", [
    $animal_id,
    $prod_date,
    (float)($_POST['milk_liters'] ?? 0),
    (int)($_POST['eggs_count'] ?? 0),
    (float)($_POST['weight_gain_kg'] ?? 0),
    trim($_POST['notes'] ?? '')
  ]);
  echo "<p>✅ Production entry added.</p>";
}
?>
<div class="card">
  <h2>Add Production</h2>
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
    <div class="col">
      <label>Date</label><br>
      <input type="date" name="prod_date" value="<?= h(date('Y-m-d')) ?>">
    </div>
    <div class="col">
      <label>Milk (L)</label><br>
      <input type="number" step="0.01" name="milk_liters">
    </div>
    <div class="col">
      <label>Eggs</label><br>
      <input type="number" name="eggs_count">
    </div>
    <div class="col">
      <label>Weight Gain (kg)</label><br>
      <input type="number" step="0.01" name="weight_gain_kg">
    </div>
    <div class="col">
      <label>Notes</label><br>
      <input name="notes">
    </div>
    <div class="col" style="align-self:end">
      <button>Add</button>
    </div>
  </form>
</div>

<div class="card">
  <h2>Recent Production (joined)</h2>
  <?php
    $rows = runQuery($pdo, "SELECT p.production_id, p.prod_date, p.milk_liters, p.eggs_count, p.weight_gain_kg, p.notes,
                                    a.tag_code, a.species
                             FROM production p
                             JOIN animals a ON a.animal_id = p.animal_id
                             ORDER BY p.prod_date DESC, p.production_id DESC
                             LIMIT 100")->fetchAll();
    renderTable($rows, "Production entries");
  ?>
</div>
<?php page_footer(); ?>
