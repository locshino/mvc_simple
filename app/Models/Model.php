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
   * Thực thi truy vấn SQL tùy chỉnh và trả về tất cả bản ghi
   *
   * @param string $sql Câu lệnh SQL (thường là SELECT)
   * @param array $params Mảng chứa các giá trị tham số để bind
   * @param int|null $limit Số lượng bản ghi tối đa (tùy chọn)
   * @return array Mảng chứa tất cả bản ghi từ truy vấn
   */
  public static function getAll(string $sql, array $params = [], ?int $limit = null): array
  {
    if ($limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $limit;
    }
    return self::query($sql, $params)->fetchAll();
  }

  /**
   * Tìm một bản ghi theo điều kiện
   *
   * @param array $conditions Điều kiện WHERE (key => value)
   * @return array|null Bản ghi đầu tiên hoặc null nếu không tìm thấy
   */
  public static function find(array $conditions = []): ?array
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

    $sql .= " LIMIT 1"; // Chỉ lấy bản ghi đầu tiên

    $stmt = self::query($sql, $params);
    return $stmt->fetch() ?: null;
  }

  /**
   * Lấy tất cả bản ghi với điều kiện, giới hạn và offset
   *
   * @param array $conditions Điều kiện WHERE (key => value)
   * @param int|null $limit Số bản ghi tối đa
   * @param int|null $offset Vị trí bắt đầu
   * @return array
   */
  public static function all(array $conditions = [], ?int $limit = null, ?int $offset = null): array
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

    if ($limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $limit;
    }

    if ($offset !== null) {
      if ($limit === null) {
        throw new RuntimeException('OFFSET requires LIMIT to be set');
      }
      $sql .= " OFFSET ?";
      $params[] = $offset;
    }

    return self::query($sql, $params)->fetchAll();
  }

  /**
   * Lấy bản ghi mới nhất theo cột, với điều kiện, giới hạn và offset
   *
   * @param string $column Cột để sắp xếp (mặc định created_at)
   * @param array $conditions Điều kiện WHERE (key => value)
   * @param int|null $limit Số bản ghi tối đa
   * @param int|null $offset Vị trí bắt đầu
   * @return array
   */
  public static function latest(string $column = 'created_at', array $conditions = [], ?int $limit = null, ?int $offset = null): array
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

    $sql .= " ORDER BY `$column` DESC";

    if ($limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $limit;
    }

    if ($offset !== null) {
      if ($limit === null) {
        throw new RuntimeException('OFFSET requires LIMIT to be set');
      }
      $sql .= " OFFSET ?";
      $params[] = $offset;
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
   * Cập nhật bản ghi theo điều kiện
   *
   * @param array $data Dữ liệu cần cập nhật (key => value)
   * @param array $conditions Điều kiện WHERE (key => value)
   * @return int Số hàng bị ảnh hưởng
   */
  public static function update(array $data, array $conditions = []): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    if (empty($data)) {
      throw new RuntimeException('No data provided to update');
    }

    $table = static::$table;
    $set = implode(',', array_map(fn ($key) => "`$key` = ?", array_keys($data)));
    $sql = "UPDATE `$table` SET $set";
    $params = array_values($data);

    if (! empty($conditions)) {
      $where = implode(' AND ', array_map(fn ($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE ".$where;
      $params = array_merge($params, array_values($conditions));
    }

    return self::exec($sql, $params);
  }

  /**
   * Xóa bản ghi theo điều kiện
   *
   * @param array $conditions Điều kiện WHERE (key => value)
   * @return int Số hàng bị ảnh hưởng
   */
  public static function delete(array $conditions = []): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $sql = "DELETE FROM `$table`";
    $params = [];

    if (! empty($conditions)) {
      $where = implode(' AND ', array_map(fn ($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE ".$where;
      $params = array_values($conditions);
    } else {
      throw new RuntimeException('Conditions are required to prevent accidental deletion of all records');
    }

    return self::exec($sql, $params);
  }

  /**
   * Tìm một bản ghi theo khóa chính
   *
   * @param int|string $id Giá trị khóa chính
   * @return array|null Bản ghi hoặc null nếu không tìm thấy
   */
  public static function findById(int|string $id): ?array
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
   * Cập nhật một bản ghi theo khóa chính
   *
   * @param int|string $id Giá trị khóa chính
   * @param array $data Dữ liệu cần cập nhật (key => value)
   * @return int Số hàng bị ảnh hưởng
   */
  public static function updateById(int|string $id, array $data): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    if (empty($data)) {
      throw new RuntimeException('No data provided to update');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    $set = implode(',', array_map(fn ($key) => "`$key` = ?", array_keys($data)));
    $sql = "UPDATE `$table` SET $set WHERE `$primaryKey` = ?";
    $params = array_merge(array_values($data), [$id]);

    return self::exec($sql, $params);
  }

  /**
   * Xóa bản ghi theo khóa chính (ID)
   *
   * @param int|string $id Giá trị khóa chính
   * @return int Số hàng bị ảnh hưởng
   */
  public static function deleteById(int|string $id): int
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    $sql = "DELETE FROM `$table` WHERE `$primaryKey` = ?";

    return self::exec($sql, [$id]);
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
