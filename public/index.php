<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../lib/helpers.php';
require_once __DIR__ . '/_layout.php';

page_header('Agro Management - Home');
?>
<div class="card">
  <h2>Welcome</h2>
  <p>Click an operation:</p>
  <ul>
    <li><a href="farmers.php">Manage Farmers (add / edit / delete / list)</a></li>
    <li><a href="animals.php">Manage Animals (add / edit / delete / list)</a></li>
    <li><a href="relations.php">Which farmer owns which animals</a></li>
    <li><a href="production.php">Production (milk / eggs / weight) add & view</a></li>
    <li><a href="feeding.php">Feed master & Feeding schedule</a></li>
    <li><a href="health.php">Health records</a></li>
  </ul>
  <p class="muted">DB: agro_mgmt (MySQL). All writes use prepared statements.</p>
</div>
<?php page_footer(); ?>
