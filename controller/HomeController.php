<?php
/**
 * Pantalla de intro / portada, una vez que se concedió el acceso.
 */
class HomeController
{
    public function index(): void
    {
        if (!Auth::haAccedido()) {
            header('Location: index.php?route=login');
            exit;
        }

        View::render('home', [
            'nombre1' => APP_NAME_1,
            'nombre2' => APP_NAME_2,
        ]);
    }
}
