<?php
/**
 * Modelo para manejar las contraseñas
 * CRUD basico para la tabla de passwords
 */
class Contraseña {
    
    private $id;
    private $sitio;
    private $usuario;
    private $password_encriptada;
    private $fecha_creacion;
    
    // TODO: implementar constructor y metodos
    
    public function guardar() {
        // TODO: guardar en BD
    }
    
    public static function obtenerTodas() {
        // TODO: traer todas las contraseñas  
        return [];
    }
    
    public static function obtenerPorId($id) {
        // TODO: obtener una contraseña por ID
    }
    
    public function eliminar() {
        // TODO: eliminar contraseña
    }
}
?>