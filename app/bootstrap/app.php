<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../Core/helpers/register.php';

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Load .env
loadEnv();

// Set timezone
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// Thiết lập cấu hình mặc định cho cookie
cookie_setup([
  'ttl' => 60 * 60 * 24 * 30, // 30 ngày
  'path' => '/',
  'secure' => false, // Thay true nếu dùng HTTPS
  'httponly' => true,
  'samesite' => 'Strict', // Có thể dùng Lax hoặc None
]);

// setOldInput();
handleRouting(); // Xử lý routing

