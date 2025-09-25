<?php
/**
 * Clase para manejar autenticacion de usuarios
 * Maneja la contraseña maestra y sesiones
 */

require_once 'BaseDatos.php';
require_once 'Encriptacion.php';

class Autenticacion {
    
    // Verificar si ya existe usuario maestro
    public static function existeUsuarioMaestro() {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT COUNT(*) as total FROM usuario_maestro");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'] > 0;
    }
    
    // Crear usuario maestro (primera vez)
    public static function crearUsuarioMaestro($passwordMaestra) {
        if (self::existeUsuarioMaestro()) {
            return false; // Ya existe
        }
        
        $datosHash = Encriptacion::generarHash($passwordMaestra);
        
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta(
            "INSERT INTO usuario_maestro (password_hash, salt) VALUES (?, ?)",
            [$datosHash['hash'], $datosHash['salt']]
        );
        
        return $stmt->rowCount() > 0;
    }
    
    // Verificar contraseña maestra
    public static function verificarContraseñaMaestra($password) {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT password_hash, salt FROM usuario_maestro LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return false;
        }
        
        return Encriptacion::verificarHash($password, $usuario['password_hash'], $usuario['salt']);
    }
    
    // Crear sesion de usuario autenticado
    public static function crearSesion($passwordMaestra = '') {
        $_SESSION['usuario_autenticado'] = true;
        $_SESSION['tiempo_login'] = time();
        
        // Generar clave de sesion para encriptar/desencriptar passwords
        if (!empty($passwordMaestra)) {
            $bd = BaseDatos::obtenerInstancia();
            $stmt = $bd->consulta("SELECT salt FROM usuario_maestro LIMIT 1");
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario) {
                $_SESSION['clave_encriptacion'] = Encriptacion::derivarClave($passwordMaestra, $usuario['salt']);
            }
        }
        
        return true;
    }
    
    // Cerrar sesion
    public static function cerrarSesion() {
        session_unset();
        session_destroy();
        header('Location: index.php?pagina=login');
        exit();
    }
    
    // Verificar si esta autenticado
    public static function estaAutenticado() {
        return isset($_SESSION['usuario_autenticado']) && $_SESSION['usuario_autenticado'] === true;
    }
    
    // Obtener clave de encriptacion de la sesion
    public static function obtenerClaveEncriptacion() {
        return isset($_SESSION['clave_encriptacion']) ? $_SESSION['clave_encriptacion'] : null;
    }
}
?>