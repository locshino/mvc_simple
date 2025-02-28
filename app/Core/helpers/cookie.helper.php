<?php
/**
 * Lấy giá trị cookie theo key
 *
 * @param string $key Tên cookie
 * @param mixed $default Giá trị mặc định nếu không tồn tại
 * @return mixed
 */
function cookie(string $key, $default = null): mixed
{
  return $_COOKIE[$key] ?? $default;
}

/**
 * Thiết lập cookie với tùy chọn
 *
 * @param string $key Tên cookie
 * @param string $value Giá trị cookie
 * @param array $options Tùy chọn (ttl, path, domain, secure, httponly, samesite)
 * @return void
 */
function cookie_set(string $key, string $value, array $options = []): void
{
  $defaultOptions = App\Core\Configs\Cookie::getDefaults();
  $options = array_merge($defaultOptions, $options);

  $ttl = $options['ttl'] ?? 0;
  $path = $options['path'] ?? '/';
  $domain = $options['domain'] ?? null;
  $secure = $options['secure'] ?? false;
  $httponly = $options['httponly'] ?? false;
  $samesite = $options['samesite'] ?? 'Lax';

  setcookie($key, $value, [
    'expires' => time() + $ttl,
    'path' => $path,
    'domain' => $domain,
    'secure' => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite,
  ]);
}

/**
 * Thiết lập cấu hình mặc định cho cookie
 *
 * @param array $options Tùy chọn mặc định (ttl, path, domain, secure, httponly, samesite)
 * @return void
 */
function cookie_setup(array $options): void
{
  App\Core\Configs\Cookie::setup($options);
}