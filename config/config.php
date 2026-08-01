<?php
/**
 * Configuración general de la aplicación.
 */

// Modo desarrollo (mostrar errores) / producción (ocultarlos)
define('APP_ENV', 'development');

if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Ruta base de la app (útil si no vive en la raíz del dominio)
define('BASE_URL', '/');

// Nombres de la pareja / datos personalizables del proyecto
define('APP_NAME_1', 'Lucas');
define('APP_NAME_2', 'Ceci');
define('FECHA_INICIO_NOVIAZGO', '2023-08-01');

// Pregunta y respuesta de acceso (versión simple, mejorable con hash + BD)
define('ACCESS_QUESTION', '¿Cuántos años cumplimos hoy?');
define('ACCESS_ANSWER', '3'); // cambiar por el valor real

// Sesión
session_start();

// Autoload simple de controllers, models y helpers
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../controller/' . $class . '.php',
        __DIR__ . '/../model/' . $class . '.php',
        __DIR__ . '/../helpers/' . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

require_once __DIR__ . '/database.php';
