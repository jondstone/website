<?php

/**
 * Handles the database connection using the Singleton pattern to ensure
 * a single PDO instance is used throughout the application.
 */
class Database {
  /** @var PDO|null The single PDO instance. */
  private static $pdo = null;

  /**
   * Retrieves the current database connection, initializing it if necessary.
   *
   * @return PDO
   */
  public static function getConnection() {
    if (self::$pdo !== null) {
      return self::$pdo;
    }

    $config = require __DIR__ . 'config.php';
    $db = $config['db'];

    $dsn = 'mysql:host=' . $db['host'] .
           ';dbname=' . $db['name'] .
           ';charset=' . $db['charset'];

    self::$pdo = new PDO(
      $dsn,
      $db['user'],
      $db['pass'],
      [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $db['charset'],
      ]
    );

    return self::$pdo;
  }
}