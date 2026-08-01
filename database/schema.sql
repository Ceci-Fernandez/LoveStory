-- Esquema de base de datos para LoveStory
-- Crear la BD primero: CREATE DATABASE lovestory CHARACTER SET utf8mb4;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- guardar con password_hash()
    foto_perfil VARCHAR(255),
    rol ENUM('admin', 'usuario') DEFAULT 'usuario'
);

CREATE TABLE albumes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    fecha DATE
);

CREATE TABLE fotos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    album_id INT NOT NULL,
    ruta VARCHAR(255) NOT NULL,
    descripcion TEXT,
    fecha DATE,
    FOREIGN KEY (album_id) REFERENCES albumes(id) ON DELETE CASCADE
);

CREATE TABLE viajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8)
);

CREATE TABLE musica (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    artista VARCHAR(150),
    spotify_url VARCHAR(255),
    youtube_url VARCHAR(255)
);

CREATE TABLE series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    plataforma VARCHAR(100),
    puntaje TINYINT
);

CREATE TABLE mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    mensaje TEXT NOT NULL,
    fecha DATETIME NOT NULL, -- también sirve como fecha de apertura para cápsulas del tiempo
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE estadisticas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cantidad_fotos INT DEFAULT 0,
    cantidad_viajes INT DEFAULT 0,
    cantidad_series INT DEFAULT 0
);
 INSERT INTO mensajes( usuario_id, mensaje, fecha) 
 VALUES (NULL, 'Nos conocimos en ..., sera nuestro secreto', '2023-01-15 20:00:00');
 TRUNCATE TABLE mensajes;

INSERT INTO mensajes (usuario_id, mensaje, fecha) VALUES
(NULL, 'Nos vimos por primera vez. Había una amiga en el medio.', '2019-12-28 20:00:00'),
(NULL, 'Nuestra segunda cita. Ya solos los dos.', '2020-12-11 20:00:00'),
(NULL, 'Nuestra tercera cita. Un día muy especial que marcó un antes y un después.', '2020-12-18 20:00:00'),
(NULL, 'Nos pusimos de novios al fin...', '2023-08-01 20:00:00');

INSERT INTO albumes (titulo, descripcion, fecha) VALUES
('Nuestros primeros días', 'Conociendonos', '2020-12-19');

ALTER TABLE mensajes ADD COLUMN album_id INT NULL;
ALTER TABLE mensajes ADD CONSTRAINT fk_mensajes_album
  FOREIGN KEY (album_id) REFERENCES albumes(id) ON DELETE SET NULL;

  INSERT INTO series (nombre, plataforma, puntaje) VALUES
('Marianne', 'Stremio', 5),
('Vikingose', 'Cuevana', 4);
UPDATE mensajes SET album_id = 1 WHERE mensaje LIKE 'Nos conociamos';

-- ============================================================
-- NUEVO: distinguir álbumes de viaje del resto de álbumes.
-- Los álbumes marcados con es_viaje = 1 dejan de mostrarse dentro de
-- las secciones (hitos) y pasan a mostrarse únicamente en el carrusel
-- automático de "Nuestros viajes".
-- ============================================================
ALTER TABLE albumes ADD COLUMN es_viaje TINYINT(1) NOT NULL DEFAULT 0;

-- Marcá acá cada álbum que sea un viaje (reemplazá el id o el título
-- por los reales de tu base). Ejemplo:
-- UPDATE albumes SET es_viaje = 1 WHERE titulo = 'Bariloche';
-- UPDATE albumes SET es_viaje = 1 WHERE id IN (2, 3, 4);

-- ============================================================
-- NUEVO: título grande opcional para cada recuerdo/hito.
-- Si se deja NULL, se arma automáticamente a partir del mensaje.
-- ============================================================
ALTER TABLE mensajes ADD COLUMN titulo VARCHAR(150) NULL;

-- Ejemplo para cargarle un título a un recuerdo puntual:
-- UPDATE mensajes SET titulo = 'Nuestra primera cita' WHERE id = 2;

UPDATE albumes SET es_viaje = 1 WHERE titulo IN ('Bariloche', 'El Chaltén', 'Ushuaia');