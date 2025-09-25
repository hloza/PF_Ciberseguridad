<?php
/**
 * Contiene constantes y configuraciones basicas
 */

// Configuracion de la base de datos
define('DB_PATH', __DIR__ . '/../../data/passwords.db');

// Configuracion de encriptacion
define('ENCRYPT_METHOD', 'AES-256-CBC');
define('HASH_ALGO', 'sha256');

// Configuracion de sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // cambiar a 1 en produccion con HTTPS

session_start();

// Configuracion de timezone
date_default_timezone_set('America/Mexico_City');

// Mostrar errores solo en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Constantes de la aplicacion
define('APP_NAME', 'Gestor de Contraseñas');
define('APP_VERSION', '2.0.0');
define('DESARROLLADOR', 'Estudiante Maestria Ciberseguridad');

// Configuración de seguridad adicional
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_TIMEOUT', 1800); // 30 minutos
define('MAX_LOGIN_ATTEMPTS', 5);
define('BACKUP_KEY_SUFFIX', 'backup_key_2025');

?>