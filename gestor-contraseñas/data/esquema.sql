-- Script SQL para crear la base de datos inicial
-- Base de datos SQLite para el gestor de contraseñas

-- Tabla para la contraseña maestra (solo un registro)
CREATE TABLE IF NOT EXISTS usuario_maestro (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    password_hash TEXT NOT NULL,
    salt TEXT NOT NULL,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tabla para almacenar las contraseñas encriptadas
CREATE TABLE IF NOT EXISTS contraseñas (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sitio TEXT NOT NULL,
    usuario TEXT NOT NULL,
    password_encriptada TEXT NOT NULL,
    notas TEXT,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Indice para busquedas rapidas por sitio
CREATE INDEX IF NOT EXISTS idx_sitio ON contraseñas(sitio);

-- Indice para busquedas por usuario  
CREATE INDEX IF NOT EXISTS idx_usuario ON contraseñas(usuario);