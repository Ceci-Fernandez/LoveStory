<?php
class MusicaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = getConnection();
    }

    public function obtenerTodas(): array
    {
        $stmt = $this->db->query('SELECT * FROM musica ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function crear(string $titulo, string $artista, ?string $spotifyUrl, ?string $youtubeUrl): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO musica (titulo, artista, spotify_url, youtube_url) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$titulo, $artista, $spotifyUrl, $youtubeUrl]);
        return (int)$this->db->lastInsertId();
    }
}
