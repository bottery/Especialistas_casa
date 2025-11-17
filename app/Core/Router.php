<?php

namespace App\Core;

/**
 * Router profesional para manejo eficiente de rutas
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $groupStack = [];

    /**
     * Registrar ruta GET
     */
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }

    /**
     * Registrar ruta POST
     */
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }

    /**
     * Registrar ruta PUT
     */
    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }

    /**
     * Registrar ruta DELETE
     */
    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }

    /**
     * Agrupar rutas con prefijo y middlewares comunes
     */
    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        $callback($this);
        array_pop($this->groupStack);
    }

    /**
     * Agregar ruta al registro
     */
    private function addRoute(string $method, string $path, $handler, array $middlewares = []): void
    {
        // Aplicar prefijos de grupos
        $prefix = '';
        $groupMiddlewares = [];
        
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
            if (isset($group['middleware'])) {
                $groupMiddlewares = array_merge($groupMiddlewares, (array)$group['middleware']);
            }
        }

        $fullPath = $prefix . '/' . ltrim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'pattern' => $this->buildPattern($fullPath),
            'handler' => $handler,
            'middlewares' => array_merge($groupMiddlewares, $middlewares)
        ];
    }

    /**
     * Construir patrón regex desde path con parámetros
     */
    private function buildPattern(string $path): string
    {
        // Convertir {id} a (\d+), {slug} a ([a-z0-9-]+), etc.
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        return $pattern;
    }

    /**
     * Despachar request al handler correspondiente
     */
    public function dispatch(string $method, string $uri): void
    {
        // Limpiar URI
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                array_shift($matches); // Remover match completo
                
                // Ejecutar middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareResult = $this->executeMiddleware($middleware);
                    if ($middlewareResult === false) {
                        return; // Middleware bloqueó la request
                    }
                }

                // Ejecutar handler
                $this->executeHandler($route['handler'], $matches);
                return;
            }
        }

        // Ruta no encontrada
        $this->sendResponse(['error' => 'Endpoint no encontrado'], 404);
    }

    /**
     * Ejecutar middleware
     */
    private function executeMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $middlewareClass = "App\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                return $instance->handle();
            }
        } elseif (is_callable($middleware)) {
            return $middleware();
        }
        return true;
    }

    /**
     * Ejecutar handler de la ruta
     */
    private function executeHandler($handler, array $params = []): void
    {
        if (is_array($handler)) {
            // [ControllerClass, 'method']
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
        } elseif (is_callable($handler)) {
            // Closure o función
            call_user_func_array($handler, $params);
        } elseif (is_string($handler)) {
            // 'ControllerClass@method'
            if (strpos($handler, '@') !== false) {
                [$class, $method] = explode('@', $handler);
                $controller = new $class();
                call_user_func_array([$controller, $method], $params);
            }
        }
    }

    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Obtener todas las rutas registradas (para debugging)
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
