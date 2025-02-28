<?php
/**
 * Lấy giá trị từ session với key, trả về default nếu không tồn tại
 */
function session(string $key, $default = null): mixed
{
  return $_SESSION[$key] ?? $default;
}

/**
 * Lấy dữ liệu cũ của form
 */
function old(string $key, $default = null): mixed
{
  return $_SESSION['old'][$key] ?? $default;
}

/**
 * Lưu dữ liệu cũ vào session để sử dụng lại (ví dụ: điền lại form)
 *
 * @param ?array $data Dữ liệu cần lưu, mặc định lấy từ $_POST và $_GET
 * @return void
 */
function setOldInput(?array $data = null): void
{
  // Chỉ lưu nếu có dữ liệu từ POST hoặc GET, tránh ghi đè không cần thiết
  $defaultData = array_merge($_POST ?? [], $_GET ?? []);
  if ($data !== null || ! empty($defaultData)) {
    $_SESSION['old'] = $data ?? $defaultData;
  }
}

/**
 * Lưu flash message vào session (hỗ trợ chuỗi hoặc mảng)
 */
function flash(string $key, string|array $message): void
{
  $_SESSION['flash'][$key] = is_array($message) ? $message : [$message];
}

/**
 * Lấy và xóa flash message khỏi session
 */
function getFlash(string $key, $default = []): array
{
  if (isset($_SESSION['flash'][$key])) {
    $value = (array) $_SESSION['flash'][$key]; // Ép thành mảng để xử lý thống nhất
    unset($_SESSION['flash'][$key]);
    return $value;
  }
  return (array) $default; // Trả về mảng rỗng hoặc default ép thành mảng
}

/**
 * Kiểm tra xem flash message có tồn tại không
 */
function hasFlash(string $key): bool
{
  return isset($_SESSION['flash'][$key]) && ! empty($_SESSION['flash'][$key]);
}

/**
 * Helper tiện lợi để set flash error
 */
function flashError(string|array $message): void
{
  flash('error', $message);
}

/**
 * Helper tiện lợi để set flash success
 */
function flashSuccess(string|array $message): void
{
  flash('success', $message);
}