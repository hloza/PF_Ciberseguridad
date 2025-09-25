<?php
/**
 * Controlador para manejar operaciones de contraseñas
 * Maneja peticiones POST/GET relacionadas con passwords
 */

require_once __DIR__ . '/../modelos/Contraseña.php';
require_once __DIR__ . '/../utilidades/Encriptacion.php';

class ControladorContraseñas {
    
    // Procesar formulario de agregar/editar
    public static function procesarFormulario() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return null;
        }
        
        $accion = $_POST['accion'] ?? '';
        $respuesta = ['exito' => false, 'mensaje' => '', 'datos' => null];
        
        switch ($accion) {
            case 'agregar':
                $respuesta = self::agregar();
                break;
                
            case 'editar':
                $respuesta = self::editar();
                break;
                
            case 'eliminar':
                $respuesta = self::eliminar();
                break;
                
            default:
                $respuesta['mensaje'] = 'Accion no valida';
        }
        
        return $respuesta;
    }
    
    // Agregar nueva contraseña
    private static function agregar() {
        $sitio = trim($_POST['sitio'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $notas = trim($_POST['notas'] ?? '');
        
        // Validaciones basicas
        if (empty($sitio) || empty($usuario) || empty($password)) {
            return [
                'exito' => false,
                'mensaje' => 'Sitio, usuario y contraseña son obligatorios'
            ];
        }
        
        try {
            $contraseñaObj = new Contraseña($sitio, $usuario, $password, $notas);
            
            // Validar datos
            $errores = $contraseñaObj->validar();
            if (!empty($errores)) {
                return [
                    'exito' => false,
                    'mensaje' => implode(', ', $errores)
                ];
            }
            
            // Guardar
            if ($contraseñaObj->guardar()) {
                return [
                    'exito' => true,
                    'mensaje' => 'Contraseña guardada exitosamente',
                    'datos' => $contraseñaObj
                ];
            } else {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al guardar la contraseña'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    // Editar contraseña existente
    private static function editar() {
        $id = intval($_POST['id'] ?? 0);
        $sitio = trim($_POST['sitio'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $notas = trim($_POST['notas'] ?? '');
        
        if ($id <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'ID de contraseña no valido'
            ];
        }
        
        try {
            $contraseñaObj = Contraseña::obtenerPorId($id);
            if (!$contraseñaObj) {
                return [
                    'exito' => false,
                    'mensaje' => 'Contraseña no encontrada'
                ];
            }
            
            // Actualizar campos
            $contraseñaObj->setSitio($sitio);
            $contraseñaObj->setUsuario($usuario);
            $contraseñaObj->setNotas($notas);
            
            // Solo actualizar password si se proporciono una nueva
            if (!empty($password)) {
                $contraseñaObj->actualizarPassword($password);
            }
            
            // Validar y guardar
            $errores = $contraseñaObj->validar();
            if (!empty($errores)) {
                return [
                    'exito' => false,
                    'mensaje' => implode(', ', $errores)
                ];
            }
            
            if ($contraseñaObj->guardar()) {
                return [
                    'exito' => true,
                    'mensaje' => 'Contraseña actualizada correctamente'
                ];
            } else {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al actualizar la contraseña'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    // Eliminar contraseña
    private static function eliminar() {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            return [
                'exito' => false,
                'mensaje' => 'ID no valido'
            ];
        }
        
        try {
            $contraseñaObj = Contraseña::obtenerPorId($id);
            if (!$contraseñaObj) {
                return [
                    'exito' => false,
                    'mensaje' => 'Contraseña no encontrada'
                ];
            }
            
            if ($contraseñaObj->eliminar()) {
                return [
                    'exito' => true,
                    'mensaje' => 'Contraseña eliminada correctamente'
                ];
            } else {
                return [
                    'exito' => false,
                    'mensaje' => 'Error al eliminar la contraseña'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    // Listar todas las contraseñas
    public static function listar($termino_busqueda = '') {
        try {
            if (!empty($termino_busqueda)) {
                return Contraseña::buscar($termino_busqueda);
            } else {
                return Contraseña::obtenerTodas();
            }
        } catch (Exception $e) {
            error_log("Error al listar contraseñas: " . $e->getMessage());
            return [];
        }
    }
    
    // Obtener contraseña por ID para edicion
    public static function obtenerParaEditar($id) {
        try {
            return Contraseña::obtenerPorId($id);
        } catch (Exception $e) {
            error_log("Error al obtener contraseña para editar: " . $e->getMessage());
            return null;
        }
    }
    
    // Obtener estadisticas
    public static function obtenerEstadisticas() {
        try {
            return [
                'total_contraseñas' => Contraseña::contarTotal(),
                'sitios_unicos' => self::contarSitiosUnicos(),
                'ultima_agregada' => self::obtenerUltimaAgregada()
            ];
        } catch (Exception $e) {
            error_log("Error al obtener estadisticas: " . $e->getMessage());
            return [
                'total_contraseñas' => 0,
                'sitios_unicos' => 0,
                'ultima_agregada' => null
            ];
        }
    }
    
    // Contar sitios unicos
    private static function contarSitiosUnicos() {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT COUNT(DISTINCT sitio) as total FROM contraseñas");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'];
    }
    
    // Obtener fecha de ultima contraseña agregada
    private static function obtenerUltimaAgregada() {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT fecha_creacion FROM contraseñas ORDER BY fecha_creacion DESC LIMIT 1");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['fecha_creacion'] : null;
    }
    
    // Generar contraseña segura aleatoria
    public static function generarPasswordSegura($longitud = 12) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $longitud; $i++) {
            $password .= $caracteres[random_int(0, strlen($caracteres) - 1)];
        }
        
        return $password;
    }
}
?>