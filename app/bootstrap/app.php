<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../helpers/index.php';

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

setOldInput(); // Lưu dữ liệu cũ của form vào session
loadEnv(); // Load .env
handleRouting(); // Xử lý routing

