<?php
/**
 * Punto de entrada único de la aplicación (patrón Front Controller).
 * Todas las rutas pasan por acá: index.php?route=nombre-de-ruta
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Router.php';

$router = new Router();

// Acceso / portada
$router->add('login', 'LoginController', 'index');
$router->add('login-verificar', 'LoginController', 'verificar');
$router->add('home', 'HomeController', 'index');
$router->add('inicio', 'InicioController', 'index');

// Contenido principal
$router->add('timeline', 'TimelineController', 'index');
$router->add('album', 'AlbumController', 'index');
$router->add('album-ver', 'AlbumController', 'ver');
$router->add('musica', 'MusicController', 'index');
$router->add('contador', 'ContadorController', 'index');

// Sorpresas
$router->add('sorpresa', 'SurpriseController', 'capsulas');
$router->add('sorpresa-desbloquear', 'SurpriseController', 'desbloquear');
$router->add('recuerdo-aleatorio', 'MemoryController', 'aleatorio');
$router->add('recuerdo-agregar', 'MemoryController', 'agregar');

// Panel privado
$router->add('admin', 'AdminController', 'index');
$router->add('admin-login', 'AdminController', 'login');

// API / JSON
$router->add('api-estadisticas', 'ApiController', 'estadisticas');
$router->add('api-ruleta', 'ApiController', 'ruleta');

$route = $_GET['route'] ?? (Auth::haAccedido() ? 'inicio' : 'login');

$router->dispatch($route);
