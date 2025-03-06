<?php
namespace App\Core\Configs;

class Cookie
{
  private static array $defaultOptions = [
    'ttl' => 3600,        // Thời gian sống (giây)
    'path' => '/',     // Đường dẫn
    'domain' => null,  // Domain
    'secure' => false, // Chỉ gửi qua HTTPS
    'httponly' => true,// Chỉ truy cập qua HTTP
    'samesite' => 'Lax' // Bảo mật chống CSRF
  ];

  /**
   * Thiết lập cấu hình mặc định cho cookie
   *
   * @param array $options Tùy chọn mặc định (ttl, path, domain, secure, httponly, samesite)
   * @return void
   */
  public static function setup(array $options): void
  {
    self::$defaultOptions = array_merge(self::$defaultOptions, $options);
  }

  /**
   * Lấy cấu hình mặc định
   *
   * @return array
   */
  public static function getDefaults(): array
  {
    return self::$defaultOptions;
  }
}