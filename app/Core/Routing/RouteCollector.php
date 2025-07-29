<?php

namespace App\Core\Routing;

/**
 * A simple, dependency-free route collector that mimics the Phroute syntax.
 * It collects route definitions into an array.
 */
class RouteCollector
{
    /**
     * @var array Stores all registered routes.
     */
    protected array $routes = [];

    /**
     * Adds a route to the collection.
     *
     * @param string $httpMethod The HTTP method (GET, POST, etc.).
     * @param string $uri The route URI.
     * @param mixed $handler The handler for the route (e.g., [Controller::class, 'method']).
     */
    protected function addRoute(string $httpMethod, string $uri, $handler): void
    {
        $this->routes[$httpMethod][] = [
            'uri' => $uri,
            'handler' => $handler
        ];
    }

    /**
     * Register a GET route.
     */
    public function get(string $uri, $handler): void
    {
        $this->addRoute('GET', $uri, $handler);
    }

    /**
     * Register a POST route.
     */
    public function post(string $uri, $handler): void
    {
        $this->addRoute('POST', $uri, $handler);
    }

    /**
     * Register a PUT route.
     */
    public function put(string $uri, $handler): void
    {
        $this->addRoute('PUT', $uri, $handler);
    }

    /**
     * Register a PATCH route.
     */
    public function patch(string $uri, $handler): void
    {
        $this->addRoute('PATCH', $uri, $handler);
    }

    /**
     * Register a DELETE route.
     */
    public function delete(string $uri, $handler): void
    {
        $this->addRoute('DELETE', $uri, $handler);
    }

    /**
     * Returns the collected route data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->routes;
    }
}
