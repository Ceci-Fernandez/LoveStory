<?php
/**
 * Cápsulas del tiempo: mensajes bloqueados hasta una fecha determinada.
 */
class MensajeModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerDisponibles(DateTime $ahora): array
    {
        $stmt = $this->db->prepare('SELECT * FROM mensajes WHERE fecha <= ? ORDER BY fecha ASC');
        $stmt->execute([$ahora->format('Y-m-d H:i:s')]);
        return $stmt->fetchAll();
    }

    public function crear(int $usuarioId, string $mensaje, string $fechaApertura): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO mensajes (usuario_id, mensaje, fecha) VALUES (?, ?, ?)'
        );
        $stmt->execute([$usuarioId, $mensaje, $fechaApertura]);
        return (int)$this->db->lastInsertId();
    }
}
