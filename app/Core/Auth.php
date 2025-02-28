<?php

namespace App\Utils\Core;

class Auth
{
  private const USER_SESSION_KEY = 'auth_user';
  private const REMEMBER_TOKEN_KEY = 'remember_token';

  /**
   * Thiết lập thông tin người dùng vào session (đăng nhập)
   *
   * @param array $userData Dữ liệu người dùng (id, email, v.v.)
   * @param bool $remember Lưu token dài hạn (nếu cần)
   * @param array $cookieOptions Tùy chọn cookie (ttl, path, domain, secure, httponly, samesite)
   * @return string|null Remember token nếu $remember = true, null nếu không
   */
  public static function set(array $userData, bool $remember = false, array $cookieOptions = []): ?string
  {
    session_set(self::USER_SESSION_KEY, $userData);

    app_log("User logged in: ".($userData['email'] ?? 'unknown')." from IP: ".request()->ip(), 'info');

    if ($remember && isset($userData['id'])) {
      $token = bin2hex(random_bytes(16));
      cookie_set(self::REMEMBER_TOKEN_KEY, $token, $cookieOptions);

      $userData['remember_token'] = $token;
      session_set(self::USER_SESSION_KEY, $userData);

      return $token;
    }

    return null;
  }

  /**
   * Lấy thông tin người dùng hiện tại từ session
   *
   * @return array|null Dữ liệu người dùng hoặc null nếu chưa đăng nhập
   */
  public static function user(): ?array
  {
    $user = session(self::USER_SESSION_KEY);
    if (! empty($user)) {
      return $user;
    }

    $token = cookie(self::REMEMBER_TOKEN_KEY);
    if ($token) {
      return ['remember_token' => $token];
    }

    return null;
  }

  /**
   * Kiểm tra xem người dùng đã đăng nhập chưa
   *
   * @return bool True nếu đã đăng nhập, false nếu chưa
   */
  public static function check(): bool
  {
    $user = self::user();
    return ! empty($user) && ! empty($user['id']);
  }

  /**
   * Đăng xuất người dùng
   *
   * @return void
   */
  public static function logout(): void
  {
    $userEmail = self::user()['email'] ?? 'unknown';
    session_delete(self::USER_SESSION_KEY);
    cookie_set(self::REMEMBER_TOKEN_KEY, '', ['ttl' => -3600]);

    app_log("User logged out: ".$userEmail." from IP: ".request()->ip(), 'info');
  }

  /**
   * Lấy ID của người dùng hiện tại
   *
   * @return int|string|null
   */
  public static function id(): int|string|null
  {
    $user = self::user();
    return $user['id'] ?? null;
  }

  /**
   * Thử đăng nhập bằng email và password thông qua callback
   *
   * @param callable $callback Hàm gọi để xác thực (nhận email, password, trả về userData hoặc null)
   * @param bool $remember Lưu token dài hạn (nếu cần)
   * @param array $cookieOptions Tùy chọn cookie
   * @return bool True nếu đăng nhập thành công, false nếu không
   */
  public static function attempt(callable $callback, bool $remember = false, array $cookieOptions = []): bool
  {
    $userData = $callback(request()->input('email'), request()->input('password'));
    if ($userData && is_array($userData) && ! empty($userData['id'])) {
      self::set($userData, $remember, $cookieOptions);
      return true;
    }
    return false;
  }
}