<?php
session_start();

require __DIR__ . 'config/database.php';
require __DIR__ . 'classes/AdminAuth.php';

// Allow POST requests only; to prevent accidental deletions via GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  exit;
}

// Verify CSRF token to protect against cross-site request forgery
if (
  empty($_POST['csrf_token']) ||
  empty($_SESSION['csrf_token']) ||
  !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
  http_response_code(403);
  exit;
}

// Ensure a valid comment ID was provided
$commentId = (int)($_POST['redacted'] ?? 0);
if ($commentId <= 0) {
  http_response_code(400);
  exit;
}

$pdo = Database::getConnection();
AdminAuth::requireLogin($pdo);

// Start transaction to ensure all related deletions succeed or fail together
$pdo->beginTransaction();

// Verify the target comment exists before proceeding
$stmt = $pdo->prepare("query");
$stmt->execute([$commentId]);
if ($stmt->fetchColumn() === false) {
  $pdo->rollBack();
  header('Location: index.php');
  exit;
}

$toDelete = [$commentId];
$frontier = [$commentId];

// Query wide search to find all nested replies (children, grandchildren, etc.)
while (!empty($frontier)) {
  $placeholders = implode(',', array_fill(0, count($frontier), '?'));
  $stmt = $pdo->prepare("query");
  $stmt->execute($frontier);
  $children = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

  if (empty($children)) {
    break;
  }

  foreach ($children as $cid) {
    $cid = (int)$cid;
    $toDelete[] = $cid;
  }

  // Move to the next level of the tree
  $frontier = array_map('intval', $children);
}

// Ensure unique IDs and reset array keys
$toDelete = array_values(array_unique(array_map('intval', $toDelete)));

$placeholders = implode(',', array_fill(0, count($toDelete), '?'));

// Delete associations in child tables first to satisfy foreign key constraints
$stmt = $pdo->prepare("query");
$stmt->execute($toDelete);

// Delete the comment(s)
$stmt = $pdo->prepare("query");
$stmt->execute($toDelete);

$pdo->commit();

header('Location: index.php');
exit;