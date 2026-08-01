<?php
class FotoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerPorAlbum(int $albumId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM fotos WHERE album_id = ? ORDER BY fecha ASC');
        $stmt->execute([$albumId]);
        return $stmt->fetchAll();
    }

    public function crear(int $albumId, string $ruta, string $descripcion): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO fotos (album_id, ruta, descripcion, fecha) VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$albumId, $ruta, $descripcion]);
        return (int)$this->db->lastInsertId();
    }
}
