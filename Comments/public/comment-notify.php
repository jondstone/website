<?php

/**
 * HTTP endpoint for sending email notifications (POST only).
 * This is called separately after a comment is successfully submitted.
 */

require __DIR__ . 'config/database.php';
require __DIR__ . 'config/csrf.php';
require __DIR__ . 'classes/EmailNotifier.php';

header('Content-Type: application/json; charset=utf-8');

// HTTP METHOD VALIDATION
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'method_not_allowed'], JSON_UNESCAPED_UNICODE);
  exit;
}

// SECURITY VALIDATION
if (!Csrf::verify($_POST['csrf'] ?? null)) {
  http_response_code(403);
  echo json_encode(['error' => 'csrf'], JSON_UNESCAPED_UNICODE);
  exit;
}

// INPUT VALIDATION
$page_key    = isset($_POST['page_key']) ? trim($_POST['page_key']) : '';
$author_name = isset($_POST['author_name']) ? trim($_POST['author_name']) : '';
$body_text   = isset($_POST['body_text']) ? trim($_POST['body_text']) : '';
$comment_id  = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if ($page_key === '' || $author_name === '' || $body_text === '' || $comment_id === 0) {
  http_response_code(400);
  echo json_encode(['error' => 'invalid_input'], JSON_UNESCAPED_UNICODE);
  exit;
}

// Send notification (best effort - errors are silently ignored)
try {
  (new EmailNotifier())->notify($page_key, $author_name, $body_text, $comment_id);
  echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  // Log error but return success anyway
  echo json_encode(['ok' => true, 'note' => 'notification_failed'], JSON_UNESCAPED_UNICODE);
}