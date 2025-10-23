<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../lib/helpers.php';
require_once __DIR__.'/_layout.php';
page_header('Analytics');

/* 1) WINDOW FUNCTIONS: rolling 7-day milk average & rank by day */
echo "<div class='card'><h2>Rolling 7-day Milk Average & Ranks (window functions)</h2>";
$sql = "SELECT tag_code, species, prod_date, milk_liters, milk_7d_sum, milk_7d_avg, rank_by_day
        FROM v_production_stats
        ORDER BY prod_date DESC, rank_by_day
        LIMIT 50";
$rows = runQuery($pdo, $sql)->fetchAll();
renderTable($rows, "From view v_production_stats");
echo "</div>";

/* 2) WITH ROLLUP: totals per farmer, per species, and grand total */
echo "<div class='card'><h2>Totals per Farmer & Species (GROUP BY with ROLLUP)</h2>";
$sql = "SELECT
          IFNULL(f.full_name, 'ALL FARMERS') AS farmer,
          IFNULL(a.species, 'ALL SPECIES')  AS species,
          SUM(p.milk_liters) AS total_milk,
          SUM(p.eggs_count)  AS total_eggs
        FROM production p
        JOIN animals a ON a.animal_id = p.animal_id
        JOIN farmers f ON f.farmer_id = a.farmer_id
        GROUP BY f.full_name, a.species WITH ROLLUP";
renderTable(runQuery($pdo,$sql)->fetchAll(), "Rollup includes subtotals and grand total");
echo "</div>";

/* 3) RECURSIVE CTE: maternal lineage (up to 5 generations) */
echo "<div class='card'><h2>Maternal Lineage (Recursive CTE)</h2>";
$animal_id = (int)($_GET['animal_id'] ?? 2); // demo
echo "<form><label>Animal ID: </label><input name='animal_id' value='".h($animal_id)."'><button>Show</button></form>";
$sql = "
WITH RECURSIVE lineage AS (
  SELECT a.animal_id, a.tag_code, a.mother_id, 0 AS gen
  FROM animals a WHERE a.animal_id = ?
  UNION ALL
  SELECT m.animal_id, m.tag_code, m.mother_id, gen+1
  FROM animals m
  JOIN lineage l ON m.animal_id = l.mother_id
  WHERE gen < 5
)
SELECT * FROM lineage ORDER BY gen;
";
renderTable(runQuery($pdo,$sql,[$animal_id])->fetchAll(), "Child → mother → grandmother chain (max 5 gens)");
echo "</div>";

/* 4) CORRELATED SUBQUERY + WINDOW tie-break: top 10 animals by lifetime milk */
echo "<div class='card'><h2>Top 10 Animals by Lifetime Milk (subquery + window)</h2>";
$sql = "
SELECT tag_code, species, total_milk, RANK() OVER (ORDER BY total_milk DESC) AS rnk
FROM (
  SELECT a.tag_code, a.species, COALESCE(SUM(p.milk_liters),0) AS total_milk
  FROM animals a
  LEFT JOIN production p ON p.animal_id = a.animal_id
  GROUP BY a.animal_id
) t
ORDER BY total_milk DESC
LIMIT 10";
renderTable(runQuery($pdo,$sql)->fetchAll());
echo "</div>";

page_footer();
