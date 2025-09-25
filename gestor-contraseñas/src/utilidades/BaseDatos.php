<?php
/**
 * Clase para manejar la base de datos SQLite
 * Implementa patron singleton para una sola conexion
 */
class BaseDatos {
    private static $instancia = null;
    private $conexion;
    
    private function __construct() {
        // TODO: implementar conexion SQLite
    }
    
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    // TODO: metodos para ejecutar consultas
}
?>