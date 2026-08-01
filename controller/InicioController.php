<?php
class InicioController
{
    private RecuerdoModel $recuerdoModel;
    private AlbumModel $albumModel;
    private FotoModel $fotoModel;
    private SeriesModel $seriesModel;

    public function __construct()
    {
        $this->recuerdoModel = new RecuerdoModel();
        $this->albumModel = new AlbumModel();
        $this->fotoModel = new FotoModel();
        $this->seriesModel = new SeriesModel();
    }

    public function index(): void
    {
        if (!Auth::haAccedido()) {
            header('Location: index.php?route=login');
            exit;
        }

        $albumes = $this->albumModel->obtenerTodos();
        $hitos = $this->armarHitos($albumes);

        // Secciones "fijas" que envuelven todo el recorrido (además de cada hito,
        // que también es su propia sección con su propia canción/color).
        $intro = [
            'id'      => 'intro',
            'label'   => 'Introducción',
            'tema'    => 'azul',
            'cancion' => $this->cancion('Perfect', 'Ed Sheeran', 'perfect.mp3', 'La canción con la que empieza todo.'),
        ];
        $viajes = [
            'id'      => 'viajes',
            'label'   => 'Viajes',
            'tema'    => 'celeste',
            'cancion' => $this->cancion('A Sky Full of Stars', 'Coldplay', 'sky-full-of-stars.mp3', 'Sonaba en cada viaje largo.'),
        ];
        $series = [
            'id'      => 'series',
            'label'   => 'Series y pelis',
            'tema'    => 'negro',
            'cancion' => $this->cancion('Photograph', 'Ed Sheeran', 'photograph.mp3', 'Nuestra playlist de las noches de maratón.'),
        ];
        $contador = [
            'id'      => 'contador-seccion',
            'label'   => 'Contador',
            'tema'    => 'violeta',
            'cancion' => $this->cancion('All of Me', 'John Legend', 'all-of-me.mp3', 'Para contar cada segundo juntos.'),
        ];
        $final = [
            'id'      => 'final',
            'label'   => 'Final',
            'tema'    => 'blanco',
            'cancion' => null, // no fuerza canción: respeta lo que esté sonando.
        ];

        // Orden real en el que aparecen en el scroll (para el nav de puntitos
        // y para el JSON que usa el reproductor).
        $secciones = array_merge([$intro], $hitos, [$viajes, $series, $contador, $final]);

        View::render('inicio', [
            'nombre1'          => APP_NAME_1,
            'nombre2'          => APP_NAME_2,
            'intro'            => $intro,
            'hitos'            => $hitos,
            'viajes_json'      => $this->armarViajesJson($albumes),
            'viajes'           => $viajes,
            'series_seccion'   => $series,
            'series'           => $this->seriesModel->obtenerTodas(),
            'contador_seccion' => $contador,
            'final'            => $final,
            'fecha_inicio'     => FECHA_INICIO_NOVIAZGO,
            'secciones'        => $secciones,
            'secciones_json'   => $this->seccionesToJson($secciones),
        ]);
    }

    private function armarHitos(array $albumes): array
    {
        $recuerdos = $this->recuerdoModel->obtenerTodosOrdenados();
        $hitos = [];
        $i = 0;

        // Mapa id de álbum => es_viaje, para poder excluir de las secciones
        // (los "hitos") las fotos que pertenecen a un álbum de viaje: esas
        // fotos ahora se muestran únicamente en el carrusel de viajes.
        $esViajePorAlbum = [];
        foreach ($albumes as $album) {
            $esViajePorAlbum[$album['id']] = !empty($album['es_viaje']);
        }

        // Pool de temas y canciones que se van repitiendo hito a hito.
        // EDITAR ACÁ: reemplazar título/artista/archivo/nota por las canciones reales
        // (y subir el mp3 correspondiente a public/audio/).
        $temaPool = ['rosa', 'celeste'];
        $cancionPool = [
            $this->cancion('Yellow', 'Coldplay', 'yellow.mp3', 'La escuchábamos cuando viajábamos en colectivo.'),
            $this->cancion('Home', 'Edward Sharpe & The Magnetic Zeros', 'home.mp3', 'Sonó el día de la mudanza.'),
            $this->cancion('Perfect Duet', 'Ed Sheeran & Beyoncé', 'perfect-duet.mp3', 'Una tarde cualquiera, en casa.'),
        ];

        // Cuántas fotos se ven "amontonadas" antes de necesitar el botón de expandir.
        $tamanioPila = 3;
        $angulos = [-8, 5, -3, 7, -5, 4]; // se repiten en rueda si hay más de 6 fotos en la pila

        foreach ($recuerdos as $recuerdo) {
            $fotos = [];
            $albumId = $recuerdo['album_id'] ?? null;
            $esAlbumDeViaje = $albumId && !empty($esViajePorAlbum[$albumId]);

            // Si el recuerdo apunta a un álbum de viaje, no traemos sus
            // fotos acá: ese álbum ya se ve, completo, en el carrusel de
            // viajes de más abajo. En la sección solo queda el texto.
            if (!empty($albumId) && !$esAlbumDeViaje) {
                $fotos = $this->fotoModel->obtenerPorAlbum((int)$albumId);
            }

            $fotosPila = [];
            foreach (array_slice($fotos, 0, $tamanioPila) as $idx => $foto) {
                $fotosPila[] = $foto + ['angulo' => $angulos[$idx % count($angulos)]];
            }
            $fotosExtra = array_slice($fotos, $tamanioPila);

            $mensaje = $recuerdo['mensaje'];
            $etiqueta = mb_substr($mensaje, 0, 30) . (mb_strlen($mensaje) > 30 ? '…' : '');

            // Título grande de la sección: si el recuerdo tiene su propio
            // 'titulo' (columna a agregar en mensajes) se usa ese; si no,
            // se arma uno corto a partir del mensaje.
            $titulo = !empty($recuerdo['titulo']) ? $recuerdo['titulo'] : $etiqueta;

            $hitos[] = [
                'id'           => 'hito-' . $i,
                'label'        => $etiqueta,
                'titulo'       => $titulo,
                'mensaje'      => $mensaje,
                'fecha'        => date('d-m-Y', strtotime($recuerdo['fecha'])),
                'alineacion'   => $i % 2 === 0 ? 'izquierda' : 'derecha',
                'fotos_pila'   => $fotosPila,
                'tiene_fotos'  => count($fotosPila) > 0,
                'fotos_extra'  => $fotosExtra,
                'tiene_extra'  => count($fotosExtra) > 0,
                'galeria_id'   => 'galeria-' . $i,
                'tema'         => $temaPool[$i % count($temaPool)],
                'cancion'      => $cancionPool[$i % count($cancionPool)],
            ];
            $i++;
        }

        return $hitos;
    }

    /**
     * Arma, en JSON, un viaje por cada álbum marcado como "es_viaje" (con
     * sus fotos). El JS del carrusel lo usa para ir pasando las fotos de
     * un viaje y, al terminar, seguir automáticamente con el próximo.
     *
     * Para marcar un álbum como viaje: UPDATE albumes SET es_viaje = 1
     * WHERE id = ... (ver database/schema.sql).
     */
    private function armarViajesJson(array $albumes): string
    {
        $viajes = [];
        foreach ($albumes as $album) {
            if (empty($album['es_viaje'])) {
                continue;
            }
            $fotos = array_map(function ($foto) {
                return [
                    'ruta'        => $foto['ruta'],
                    'descripcion' => $foto['descripcion'] ?? '',
                ];
            }, $this->fotoModel->obtenerPorAlbum((int)$album['id']));

            if (empty($fotos)) {
                continue;
            }

            $viajes[] = [
                'lugar' => $album['titulo'],
                'fotos' => $fotos,
            ];
        }

        $json = json_encode($viajes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return str_replace('</script', '<\/script', $json);
    }

    /**
     * Arma el array de una canción con su "nota" (la explicación que aparece
     * en el reproductor).
     */
    private function cancion(string $titulo, string $artista, string $archivo, string $nota): array
    {
        return [
            'titulo'  => $titulo,
            'artista' => $artista,
            'archivo' => 'public/audio/' . $archivo,
            'nota'    => $nota,
        ];
    }

    /**
     * Versión "plana" de las secciones, lista para el reproductor en JS:
     * solo id, tema y datos de la canción (o null si la sección no fuerza canción).
     */
    private function seccionesToJson(array $secciones): string
    {
        $plano = array_map(function ($s) {
            return [
                'id'      => $s['id'],
                'label'   => $s['label'],
                'tema'    => $s['tema'],
                'cancion' => $s['cancion'],
            ];
        }, $secciones);

        $json = json_encode($plano, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        // Por las dudas, evitar que un "</script>" dentro de un mensaje rompa el HTML.
        return str_replace('</script', '<\/script', $json);
    }
}