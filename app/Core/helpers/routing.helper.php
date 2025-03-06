<?php
function handleRouting(): void
{
  try {
    $route = new Phroute\Phroute\RouteCollector();

    require_once basePath('app/Routes/web.php');

    $dispatcher = new Phroute\Phroute\Dispatcher($route->getData());

    // Lấy URI và sanitize
    $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $uri = filter_var($uri, FILTER_SANITIZE_URL);
    $uri = rtrim($uri, '/') ?: '/';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    // Log request
    app_log("Request: $method $uri", 'info');

    $response = $dispatcher->dispatch($method, $uri);

    // Log response
    app_log("Response dispatched for $method $uri", 'info');

    echo $response;

  } catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    app_log("404 Not Found: {$e->getMessage()} | URI: $uri", 'error');
    http_response_code(404);
    echo viewError('404', ['message' => 'Trang không tìm thấy']);
  } catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    app_log("405 Method Not Allowed: {$e->getMessage()} | Method: $method | URI: $uri", 'error');
    http_response_code(405);
    echo viewError('405', ['message' => 'Phương thức không được phép']);
  } catch (Exception $e) {
    app_log("500 Internal Server Error: {$e->getMessage()} | File: {$e->getFile()} | Line: {$e->getLine()}", 'error');
    http_response_code(500);

    // Hiển thị lỗi chi tiết nếu ở chế độ debug
    $data = env('APP_DEBUG', false)
      ? ['message' => 'Lỗi hệ thống',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()]
      : ['message' => 'Có lỗi xảy ra, vui lòng thử lại sau'];

    echo viewError('500', $data);
  }
}

function redirect(string $url): never
{
  header('Location: '.$url);
  exit();
}

function back(): never
{
  header('Location: '.($_SERVER['HTTP_REFERER'] ?? env('APP_URL', '/')));
  exit();
}

/**
 * Tạo đường dẫn từ path và tham số
 * 
 * @param string $path Đường dẫn
 * @param array $params Tham số
 * @return string Đường dẫn hoàn chỉnh
 * @throws RuntimeException Nếu APP_URL không được định nghĩa
 */
function route(string $path = '', array $params = []): string
{
  // Lấy APP_URL từ biến môi trường, mặc định là ''
  $appUrl = env('APP_URL', '');

  // Kiểm tra xem APP_URL có được định nghĩa hay không
  if (empty($appUrl)) {
    throw new RuntimeException('APP_URL không được định nghĩa trong biến môi trường');
  }

  // Chuẩn hóa APP_URL (loại bỏ dấu / ở cuối)
  $baseUrl = rtrim($appUrl, '/');

  // Chuyển dấu chấm thành dấu gạch chéo và chuẩn hóa đường dẫn
  $normalizedPath = str_replace('.', '/', trim($path, '/'));

  // Kết hợp base URL và path
  $fullPath = $normalizedPath ? "$baseUrl/$normalizedPath" : $baseUrl;

  // Thêm query string nếu có tham số
  if (! empty($params)) {
    $queryString = http_build_query($params);
    $fullPath .= "?$queryString";
  }

  return $fullPath;
}

function asset(string $path): string
{
  return route("assets.$path");
}