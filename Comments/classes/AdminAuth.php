<?php

/**
 * Handles administrative authentication and session management.
 */
class AdminAuth {
  private const SESSION_USER = 'redacted';
  private const SESSION_2FA  = 'redacted';

  /**
   * Validates the current session and enforces 2FA if enabled.
   *
   * @param PDO $pdo
   * @return void
   */
  public static function requireLogin(PDO $pdo): void {
      if (empty($_SESSION[self::SESSION_USER])) {
          header('Location: login.php');
          exit;
      }

      $stmt = $pdo->prepare("query");
      $stmt->execute([$_SESSION[self::SESSION_USER]]);
      $enabled = (int)$stmt->fetchColumn();

      if ($enabled === 1 && empty($_SESSION[self::SESSION_2FA])) {
          self::logout();
          header('Location: login.php');
          exit;
      }
  }

  /**
   * Sets session variables for a successful login.
   *
   * @param int $adminUserId
   * @param bool $totpOk
   * @return void
   */
  public static function login(int $adminUserId, bool $totpOk): void {
    session_regenerate_id(true);
    $_SESSION[self::SESSION_USER] = $adminUserId;
    $_SESSION[self::SESSION_2FA]  = $totpOk;
  }

  /**
   * Clears the session and expires the session cookie.
   *
   * @return void
   */
  public static function logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
    }
    session_destroy();
  }
}