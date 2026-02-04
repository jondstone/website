<?php

/**
 * Endpoint: processing "like" requests on comments.
 */

require __DIR__ . 'config/database.php';
require __DIR__ . 'config/csrf.php';
require __DIR__ . 'classes/LikeRepository.php';
require __DIR__ . 'classes/RateLimiter.php';

// POST only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error'=>'method_not_allowed']);
  exit;
}

// CSRF
if (!Csrf::verify($_POST['csrf'] ?? null)) {
  http_response_code(403);
  echo json_encode(['error'=>'csrf']);
  exit;
}

// Input
$commentId = (int)($_POST[''] ?? 0);
if ($commentId <= 0) {
  http_response_code(400);
  echo json_encode(['error'=>'invalid_comment']);
  exit;
}

// Client identity via cookie
if (empty($_COOKIE[''])) {
  setcookie('', bin2hex(random_bytes(16)), time()+31536000, '/', '', false, true);
}
$clientId = $_COOKIE[''] ?? '';
$clientHash = hash('sha256', $clientId);

// Rate limit 
$pdo = Database::getConnection();
$limiter = new RateLimiter($pdo);
$repo = new LikeRepository($pdo);

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ipHash = hash('sha256', $ip);

$okIp = $limiter->hit('' . $ipHash, 0, 0);
$okCid = $limiter->hit('' . $ipHash . ':' . $commentId, 0, 0);

if (!$okIp || !$okCid) {
  http_response_code(429);
  echo json_encode([
    'error' => 'rate_limit',
    'message' => 'You are liking too quickly. Please wait a minute and try again.'
  ]);
  exit;
}

// Attempt to record the like
$added = $repo->addLike($commentId, $clientHash);

echo json_encode([
  'ok' => true,
  'added' => $added
]);