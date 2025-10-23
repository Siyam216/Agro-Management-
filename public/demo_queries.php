<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Agro Demo</title>
<style>
body{font-family:system-ui,Arial;padding:20px;background:#0f172a;color:#e2e8f0}
h2{margin-top:28px}
section{border:1px solid #1f2937;border-radius:12px;padding:16px;margin:16px 0;background:#111827}
input,select{padding:6px;border-radius:6px;border:1px solid #374151;background:#0b1220;color:#e2e8f0}
button{padding:8px 12px;border-radius:8px;background:#22c55e;color:#0f172a;border:0;font-weight:700;cursor:pointer}
a{color:#93c5fd}
table{border-color:#334155;background:#0b1220;color:#e2e8f0}
th{background:#0f172a}
code{background:#0b1220;padding:2px 4px;border-radius:4px}
small{color:#94a3b8}
hr{border:0;border-top:1px solid #334155}
</style>
</head><body>";

echo "<h1>Agro Management System — Demo & SQL Examples</h1>";
echo "<p><a href='index.php'>&larr; Back</a></p>";

/* ------------------------------ DATA ENTRY / UPDATE / DELETE ------------------------------ */
echo "<section><h2>Data Entry (INSERT), Update, Delete</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // INSERT: farmers
  if (isset($_POST['add_farmer'])) {
    $name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $addr = trim($_POST['address'] ?? '');
    if ($name !== '') {
      runQuery($pdo, "INSERT INTO farmers (full_name, phone, address) VALUES (?, ?, ?)",
               [$name, $phone, $addr]);
      echo "<p>✅ Farmer added.</p>";
    }
  }

  // INSERT: animals
  if (isset($_POST['add_animal'])) {
    $farmer_id = (int)($_POST['farmer_id'] ?? 0);
    $tag = trim($_POST['tag_code'] ?? '');
    $species = $_POST['species'] ?? 'Cattle';
    $breed = trim($_POST['breed'] ?? '');
    $sex = $_POST['sex'] ?? 'F';
    $dob = $_POST['date_of_birth'] ?? null;
    if ($farmer_id && $tag !== '') {
      runQuery($pdo, "INSERT INTO animals (farmer_id, tag_code, species, breed, sex, date_of_birth)
                      VALUES (?,?,?,?,?,?)",
               [$farmer_id, $tag, $species, $breed, $sex, $dob?:null]);
      echo "<p>✅ Animal added.</p>";
    }
  }

  // UPDATE: farmer phone
  if (isset($_POST['update_farmer_phone'])) {
    $fid = (int)($_POST['u_farmer_id'] ?? 0);
    $newPhone = trim($_POST['u_phone'] ?? '');
    if ($fid && $newPhone !== '') {
      runQuery($pdo, "UPDATE farmers SET phone = ? WHERE farmer_id = ?", [$newPhone, $fid]);
      echo "<p>✏️ Farmer phone updated.</p>";
    }
  }

  // DELETE: farmer (cascade)
  if (isset($_POST['delete_farmer'])) {
    $fid = (int)($_POST['d_farmer_id'] ?? 0);
    if ($fid) {
      runQuery($pdo, "DELETE FROM farmers WHERE farmer_id = ?", [$fid]);
      echo "<p>🗑️ Farmer deleted (animals cascade due to FK).</p>";
    }
  }
}

/* forms */
$farmerRows = runQuery($pdo, "SELECT farmer_id, full_name FROM farmers ORDER BY full_name")->fetchAll();

echo "<form method='post' style='margin-bottom:12px'>
  <h3>Add Farmer</h3>
  <input name='full_name' placeholder='Full name' required />
  <input name='phone' placeholder='Phone' />
  <input name='address' placeholder='Address' />
  <button name='add_farmer'>Add Farmer</button>
</form>";

echo "<form method='post' style='margin-bottom:12px'>
  <h3>Add Animal</h3>
  <select name='farmer_id' required>
    <option value=''>-- Select farmer --</option>";
foreach ($farmerRows as $f) {
  echo "<option value='{$f['farmer_id']}'>[{$f['farmer_id']}] {$f['full_name']}</option>";
}
echo "</select>
  <input name='tag_code' placeholder='Tag code' required />
  <select name='species'>
    <option>Cattle</option><option>Goat</option><option>Sheep</option>
    <option>Poultry</option><option>Duck</option>
  </select>
  <input name='breed' placeholder='Breed' />
  <select name='sex'><option>F</option><option>M</option></select>
  <input type='date' name='date_of_birth' />
  <button name='add_animal'>Add Animal</button>
</form>";

echo "<form method='post' style='margin-bottom:12px'>
  <h3>Update Farmer Phone</h3>
  <input type='number' name='u_farmer_id' placeholder='Farmer ID' required />
  <input name='u_phone' placeholder='New phone' required />
  <button name='update_farmer_phone'>Update</button>
</form>";

echo "<form method='post'>
  <h3>Delete Farmer</h3>
  <input type='number' name='d_farmer_id' placeholder='Farmer ID' required />
  <button name='delete_farmer' style='background:#ef4444;color:white'>Delete</button>
  <br><small>Note: animals of this farmer will be deleted due to ON DELETE CASCADE.</small>
</form>";
echo "</section>";

/* ------------------------------ SELECT + ORDER BY + WHERE (LIKE) ------------------------------ */
echo "<section><h2>SELECT + WHERE + ORDER BY + LIKE (pattern matching)</h2>";
$sql = "SELECT farmer_id, full_name, phone, address
        FROM farmers
        WHERE full_name LIKE ?
        ORDER BY full_name ASC";
$rows = runQuery($pdo, $sql, ['%a%'])->fetchAll();
renderTable($rows, "Farmers with letter 'a' in name, ordered A→Z");
echo "<small>SQL: <code>$sql</code> with param <code>%a%</code></small>";
echo "</section>";

/* ------------------------------ INNER JOIN (equi-join) ------------------------------ */
echo "<section><h2>INNER JOIN (Equi-Join)</h2>";
$sql = "SELECT a.animal_id, a.tag_code, a.species, f.full_name AS farmer
        FROM animals a
        INNER JOIN farmers f ON a.farmer_id = f.farmer_id  -- equi-join
        ORDER BY a.animal_id";
renderTable(runQuery($pdo, $sql)->fetchAll(), "Animals with their farmer (inner/equi-join)");
echo "<small>SQL: <code>$sql</code></small>";
echo "</section>";

/* ------------------------------ LEFT/RIGHT OUTER JOIN ------------------------------ */
echo "<section><h2>LEFT / RIGHT OUTER JOIN</h2>";
$sqlL = "SELECT f.farmer_id, f.full_name, a.animal_id, a.tag_code
         FROM farmers f
         LEFT JOIN animals a ON a.farmer_id = f.farmer_id
         ORDER BY f.farmer_id, a.animal_id";
renderTable(runQuery($pdo, $sqlL)->fetchAll(), "LEFT OUTER JOIN: all farmers + their animals (if any)");
echo "<small>SQL: <code>$sqlL</code></small><hr>";

$sqlR = "SELECT a.animal_id, a.tag_code, f.farmer_id, f.full_name
         FROM animals a
         RIGHT JOIN farmers f ON a.farmer_id = f.farmer_id
         ORDER BY f.farmer_id, a.animal_id";
renderTable(runQuery($pdo, $sqlR)->fetchAll(), "RIGHT OUTER JOIN: all farmers + animals");
echo "<small>SQL: <code>$sqlR</code></small>";
echo "</section>";

/* ------------------------------ CROSS JOIN ------------------------------ */
echo "<section><h2>CROSS JOIN (Cartesian)</h2>";
$sql = "SELECT f.feed_name, a.species
        FROM feed f
        CROSS JOIN (SELECT DISTINCT species FROM animals) a
        ORDER BY f.feed_name, a.species";
renderTable(runQuery($pdo, $sql)->fetchAll(), "Every feed × every species (planning combinations)");
echo "<small>SQL: <code>$sql</code></small>";
echo "</section>";

/* ------------------------------ SELF JOIN ------------------------------ */
echo "<section><h2>SELF JOIN</h2>";
$sql = "SELECT a1.animal_id AS a_id, a1.tag_code AS a_tag,
               a2.animal_id AS b_id, a2.tag_code AS b_tag, a1.species
        FROM animals a1
        JOIN animals a2 ON a1.species = a2.species AND a1.animal_id <> a2.animal_id
        ORDER BY a1.species, a1.animal_id, a2.animal_id";
renderTable(runQuery($pdo, $sql)->fetchAll(), "Pairs of animals with the same species (self-join)");
echo "<small>SQL: <code>$sql</code></small>";
echo "</section>";

/* ------------------------------ AGGREGATES + GROUP BY + HAVING ------------------------------ */
echo "<section><h2>Aggregates + GROUP BY + HAVING</h2>";
$sql = "SELECT a.species,
               COUNT(*) AS total_animals,
               AVG(TIMESTAMPDIFF(DAY, a.date_of_birth, CURDATE())) AS avg_age_days
        FROM animals a
        GROUP BY a.species
        HAVING COUNT(*) >= 1
        ORDER BY total_animals DESC";
renderTable(runQuery($pdo, $sql)->fetchAll(), "Animal counts & avg age by species (groups with ≥1)");
echo "<small>SQL: <code>$sql</code></small><hr>";

$sql2 = "SELECT DATE(p.prod_date) AS prod_date,
                SUM(p.milk_liters) AS sum_milk,
                SUM(p.eggs_count) AS sum_eggs
         FROM production p
         GROUP BY DATE(p.prod_date)
         ORDER BY prod_date DESC";
renderTable(runQuery($pdo, $sql2)->fetchAll(), "Daily total milk & eggs");
echo "<small>SQL: <code>$sql2</code></small>";
echo "</section>";

/* ------------------------------ SUBQUERIES (IN / EXISTS / Scalar) ------------------------------ */
echo "<section><h2>Subqueries (IN / EXISTS / Scalar)</h2>";
$sql = "SELECT f.farmer_id, f.full_name
        FROM farmers f
        WHERE f.farmer_id IN (SELECT DISTINCT a.farmer_id FROM animals a)
        ORDER BY f.farmer_id";
renderTable(runQuery($pdo, $sql)->fetchAll(), "Farmers who own animals (IN)");
echo "<small>SQL: <code>$sql</code></small><hr>";

$sql2 = "SELECT f.farmer_id, f.full_name
         FROM farmers f
         WHERE EXISTS (SELECT 1 FROM animals a WHERE a.farmer_id = f.farmer_id AND a.species='Cattle')
         ORDER BY f.farmer_id";
renderTable(runQuery($pdo, $sql2)->fetchAll(), "Farmers who own cattle (EXISTS)");
echo "<small>SQL: <code>$sql2</code></small><hr>";

$sql3 = "SELECT a.animal_id, a.tag_code,
                (SELECT SUM(p.milk_liters) FROM production p WHERE p.animal_id = a.animal_id) AS total_milk
         FROM animals a
         ORDER BY total_milk DESC";
renderTable(runQuery($pdo, $sql3)->fetchAll(), "Total milk per animal (scalar subquery)");
echo "<small>SQL: <code>$sql3</code></small>";
echo "</section>";

/* ------------------------------ SET OPERATIONS ------------------------------ */
/* MySQL supports UNION. INTERSECT/EXCEPT emulated. */
echo "<section><h2>Set Operations (UNION, INTERSECT*, EXCEPT*)</h2>";

$sql_union = "
SELECT DISTINCT animal_id FROM production WHERE milk_liters > 0
UNION
SELECT DISTINCT animal_id FROM production WHERE eggs_count > 0
";
renderTable(runQuery($pdo, $sql_union)->fetchAll(), "UNION: animals that produced milk OR eggs");
echo "<small>SQL: <code>milk>0 UNION eggs>0</code></small><hr>";

$sql_intersect_like = "
SELECT DISTINCT m.animal_id
FROM (SELECT animal_id FROM production WHERE milk_liters > 0) m
INNER JOIN (SELECT animal_id FROM production WHERE eggs_count > 0) e
ON m.animal_id = e.animal_id
";
renderTable(runQuery($pdo, $sql_intersect_like)->fetchAll(), "INTERSECT*: animals that produced BOTH milk AND eggs");
echo "<small>SQL: <code>INNER JOIN subqueries</code></small><hr>";

$sql_except_like = "
SELECT DISTINCT m.animal_id
FROM (SELECT animal_id FROM production WHERE milk_liters > 0) m
LEFT JOIN (SELECT animal_id FROM production WHERE eggs_count > 0) e
ON m.animal_id = e.animal_id
WHERE e.animal_id IS NULL
";
renderTable(runQuery($pdo, $sql_except_like)->fetchAll(), "EXCEPT*: milk producers that did NOT produce eggs");
echo "<small>SQL: <code>LEFT JOIN ... WHERE right IS NULL</code></small>";
echo "</section>";

/* ------------------------------ VIEW ------------------------------ */
echo "<section><h2>View (CREATE VIEW + SELECT)</h2>";
try {
  runQuery($pdo, "DROP VIEW IF EXISTS v_animal_overview");
  runQuery($pdo, "
  CREATE VIEW v_animal_overview AS
  SELECT a.animal_id, a.tag_code, a.species, a.breed, a.sex, a.status,
         f.farmer_id, f.full_name AS farmer_name, f.phone,
         (SELECT SUM(p.milk_liters) FROM production p WHERE p.animal_id=a.animal_id) AS total_milk,
         (SELECT SUM(p.eggs_count)  FROM production p WHERE p.animal_id=a.animal_id) AS total_eggs
  FROM animals a
  JOIN farmers f ON f.farmer_id = a.farmer_id
  ");
  $rows = runQuery($pdo, "SELECT * FROM v_animal_overview ORDER BY animal_id")->fetchAll();
  renderTable($rows, "v_animal_overview");
  echo "<small>SQL View created and selected.</small>";
} catch (Exception $e) {
  echo "<p>Error creating view: ".htmlspecialchars($e->getMessage())."</p>";
}
echo "</section>";

echo "</body></html>";
