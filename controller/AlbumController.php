<?php
/**
 * Álbumes de fotos (por viaje, evento, etc.)
 */
class AlbumController
{
    private AlbumModel $albumModel;
    private FotoModel $fotoModel;

    public function __construct()
    {
        $this->albumModel = new AlbumModel();
        $this->fotoModel = new FotoModel();
    }

    public function index(): void
    {
        $albumes = $this->albumModel->obtenerTodos();
        View::render('album', ['albumes' => $albumes]);
    }

    public function ver(): void
    {
        $albumId = (int)($_GET['id'] ?? 0);
        $album = $this->albumModel->obtenerPorId($albumId);
        $fotos = $this->fotoModel->obtenerPorAlbum($albumId);

        View::render('album_detalle', [
            'album' => $album,
            'fotos' => $fotos,
        ]);
    }
}
