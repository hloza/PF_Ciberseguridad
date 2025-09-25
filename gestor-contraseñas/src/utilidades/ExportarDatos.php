<?php
/**
 * Utilidad para exportar contraseñas (backup)
 * Genera archivo JSON encriptado con todas las contraseñas
 */

require_once __DIR__ . '/../controladores/ControladorContraseñas.php';
require_once __DIR__ . '/../utilidades/Autenticacion.php';
require_once __DIR__ . '/../utilidades/Encriptacion.php';

class ExportarDatos {
    
    // Exportar todas las contraseñas en formato JSON encriptado
    public static function exportarJSON($passwordMaestra) {
        if (!Autenticacion::estaAutenticado()) {
            return ['exito' => false, 'mensaje' => 'No autorizado'];
        }
        
        try {
            $contraseñas = ControladorContraseñas::listar();
            $estadisticas = ControladorContraseñas::obtenerEstadisticas();
            
            // Preparar datos para export
            $datosExport = [
                'version' => APP_VERSION,
                'fecha_export' => date('Y-m-d H:i:s'),
                'total_contraseñas' => count($contraseñas),
                'estadisticas' => $estadisticas,
                'contraseñas' => []
            ];
            
            // Procesar cada contraseña (desencriptar temporalmente para re-encriptar con clave de backup)
            foreach ($contraseñas as $password) {
                $datosExport['contraseñas'][] = [
                    'id' => $password->getId(),
                    'sitio' => $password->getSitio(),
                    'usuario' => $password->getUsuario(),
                    'password_desencriptada' => $password->getPasswordDesencriptada(), // Se re-encriptará
                    'notas' => $password->getNotas(),
                    'fecha_creacion' => $password->getFechaCreacion(),
                    'fecha_modificacion' => $password->getFechaModificacion()
                ];
            }
            
            // Convertir a JSON
            $jsonDatos = json_encode($datosExport, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            // Encriptar JSON con la contraseña maestra para backup
            $claveBackup = hash('sha256', $passwordMaestra . 'backup_key_2025');
            $jsonEncriptado = Encriptacion::encriptar($jsonDatos, $claveBackup);
            
            if (!$jsonEncriptado) {
                return ['exito' => false, 'mensaje' => 'Error al encriptar backup'];
            }
            
            // Preparar datos para descarga
            $nombreArchivo = 'backup_passwords_' . date('Y-m-d_H-i-s') . '.gpw';
            
            return [
                'exito' => true,
                'datos_encriptados' => $jsonEncriptado,
                'nombre_archivo' => $nombreArchivo,
                'tamaño' => strlen($jsonEncriptado),
                'total_contraseñas' => count($contraseñas)
            ];
            
        } catch (Exception $e) {
            return [
                'exito' => false, 
                'mensaje' => 'Error interno: ' . $e->getMessage()
            ];
        }
    }
    
    // Importar contraseñas desde archivo backup
    public static function importarJSON($archivoContenido, $passwordMaestra) {
        if (!Autenticacion::estaAutenticado()) {
            return ['exito' => false, 'mensaje' => 'No autorizado'];
        }
        
        try {
            // Intentar desencriptar
            $claveBackup = hash('sha256', $passwordMaestra . 'backup_key_2025');
            $jsonDesencriptado = Encriptacion::desencriptar($archivoContenido, $claveBackup);
            
            if (!$jsonDesencriptado) {
                return ['exito' => false, 'mensaje' => 'Error al desencriptar backup - contraseña incorrecta'];
            }
            
            // Decodificar JSON
            $datosImport = json_decode($jsonDesencriptado, true);
            
            if (!$datosImport || !isset($datosImport['contraseñas'])) {
                return ['exito' => false, 'mensaje' => 'Formato de archivo inválido'];
            }
            
            // Validar estructura básica
            $contadorImportadas = 0;
            $errores = [];
            
            foreach ($datosImport['contraseñas'] as $passwordData) {
                try {
                    // Crear nueva contraseña
                    $nuevaPassword = new Contraseña(
                        $passwordData['sitio'],
                        $passwordData['usuario'], 
                        $passwordData['password_desencriptada'],
                        $passwordData['notas'] ?? ''
                    );
                    
                    if ($nuevaPassword->guardar()) {
                        $contadorImportadas++;
                    } else {
                        $errores[] = "Error guardando: {$passwordData['sitio']}";
                    }
                    
                } catch (Exception $e) {
                    $errores[] = "Error procesando {$passwordData['sitio']}: " . $e->getMessage();
                }
            }
            
            return [
                'exito' => true,
                'contraseñas_importadas' => $contadorImportadas,
                'total_en_backup' => count($datosImport['contraseñas']),
                'errores' => $errores,
                'version_backup' => $datosImport['version'] ?? 'desconocida',
                'fecha_backup' => $datosImport['fecha_export'] ?? 'desconocida'
            ];
            
        } catch (Exception $e) {
            return [
                'exito' => false,
                'mensaje' => 'Error procesando import: ' . $e->getMessage()
            ];
        }
    }
    
    // Generar estadisticas de seguridad
    public static function analizarSeguridad() {
        if (!Autenticacion::estaAutenticado()) {
            return null;
        }
        
        $contraseñas = ControladorContraseñas::listar();
        $analisis = [
            'total' => count($contraseñas),
            'debiles' => 0,
            'regulares' => 0,
            'fuertes' => 0,
            'duplicadas' => 0,
            'sitios_inseguros' => [],
            'recomendaciones' => []
        ];
        
        $passwordsVistas = [];
        
        foreach ($contraseñas as $password) {
            $passwordTexto = $password->getPasswordDesencriptada();
            
            // Analizar fortaleza
            $fortaleza = self::evaluarFortalezaAvanzada($passwordTexto);
            
            if ($fortaleza <= 2) {
                $analisis['debiles']++;
                $analisis['sitios_inseguros'][] = $password->getSitio();
            } elseif ($fortaleza <= 3) {
                $analisis['regulares']++;
            } else {
                $analisis['fuertes']++;
            }
            
            // Detectar duplicadas
            if (in_array($passwordTexto, $passwordsVistas)) {
                $analisis['duplicadas']++;
            } else {
                $passwordsVistas[] = $passwordTexto;
            }
        }
        
        // Generar recomendaciones
        if ($analisis['debiles'] > 0) {
            $analisis['recomendaciones'][] = "Tienes {$analisis['debiles']} contraseñas débiles que debes cambiar";
        }
        
        if ($analisis['duplicadas'] > 0) {
            $analisis['recomendaciones'][] = "Evita reutilizar contraseñas - tienes {$analisis['duplicadas']} duplicadas";
        }
        
        if ($analisis['total'] > 0) {
            $porcentajeSeguras = round(($analisis['fuertes'] / $analisis['total']) * 100, 1);
            $analisis['porcentaje_seguras'] = $porcentajeSeguras;
            
            if ($porcentajeSeguras < 70) {
                $analisis['recomendaciones'][] = "Solo el {$porcentajeSeguras}% de tus contraseñas son fuertes";
            }
        }
        
        return $analisis;
    }
    
    // Evaluación de fortaleza avanzada
    private static function evaluarFortalezaAvanzada($password) {
        $puntos = 0;
        
        // Longitud
        if (strlen($password) >= 8) $puntos++;
        if (strlen($password) >= 12) $puntos++;
        if (strlen($password) >= 16) $puntos++;
        
        // Variedad de caracteres
        if (preg_match('/[a-z]/', $password)) $puntos++;
        if (preg_match('/[A-Z]/', $password)) $puntos++;
        if (preg_match('/\d/', $password)) $puntos++;
        if (preg_match('/[^a-zA-Z\d]/', $password)) $puntos++;
        
        // Penalizar patrones débiles
        if (preg_match('/(.)\1{2,}/', $password)) $puntos--; // repetición
        if (preg_match('/123|abc|qwe|asd/i', $password)) $puntos--; // secuencias
        if (preg_match('/password|123456|qwerty/i', $password)) $puntos -= 2; // comunes
        
        return max(0, min(7, $puntos));
    }
}
?>