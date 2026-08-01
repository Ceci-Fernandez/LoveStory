<?php
/**
 * Maneja la pantalla de acceso ("¿Cuántos años cumplimos hoy?")
 */
class LoginController
{
    public function index(): void
    {
        View::render('login', [
            'pregunta' => ACCESS_QUESTION,
        ]);
    }

    public function verificar(): void
    {
        $respuesta = trim($_POST['respuesta'] ?? '');

        if ($respuesta === ACCESS_ANSWER) {
            Auth::concederAcceso();
            header('Location: index.php?route=inicio');
            exit;
        }

        View::render('login', [
            'pregunta' => ACCESS_QUESTION,
            'error' => 'Pensalo otra vez...',
        ]);
    }
}
