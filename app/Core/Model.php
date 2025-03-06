<?php

namespace App\Core;

use RuntimeException;

class Model
{
  /** @var string Tên bảng trong cơ sở dữ liệu */
  protected static string $table = '';

  /** @var string Tên khóa chính của bảng */
  protected static string $primaryKey = 'id';

  /** @var array Điều kiện WHERE */
  protected array $whereConditions = [];

  /** @var array Các cột cần chọn */
  protected array $selectColumns = ['*'];

  /** @var int|null Giới hạn số bản ghi */
  protected ?int $limit = null;

  /** @var int|null Vị trí bắt đầu */
  protected ?int $offset = null;

  /** @var array|null Sắp xếp theo cột */
  protected ?array $orderBy = null;

  /** @var array Các bảng JOIN */
  protected array $joins = [];

  /**
   * Khởi tạo một instance mới của model (hỗ trợ chainable)
   *
   * @return static
   */
  public static function query(): static
  {
    return new static();
  }

  /**
   * Thêm điều kiện WHERE vào truy vấn
   *
   * @param string $column Tên cột
   * @param string $operator Toán tử so sánh (=, >, <, LIKE, etc.)
   * @param mixed $value Giá trị để so sánh
   * @return $this Trả về instance hiện tại để chain
   */
  public function where(string $column, string $operator, $value): self
  {
    $this->whereConditions[] = [
      'column' => $column,
      'operator' => $operator,
      'value' => $value
    ];
    return $this;
  }

  /**
   * Thêm bảng JOIN vào truy vấn
   *
   * @param string $table Tên bảng cần join
   * @param string $condition Điều kiện join (ví dụ: 'products.category_id = categories.id')
   * @param string $type Loại join (INNER, LEFT, RIGHT, mặc định: INNER)
   * @return $this Trả về instance hiện tại để chain
   */
  public function join(string $table, string $condition, string $type = 'INNER'): self
  {
    $this->joins[] = [
      'table' => $table,
      'condition' => $condition,
      'type' => strtoupper($type)
    ];
    return $this;
  }

  /**
   * Chọn các cột cần lấy
   *
   * @param array $columns Mảng các cột (ví dụ: ['id', 'name'])
   * @return $this Trả về instance hiện tại để chain
   */
  public function select(array $columns): self
  {
    $this->selectColumns = $columns;
    return $this;
  }

  /**
   * Thiết lập giới hạn số bản ghi
   *
   * @param int $limit Số bản ghi tối đa
   * @return $this Trả về instance hiện tại để chain
   */
  public function limit(int $limit): self
  {
    $this->limit = $limit;
    return $this;
  }

  /**
   * Thiết lập vị trí bắt đầu
   *
   * @param int $offset Vị trí bắt đầu
   * @return $this Trả về instance hiện tại để chain
   */
  public function offset(int $offset): self
  {
    $this->offset = $offset;
    return $this;
  }

  /**
   * Sắp xếp kết quả theo cột
   *
   * @param string $column Tên cột để sắp xếp
   * @param string $direction Hướng sắp xếp (ASC hoặc DESC, mặc định ASC)
   * @return $this Trả về instance hiện tại để chain
   */
  public function orderBy(string $column, string $direction = 'ASC'): self
  {
    $this->orderBy = ['column' => $column, 'direction' => strtoupper($direction)];
    return $this;
  }

  /**
   * Thực thi truy vấn và trả về tất cả bản ghi
   *
   * @return array Mảng chứa tất cả bản ghi từ truy vấn
   * @throws RuntimeException Nếu bảng không được định nghĩa hoặc loại join không hợp lệ
   */
  public function get(): array
  {
    if (empty(static::$table)) {
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $params = [];

    // Chuẩn bị cột SELECT
    $columns = implode(', ', $this->selectColumns);
    $sql = "SELECT $columns FROM `$table`";

    // Thêm các JOIN
    if (!empty($this->joins)) {
      foreach ($this->joins as $join) {
        $joinType = $join['type'];
        if (!in_array($joinType, ['INNER', 'LEFT', 'RIGHT'])) {
          throw new RuntimeException("Loại join không hợp lệ: $joinType");
        }
        $sql .= " $joinType JOIN `{$join['table']}` ON {$join['condition']}";
      }
    }

    // Điều kiện WHERE
    if (!empty($this->whereConditions)) {
      $whereParts = [];
      foreach ($this->whereConditions as $condition) {
        $whereParts[] = "`{$condition['column']}` {$condition['operator']} ?";
        $params[] = $condition['value'];
      }
      $sql .= " WHERE " . implode(' AND ', $whereParts);
    }

    // Sắp xếp ORDER BY
    if ($this->orderBy !== null) {
      $sql .= " ORDER BY `{$this->orderBy['column']}` {$this->orderBy['direction']}";
    }

    // Giới hạn LIMIT và OFFSET
    if ($this->limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $this->limit;
    }

    if ($this->offset !== null) {
      if ($this->limit === null) {
        throw new RuntimeException('OFFSET yêu cầu LIMIT phải được thiết lập');
      }
      $sql .= " OFFSET ?";
      $params[] = $this->offset;
    }

    return DB::query($sql, $params)->fetchAll();
  }

  /**
   * Lấy bản ghi đầu tiên từ truy vấn
   *
   * @return array|null Bản ghi đầu tiên hoặc null nếu không có
   */
  public function first(): ?array
  {
    $this->limit(1);
    $results = $this->get();
    return $results[0] ?? null;
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $sql = "SELECT * FROM `$table`";
    $params = [];

    if (!empty($conditions)) {
      $where = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE " . $where;
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $sql = "SELECT * FROM `$table`";
    $params = [];

    if (!empty($conditions)) {
      $where = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE " . $where;
      $params = array_values($conditions);
    }

    if ($limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $limit;
    }

    if ($offset !== null) {
      if ($limit === null) {
        throw new RuntimeException('OFFSET yêu cầu LIMIT phải được thiết lập');
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $sql = "SELECT * FROM `$table`";
    $params = [];

    if (!empty($conditions)) {
      $where = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE " . $where;
      $params = array_values($conditions);
    }

    $sql .= " ORDER BY `$column` DESC";

    if ($limit !== null) {
      $sql .= " LIMIT ?";
      $params[] = $limit;
    }

    if ($offset !== null) {
      if ($limit === null) {
        throw new RuntimeException('OFFSET yêu cầu LIMIT phải được thiết lập');
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $columns = array_map(fn($col) => "`$col`", array_keys($data));
    $placeholders = array_fill(0, count($data), '?');

    $sql = "INSERT INTO `$table` (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    if (empty($data)) {
      throw new RuntimeException('Không có dữ liệu để cập nhật');
    }

    $table = static::$table;
    $set = implode(',', array_map(fn($key) => "`$key` = ?", array_keys($data)));
    $sql = "UPDATE `$table` SET $set";
    $params = array_values($data);

    if (!empty($conditions)) {
      $where = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE " . $where;
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $sql = "DELETE FROM `$table`";
    $params = [];

    if (!empty($conditions)) {
      $where = implode(' AND ', array_map(fn($key) => "`$key` = ?", array_keys($conditions)));
      $sql .= " WHERE " . $where;
      $params = array_values($conditions);
    } else {
      throw new RuntimeException('Yêu cầu điều kiện để tránh xóa toàn bộ bản ghi');
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    if (empty($data)) {
      throw new RuntimeException('Không có dữ liệu để cập nhật');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    $set = implode(',', array_map(fn($key) => "`$key` = ?", array_keys($data)));
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
      throw new RuntimeException('Tên bảng phải được định nghĩa trong lớp con');
    }

    $table = static::$table;
    $primaryKey = static::$primaryKey;
    $sql = "DELETE FROM `$table` WHERE `$primaryKey` = ?";

    return DB::exec($sql, [$id]);
  }
}
