<?php

/**
 * Funciones helper globales
 */

if (!function_exists('env')) {
    /**
     * Obtiene una variable de entorno
     * 
     * @param string $key Clave de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        // Convertir valores booleanos string a boolean
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }
        
        return $value;
    }
}

if (!function_exists('config')) {
    /**
     * Obtiene un valor de configuración
     * 
     * @param string $key Clave de configuración (ej: 'database.host')
     * @param mixed $default Valor por defecto
     * @return mixed
     */
    function config(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $file = array_shift($keys);
        
        $configPath = __DIR__ . '/../config/' . $file . '.php';
        if (!file_exists($configPath)) {
            return $default;
        }
        
        $config = require $configPath;
        
        foreach ($keys as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }
        
        return $config;
    }
}

if (!function_exists('base_path')) {
    /**
     * Obtiene la ruta base del proyecto
     * 
     * @param string $path Ruta adicional
     * @return string
     */
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/../' . ltrim($path, '/');
    }
}

if (!function_exists('storage_path')) {
    /**
     * Obtiene la ruta del directorio storage
     * 
     * @param string $path Ruta adicional
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('public_path')) {
    /**
     * Obtiene la ruta del directorio public
     * 
     * @param string $path Ruta adicional
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return base_path('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die - Muestra una variable y detiene la ejecución
     * 
     * @param mixed ...$vars Variables a mostrar
     * @return void
     */
    function dd(...$vars): void
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;font-family:monospace;font-size:14px;line-height:1.5;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump - Muestra una variable sin detener la ejecución
     * 
     * @param mixed ...$vars Variables a mostrar
     * @return void
     */
    function dump(...$vars): void
    {
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:20px;font-family:monospace;font-size:14px;line-height:1.5;">';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
    }
}

if (!function_exists('url')) {
    /**
     * Genera una URL completa con la base del proyecto
     * Usa BASE_URL definida en index.php (desde .env APP_URL)
     * 
     * @param string $path Ruta relativa
     * @return string URL completa
     */
    function url(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /**
     * Genera una URL para un asset estático
     * 
     * @param string $path Ruta del asset
     * @return string URL del asset
     */
    function asset(string $path = ''): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '';
        return $base . '/' . ltrim($path, '/');
    }
}
