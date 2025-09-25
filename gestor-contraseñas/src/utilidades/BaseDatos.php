<?php
/**
 * Clase para manejar la base de datos SQLite
 * Implementa patron singleton para una sola conexion
 */
class BaseDatos {
    private static $instancia = null;
    private $conexion;
    
    private function __construct() {
        try {
            // Crear directorio data si no existe
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $this->conexion = new PDO('sqlite:' . DB_PATH);
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear tablas si no existen
            $this->inicializarTablas();
            
        } catch (PDOException $e) {
            die("Error conectando a BD: " . $e->getMessage());
        }
    }
    
    public static function obtenerInstancia() {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }
    
    public function getConexion() {
        return $this->conexion;
    }
    
    private function inicializarTablas() {
        $sql = file_get_contents(__DIR__ . '/../../data/esquema.sql');
        $this->conexion->exec($sql);
    }
    
    // Ejecutar consulta simple
    public function consulta($sql, $parametros = []) {
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($parametros);
        return $stmt;
    }
}
?>