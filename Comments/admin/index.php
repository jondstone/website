<?php
session_start();

require __DIR__ . 'config/database.php';
require __DIR__ . 'classes/AdminAuth.php';

// Initialize database connection and verify admin session
$pdo = Database::getConnection();
AdminAuth::requireLogin($pdo);

// Generate a CSRF token if one doesn't exist to secure actions
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all comments ordered by page and creation date
$stmt = $pdo->query("
  query
");

$all = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Data structures for building the threaded (tree) view
$byId = [];     // Fast lookup by ID
$children = []; // Maps parent IDs to arrays of child IDs
$roots = [];    // IDs of top-level comments

foreach ($all as $row) {
  $id = (int)$row[''];
  $pid = $row[''] === null ? null : (int)$row[''];
  
  $byId[$id] = $row;
  
  // Initialize children array for this ID if not already set
  if (!isset($children[$id])) $children[$id] = [];
  
  if ($pid === null) {
    $roots[] = $id;
  } else {
    // Group children under their respective parent IDs
    if (!isset($children[$pid])) $children[$pid] = [];
    $children[$pid][] = $id;
  }
}

/**
 * Recursively renders table rows for comments to create a threaded UI
 *
 * @param array  $ids      The IDs of comments to render at the current level
 * @param array  $byId     The flat array of comment data
 * @param array  $children The mapping of parent IDs to child IDs
 * @param string $csrf     The CSRF token for the delete forms
 * @param int    $depth    Current recursion depth (used for indentation)
 */
function renderRows(array $ids, array $byId, array $children, string $csrf, int $depth = 0): void {
  foreach ($ids as $id) {
    $c = $byId[$id] ?? null;
    if (!$c) continue;

    // Calculate left padding to visually represent nesting levels
    $pad = $depth * 18;

    // Highlight rows marked as spam via CSS class
    $rowClass = ($c['status'] === 'spam') ? 'row-spam' : '';
    
    echo "<tr class=\"$rowClass\">";
    echo "<td class=\"col-id\">" . (int)$c[''] . "</td>";
    echo "<td class=\"col-parent\">" . ($c[''] === null ? "" : (int)$c['']) . "</td>";
    echo "<td class=\"col-page\">" . htmlspecialchars($c['']) . "</td>";
    echo "<td class=\"col-author\">" . htmlspecialchars($c['']) . "</td>";
    
    // Render the comment body with a reply arrow for nested items and a length limit
    echo "<td class=\"col-msg\"><div class=\"msg\" style=\"padding-left:{$pad}px\">" .
         ($depth > 0 ? "<span class=\"reply-tag\">↳</span>" : "") .
         htmlspecialchars(mb_strimwidth($c['body_text'], 0, 260, '…', 'UTF-8')) .
         "</div></td>";
         
    echo "<td class=\"col-status\">" . htmlspecialchars((string)$c['']) . "</td>";
    echo "<td class=\"col-likes\">" . (int)$c[''] . "</td>";
    echo "<td class=\"col-date\">" . htmlspecialchars($c['']) . "</td>";
    
    // Form to handle comment deletion, including CSRF protection
    echo "<td class=\"col-delete delete-button\">
            <form method=\"post\" action=\"delete-comment.php\" onsubmit=\"return confirm('Delete this comment and all replies under it?')\">
              <input type=\"hidden\" name=\"comment_id\" value=\"" . (int)$c[''] . "\">
              <input type=\"hidden\" name=\"csrf_token\" value=\"" . htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') . "\">
              <button type=\"submit\">X</button>
            </form>
          </td>";
    echo "</tr>";

    // Recursively render any replies to the current comment
    if (!empty($children[$id])) {
      renderRows($children[$id], $byId, $children, $csrf, $depth + 1);
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Comment Admin Panel</title>
  <style>
    html,body{margin:0;padding:0;background:#0f1115;color:#e6e6eb;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;}
    body{padding:24px;}
    h1{margin:0 0 16px 0;font-weight:600;color:#f2f2f7;}
    table{width:100%;border-collapse:collapse;background:#151822;border-radius:8px;overflow:hidden;}
    th{text-align:left;font-size:13px;font-weight:600;padding:10px 12px;background:#1b2030;color:#9aa3b2;border-bottom:1px solid #2a2f44;white-space:nowrap;}
    td{padding:10px 12px;font-size:14px;vertical-align:top;border-bottom:1px solid #23283a;}
    tr:hover td{background:#1a1f2e;}
    button{background:#2b3150;color:#e6e6eb;border:1px solid #3a4170;border-radius:6px;padding:6px 10px;cursor:pointer;font-size:13px;}
    button:hover{background:#3a4170;}
    .delete-button button{background:#402027;border-color:#70323f;color:#ffd7dc;}
    .delete-button button:hover{background:#70323f;}
    .top-bar{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;}
    .logout button{background:#1e2336;border-color:#343a5c;}
    .logout button:hover{background:#343a5c;}
    .col-id{width:70px;white-space:nowrap;}
    .col-parent{width:90px;white-space:nowrap;color:#9aa3b2;}
    .col-author{width:170px;white-space:nowrap;}
    .col-status{width:90px;white-space:nowrap;}
    .col-likes{width:80px;white-space:nowrap;}
    .col-date{width:170px;white-space:nowrap;}
    .col-delete{width:70px;white-space:nowrap;}
    .msg{color:#d6d9e0;}
    .reply-tag{color:#9aa3b2;margin-right:8px;}
    .row-spam td{background:#3b1e24!important;color:#ffd7dc;}
    .row-spam:hover td{background:#4a252d!important;}
  </style>
</head>
<body>
  <div class="top-bar">
    <h1>Comments</h1>
    <form method="post" action="logout.php" class="logout">
      <button type="submit">Logout</button>
    </form>
  </div>

  <table>
    <tr>
      <th>ID</th>
      <th>Parent</th>
      <th>Page</th>
      <th>Author</th>
      <th>Message</th>
      <th>Visible</th>
      <th>Likes</th>
      <th>Date</th>
      <th>Delete</th>
    </tr>

    <?php
      // First pass: Render all comments starting from the root level
      renderRows($roots, $byId, $children, $_SESSION['csrf_token'], 0);

      // Second pass: Find "orphan" comments whose parent_id isn't in our current result set
      // This ensures comments aren't hidden if their parent was previously deleted or filtered out
      $orphans = [];
      foreach ($all as $r) {
        if ($r['parent_id'] !== null && !isset($byId[(int)$r['parent_id']])) {
          $orphans[] = (int)$r['id'];
        }
      }
      if (!empty($orphans)) {
        renderRows($orphans, $byId, $children, $_SESSION['csrf_token'], 0);
      }
    ?>
  </table>
</body>
</html>