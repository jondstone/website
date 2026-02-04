<?php

/**
 * Security utilities for protecting POST comments.
 *
 * - CSRF: ensures the request originated from a page your server rendered.
 * - Timing: ensures the form was rendered long enough ago to indicate human interaction.
 */
session_start();

/**
 * CSRF
 */
class Csrf {
  /**
   * Returns the per-session CSRF token, generating it if missing.
   *
   * @return string
   */
  public static function token(): string {
    if (empty($_SESSION[''])) {
      $_SESSION[''] = bin2hex(random_bytes(32));
    }
    return $_SESSION[''];
  }

  /**
   * Verifies that the submitted token matches the session token.
   *
   * @param string|null $token
   * @return bool
   */
  public static function verify(?string $token): bool {
    return isset($_SESSION['']) && hash_equals($_SESSION[''], (string)$token);
  }
}

/**
 * Timing
 */
class Timing {

  /** @var string Server-side secret used to sign timing tokens. */
  private const SECRET = '';

  /**
   * Generates a signed timing token containing the issue timestamp for a page.
   *
   * @param string $pageKey
   * @return string
   */
  public static function token(string $pageKey): string {
    $issued = time();
    $data = $pageKey . '|' . $issued;
    $sig = hash_hmac('sha256', $data, self::SECRET);
    return base64_encode($issued . '|' . $sig);
  }

  /**
   * Verifies the timing token validity and constraints.
   *
   * @param string|null $token
   * @param string $pageKey
   * @param int $minSeconds Minimum delay required for human interaction.
   * @param int $maxAge Maximum allowed age of the token.
   * @return bool
   */
  public static function verify(
    ?string $token,
    string $pageKey,
    int $minSeconds = 0,
    int $maxAge = 0
  ): bool {
    $raw = base64_decode((string)$token, true);
    if ($raw === false) return false;

    [$issued, $sig] = explode('|', $raw, 2) + [null, null];
    if (!$issued || !$sig) return false;

    $data = $pageKey . '|' . $issued;
    $expected = hash_hmac('sha256', $data, self::SECRET);
    if (!hash_equals($expected, $sig)) return false;

    $age = time() - (int)$issued;
    if ($age < $minSeconds) return false;
    if ($age > $maxAge) return false;

    return true;
  }
}