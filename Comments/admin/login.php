<?php

session_start();

require __DIR__ . 'config/database.php';
require __DIR__ . 'classes/RateLimiter.php';
require __DIR__ . 'classes/Totp.php';
require __DIR__ . 'classes/AdminAuth.php';

header('Content-Type: text/html; charset=utf-8');

$pdo = Database::getConnection();
$limiter = new RateLimiter($pdo);

// Identify user by IP address for rate limiting
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ipHash = hash('sha256', $ip);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
  $totp     = isset($_POST['totp']) ? trim($_POST['totp']) : '';

  // Limit login attempts (e.g., max x attempts per x minutes)
  if (!$limiter->hit('a:login:' . $ipHash, 1, 1)) {
    $error = 'Too many login attempts. Try again later.';
  } else {
    // Look up the admin user by username
    $stmt = $pdo->prepare("
      query
    ");
    $stmt->execute([$username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Phase 1: Verify the password against the stored hash
    if (!$row || !password_verify($password, $row[''])) {
      $error = 'Invalid username or password.';
      unset($_SESSION['redacted']);
    } else {
      $totpEnabled = ((int)$row[''] === 1);

      // Phase 2: Check Two-Factor Authentication if enabled
      if ($totpEnabled) {
        // Verify the 6-digit TOTP code against the stored secret
        if ($totp === '' || empty($row['']) || !Totp::verify($row[''], $totp)) {
          $error = 'Invalid code.';
          unset($_SESSION['redacted']);
        } else {
          // Success: Both password and TOTP are valid
          AdminAuth::login((int)$row['id'], true);
          header('Location: index.php');
          exit;
        }
      } else {
        // Success: Password valid and 2FA is not required
        AdminAuth::login((int)$row['id'], false);
        header('Location: index.php');
        exit;
      }
    }
  }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Login</title>
    <style>
      html,body{margin:0;padding:0;height:100%;background:#0f1115;color:#e6e6eb;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;}
      body{display:flex;align-items:center;justify-content:center;}
      .login-box{width:340px;background:#151822;border-radius:8px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.4);}
      h2{margin:0 0 16px;font-weight:600;color:#f2f2f7;text-align:center;}
      label{font-size:13px;color:#9aa3b2;}
      input{width:100%;margin-top:4px;margin-bottom:14px;padding:8px 10px;border-radius:6px;border:1px solid #2a2f44;background:#0f1115;color:#e6e6eb;font-size:14px;}
      input:focus{outline:none;border-color:#3a4170;}
      button{width:100%;padding:10px;border-radius:6px;border:1px solid #3a4170;background:#2b3150;color:#e6e6eb;font-size:14px;cursor:pointer;}
      button:hover{background:#3a4170;}
      .error{margin-bottom:12px;color:#ffd7dc;background:#402027;border:1px solid #70323f;border-radius:6px;padding:8px;font-size:13px;text-align:center;}
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Login</h2>

        <?php if ($error !== ''): ?>
            <div class="error"><?=htmlspecialchars($error, ENT_QUOTES, 'UTF-8')?></div>
        <?php endif; ?>

        <form method="post" action="login.php" autocomplete="off">
            <label>Username</label>
            <input name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Code</label>
            <input name="totp" inputmode="numeric" autocomplete="one-time-code">

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>