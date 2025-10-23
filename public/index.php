<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Agro Management System</title>
  <style>
    body{font-family:system-ui,Arial;padding:24px;background:#0f172a;color:#e2e8f0}
    a.btn{display:inline-block;margin:10px 0;padding:10px 16px;background:#22c55e;color:#0f172a;
         text-decoration:none;border-radius:8px;font-weight:700}
    code{background:#111827;padding:2px 6px;border-radius:4px}
  </style>
</head>
<body>
  <h1>Agro Management System</h1>
  <p>DB: <code>agro_mgmt</code>. Start by running the schema in phpMyAdmin or MySQL client.</p>
  <ol>
    <li>Import <code>schema/schema.sql</code> into MySQL.</li>
    <li>Update DB creds in <code>config/db.php</code> if needed.</li>
    <li>Open the demo queries page:</li>
  </ol>
  <p><a class="btn" href="demo_queries.php">Open Demo & SQL Examples</a></p>
</body>
</html>
