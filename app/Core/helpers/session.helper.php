<?php
/**
 * Lấy giá trị từ session với key, trả về default nếu không tồn tại
 */
function session(string $key, $default = null): mixed
{
  return $_SESSION[$key] ?? $default;
}

/**
 * Thiết lập giá trị cho session
 */
function session_set(string $key, mixed $value): void
{
  $_SESSION[$key] = $value;
}

/**
 * Xóa giá trị của session theo key
 */
function session_delete(string $key): void
{
  if (! isset($_SESSION[$key])) {
    return;
  }

  unset($_SESSION[$key]);
}

/**
 * Lấy dữ liệu cũ của form
 */
function old(string $key, $default = null): mixed
{
  return $_SESSION['old'][$key] ?? $default;
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