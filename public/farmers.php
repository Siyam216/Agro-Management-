<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Farmers');

// HANDLE POST: add / update / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Add
  if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['full_name'] ?? '');
    if ($name !== '') {
      runQuery($pdo, "INSERT INTO farmers (full_name, phone, address) VALUES (?,?,?)", [
        $name, trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? '')
      ]);
      echo "<p>✅ Farmer added.</p>";
    }
  }

  // Update
  if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = (int)($_POST['farmer_id'] ?? 0);
    $name = trim($_POST['full_name'] ?? '');
    if ($id && $name !== '') {
      runQuery($pdo, "UPDATE farmers SET full_name=?, phone=?, address=? WHERE farmer_id=?", [
        $name, trim($_POST['phone'] ?? ''), trim($_POST['address'] ?? ''), $id
      ]);
      echo "<p>✏️ Farmer updated.</p>";
    }
  }

  // Delete
  if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)($_POST['farmer_id'] ?? 0);
    if ($id) {
      runQuery($pdo, "DELETE FROM farmers WHERE farmer_id=?", [$id]);
      echo "<p>🗑️ Farmer deleted (animals cascade).</p>";
    }
  }
}

// If editing, fetch row
$edit = null;
if (isset($_GET['edit'])) {
  $edit = runQuery($pdo, "SELECT * FROM farmers WHERE farmer_id=?", [(int)$_GET['edit']])->fetch();
}
?>

<div class="card">
  <h2><?= $edit ? 'Edit Farmer' : 'Add Farmer' ?></h2>
  <form method="post" class="row">
    <input type="hidden" name="action" value="<?= $edit ? 'update' : 'add' ?>">
    <?php if ($edit): ?>
      <input type="hidden" name="farmer_id" value="<?= h($edit['farmer_id']) ?>">
    <?php endif; ?>
    <div class="col">
      <label>Full Name</label><br>
      <input name="full_name" required value="<?= h($edit['full_name'] ?? '') ?>">
    </div>
    <div class="col">
      <label>Phone</label><br>
      <input name="phone" value="<?= h($edit['phone'] ?? '') ?>">
    </div>
    <div class="col">
      <label>Address</label><br>
      <input name="address" value="<?= h($edit['address'] ?? '') ?>">
    </div>
    <div class="col" style="align-self:end">
      <button><?= $edit ? 'Update' : 'Add' ?></button>
      <?php if ($edit): ?>
        <a class="muted" href="farmers.php">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h2>Farmers</h2>
  <?php
    $rows = runQuery($pdo, "SELECT farmer_id, full_name, phone, address, created_at FROM farmers ORDER BY farmer_id DESC")->fetchAll();
    if ($rows) {
      echo "<table border='1' cellpadding='6' cellspacing='0'><thead><tr>
            <th>ID</th><th>Name</th><th>Phone</th><th>Address</th><th>Actions</th></tr></thead><tbody>";
      foreach ($rows as $r) {
        echo "<tr>
            <td>".h($r['farmer_id'])."</td>
            <td>".h($r['full_name'])."</td>
            <td>".h($r['phone'])."</td>
            <td>".h($r['address'])."</td>
            <td>
              <a href='farmers.php?edit=".h($r['farmer_id'])."'>Edit</a> |
              <a href='relations.php?farmer_id=".h($r['farmer_id'])."'>Details</a>
              <form method='post' style='display:inline' onsubmit='return confirm(\"Delete farmer and related animals?\")'>
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='farmer_id' value='".h($r['farmer_id'])."'>
                <button class='danger'>Delete</button>
              </form>
            </td>
          </tr>";
      }
      echo "</tbody></table>";
      echo "<p class='muted'>Tip: Click <em>Details</em> to see animals owned by the farmer.</p>";
    } else {
      echo "<p><em>No farmers yet.</em></p>";
    }
  ?>
</div>

<?php page_footer(); ?>
