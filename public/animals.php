<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Animals');

// Farmers list for selects
$farmers = runQuery($pdo, "SELECT farmer_id, full_name FROM farmers ORDER BY full_name")->fetchAll();

// POST handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (($_POST['action'] ?? '') === 'add') {
    $farmer_id = (int)($_POST['farmer_id'] ?? 0);
    $tag = trim($_POST['tag_code'] ?? '');
    if ($farmer_id && $tag !== '') {
      runQuery($pdo, "INSERT INTO animals (farmer_id, tag_code, species, breed, sex, date_of_birth)
                      VALUES (?,?,?,?,?,?)", [
        $farmer_id,
        $tag,
        $_POST['species'] ?? 'Cattle',
        trim($_POST['breed'] ?? ''),
        $_POST['sex'] ?? 'F',
        $_POST['date_of_birth'] ?: null
      ]);
      echo "<p>✅ Animal added.</p>";
    }
  }

  if (($_POST['action'] ?? '') === 'update') {
    $animal_id = (int)($_POST['animal_id'] ?? 0);
    $farmer_id = (int)($_POST['farmer_id'] ?? 0);
    $tag = trim($_POST['tag_code'] ?? '');
    if ($animal_id && $farmer_id && $tag !== '') {
      runQuery($pdo, "UPDATE animals SET farmer_id=?, tag_code=?, species=?, breed=?, sex=?, date_of_birth=? WHERE animal_id=?", [
        $farmer_id,
        $tag,
        $_POST['species'] ?? 'Cattle',
        trim($_POST['breed'] ?? ''),
        $_POST['sex'] ?? 'F',
        $_POST['date_of_birth'] ?: null,
        $animal_id
      ]);
      echo "<p>✏️ Animal updated.</p>";
    }
  }

  if (($_POST['action'] ?? '') === 'delete') {
    $id = (int)($_POST['animal_id'] ?? 0);
    if ($id) {
      runQuery($pdo, "DELETE FROM animals WHERE animal_id=?", [$id]);
      echo "<p>🗑️ Animal deleted.</p>";
    }
  }
}

// edit record
$edit = null;
if (isset($_GET['edit'])) {
  $edit = runQuery($pdo, "SELECT * FROM animals WHERE animal_id=?", [(int)$_GET['edit']])->fetch();
}
?>

<div class="card">
  <h2><?= $edit ? 'Edit Animal' : 'Add Animal' ?></h2>
  <form method="post" class="row">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?>
      <input type="hidden" name="animal_id" value="<?= h($edit['animal_id']) ?>">
    <?php endif; ?>

    <div class="col">
      <label>Farmer</label><br>
      <select name="farmer_id" required>
        <option value="">--select--</option>
        <?php foreach ($farmers as $f): ?>
          <option value="<?= h($f['farmer_id']) ?>"
            <?= isset($edit['farmer_id']) && $edit['farmer_id']==$f['farmer_id'] ? 'selected' : '' ?>>
            [<?= h($f['farmer_id']) ?>] <?= h($f['full_name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col">
      <label>Tag Code</label><br>
      <input name="tag_code" required value="<?= h($edit['tag_code'] ?? '') ?>">
    </div>

    <div class="col">
      <label>Species</label><br>
      <select name="species">
        <?php
          $choices = ['Cattle','Goat','Sheep','Poultry','Duck'];
          $sel = $edit['species'] ?? 'Cattle';
          foreach ($choices as $c) {
            $s = ($sel===$c) ? 'selected' : '';
            echo "<option $s>".h($c)."</option>";
          }
        ?>
      </select>
    </div>

    <div class="col">
      <label>Breed</label><br>
      <input name="breed" value="<?= h($edit['breed'] ?? '') ?>">
    </div>

    <div class="col">
      <label>Sex</label><br>
      <select name="sex">
        <option <?= (($edit['sex'] ?? 'F')==='F'?'selected':'') ?>>F</option>
        <option <?= (($edit['sex'] ?? 'F')==='M'?'selected':'') ?>>M</option>
      </select>
    </div>

    <div class="col">
      <label>DOB</label><br>
      <input type="date" name="date_of_birth" value="<?= h($edit['date_of_birth'] ?? '') ?>">
    </div>

    <div class="col" style="align-self:end">
      <button><?= $edit ? 'Update' : 'Add' ?></button>
      <?php if ($edit): ?><a class="muted" href="animals.php">Cancel</a><?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2>Animals</h2>
  <?php
    $sql = "SELECT a.animal_id, a.tag_code, a.species, a.breed, a.sex, a.date_of_birth,
                   f.farmer_id, f.full_name AS farmer_name
            FROM animals a
            INNER JOIN farmers f ON a.farmer_id = f.farmer_id
            ORDER BY a.animal_id DESC";
    $rows = runQuery($pdo, $sql)->fetchAll();

    if ($rows) {
      echo "<table border='1' cellpadding='6' cellspacing='0'><thead><tr>
            <th>ID</th><th>Tag</th><th>Species</th><th>Breed</th><th>Sex</th><th>DOB</th>
            <th>Farmer</th><th>Actions</th></tr></thead><tbody>";
      foreach ($rows as $r) {
        echo "<tr>
          <td>".h($r['animal_id'])."</td>
          <td>".h($r['tag_code'])."</td>
          <td>".h($r['species'])."</td>
          <td>".h($r['breed'])."</td>
          <td>".h($r['sex'])."</td>
          <td>".h($r['date_of_birth'])."</td>
          <td><a href='relations.php?farmer_id=".h($r['farmer_id'])."'>".h($r['farmer_name'])."</a></td>
          <td>
            <a href='animals.php?edit=".h($r['animal_id'])."'>Edit</a>
            <form method='post' style='display:inline' onsubmit='return confirm(\"Delete animal?\")'>
              <input type='hidden' name='action' value='delete'>
              <input type='hidden' name='animal_id' value='".h($r['animal_id'])."'>
              <button class='danger'>Delete</button>
            </form>
          </td>
        </tr>";
      }
      echo "</tbody></table>";
    } else {
      echo "<p><em>No animals yet.</em></p>";
    }
  ?>
</div>

<?php page_footer(); ?>
