<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/_layout.php';
page_header('Routines (Stored Procedures)');

// For selects returned by procedures
function renderProc($pdo, $call, $params=[]) {
  $stmt = $pdo->prepare($call);
  $stmt->execute($params);
  renderTable($stmt->fetchAll(), "Result of: $call");
}

// Handle transfers
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='transfer') {
  try {
    runQuery($pdo, "CALL sp_transfer_animal(?,?,?)", [
      (int)$_POST['animal_id'],
      (int)$_POST['from_farmer'],
      (int)$_POST['to_farmer'],
    ]);
    echo "<p>✅ Transfer successful.</p>";
  } catch (Exception $e) {
    echo "<p class='muted'>⚠️ ".h($e->getMessage())."</p>";
  }
}

// Handle daily totals
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='totals') {
  $from = $_POST['from'] ?: date('Y-m-d');
  $to   = $_POST['to']   ?: date('Y-m-d');
  echo "<div class='card'><h3>Daily Totals ($from → $to)</h3>";
  renderProc($pdo, "CALL sp_daily_totals(?,?)", [$from,$to]);
  echo "</div>";
}

// Select options
$farmers = runQuery($pdo, "SELECT farmer_id, full_name FROM farmers ORDER BY full_name")->fetchAll();
$animals = runQuery($pdo, "SELECT animal_id, tag_code, farmer_id FROM animals ORDER BY animal_id DESC")->fetchAll();
?>
<div class="row">
  <div class="card col">
    <h2>Transfer Animal (CALL sp_transfer_animal)</h2>
    <form method="post">
      <input type="hidden" name="action" value="transfer">
      <div>
        <label>Animal</label><br>
        <select name="animal_id" required>
          <option value="">--select--</option>
          <?php foreach($animals as $a): ?>
            <option value="<?= h($a['animal_id']) ?>">[<?= h($a['animal_id']) ?>] <?= h($a['tag_code']) ?> (owner: <?= h($a['farmer_id']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>From Farmer</label><br>
        <select name="from_farmer" required>
          <option value="">--select--</option>
          <?php foreach($farmers as $f): ?>
            <option value="<?= h($f['farmer_id']) ?>">[<?= h($f['farmer_id']) ?>] <?= h($f['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label>To Farmer</label><br>
        <select name="to_farmer" required>
          <option value="">--select--</option>
          <?php foreach($farmers as $f): ?>
            <option value="<?= h($f['farmer_id']) ?>">[<?= h($f['farmer_id']) ?>] <?= h($f['full_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button>Transfer</button>
    </form>
    <p class="muted">Validations run inside the procedure; errors will show here.</p>
  </div>

  <div class="card col">
    <h2>Daily Totals (CALL sp_daily_totals)</h2>
    <form method="post">
      <input type="hidden" name="action" value="totals">
      <label>From</label><br><input type="date" name="from" value="<?= h(date('Y-m-d', strtotime('-7 days'))) ?>"><br>
      <label>To</label><br><input type="date" name="to" value="<?= h(date('Y-m-d')) ?>"><br>
      <button>Get Totals</button>
    </form>
  </div>
</div>

<div class="card">
  <h2>Function demo (fn_animal_age_days)</h2>
  <form>
    <label>Animal ID</label>
    <input name="aid" value="<?= h($_GET['aid'] ?? 1) ?>">
    <button>Check</button>
  </form>
  <?php
    if (isset($_GET['aid'])) {
      $stmt = runQuery($pdo, "SELECT fn_animal_age_days(?) AS age_days", [(int)$_GET['aid']]);
      renderTable($stmt->fetchAll(), "Age in days");
    }
  ?>
</div>
<?php page_footer(); ?>
