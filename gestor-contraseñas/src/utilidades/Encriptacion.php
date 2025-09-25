<?php
/**
 * Clase para manejar encriptacion y desencriptacion
 * Usa AES-256-CBC para cifrado simetrico
 */
class Encriptacion {
    
    // Encriptar texto con AES-256-CBC
    public static function encriptar($texto, $clave) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(ENCRYPT_METHOD));
        $textoEncriptado = openssl_encrypt($texto, ENCRYPT_METHOD, $clave, 0, $iv);
        
        if ($textoEncriptado === false) {
            return false;
        }
        
        // Combinar IV con texto encriptado para poder desencriptar despues
        return base64_encode($iv . $textoEncriptado);
    }
    
    public static function desencriptar($textoEncriptado, $clave) {
        $datos = base64_decode($textoEncriptado);
        $ivLength = openssl_cipher_iv_length(ENCRYPT_METHOD);
        
        if (strlen($datos) < $ivLength) {
            return false;
        }
        
        $iv = substr($datos, 0, $ivLength);
        $encrypted = substr($datos, $ivLength);
        
        return openssl_decrypt($encrypted, ENCRYPT_METHOD, $clave, 0, $iv);
    }
    
    // Generar hash seguro con salt
    public static function generarHash($texto, $salt = '') {
        if (empty($salt)) {
            $salt = self::generarSalt();
        }
        
        $hash = hash(HASH_ALGO, $salt . $texto);
        return ['hash' => $hash, 'salt' => $salt];
    }
    
    // Verificar hash
    public static function verificarHash($texto, $hash, $salt) {
        $hashCalculado = hash(HASH_ALGO, $salt . $texto);
        return hash_equals($hash, $hashCalculado);
    }
    
    // Generar salt aleatorio
    public static function generarSalt($longitud = 16) {
        return bin2hex(random_bytes($longitud));
    }
    
    // Generar clave derivada de contraseña maestra para encriptar passwords
    public static function derivarClave($passwordMaestra, $salt) {
        return hash('sha256', $passwordMaestra . $salt);
    }
}
?>