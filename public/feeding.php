<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Feeding');

$animals = runQuery($pdo, "SELECT animal_id, tag_code FROM animals ORDER BY animal_id DESC")->fetchAll();

// Feed master: add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '')==='add_feed') {
  runQuery($pdo, "INSERT INTO feed (feed_name, feed_type, unit, energy_kcal_per_kg) VALUES (?,?,?,?)", [
    trim($_POST['feed_name'] ?? ''),
    $_POST['feed_type'] ?? 'Concentrate',
    trim($_POST['unit'] ?? 'kg'),
    (float)($_POST['energy_kcal_per_kg'] ?? 0)
  ]);
  echo "<p>✅ Feed added.</p>";
}

// Schedule: add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '')==='add_sched') {
  runQuery($pdo, "INSERT INTO feeding_schedule (animal_id, feed_id, amount, feed_time, notes) VALUES (?,?,?,?,?)", [
    (int)($_POST['animal_id'] ?? 0),
    (int)($_POST['feed_id'] ?? 0),
    (float)($_POST['amount'] ?? 0),
    $_POST['feed_time'] ?? '08:00:00',
    trim($_POST['notes'] ?? '')
  ]);
  echo "<p>✅ Feeding schedule added.</p>";
}

$feeds = runQuery($pdo, "SELECT feed_id, feed_name FROM feed ORDER BY feed_name")->fetchAll();
?>

<div class="row">
  <div class="card col">
    <h2>Add Feed</h2>
    <form method="post">
      <input type="hidden" name="action" value="add_feed">
      <div><label>Name</label><br><input name="feed_name" required></div>
      <div><label>Type</label><br>
        <select name="feed_type">
          <option>Concentrate</option><option>Roughage</option><option>Mineral</option><option>Vitamin</option><option>Mixed</option>
        </select>
      </div>
      <div><label>Unit</label><br><input name="unit" value="kg"></div>
      <div><label>Energy (kcal/kg)</label><br><input type="number" step="0.01" name="energy_kcal_per_kg" value="0"></div>
      <button>Add Feed</button>
    </form>
  </div>

  <div class="card col">
    <h2>Add Feeding Schedule</h2>
    <form method="post">
      <input type="hidden" name="action" value="add_sched">
      <div><label>Animal</label><br>
        <select name="animal_id" required>
          <option value="">--select--</option>
          <?php foreach($animals as $a): ?>
            <option value="<?= h($a['animal_id']) ?>">[<?= h($a['animal_id']) ?>] <?= h($a['tag_code']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>Feed</label><br>
        <select name="feed_id" required>
          <option value="">--select--</option>
          <?php foreach($feeds as $f): ?>
            <option value="<?= h($f['feed_id']) ?>"><?= h($f['feed_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div><label>Amount</label><br><input type="number" step="0.01" name="amount" required></div>
      <div><label>Time</label><br><input type="time" name="feed_time" value="08:00"></div>
      <div><label>Notes</label><br><input name="notes"></div>
      <button>Add Schedule</button>
    </form>
  </div>
</div>

<div class="card">
  <h2>Feeds</h2>
  <?php
    $feedRows = runQuery($pdo, "SELECT feed_id, feed_name, feed_type, unit, energy_kcal_per_kg FROM feed ORDER BY feed_name")->fetchAll();
    renderTable($feedRows, "Feed master");
  ?>
</div>

<div class="card">
  <h2>Feeding Schedules</h2>
  <?php
    $rows = runQuery($pdo, "SELECT s.schedule_id, a.tag_code, f.feed_name, s.amount, s.feed_time, s.notes
                             FROM feeding_schedule s
                             JOIN animals a ON a.animal_id = s.animal_id
                             JOIN feed f ON f.feed_id = s.feed_id
                             ORDER BY s.schedule_id DESC")->fetchAll();
    renderTable($rows, "Schedules");
  ?>
</div>
<?php page_footer(); ?>
