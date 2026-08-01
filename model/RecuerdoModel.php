<?php
/**
 * Recuerdos libres (texto + foto opcional) que alimentan el timeline
 * y el "frasco de recuerdos" aleatorio.
 */
class RecuerdoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerTodosOrdenados(): array
    {
        $stmt = $this->db->query('SELECT * FROM mensajes ORDER BY fecha ASC');
        return $stmt->fetchAll();
    }

    public function obtenerAleatorio(): ?array
    {
        $stmt = $this->db->query('SELECT * FROM mensajes ORDER BY RAND() LIMIT 1');
        return $stmt->fetch() ?: null;
    }

    public function crear(string $texto, ?string $rutaFoto): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO mensajes (usuario_id, mensaje, fecha) VALUES (?, ?, NOW())'
        );
        $stmt->execute([$_SESSION['usuario_id'] ?? null, $texto]);
        return (int)$this->db->lastInsertId();
    }
}
