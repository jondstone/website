<?php
/**
 * Utility class for generating and verifying Time-based One-Time Passwords (TOTP).
 */
class Totp {
  private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

  /**
   * Generates a random Base32 secret key.
   *
   * @param int $length
   * @return string
   */
  public static function generateSecret(int $length = 32): string {
    $bytes = random_bytes($length);
    $out = '';
    for ($i = 0; $i < $length; $i++) {
      $out .= self::ALPHABET[ord($bytes[$i]) & 31];
    }
    return $out;
  }

  /**
   * Decodes a Base32 encoded string.
   *
   * @param string $s
   * @return string
   */
  private static function base32Decode(string $s): string {
    $s = strtoupper(preg_replace('/[^A-Z2-7]/', '', $s));
    $bits = 0;
    $value = 0;
    $out = '';
    $map = array_flip(str_split(self::ALPHABET));

    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
      $value = ($value << 5) | $map[$s[$i]];
      $bits += 5;
      if ($bits >= 8) {
        $bits -= 8;
        $out .= chr(($value >> $bits) & 0xFF);
      }
    }
    return $out;
  }

  /**
   * Calculates the TOTP code for a given secret and timestamp.
   *
   * @param string $secret
   * @param int|null $time
   * @param int $step
   * @param int $digits
   * @return string
   */
  public static function code(string $secret, ?int $time = null, int $step = 30, int $digits = 6): string{
    $time = $time ?? time();
    $key = self::base32Decode($secret);
    $counter = intdiv($time, $step);
    $msg = pack('N*', 0) . pack('N*', $counter);
    $hash = hash_hmac('sha1', $msg, $key, true);
    $offset = ord($hash[19]) & 0x0f;
    $bin = ((ord($hash[$offset]) & 0x7f) << 24)
         | ((ord($hash[$offset+1]) & 0xff) << 16)
         | ((ord($hash[$offset+2]) & 0xff) << 8)
         | (ord($hash[$offset+3]) & 0xff);
    $mod = $bin % (10 ** $digits);
    return str_pad((string)$mod, $digits, '0', STR_PAD_LEFT);
  }

  /**
   * Verifies a TOTP code against a secret, allowing for a defined time window.
   *
   * @param string $secret
   * @param string $code
   * @param int $window
   * @return bool
   */
  public static function verify(string $secret, string $code, int $window = 1): bool {
    $code = preg_replace('/\D/', '', $code);
    if ($code === '') return false;
    $now = time();
    for ($i = -$window; $i <= $window; $i++) {
      if (hash_equals(self::code($secret, $now + ($i * 30)), $code)) return true;
    }
    return false;
  }

  /**
   * Generates a provisioning URI for QR code integration (e.g., Google Authenticator).
   *
   * @param string $user
   * @param string $issuer
   * @param string $secret
   * @return string
   */
  public static function provisioningUri(string $user, string $issuer, string $secret): string {
    return sprintf(
      'otpauth://totp/%s:%s?secret=%s&issuer=%s',
      rawurlencode($issuer),
      rawurlencode($user),
      $secret,
      rawurlencode($issuer)
    );
  }
}