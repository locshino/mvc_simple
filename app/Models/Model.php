<?php
namespace App\Models;

use PDO;
use PDOStatement;
use PDOException;
use RuntimeException;

class Model
{
  protected static ?PDO $pdo = null;
  protected static string $table = '';
  protected static string $primaryKey = 'id';

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
   * Chạy truy vấn và trả về tất cả kết quả
   *
   * @param string $sql Câu lệnh SQL
   * @param array $params Mảng chứa các giá trị tham số
   * @return array
   */
  public static function raw(string $sql, array $params = []): array
  {
    return self::query($sql, $params)->fetchAll();
  }

  /**
   * Tìm một bản ghi theo khóa chính
   *
   * @param int|string $id Giá trị khóa chính
   * @return array|null
   */
  public static function find(int|string $id): ?array
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $primaryKey = static::$primaryKey;
    $table = static::$table;
    $stmt = self::query("SELECT * FROM `$table` WHERE `$primaryKey` = ?", [$id]);
    return $stmt->fetch() ?: null;
  }

  /**
   * Lấy tất cả bản ghi
   *
   * @param array $conditions Điều kiện WHERE (tùy chọn)
   * @return array
   */
  public static function all(array $conditions = []): array
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $sql = "SELECT * FROM `$table`";
    $params = [];

    if (! empty($conditions)) {
      $where = implode(' AND ', array_map(fn ($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE ".$where;
      $params = array_values($conditions);
    }

    return self::query($sql, $params)->fetchAll();
  }

  /**
   * Thêm bản ghi mới
   *
   * @param array $data Dữ liệu cần chèn
   * @return string|int ID của bản ghi mới
   * @throws PDOException
   */
  public static function create(array $data): string|int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $columns = array_map(fn ($col) => "`$col`", array_keys($data));
    $placeholders = array_fill(0, count($data), '?');

    $sql = "INSERT INTO `$table` (".implode(',', $columns).") VALUES (".implode(',', $placeholders).")";
    self::query($sql, array_values($data));

    return self::$pdo->lastInsertId();
  }

  /**
   * Cập nhật bản ghi
   *
   * @param int|string $id Giá trị khóa chính
   * @param array $data Dữ liệu cần cập nhật
   * @return int Số hàng bị ảnh hưởng
   */
  public static function update(int|string $id, array $data): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    $set = implode(',', array_map(fn ($key) => "`$key` = ?", array_keys($data)));

    $sql = "UPDATE `$table` SET $set WHERE `$primaryKey` = ?";
    return self::exec($sql, array_merge(array_values($data), [$id]));
  }

  /**
   * Xóa bản ghi
   *
   * @param int|string $id Giá trị khóa chính
   * @return int Số hàng bị ảnh hưởng
   */
  public static function delete(int|string $id): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    return self::exec("DELETE FROM `$table` WHERE `$primaryKey` = ?", [$id]);
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
