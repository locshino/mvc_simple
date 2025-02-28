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
    echo view('errors.404', ['message' => 'Trang không tìm thấy']);
  } catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    app_log("405 Method Not Allowed: {$e->getMessage()} | Method: $method | URI: $uri", 'error');
    http_response_code(405);
    echo view('errors.405', ['message' => 'Phương thức không được phép']);
  } catch (Exception $e) {
    app_log("500 Internal Server Error: {$e->getMessage()} | File: {$e->getFile()} | Line: {$e->getLine()}", 'error');
    http_response_code(500);
    if (env('APP_DEBUG', false)) {
      echo view('errors.500', [
        'message' => 'Lỗi hệ thống',
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ]);
    } else {
      echo view('errors.500', ['message' => 'Có lỗi xảy ra, vui lòng thử lại sau']);
    }
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