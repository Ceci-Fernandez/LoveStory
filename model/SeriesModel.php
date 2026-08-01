<?php
class SeriesModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query('SELECT * FROM series ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function crear(string $nombre, string $plataforma, int $puntaje): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO series (nombre, plataforma, puntaje) VALUES (?, ?, ?)'
        );
        $stmt->execute([$nombre, $plataforma, $puntaje]);
        return (int)$this->db->lastInsertId();
    }
}