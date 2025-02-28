<?php
namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

class DB
{
  protected static ?PDO $pdo = null;

  /**
   * Khởi tạo kết nối PDO với cơ sở dữ liệu nếu chưa có.
   *
   * @throws PDOException Nếu kết nối thất bại
   * @return void
   */
  public static function init(): void
  {
    if (! self::$pdo) {
      try {
        $db_host = env('DB_HOST', 'localhost');
        $db_database = env('DB_DATABASE', 'mysql');
        $db_name = env('DB_NAME', 'test');
        $db_user = env('DB_USER', 'root');
        $db_pass = env('DB_PASS', '');
        $db_charset = env('DB_CHARSET', 'utf8mb4');

        $dsn = "$db_database:host=$db_host;dbname=$db_name;charset=$db_charset";

        self::$pdo = new PDO(
          $dsn,
          $db_user,
          $db_pass,
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
          ]
        );
      } catch (PDOException $e) {
        throw new PDOException("Connection failed: ".$e->getMessage());
      }
    }
  }

  /**
   * Lấy instance PDO
   *
   * @return PDO
   */
  public static function getPdo(): PDO
  {
    self::init();
    return self::$pdo;
  }

  /**
   * Thực hiện truy vấn SQL với các tham số
   *
   * @param string $sql Câu lệnh SQL
   * @param array $params Mảng chứa các giá trị tham số
   * @return PDOStatement
   * @throws PDOException
   */
  public static function query(string $sql, array $params = []): PDOStatement
  {
    self::init();
    try {
      $stmt = self::$pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt;
    } catch (PDOException $e) {
      throw new PDOException("Query failed: ".$e->getMessage());
    }
  }

  /**
   * Thực thi một lệnh SQL (INSERT, UPDATE, DELETE)
   *
   * @param string $sql Câu lệnh SQL
   * @param array $params Mảng chứa các giá trị tham số
   * @return int Số hàng bị ảnh hưởng
   * @throws PDOException
   */
  public static function exec(string $sql, array $params = []): int
  {
    self::init();
    try {
      $stmt = self::$pdo->prepare($sql);
      $stmt->execute($params);
      return $stmt->rowCount();
    } catch (PDOException $e) {
      throw new PDOException("Execution failed: ".$e->getMessage());
    }
  }

  /**
   * Bắt đầu transaction
   *
   * @return bool
   */
  public static function beginTransaction(): bool
  {
    self::init();
    return self::$pdo->beginTransaction();
  }

  /**
   * Commit transaction
   *
   * @return bool
   */
  public static function commit(): bool
  {
    return self::$pdo->commit();
  }

  /**
   * Rollback transaction
   *
   * @return bool
   */
  public static function rollBack(): bool
  {
    return self::$pdo->rollBack();
  }
}