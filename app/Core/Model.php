<?php
namespace App\Core;

use RuntimeException;

class Model
{
  protected static string $table = '';
  protected static string $primaryKey = 'id';

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
    return DB::query($sql, $params)->fetchAll();
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

    $sql .= " LIMIT 1";

    $stmt = DB::query($sql, $params);
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

    return DB::query($sql, $params)->fetchAll();
  }

  /**
   * Lấy bản ghi mới nhất theo cột
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

    return DB::query($sql, $params)->fetchAll();
  }

  /**
   * Thêm bản ghi mới
   *
   * @param array $data Dữ liệu cần chèn
   * @return string|int ID của bản ghi mới
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
    DB::query($sql, array_values($data));

    return DB::getPdo()->lastInsertId();
  }

  /**
   * Cập nhật bản ghi theo điều kiện
   *
   * @param array $data Dữ liệu cần cập nhật
   * @param array $conditions Điều kiện WHERE
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

    return DB::exec($sql, $params);
  }

  /**
   * Xóa bản ghi theo điều kiện
   *
   * @param array $conditions Điều kiện WHERE
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

    return DB::exec($sql, $params);
  }

  /**
   * Tìm một bản ghi theo khóa chính
   *
   * @param int|string $id Giá trị khóa chính
   * @return array|null
   */
  public static function findById(int|string $id): ?array
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Table name must be defined in child class');
    }

    $primaryKey = static::$primaryKey;
    $table = static::$table;
    $stmt = DB::query("SELECT * FROM `$table` WHERE `$primaryKey` = ?", [$id]);
    return $stmt->fetch() ?: null;
  }

  /**
   * Cập nhật một bản ghi theo khóa chính
   *
   * @param int|string $id Giá trị khóa chính
   * @param array $data Dữ liệu cần cập nhật
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

    return DB::exec($sql, $params);
  }

  /**
   * Xóa bản ghi theo khóa chính
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

    return DB::exec($sql, [$id]);
  }
}
