<?php
/**
 * Panel privado: subir fotos, crear álbumes, agregar viajes, escribir
 * recuerdos, agregar canciones, editar textos. Todo sin tocar código.
 */
class AdminController
{
    public function index(): void
    {
        if (!Auth::esAdmin()) {
            header('Location: index.php?route=login');
            exit;
        }

        View::render('admin', []);
    }

    public function login(): void
    {
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->verificarCredenciales(
            $_POST['usuario'] ?? '',
            $_POST['password'] ?? ''
        );

        if ($usuario) {
            Auth::login($usuario);
            header('Location: index.php?route=admin');
            exit;
        }

        View::render('login', ['error' => 'Usuario o contraseña incorrectos']);
    }
}
