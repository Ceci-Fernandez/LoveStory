<?php
/**
 * Endpoints JSON usados por JS del frontend (mapa de recuerdos, ruleta, etc.)
 */
class ApiController
{
    public function estadisticas(): void
    {
        // TODO: calcular desde EstadisticaModel / consultas reales
        header('Content-Type: application/json');
        echo json_encode([
            'fotos' => 0,
            'viajes' => 0,
            'series' => 0,
            'mates' => 0,
        ]);
    }

    public function ruleta(): void
    {
        $opciones = ['🍕 Salir a comer', '🎬 Noche de cine', '🚶 Caminar', '🎲 Juegos de mesa'];
        header('Content-Type: application/json');
        echo json_encode(['resultado' => $opciones[array_rand($opciones)]]);
    }
}
