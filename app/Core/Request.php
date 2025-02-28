<?php

namespace App\Core;

class Request
{
  private static ?self $instance = null;

  /**
   * Lấy instance singleton của Request
   *
   * @return self
   */
  public static function instance(): self
  {
    if (self::$instance === null) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Lấy dữ liệu từ request (POST hoặc GET) với giá trị mặc định
   *
   * @param string $key Tên field
   * @param mixed $default Giá trị mặc định nếu không tồn tại
   * @return mixed
   */
  public function input(string $key, $default = null): mixed
  {
    return $_POST[$key] ?? $_GET[$key] ?? $default;
  }

  /**
   * Kiểm tra request method
   *
   * @param string $method Method cần kiểm tra (GET, POST, v.v.)
   * @return bool
   */
  public function isMethod(string $method): bool
  {
    return strtoupper($_SERVER['REQUEST_METHOD']) === strtoupper($method);
  }

  /**
   * Lấy method của request
   *
   * @return string
   */
  public function method(): string
  {
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
  }

  /**
   * Lấy dữ liệu từ POST hoặc toàn bộ mảng POST
   *
   * @param string|null $key Tên field (nếu null, trả về toàn bộ mảng)
   * @param mixed $default Giá trị mặc định nếu không tồn tại
   * @return mixed
   */
  public function post(?string $key = null, $default = null): mixed
  {
    if ($key === null) {
      return $_POST ?? [];
    }
    return $_POST[$key] ?? $default;
  }

  /**
   * Lấy dữ liệu từ GET hoặc toàn bộ mảng GET
   *
   * @param string|null $key Tên field (nếu null, trả về toàn bộ mảng)
   * @param mixed $default Giá trị mặc định nếu không tồn tại
   * @return mixed
   */
  public function get(?string $key = null, $default = null): mixed
  {
    if ($key === null) {
      return $_GET ?? [];
    }
    return $_GET[$key] ?? $default;
  }

  /**
   * Lấy tất cả dữ liệu từ request (POST và GET)
   *
   * @return array
   */
  public function all(): array
  {
    return array_merge($this->get(), $this->post());
  }

  /**
   * Lấy chỉ các field được chỉ định từ request
   *
   * @param array $keys Mảng các key cần lấy
   * @return array
   */
  public function only(array $keys): array
  {
    return array_intersect_key($this->all(), array_flip($keys));
  }

  /**
   * Lấy tất cả dữ liệu trừ các field được chỉ định
   *
   * @param array $keys Mảng các key cần loại bỏ
   * @return array
   */
  public function except(array $keys): array
  {
    return array_diff_key($this->all(), array_flip($keys));
  }

  /**
   * Validate dữ liệu request và tổng hợp lỗi
   *
   * @param array $rules Quy tắc validate (field => rule hoặc rule array)
   * ví dụ: ['email' => 'required|email', 'password' => 'required|min:6']
   * @param array|null $data Dữ liệu cần validate (mặc định dùng all())
   * @return bool Trả về true nếu hợp lệ, false nếu có lỗi
   */
  public function validate(array $rules, ?array $data = null): bool
  {
    $data ??= $this->all(); // Mặc định dùng toàn bộ dữ liệu request
    $errors = [];

    foreach ($rules as $field => $ruleSet) {
      $rulesArray = is_array($ruleSet) ? $ruleSet : explode('|', $ruleSet);
      $value = $data[$field] ?? '';
      $isEmpty = empty(trim($value));

      foreach ($rulesArray as $rule) {
        if (strpos($rule, ':') !== false) {
          [$ruleName, $param] = explode(':', $rule);
        } else {
          $ruleName = $rule;
          $param = null;
        }

        switch ($ruleName) {
          case 'required':
            if ($isEmpty) {
              $errors[] = "Trường $field không được để trống!";
            }
            break;

          case 'nullable':
            if ($isEmpty) {
              continue 2;
            }
            break;

          case 'email':
            if (! $isEmpty && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
              $errors[] = "Trường $field phải là email hợp lệ!";
            }
            break;

          case 'min':
            if (! $isEmpty && strlen($value) < (int) $param) {
              $errors[] = "Trường $field phải có ít nhất $param ký tự!";
            }
            break;

          case 'max':
            if (! $isEmpty && strlen($value) > (int) $param) {
              $errors[] = "Trường $field không được vượt quá $param ký tự!";
            }
            break;
        }
      }
    }

    if (! empty($errors)) {
      $this->setOldInput($data); // Lưu dữ liệu cũ
      flashError($errors); // Flash tất cả lỗi
      return false;
    }

    return true;
  }

  /**
   * Lưu dữ liệu cũ vào session để sử dụng lại
   *
   * @param ?array $data Dữ liệu cần lưu, mặc định dùng all()
   * @return void
   */
  public function setOldInput(?array $data = null): void
  {
    $defaultData = array_merge($this->get(), $this->post());
    if ($data !== null || ! empty($defaultData)) {
      $_SESSION['old'] = $data ?? $defaultData;
    }
  }

  /**
   * Lấy file upload từ request
   *
   * @param string $key Tên field file
   * @return array|null Thông tin file hoặc null nếu không tồn tại
   */
  public function file(string $key): ?array
  {
    return $_FILES[$key] ?? null;
  }

  /**
   * Kiểm tra xem request có chứa file không
   *
   * @param string $key Tên field file (tùy chọn)
   * @return bool
   */
  public function hasFile(string $key = ''): bool
  {
    if (empty($key)) {
      return ! empty($_FILES);
    }
    return isset($_FILES[$key]) && ! empty($_FILES[$key]['name']);
  }

  /**
   * Lấy URI hiện tại
   *
   * @return string
   */
  public function uri(): string
  {
    return rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
  }

  /**
   * Lấy header từ request
   *
   * @param string $key Tên header
   * @param mixed $default Giá trị mặc định nếu không tồn tại
   * @return string|null
   */
  public function header(string $key, $default = null): ?string
  {
    $headers = getallheaders();
    return $headers[$key] ?? $default;
  }

  /**
   * Kiểm tra xem request có phải AJAX không
   *
   * @return bool
   */
  public function isAjax(): bool
  {
    return strtolower($this->header('X-Requested-With', '')) === 'xmlhttprequest';
  }

  /**
   * Lấy IP của client
   *
   * @return string
   */
  public function ip(): string
  {
    return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
  }
}