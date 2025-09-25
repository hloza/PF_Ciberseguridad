<?php
/**
 * Vista para listar contraseñas guardadas
 */

require_once 'controladores/ControladorContraseñas.php';

$mensaje = '';
$tipoMensaje = '';

// Procesar acciones (eliminar principalmente)
$resultado = ControladorContraseñas::procesarFormulario();
if ($resultado) {
    $mensaje = $resultado['mensaje'];
    $tipoMensaje = $resultado['exito'] ? 'exito' : 'error';
}

// Mensajes desde URL (ej: despues de editar)
if (isset($_GET['mensaje'])) {
    switch($_GET['mensaje']) {
        case 'editada':
            $mensaje = 'Contraseña actualizada correctamente';
            $tipoMensaje = 'exito';
            break;
        case 'eliminada':
            $mensaje = 'Contraseña eliminada correctamente';
            $tipoMensaje = 'exito';
            break;
    }
}

if (isset($_GET['error'])) {
    switch($_GET['error']) {
        case 'no_encontrada':
            $mensaje = 'La contraseña solicitada no fue encontrada';
            $tipoMensaje = 'error';
            break;
    }
}

// Obtener termino de busqueda
$terminoBusqueda = trim($_GET['buscar'] ?? '');

// Obtener lista de contraseñas
$contraseñas = ControladorContraseñas::listar($terminoBusqueda);

?>

<div class="listar-passwords">
    <div class="header-seccion">
        <h2>📋 Mis Contraseñas Guardadas</h2>
        <a href="?pagina=panel" class="btn-volver">← Volver al Panel</a>
    </div>
    
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <!-- Barra de busqueda y acciones -->
    <div class="barra-acciones">
        <div class="busqueda">
            <form method="GET" style="display: flex; gap: 10px;">
                <input type="hidden" name="pagina" value="listar">
                <input type="text" name="buscar" placeholder="Buscar por sitio o usuario..." 
                       value="<?php echo htmlspecialchars($terminoBusqueda); ?>" style="flex: 1;">
                <button type="submit" class="btn-buscar">🔍 Buscar</button>
                <?php if (!empty($terminoBusqueda)): ?>
                    <a href="?pagina=listar" class="btn-limpiar">✕ Limpiar</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="acciones-lista">
            <a href="?pagina=agregar" class="btn-agregar">➕ Nueva Contraseña</a>
        </div>
    </div>
    
    <?php if (!empty($terminoBusqueda)): ?>
        <div class="info-busqueda">
            <p>Mostrando resultados para: "<strong><?php echo htmlspecialchars($terminoBusqueda); ?></strong>" 
               (<?php echo count($contraseñas); ?> encontradas)</p>
        </div>
    <?php endif; ?>
    
    <?php if (empty($contraseñas)): ?>
        <div class="sin-resultados">
            <?php if (empty($terminoBusqueda)): ?>
                <div class="mensaje-vacio">
                    <h3>📭 No tienes contraseñas guardadas</h3>
                    <p>Comienza guardando tu primera contraseña de forma segura.</p>
                    <a href="?pagina=agregar" class="btn-primary">➕ Agregar Primera Contraseña</a>
                </div>
            <?php else: ?>
                <div class="mensaje-sin-busqueda">
                    <h3>🔍 Sin resultados</h3>
                    <p>No se encontraron contraseñas que coincidan con "<strong><?php echo htmlspecialchars($terminoBusqueda); ?></strong>"</p>
                    <a href="?pagina=listar" class="btn-secondary">📋 Ver Todas</a>
                </div>
            <?php endif; ?>
        </div>
    
    <?php else: ?>
        <div class="tabla-contraseñas">
            <div class="info-total">
                <span>Total: <?php echo count($contraseñas); ?> contraseñas</span>
            </div>
            
            <div class="contraseñas-grid">
                <?php foreach ($contraseñas as $index => $password): ?>
                    <div class="password-card" data-id="<?php echo $password->getId(); ?>">
                        <div class="card-header">
                            <h3 class="sitio"><?php echo htmlspecialchars($password->getSitio()); ?></h3>
                            <div class="acciones-card">
                                <button onclick="togglePasswordVisibility(<?php echo $password->getId(); ?>)" 
                                        class="btn-mostrar" title="Mostrar/Ocultar contraseña">👁️</button>
                                <a href="?pagina=editar&id=<?php echo $password->getId(); ?>" 
                                   class="btn-editar" title="Editar">✏️</a>
                                <button onclick="confirmarEliminar(<?php echo $password->getId(); ?>, '<?php echo htmlspecialchars($password->getSitio()); ?>')" 
                                        class="btn-eliminar" title="Eliminar">🗑️</button>
                            </div>
                        </div>
                        
                        <div class="card-content">
                            <div class="campo-info">
                                <strong>Usuario:</strong>
                                <span class="selectable"><?php echo htmlspecialchars($password->getUsuario()); ?></span>
                                <button onclick="copiarAlPortapapeles('<?php echo htmlspecialchars($password->getUsuario()); ?>')" 
                                        class="btn-copiar-pequeno" title="Copiar usuario">📋</button>
                            </div>
                            
                            <div class="campo-info">
                                <strong>Contraseña:</strong>
                                <span class="password-oculta" id="password-<?php echo $password->getId(); ?>">••••••••</span>
                                <span class="password-visible" id="password-real-<?php echo $password->getId(); ?>" style="display: none;">
                                    <?php echo htmlspecialchars($password->getPasswordDesencriptada()); ?>
                                </span>
                                <button onclick="copiarPassword(<?php echo $password->getId(); ?>)" 
                                        class="btn-copiar-pequeno" title="Copiar contraseña">📋</button>
                            </div>
                            
                            <?php if (!empty($password->getNotas())): ?>
                                <div class="campo-info notas">
                                    <strong>Notas:</strong>
                                    <span><?php echo nl2br(htmlspecialchars($password->getNotas())); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="campo-info fecha">
                                <small>Creada: <?php echo date('d/m/Y H:i', strtotime($password->getFechaCreacion())); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal/Form oculto para eliminar -->
<div id="modal-eliminar" style="display: none;">
    <form method="POST" id="form-eliminar">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" id="eliminar-id">
    </form>
</div>

<script>
// Mostrar/ocultar contraseña individual
function togglePasswordVisibility(id) {
    const oculta = document.getElementById('password-' + id);
    const visible = document.getElementById('password-real-' + id);
    
    if (oculta.style.display !== 'none') {
        oculta.style.display = 'none';
        visible.style.display = 'inline';
    } else {
        oculta.style.display = 'inline';
        visible.style.display = 'none';
    }
}

// Copiar contraseña al portapapeles
function copiarPassword(id) {
    const passwordElement = document.getElementById('password-real-' + id);
    const password = passwordElement.textContent.trim();
    
    copiarAlPortapapeles(password);
    
    // Mostrar feedback visual
    const btn = event.target;
    const textoOriginal = btn.textContent;
    btn.textContent = '✓';
    btn.style.background = '#27ae60';
    
    setTimeout(() => {
        btn.textContent = textoOriginal;
        btn.style.background = '';
    }, 1500);
}

// Confirmar eliminacion
function confirmarEliminar(id, sitio) {
    if (confirm(`¿Estás seguro de eliminar la contraseña de "${sitio}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('eliminar-id').value = id;
        document.getElementById('form-eliminar').submit();
    }
}
</script>