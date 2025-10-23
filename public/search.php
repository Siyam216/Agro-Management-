<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/_layout.php';
page_header('Advanced Search');

$q_name = trim($_GET['name'] ?? '');
$q_species = $_GET['species'] ?? '';
$q_hasHealth = $_GET['has_health'] ?? '';    // yes/no/blank
$q_hasEggs = $_GET['has_eggs'] ?? '';        // yes/no/blank

$speciesList = ['','Cattle','Goat','Sheep','Poultry','Duck'];
?>
<div class="card">
  <h2>Filter Animals by Complex Conditions</h2>
  <form>
    <label>Farmer name contains:</label>
    <input name="name" value="<?= h($q_name) ?>">
    <label>Species:</label>
    <select name="species">
      <?php foreach ($speciesList as $s): ?>
        <option <?= $q_species===$s?'selected':'' ?>><?= h($s) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Has any health record?</label>
    <select name="has_health">
      <option value="">(Either)</option>
      <option value="yes" <?= $q_hasHealth==='yes'?'selected':'' ?>>Yes</option>
      <option value="no"  <?= $q_hasHealth==='no'?'selected':''  ?>>No</option>
    </select>
    <label>Produced eggs ever?</label>
    <select name="has_eggs">
      <option value="">(Either)</option>
      <option value="yes" <?= $q_hasEggs==='yes'?'selected':'' ?>>Yes</option>
      <option value="no"  <?= $q_hasEggs==='no'?'selected':''  ?>>No</option>
    </select>
    <button>Search</button>
  </form>
</div>

<div class="card">
<?php
$sql = "SELECT a.animal_id, a.tag_code, a.species, f.full_name AS farmer
        FROM animals a
        JOIN farmers f ON f.farmer_id = a.farmer_id
        WHERE 1=1";
$params = [];

if ($q_name !== '') { $sql .= " AND f.full_name LIKE ?"; $params[] = "%$q_name%"; }
if ($q_species !== '') { $sql .= " AND a.species = ?"; $params[] = $q_species; }

if ($q_hasHealth === 'yes') {
  $sql .= " AND EXISTS (SELECT 1 FROM health_record h WHERE h.animal_id = a.animal_id)";
} elseif ($q_hasHealth === 'no') {
  $sql .= " AND NOT EXISTS (SELECT 1 FROM health_record h WHERE h.animal_id = a.animal_id)";
}

if ($q_hasEggs === 'yes') {
  $sql .= " AND EXISTS (SELECT 1 FROM production p WHERE p.animal_id = a.animal_id AND p.eggs_count > 0)";
} elseif ($q_hasEggs === 'no') {
  $sql .= " AND NOT EXISTS (SELECT 1 FROM production p WHERE p.animal_id = a.animal_id AND p.eggs_count > 0)";
}

$sql .= " ORDER BY f.full_name, a.animal_id DESC";
renderTable(runQuery($pdo,$sql,$params)->fetchAll(), "Results");
?>
</div>
<?php page_footer(); ?>
