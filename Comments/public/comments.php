<?php

/**
 * Endpoint: returns comments for a given page_key as JSON.
 */

require __DIR__ . 'config/database.php';           
require __DIR__ . 'classes/CommentRepository.php'; 

// Read page_key from query string
$page_key = isset($_GET['page_key']) ? trim($_GET['page_key']) : '';

// Validate page_key (required, max length)
if ($page_key === '' || mb_strlen($page_key, 'UTF-8') > 255) {
  http_response_code(400);                   // Bad Request
  echo json_encode(['error' => 'invalid page_key']);
  exit;
}

// Get shared DB connection (utf8mb4 configured in Database.php)
$pdo = Database::getConnection();

// Create repository for comment queries
$repo = new CommentRepository($pdo);

// Output JSON response
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode([
  'page_key' => $page_key,
  'comments' => $repo->getCommentsByPage($page_key)
], JSON_UNESCAPED_UNICODE);