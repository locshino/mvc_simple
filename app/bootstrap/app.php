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

// setOldInput();
handleRouting(); // Xử lý routing

