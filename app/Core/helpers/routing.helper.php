<?php

// Use our custom classes with aliases for clarity
use App\Core\Routing\RouteCollector as SimpleRouteCollector;
use App\Core\Routing\Dispatcher as SimpleDispatcher;

/**
 * Main routing handler.
 * It checks for Phroute and uses a custom fallback if it's not installed.
 */
function handleRouting(): void
{
    try {
        $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $uri = rtrim($uri, '/') ?: '/';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        app_log("Request: $method $uri", 'info');

        // Check if the Phroute library is installed
        if (class_exists(\Phroute\Phroute\RouteCollector::class)) {
            // --- Logic for Phroute (if installed) ---
            $route = new \Phroute\Phroute\RouteCollector();
            require_once basePath('app/Routes/web.php');
            $dispatcher = new \Phroute\Phroute\Dispatcher($route->getData());
            $response = $dispatcher->dispatch($method, $uri);
        } else {
            // --- Logic for our custom fallback router ---
            $route = new SimpleRouteCollector();
            require_once basePath('app/Routes/web.php');
            $dispatcher = new SimpleDispatcher($route->getData());
            $response = $dispatcher->dispatch($method, $uri);

            // Our simple dispatcher returns null on failure
            if ($response === null) {
                http_response_code(404);
                echo viewError('404', ['message' => 'Trang không tìm thấy']);
                return;
            }
        }

        app_log("Response dispatched for $method $uri", 'info');
        echo $response;

    } catch (\Phroute\Phroute\Exception\HttpRouteNotFoundException $e) { // Phroute specific exception
        app_log("404 Not Found (Phroute): {$e->getMessage()}", 'error');
        http_response_code(404);
        echo viewError('404', ['message' => 'Trang không tìm thấy']);
    } catch (\Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) { // Phroute specific exception
        app_log("405 Method Not Allowed (Phroute): {$e->getMessage()}", 'error');
        http_response_code(405);
        echo viewError('405', ['message' => 'Phương thức không được phép']);
    } catch (\Exception $e) { // General exception for our router and others
        app_log("500 Server Error: {$e->getMessage()}\n{$e->getTraceAsString()}", 'error');
        http_response_code(500);
        $data = !isProduction()
            ? ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]
            : ['message' => 'Có lỗi xảy ra, vui lòng thử lại sau'];
        echo viewError('500', $data);
    }
}

// ... other helper functions like redirect(), back(), route() remain the same ...
