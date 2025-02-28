<?php
/**
 * Load biến môi trường từ file .env
 *
 * @param string $path Đường dẫn đến thư mục chứa .env
 * @return void
 * @throws Exception Nếu load thất bại
 */
function loadEnv(string $path = ''): void
{
  $envPath = $path ?: basePath();
  $envFile = $envPath.'/.env';

  if (! file_exists($envFile)) {
    die("File .env không tồn tại tại: $envFile");
  }

  try {
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
  } catch (Exception $e) {
    // Ghi log lỗi thay vì echo trực tiếp
    app_log("Environment error: ".$e->getMessage(), 'error');
    // Nếu đang debug, hiển thị lỗi chi tiết
    if (env('APP_DEBUG', false)) {
      die("Environment error: ".$e->getMessage());
    }

    // Nếu không debug, chỉ dừng ứng dụng với thông báo chung
    die("Failed to load environment configuration");
  }
}

function env(string $key, $default = null): mixed
{
  return $_ENV[$key] ?? $default;
}