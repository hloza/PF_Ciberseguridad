<?php
require_once __DIR__ . '/../includes/cabecera.php';
require_once __DIR__ . '/../utilidades/ExportarDatos.php';

// Procesar formularios de export/import
$resultado = ['tipo' => '', 'mensaje' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'exportar':
                $passwordMaestra = $_POST['password_maestra'] ?? '';
                if (empty($passwordMaestra)) {
                    $resultado = ['tipo' => 'error', 'mensaje' => 'La contraseña maestra es obligatoria'];
                } else {
                    $exportResult = ExportarDatos::exportarJSON($passwordMaestra);
                    if ($exportResult['exito']) {
                        // Preparar descarga
                        header('Content-Type: application/octet-stream');
                        header('Content-Disposition: attachment; filename="' . $exportResult['nombre_archivo'] . '"');
                        header('Content-Length: ' . $exportResult['tamaño']);
                        echo $exportResult['datos_encriptados'];
                        exit;
                    } else {
                        $resultado = ['tipo' => 'error', 'mensaje' => $exportResult['mensaje']];
                    }
                }
                break;
                
            case 'importar':
                if (isset($_FILES['archivo_backup']) && $_FILES['archivo_backup']['error'] === UPLOAD_ERR_OK) {
                    $passwordMaestra = $_POST['password_import'] ?? '';
                    if (empty($passwordMaestra)) {
                        $resultado = ['tipo' => 'error', 'mensaje' => 'La contraseña maestra es obligatoria'];
                    } else {
                        $contenidoArchivo = file_get_contents($_FILES['archivo_backup']['tmp_name']);
                        $importResult = ExportarDatos::importarJSON($contenidoArchivo, $passwordMaestra);
                        
                        if ($importResult['exito']) {
                            $mensaje = "✅ Import exitoso: {$importResult['contraseñas_importadas']}/{$importResult['total_en_backup']} contraseñas";
                            if (!empty($importResult['errores'])) {
                                $mensaje .= " (con " . count($importResult['errores']) . " errores)";
                            }
                            $resultado = ['tipo' => 'exito', 'mensaje' => $mensaje];
                        } else {
                            $resultado = ['tipo' => 'error', 'mensaje' => $importResult['mensaje']];
                        }
                    }
                } else {
                    $resultado = ['tipo' => 'error', 'mensaje' => 'Debe seleccionar un archivo de backup válido'];
                }
                break;
        }
    }
}

// Obtener análisis de seguridad
$analisisSeguridad = ExportarDatos::analizarSeguridad();
?>

<div class="contenedor">
    <div class="cabecera-seccion">
        <h2>🛡️ Backup y Seguridad</h2>
        <p>Exportar e importar contraseñas de forma segura</p>
    </div>

    <?php if ($resultado['mensaje']): ?>
        <div class="mensaje mensaje-<?= $resultado['tipo'] ?>">
            <?= htmlspecialchars($resultado['mensaje']) ?>
        </div>
    <?php endif; ?>

    <!-- Análisis de Seguridad -->
    <?php if ($analisisSeguridad): ?>
    <div class="tarjeta">
        <h3>📊 Análisis de Seguridad</h3>
        <div class="estadisticas-seguridad">
            <div class="stat-item">
                <span class="stat-numero"><?= $analisisSeguridad['total'] ?></span>
                <span class="stat-label">Total Contraseñas</span>
            </div>
            
            <div class="stat-item stat-fuerte">
                <span class="stat-numero"><?= $analisisSeguridad['fuertes'] ?></span>
                <span class="stat-label">Fuertes</span>
            </div>
            
            <div class="stat-item stat-regular">
                <span class="stat-numero"><?= $analisisSeguridad['regulares'] ?></span>
                <span class="stat-label">Regulares</span>
            </div>
            
            <div class="stat-item stat-debil">
                <span class="stat-numero"><?= $analisisSeguridad['debiles'] ?></span>
                <span class="stat-label">Débiles</span>
            </div>
            
            <?php if ($analisisSeguridad['duplicadas'] > 0): ?>
            <div class="stat-item stat-duplicada">
                <span class="stat-numero"><?= $analisisSeguridad['duplicadas'] ?></span>
                <span class="stat-label">Duplicadas</span>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if (isset($analisisSeguridad['porcentaje_seguras'])): ?>
        <div class="barra-seguridad">
            <div class="barra-progreso">
                <div class="progreso-fill" style="width: <?= $analisisSeguridad['porcentaje_seguras'] ?>%"></div>
            </div>
            <span><?= $analisisSeguridad['porcentaje_seguras'] ?>% de contraseñas seguras</span>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($analisisSeguridad['recomendaciones'])): ?>
        <div class="recomendaciones-seguridad">
            <h4>💡 Recomendaciones:</h4>
            <ul>
                <?php foreach ($analisisSeguridad['recomendaciones'] as $recomendacion): ?>
                    <li><?= htmlspecialchars($recomendacion) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($analisisSeguridad['sitios_inseguros'])): ?>
        <div class="sitios-inseguros">
            <h4>⚠️ Sitios con contraseñas débiles:</h4>
            <div class="lista-sitios">
                <?php foreach ($analisisSeguridad['sitios_inseguros'] as $sitio): ?>
                    <span class="sitio-inseguro"><?= htmlspecialchars($sitio) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="seccion-backup">
        <!-- Exportar Contraseñas -->
        <div class="tarjeta">
            <h3>📤 Exportar Contraseñas</h3>
            <p>Crea un backup encriptado de todas tus contraseñas</p>
            
            <form method="POST" class="formulario-backup">
                <input type="hidden" name="accion" value="exportar">
                
                <div class="campo">
                    <label for="password_maestra_export">Contraseña Maestra:</label>
                    <div class="input-password-group">
                        <input type="password" id="password_maestra_export" name="password_maestra" required>
                        <button type="button" class="boton-toggle" onclick="togglePassword('password_maestra_export')" title="Mostrar contraseña">👁️</button>
                    </div>
                    <small>Necesaria para cifrar el backup</small>
                </div>
                
                <div class="campo">
                    <div class="info-export">
                        <p>🔐 <strong>El backup incluye:</strong></p>
                        <ul>
                            <li>Todas las contraseñas (re-encriptadas)</li>
                            <li>Metadatos y estadísticas</li>
                            <li>Fechas de creación y modificación</li>
                        </ul>
                        <p><strong>Formato:</strong> Archivo .gpw encriptado con AES-256</p>
                    </div>
                </div>
                
                <button type="submit" class="boton boton-primario">
                    📥 Descargar Backup
                </button>
            </form>
        </div>

        <!-- Importar Contraseñas -->
        <div class="tarjeta">
            <h3>📥 Importar Contraseñas</h3>
            <p>Restaurar contraseñas desde un archivo de backup</p>
            
            <form method="POST" enctype="multipart/form-data" class="formulario-backup">
                <input type="hidden" name="accion" value="importar">
                
                <div class="campo">
                    <label for="archivo_backup">Archivo de Backup (.gpw):</label>
                    <input type="file" id="archivo_backup" name="archivo_backup" accept=".gpw" required>
                    <small>Solo archivos .gpw generados por este sistema</small>
                </div>
                
                <div class="campo">
                    <label for="password_import">Contraseña Maestra del Backup:</label>
                    <div class="input-password-group">
                        <input type="password" id="password_import" name="password_import" required>
                        <button type="button" class="boton-toggle" onclick="togglePassword('password_import')" title="Mostrar contraseña">👁️</button>
                    </div>
                    <small>La contraseña usada al crear el backup</small>
                </div>
                
                <div class="campo">
                    <div class="advertencia">
                        <p>⚠️ <strong>Advertencia:</strong></p>
                        <ul>
                            <li>Las contraseñas importadas se agregarán a las existentes</li>
                            <li>No se reemplazarán contraseñas duplicadas</li>
                            <li>El proceso puede tomar unos momentos</li>
                        </ul>
                    </div>
                </div>
                
                <button type="submit" class="boton boton-secundario">
                    📤 Importar Backup
                </button>
            </form>
        </div>
    </div>

    <!-- Consejos de Seguridad -->
    <div class="tarjeta consejos-seguridad">
        <h3>💡 Consejos de Seguridad</h3>
        <div class="consejos-grid">
            <div class="consejo">
                <span class="consejo-icono">🔒</span>
                <div>
                    <h4>Backups Regulares</h4>
                    <p>Realiza backups periódicamente y guárdalos en lugares seguros</p>
                </div>
            </div>
            
            <div class="consejo">
                <span class="consejo-icono">🔑</span>
                <div>
                    <h4>Contraseña Maestra Fuerte</h4>
                    <p>Usa una contraseña única y compleja para tu gestor</p>
                </div>
            </div>
            
            <div class="consejo">
                <span class="consejo-icono">🔄</span>
                <div>
                    <h4>Actualiza Contraseñas Débiles</h4>
                    <p>Cambia regularmente las contraseñas marcadas como débiles</p>
                </div>
            </div>
            
            <div class="consejo">
                <span class="consejo-icono">❌</span>
                <div>
                    <h4>Evita Duplicados</h4>
                    <p>Usa contraseñas únicas para cada sitio o servicio</p>
                </div>
            </div>
        </div>
    </div>

    <div class="acciones-navegacion">
        <a href="?vista=panel_principal" class="boton boton-secundario">← Volver al Panel</a>
    </div>
</div>

<style>
.seccion-backup {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .seccion-backup {
        grid-template-columns: 1fr;
    }
}

.estadisticas-seguridad {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 2px solid #e9ecef;
}

.stat-numero {
    display: block;
    font-size: 2em;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    display: block;
    font-size: 0.9em;
    color: #666;
}

.stat-fuerte { border-color: #28a745; }
.stat-fuerte .stat-numero { color: #28a745; }

.stat-regular { border-color: #ffc107; }
.stat-regular .stat-numero { color: #ffc107; }

.stat-debil { border-color: #dc3545; }
.stat-debil .stat-numero { color: #dc3545; }

.stat-duplicada { border-color: #fd7e14; }
.stat-duplicada .stat-numero { color: #fd7e14; }

.barra-seguridad {
    margin: 15px 0;
}

.barra-progreso {
    width: 100%;
    height: 20px;
    background: #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 5px;
}

.progreso-fill {
    height: 100%;
    background: linear-gradient(90deg, #dc3545, #ffc107, #28a745);
    transition: width 0.5s ease;
}

.recomendaciones-seguridad, .sitios-inseguros {
    margin-top: 15px;
    padding: 15px;
    background: #fff3cd;
    border-radius: 5px;
    border: 1px solid #ffeaa7;
}

.lista-sitios {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}

.sitio-inseguro {
    background: #dc3545;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
}

.formulario-backup {
    max-width: 400px;
}

.input-password-group {
    display: flex;
    align-items: center;
}

.input-password-group input {
    flex: 1;
    margin-right: 10px;
}

.boton-toggle {
    padding: 8px 12px;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.boton-toggle:hover {
    background: #5a6268;
}

.info-export, .advertencia {
    padding: 15px;
    border-radius: 5px;
    margin: 10px 0;
}

.info-export {
    background: #d1ecf1;
    border: 1px solid #bee5eb;
}

.advertencia {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
}

.consejos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.consejo {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.consejo-icono {
    font-size: 2em;
}

.consejo h4 {
    margin: 0 0 8px 0;
    color: #333;
}

.consejo p {
    margin: 0;
    color: #666;
    font-size: 0.9em;
}
</style>

<?php require_once __DIR__ . '/../includes/pie_pagina.php'; ?>