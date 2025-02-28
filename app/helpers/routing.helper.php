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
    $uri = rtrim($uri, '/') ?: '/'; // Chuẩn hóa URI
    $method = $_SERVER['REQUEST_METHOD'];

    $dispatcher->dispatch($method, $uri);

  } catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    http_response_code(404);
    echo "404 Not Found";
  } catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    http_response_code(405);
    echo "405 Method Not Allowed";
  } catch (Exception $e) {
    http_response_code(500);
    echo "500 Internal Server Error: ".$e->getMessage();
  }
}

function redirect(string $url): never
{
  header('Location: '.$url);
  exit();
}

function back(): never
{
  header('Location: '.($_SERVER['HTTP_REFERER'] ?? '/'));
  exit();
}