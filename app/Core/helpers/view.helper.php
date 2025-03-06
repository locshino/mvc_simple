<?php

use eftec\bladeone\BladeOne;

/**
 * Render một view Blade và trả về nội dung
 *
 * @param string $view Tên view (ví dụ: 'home.index' hoặc 'pages.profile')
 * @param array $data Dữ liệu truyền vào view (mặc định: [])
 * @param bool $bladeDebug Bật chế độ debug cho Blade (mặc định: false)
 * @param string|null $viewsDir Đường dẫn thư mục views (mặc định: app/Views)
 * @param string|null $cacheDir Đường dẫn thư mục cache (mặc định: app/storage/blade)
 * @return string Nội dung HTML đã render
 * @throws RuntimeException Nếu không thể render view hoặc tạo thư mục
 */
function view(string $view, array $data = [], bool $bladeDebug = false, ?string $viewsDir = null, ?string $cacheDir = null): string
{
  static $blade = null;

  // Đường dẫn mặc định
  $defaultViewsDir = basePath('app/Views');
  $defaultCacheDir = basePath('app/storage/blade');
  $viewsDir ??= $defaultViewsDir;
  $cacheDir ??= $defaultCacheDir;

  // Tạo thư mục nếu chưa tồn tại
  if (! file_exists($cacheDir) && ! mkdir($cacheDir, 0755, true) && ! is_dir($cacheDir)) {
    throw new RuntimeException("Không thể tạo thư mục cache: $cacheDir");
  }
  if (! file_exists($viewsDir) && ! mkdir($viewsDir, 0755, true) && ! is_dir($viewsDir)) {
    throw new RuntimeException("Không thể tạo thư mục views: $viewsDir");
  }

  // Khởi tạo BladeOne nếu chưa có hoặc cần thay đổi cấu hình
  $mode = $bladeDebug && ! isProduction()
    ? BladeOne::MODE_DEBUG : BladeOne::MODE_AUTO;
  if ($blade === null || $blade->getMode() !== $mode || $blade->getCompiledPath() !== $cacheDir || $blade->getTemplatePath() !== $viewsDir) {
    $blade = new BladeOne($viewsDir, $cacheDir, $mode);
  }

  try {
    return $blade->run($view, $data);
  } catch (\Exception $e) {
    throw new RuntimeException("Không thể render view '$view': ".$e->getMessage());
  }
}

/**
 * Render một trang Blade trong namespace 'pages'
 *
 * @param string $page Tên page trong thư mục pages (ví dụ: 'profile' sẽ thành 'pages.profile')
 * @param array $data Dữ liệu truyền vào view (mặc định: [])
 * @param bool $bladeDebug Bật chế độ debug cho Blade (mặc định: false)
 * @return string Nội dung HTML đã render
 * @throws RuntimeException Nếu không thể render view
 */
function viewPage(string $page, array $data = [], bool $bladeDebug = false): string
{
  return view("pages.$page", $data, $bladeDebug);
}

/**
 * Render một trang lỗi Blade
 *
 * @param string $error Tên trang lỗi (ví dụ: '404' hoặc '500')
 * @param array $data Dữ liệu truyền vào view (mặc định: [])
 * @param bool $bladeDebug Bật chế độ debug cho Blade (mặc định: false)
 * @return string Nội dung HTML đã render
 */
function viewError(string $error, array $data = [], bool $bladeDebug = false): string
{
  return viewPage("errors.$error", $data, $bladeDebug);
}

/**
 * Kiểm tra xem ứng dụng đang ở môi trường production hay không
 *
 * @return bool True nếu là production, false nếu không
 */
function isProduction(): bool
{
  return env('APP_ENV') === 'production';
}