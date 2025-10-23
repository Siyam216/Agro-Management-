<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Relations: Farmer ↔ Animals');

$farmers = runQuery($pdo, "SELECT farmer_id, full_name FROM farmers ORDER BY full_name")->fetchAll();
$farmer_id = isset($_GET['farmer_id']) ? (int)$_GET['farmer_id'] : 0;

echo "<div class='card'><h2>Which farmer owns which animals</h2>";
echo "<form method='get'><label>Choose farmer: </label>
      <select name='farmer_id'><option value='0'>(All)</option>";
foreach ($farmers as $f) {
  $sel = ($farmer_id === (int)$f['farmer_id']) ? 'selected' : '';
  echo "<option $sel value='".h($f['farmer_id'])."'>".h($f['full_name'])."</option>";
}
echo "</select> <button>Show</button></form></div>";

if ($farmer_id > 0) {
  // details for one farmer
  $farmer = runQuery($pdo, "SELECT * FROM farmers WHERE farmer_id=?", [$farmer_id])->fetch();
  echo "<div class='card'><h3>Farmer Details</h3>";
  if ($farmer) {
    echo "<p><strong>".h($farmer['full_name'])."</strong><br>
          Phone: ".h($farmer['phone'])."<br>
          Address: ".h($farmer['address'])."</p>";
    $animals = runQuery($pdo, "SELECT animal_id, tag_code, species, breed, sex, date_of_birth
                               FROM animals WHERE farmer_id=? ORDER BY animal_id DESC", [$farmer_id])->fetchAll();
    renderTable($animals, "Animals owned by this farmer");
  } else {
    echo "<p><em>Farmer not found.</em></p>";
  }
  echo "</div>";
} else {
  // all mapping
  $rows = runQuery($pdo, "SELECT f.full_name AS farmer, a.animal_id, a.tag_code, a.species
                          FROM farmers f
                          LEFT JOIN animals a ON a.farmer_id = f.farmer_id
                          ORDER BY f.full_name, a.animal_id")->fetchAll();
  renderTable($rows, "All farmers & their animals");
}
page_footer();
