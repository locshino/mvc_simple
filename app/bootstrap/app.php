<?php
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__.'/../helpers/index.php';

// Khởi tạo session nếu chưa có
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// setOldInput();
loadEnv(); // Load .env
handleRouting(); // Xử lý routing

