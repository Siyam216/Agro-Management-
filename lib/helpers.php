<?php
// lib/helpers.php
function runQuery(PDO $pdo, string $sql, array $params = []) {
  // For learning: display the SQL and bound values
  echo "<pre style='background:#111827;color:#93c5fd;padding:6px;border-radius:8px'>";
  echo "SQL: " . htmlspecialchars($sql) . "\n";
  if ($params) {
    echo "Params: " . htmlspecialchars(json_encode($params, JSON_PRETTY_PRINT)) . "\n";
  }
  echo "</pre>";

  // Execute normally
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt;
}


function renderTable(array $rows, string $title = '') {
  echo "<div style='margin:12px 0'>";
  if ($title) echo "<h3>$title</h3>";
  if (!$rows) { echo "<p><em>No rows.</em></p></div>"; return; }
  echo "<div style='overflow:auto'><table border='1' cellpadding='6' cellspacing='0'>";
  echo "<thead><tr>";
  foreach (array_keys($rows[0]) as $c) echo "<th>".htmlspecialchars($c)."</th>";
  echo "</tr></thead><tbody>";
  foreach ($rows as $r) {
    echo "<tr>";
    foreach ($r as $v) echo "<td>".htmlspecialchars((string)$v)."</td>";
    echo "</tr>";
  }
  echo "</tbody></table></div></div>";
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
