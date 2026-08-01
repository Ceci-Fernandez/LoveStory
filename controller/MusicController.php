<?php
/**
 * Sección de música compartida (integración con Spotify/YouTube embebido).
 */
class MusicController
{
    private MusicaModel $musicaModel;

    public function __construct()
    {
        $this->musicaModel = new MusicaModel();
    }

    public function index(): void
    {
        $canciones = $this->musicaModel->obtenerTodas();
        View::render('musica', ['canciones' => $canciones]);
    }
}
