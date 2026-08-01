<?php
/**
 * Helper de autenticación básica.
 * Maneja dos niveles: acceso general (pregunta) y admin (panel privado).
 */

class Auth
{
    public static function haAccedido(): bool
    {
        return isset($_SESSION['acceso_concedido']) && $_SESSION['acceso_concedido'] === true;
    }

    public static function concederAcceso(): void
    {
        $_SESSION['acceso_concedido'] = true;
    }

    public static function esAdmin(): bool
    {
        return isset($_SESSION['usuario_id']) && ($_SESSION['rol'] ?? '') === 'admin';
    }

    public static function login(array $usuario): void
    {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['nombre'] = $usuario['nombre'];
    }

    public static function logout(): void
    {
        session_unset();
        session_destroy();
    }
}
