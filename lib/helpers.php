<?php
function runQuery(PDO $pdo, string $sql, array $params = []) {
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  return $stmt;
}

function renderTable($rows, $caption = '') {
  if (!$rows) { echo "<p><em>No rows.</em></p>"; return; }
  echo "<div style='overflow:auto; margin:8px 0;'>";
  if ($caption) echo "<h4>$caption</h4>";
  echo "<table border='1' cellpadding='6' cellspacing='0'>";
  echo "<thead><tr>";
  foreach (array_keys($rows[0]) as $col) echo "<th>".htmlspecialchars($col)."</th>";
  echo "</tr></thead><tbody>";
  foreach ($rows as $r) {
    echo "<tr>";
    foreach ($r as $v) echo "<td>".htmlspecialchars((string)$v)."</td>";
    echo "</tr>";
  }
  echo "</tbody></table></div>";
}
