<?php
/**
 * Modelo para manejar las contraseñas
 * CRUD basico para la tabla de passwords
 */

require_once __DIR__ . '/../utilidades/BaseDatos.php';
require_once __DIR__ . '/../utilidades/Encriptacion.php';
require_once __DIR__ . '/../utilidades/Autenticacion.php';

class Contraseña {
    
    private $id;
    private $sitio;
    private $usuario;
    private $password_encriptada;
    private $notas;
    private $fecha_creacion;
    private $fecha_modificacion;
    
    public function __construct($sitio = '', $usuario = '', $password = '', $notas = '') {
        $this->sitio = $sitio;
        $this->usuario = $usuario;
        $this->notas = $notas;
        
        // Encriptar la contraseña si se proporciona
        if (!empty($password)) {
            $this->encriptarPassword($password);
        }
    }
    
    // Getters
    public function getId() { return $this->id; }
    public function getSitio() { return $this->sitio; }
    public function getUsuario() { return $this->usuario; }
    public function getNotas() { return $this->notas; }
    public function getFechaCreacion() { return $this->fecha_creacion; }
    public function getFechaModificacion() { return $this->fecha_modificacion; }
    
    // Setters
    public function setSitio($sitio) { $this->sitio = $sitio; }
    public function setUsuario($usuario) { $this->usuario = $usuario; }
    public function setNotas($notas) { $this->notas = $notas; }
    
    // Encriptar password usando la clave de sesion
    private function encriptarPassword($password) {
        $claveEncriptacion = Autenticacion::obtenerClaveEncriptacion();
        if ($claveEncriptacion) {
            $this->password_encriptada = Encriptacion::encriptar($password, $claveEncriptacion);
        }
    }
    
    // Desencriptar y obtener password
    public function getPasswordDesencriptada() {
        $claveEncriptacion = Autenticacion::obtenerClaveEncriptacion();
        if ($claveEncriptacion && $this->password_encriptada) {
            return Encriptacion::desencriptar($this->password_encriptada, $claveEncriptacion);
        }
        return null;
    }
    
    // Actualizar password
    public function actualizarPassword($nuevaPassword) {
        $this->encriptarPassword($nuevaPassword);
    }
    
    // Guardar en BD (crear o actualizar)
    public function guardar() {
        $bd = BaseDatos::obtenerInstancia();
        
        if (empty($this->id)) {
            // Crear nuevo registro
            $sql = "INSERT INTO contraseñas (sitio, usuario, password_encriptada, notas, fecha_creacion, fecha_modificacion) 
                    VALUES (?, ?, ?, ?, datetime('now'), datetime('now'))";
            
            $stmt = $bd->consulta($sql, [
                $this->sitio,
                $this->usuario, 
                $this->password_encriptada,
                $this->notas
            ]);
            
            if ($stmt->rowCount() > 0) {
                $this->id = $bd->getConexion()->lastInsertId();
                return true;
            }
        } else {
            // Actualizar existente
            $sql = "UPDATE contraseñas SET sitio = ?, usuario = ?, password_encriptada = ?, 
                    notas = ?, fecha_modificacion = datetime('now') WHERE id = ?";
            
            $stmt = $bd->consulta($sql, [
                $this->sitio,
                $this->usuario,
                $this->password_encriptada,
                $this->notas,
                $this->id
            ]);
            
            return $stmt->rowCount() > 0;
        }
        
        return false;
    }
    
    // Obtener todas las contraseñas
    public static function obtenerTodas() {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT * FROM contraseñas ORDER BY sitio ASC");
        
        $resultados = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $password = new self();
            $password->cargarDesdeBD($fila);
            $resultados[] = $password;
        }
        
        return $resultados;
    }
    
    // Obtener contraseña por ID
    public static function obtenerPorId($id) {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT * FROM contraseñas WHERE id = ?", [$id]);
        
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $password = new self();
            $password->cargarDesdeBD($fila);
            return $password;
        }
        
        return null;
    }
    
    // Buscar por sitio o usuario
    public static function buscar($termino) {
        $bd = BaseDatos::obtenerInstancia();
        $terminoBusqueda = "%{$termino}%";
        
        $stmt = $bd->consulta(
            "SELECT * FROM contraseñas WHERE sitio LIKE ? OR usuario LIKE ? ORDER BY sitio ASC", 
            [$terminoBusqueda, $terminoBusqueda]
        );
        
        $resultados = [];
        while ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $password = new self();
            $password->cargarDesdeBD($fila);
            $resultados[] = $password;
        }
        
        return $resultados;
    }
    
    // Eliminar contraseña
    public function eliminar() {
        if (empty($this->id)) {
            return false;
        }
        
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("DELETE FROM contraseñas WHERE id = ?", [$this->id]);
        
        return $stmt->rowCount() > 0;
    }
    
    // Contar total de contraseñas
    public static function contarTotal() {
        $bd = BaseDatos::obtenerInstancia();
        $stmt = $bd->consulta("SELECT COUNT(*) as total FROM contraseñas");
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado['total'];
    }
    
    // Cargar datos desde array de BD
    private function cargarDesdeBD($datos) {
        $this->id = $datos['id'];
        $this->sitio = $datos['sitio'];
        $this->usuario = $datos['usuario'];
        $this->password_encriptada = $datos['password_encriptada'];
        $this->notas = $datos['notas'];
        $this->fecha_creacion = $datos['fecha_creacion'];
        $this->fecha_modificacion = $datos['fecha_modificacion'];
    }
    
    // Validar datos antes de guardar
    public function validar() {
        $errores = [];
        
        if (empty($this->sitio)) {
            $errores[] = 'El sitio es obligatorio';
        }
        
        if (empty($this->usuario)) {
            $errores[] = 'El usuario es obligatorio';
        }
        
        if (empty($this->password_encriptada)) {
            $errores[] = 'La contraseña es obligatoria';
        }
        
        return $errores;
    }
}
?>