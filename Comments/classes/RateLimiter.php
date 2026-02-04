<?php

/**
 * Implements a fixed-window rate limiting mechanism using a database backend.
 */
class RateLimiter {
  /** @var PDO PDO connection instance. */
  private PDO $pdo;

  /**
   * Inject PDO so all queries share the same DB connection/config.
   *
   * @param PDO $pdo
   */
  public function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

  /**
   * Increments the hit count for a given key and checks if it exceeds the limit.
   *
   * @param string $key The unique identifier to rate limit (e.g., IP address or action).
   * @param int $limit The maximum number of allowed hits within the window.
   * @param int $windowSeconds The duration of the rate limit window in seconds.
   * @return bool True if the hit is within the limit, false if exceeded.
   */
  public function hit(string $key, int $limit, int $windowSeconds): bool {
    $now = time();

    $sql = "query";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$key, $now - ($now % $windowSeconds)]);

    $q = $this->pdo->prepare("query");
    $q->execute([$key]);
    $c = (int)$q->fetchColumn();

    return $c <= $limit;
  }
}