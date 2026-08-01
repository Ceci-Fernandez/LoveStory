# LoveStory

Aplicación web en PHP (MVC) para armar "la página de aniversario" propia,
con panel de administración, timeline animado, álbumes, música, contador,
y sorpresas (cápsulas del tiempo, ruleta, frasco de recuerdos, easter eggs).

## Estructura

```
LoveStory/
├── index.php              Front controller (todas las rutas pasan por acá)
├── config/
│   ├── config.php          Configuración general, autoload, sesión
│   └── database.php        Conexión PDO a MySQL
├── public/                 Assets accesibles desde el navegador
│   ├── css/style.css
│   ├── js/main.js
│   ├── images/
│   ├── uploads/             Fotos subidas por el panel admin
│   └── audio/
├── controller/              Un controller por sección
├── model/                   Un model por tabla / entidad
├── view/                    Plantillas .mustache
├── helpers/                 Router, Auth, View (utilidades transversales)
└── database/
    └── schema.sql           Script para crear todas las tablas
```

## Cómo correrlo (versión 1)

1. Crear la base de datos:
   ```sql
   CREATE DATABASE lovestory CHARACTER SET utf8mb4;
   ```
2. Importar el esquema:
   ```bash
   mysql -u root -p lovestory < database/schema.sql
   ```
3. Ajustar credenciales en `config/database.php`.
4. (Opcional pero recomendado) Instalar Mustache vía Composer para que las
   vistas `.mustache` se rendericen con el motor real:
   ```bash
   composer require mustache/mustache
   ```
   Si no lo instalás, `helpers/View.php` cae en un modo "fallback" que busca
   un archivo `.php` equivalente — sirve para arrancar rápido, pero conviene
   migrar a Mustache o Twig antes de la versión 2.
5. Levantar un servidor local:
   ```bash
   php -S localhost:8000
   ```
6. Entrar a `http://localhost:8000/index.php?route=login`.

## Rutas principales

| Ruta                     | Controller           | Descripción                          |
|---------------------------|-----------------------|----------------------------------------|
| `login`                   | LoginController        | Pregunta de acceso                     |
| `home`                    | HomeController          | Portada / intro                        |
| `timeline`                | TimelineController      | Línea de tiempo                        |
| `album`, `album-ver`      | AlbumController         | Listado y detalle de álbumes           |
| `musica`                  | MusicController          | Canciones compartidas                  |
| `sorpresa`                | SurpriseController       | Cápsulas del tiempo                    |
| `recuerdo-aleatorio`      | MemoryController         | Frasco de recuerdos (JSON)             |
| `admin`, `admin-login`    | AdminController          | Panel privado                          |
| `api-estadisticas`, `api-ruleta` | ApiController      | Endpoints JSON para el frontend        |

## Roadmap sugerido

- **v1**: login con pregunta, portada, timeline, álbumes, contador.
- **v2**: panel administrador, subida de fotos, música, series, viajes.
- **v3**: mapa de recuerdos, cápsulas del tiempo, recuerdos aleatorios,
  easter eggs, animaciones avanzadas.

## La experiencia "álbum digital" (página `inicio`)

La página principal (`view/inicio.mustache`) ya no es una web de empresa: es un scroll
narrativo, con:

- **Un capítulo por pantalla**: la intro, cada hito/recuerdo, viajes, series, contador
  y un cierre, cada uno con su propio color (`tema-azul`, `tema-rosa`, `tema-celeste`,
  `tema-negro`, `tema-violeta`, `tema-blanco` en `public/css/style.css`).
- **Reproductor fijo** abajo a la derecha (`public/js/reproductor.js`), con play/pausa,
  anterior/siguiente, barra de progreso, volumen y una notita ("La escuchábamos
  cuando...") por canción.
- **Modo historia**: al entrar por primera vez a una sección, suena sola la canción
  asociada, con un fade de ~2s entre una y otra. Si el usuario toca "anterior" o
  "siguiente" a mano, el modo historia se apaga (se puede reactivar con el chip
  "🔗 Modo historia").
- **Indicador lateral** tipo línea de tiempo (los puntitos a la izquierda), en vez de
  un menú tradicional.
- Animaciones de aparición suaves con [AOS](https://michalsnik.github.io/aos/) (vía CDN).

### Cómo cargar las canciones reales

Las canciones (título, artista, nombre de archivo y "nota") están definidas en
`controller/InicioController.php`, en el método `armarHitos()` (una por cada hito,
repitiendo un pool de 3) y en `index()` (intro, viajes, series, contador). Son
placeholders para que edites:

1. Cambiá título/artista/nota por los reales.
2. Subí el .mp3 correspondiente a `public/audio/`.
3. El nombre de archivo que pongas en el controller (por ej. `'yellow.mp3'`) tiene que
   coincidir con el archivo subido.

Si una sección no tiene canción asociada (por ahora, el Final), el reproductor
simplemente no la corta ni la cambia — respeta lo que esté sonando.

## Notas de seguridad para producción

- Las contraseñas deben guardarse siempre con `password_hash()` /
  `password_verify()` (ya contemplado en `UsuarioModel`).
- Validar y sanitizar cualquier archivo subido en `MemoryController::agregar`
  y en el panel admin (tipo MIME real, tamaño máximo, extensión permitida).
- Cambiar `ACCESS_ANSWER` en `config/config.php` por el valor real antes de
  publicar el sitio, y no dejarlo en el repositorio si es público.
