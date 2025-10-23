<?php
// public/_layout.php
function page_header(string $title) {
  echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>".h($title)."</title>
  <style>
    body{font-family:system-ui,Arial;background:#0f172a;color:#e2e8f0;margin:0}
    header{background:#111827;padding:14px 20px;border-bottom:1px solid #1f2937}
    main{padding:20px;max-width:1100px;margin:0 auto}
    a, a:visited{color:#93c5fd;text-decoration:none}
    nav a{margin-right:14px}
    input,select,button,textarea{
      background:#0b1220;color:#e2e8f0;border:1px solid #374151;border-radius:8px;
      padding:8px;margin:4px 2px
    }
    button{cursor:pointer;background:#22c55e;color:#0f172a;border:0;font-weight:700}
    .card{background:#111827;border:1px solid #1f2937;border-radius:12px;padding:16px;margin:16px 0}
    .row{display:flex;gap:14px;flex-wrap:wrap}
    .row > .col{flex:1 1 320px}
    table{border-color:#334155;background:#0b1220;color:#e2e8f0}
    th{background:#0f172a}
    .danger{background:#ef4444;color:white}
    .muted{color:#94a3b8}
  </style></head><body>";
  echo "<header><nav>
    <a href='index.php'><strong>Agro Management</strong></a>
    <a href='farmers.php'>Farmers</a>
    <a href='animals.php'>Animals</a>
    <a href='relations.php'>Relations</a>
    <a href='production.php'>Production</a>
    <a href='feeding.php'>Feeding</a>
    <a href='health.php'>Health</a>
    <a href='analytics.php'>Analytics</a>
    <a href='search.php'>Advanced Search</a>
    <a href='routines.php'>Routines</a>

  </nav></header><main>";
}

function page_footer() {
  echo "</main></body></html>";
}
