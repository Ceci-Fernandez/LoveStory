<?php
class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function verificarCredenciales(string $nombre, string $password): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE nombre = ? LIMIT 1');
        $stmt->execute([$nombre]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            return $usuario;
        }
        return null;
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }
}
