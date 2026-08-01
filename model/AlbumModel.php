<?php
class AlbumModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query('SELECT * FROM albumes ORDER BY fecha DESC');
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM albumes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(string $titulo, string $descripcion): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO albumes (titulo, descripcion, fecha) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$titulo, $descripcion]);
        return (int)$this->db->lastInsertId();
    }
}
