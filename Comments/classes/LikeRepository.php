<?php

/**
 * Repository for managing comment "likes" and preventing duplicate votes.
 */
class LikeRepository {
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
   * Attempts to add a like; returns true if added, false if already liked.
   *
   * @param int $commentId The ID of the comment being liked.
   * @param string $clientHash A unique hash representing the user/client to prevent duplicate likes.
   * @return bool True if the like was successfully added, false otherwise.
   */
  public function addLike(int $commentId, string $clientHash): bool {
    $sql = "query";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$commentId, $clientHash]);

    if ($stmt->rowCount() === 1) {
      $u = $this->pdo->prepare("query");
      $u->execute([$commentId]);
      return true;
    }
    return false;
  }
}