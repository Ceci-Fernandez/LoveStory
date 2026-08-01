<?php
/**
 * Contador de tiempo juntos, desde el noviazgo oficial.
 */
class ContadorController
{
    public function index(): void
    {
        View::render('contador', [
            'fecha_inicio' => FECHA_INICIO_NOVIAZGO,
        ]);
    }
}