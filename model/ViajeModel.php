<?php
class ViajeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerTodos(): array
    {
        $stmt = $this->db->query('SELECT * FROM viajes');
        return $stmt->fetchAll();
    }

    public function crear(string $nombre, string $descripcion): int
    {
        $stmt = $this->db->prepare('INSERT INTO viajes (nombre, descripcion) VALUES (?, ?)');
        $stmt->execute([$nombre, $descripcion]);
        return (int)$this->db->lastInsertId();
    }
}
