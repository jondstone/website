<?php

/**
 * Data access class for reading and writing comments to the database.
 */
class CommentRepository {
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
   * Fetch all visible comments (top-level + replies) for a page and build a nested tree structure.
   *
   * @param string $pageKey
   * @return array
   */
  public function getCommentsByPage(string $pageKey): array {
    $sql = "
      query
    ";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$pageKey]);
    $rows = $stmt->fetchAll();

    $byId = [];
    foreach ($rows as $r) {
      $r[''] = [];
      $byId[$r['']] = $r;
    }

    $out = [];
    foreach ($byId as $id => &$r) {
      if ($r[''] === null) {
        $out[] = &$r;
      } elseif (isset($byId[$r['']])) {
        $byId[$r['']][''][] = &$r;
      }
    }

    return $out;
  }

  /**
   * Insert a new comment into the database.
   *
   * @param string $pageKey
   * @param int|null $parentId
   * @param string $authorName
   * @param string $bodyText
   * @param string $status
   * @return int The ID of the inserted row.
   */
  public function insertCommentWithStatus(string $pageKey, ?int $parentId, string $authorName, string $bodyText, string $status): int {
    $sql = "query";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$pageKey, $parentId, $authorName, $bodyText, $status]);
    return (int)$this->pdo->lastInsertId();
  }
}