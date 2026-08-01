<?php
/**
 * Router muy simple: mapea ?route=algo a Controlador::metodo
 * Ejemplo de uso en URL: index.php?route=timeline
 */

class Router
{
    private array $routes = [];

    public function add(string $route, string $controller, string $action = 'index'): void
    {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch(string $route): void
    {
        if (!isset($this->routes[$route])) {
            $route = 'home'; // fallback
        }

        $config = $this->routes[$route];
        $controllerClass = $config['controller'];
        $action = $config['action'];

        if (!class_exists($controllerClass)) {
            http_response_code(404);
            echo "Página no encontrada.";
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo "Acción no encontrada.";
            return;
        }

        $controller->$action();
    }
}
