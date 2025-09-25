<?php
/**
 * Vista de busqueda avanzada de contraseñas
 */

require_once 'controladores/ControladorContraseñas.php';

$termino = trim($_GET['q'] ?? '');
$contraseñas = [];
$mostrarResultados = false;

if (!empty($termino)) {
    $contraseñas = ControladorContraseñas::listar($termino);
    $mostrarResultados = true;
}

// Obtener algunas estadisticas para sugerencias
$estadisticas = ControladorContraseñas::obtenerEstadisticas();

?>

<div class="buscar-passwords">
    <div class="header-seccion">
        <h2>🔍 Buscar Contraseñas</h2>
        <a href="?pagina=panel" class="btn-volver">← Volver al Panel</a>
    </div>
    
    <div class="busqueda-avanzada">
        <form method="GET" class="form-busqueda-principal">
            <input type="hidden" name="pagina" value="buscar">
            
            <div class="campo-busqueda-grande">
                <input type="text" name="q" placeholder="Buscar por sitio, usuario, notas..." 
                       value="<?php echo htmlspecialchars($termino); ?>" 
                       id="campo-busqueda" autofocus>
                <button type="submit" class="btn-buscar-grande">🔍 Buscar</button>
            </div>
            
            <?php if (!empty($termino)): ?>
                <a href="?pagina=buscar" class="limpiar-busqueda">✕ Limpiar búsqueda</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if (!$mostrarResultados): ?>
        <!-- Estado inicial - sin busqueda -->
        <div class="busqueda-inicial">
            <div class="busqueda-placeholder">
                <div class="icono-busqueda">🔍</div>
                <h3>Encuentra tus contraseñas rapidamente</h3>
                <p>Busca por nombre del sitio, usuario, email o cualquier texto en las notas.</p>
            </div>
            
            <?php if ($estadisticas['total_contraseñas'] > 0): ?>
                <div class="sugerencias-busqueda">
                    <h4>💡 Sugerencias:</h4>
                    <div class="chips-sugerencias">
                        <a href="?pagina=buscar&q=gmail" class="chip-sugerencia">Gmail</a>
                        <a href="?pagina=buscar&q=facebook" class="chip-sugerencia">Facebook</a>
                        <a href="?pagina=buscar&q=banco" class="chip-sugerencia">Banco</a>
                        <a href="?pagina=buscar&q=@" class="chip-sugerencia">Emails (@)</a>
                    </div>
                </div>
                
                <div class="acciones-rapidas-busqueda">
                    <h4>Acciones rápidas:</h4>
                    <div class="botones-acciones">
                        <a href="?pagina=listar" class="btn-accion-busqueda">📋 Ver Todas (<?php echo $estadisticas['total_contraseñas']; ?>)</a>
                        <a href="?pagina=agregar" class="btn-accion-busqueda">➕ Agregar Nueva</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="sin-datos-busqueda">
                    <p>No tienes contraseñas guardadas aún.</p>
                    <a href="?pagina=agregar" class="btn-primary">➕ Agregar Primera Contraseña</a>
                </div>
            <?php endif; ?>
        </div>
    
    <?php else: ?>
        <!-- Resultados de busqueda -->
        <div class="resultados-busqueda">
            <div class="info-resultados">
                <h3>Resultados para: "<span class="termino-busqueda"><?php echo htmlspecialchars($termino); ?></span>"</h3>
                <p>Se encontraron <strong><?php echo count($contraseñas); ?></strong> contraseñas</p>
            </div>
            
            <?php if (empty($contraseñas)): ?>
                <div class="sin-resultados-busqueda">
                    <div class="icono-sin-resultados">😕</div>
                    <h4>Sin resultados</h4>
                    <p>No se encontraron contraseñas que contengan "<strong><?php echo htmlspecialchars($termino); ?></strong>"</p>
                    
                    <div class="sugerencias-sin-resultados">
                        <p>Intenta:</p>
                        <ul>
                            <li>Usar palabras clave más generales</li>
                            <li>Verificar la ortografia</li>
                            <li>Buscar por parte del nombre del sitio</li>
                            <li>Buscar por usuario o email</li>
                        </ul>
                    </div>
                    
                    <div class="acciones-sin-resultados">
                        <a href="?pagina=listar" class="btn-secondary">📋 Ver Todas las Contraseñas</a>
                        <a href="?pagina=agregar" class="btn-primary">➕ Agregar Nueva</a>
                    </div>
                </div>
            
            <?php else: ?>
                <!-- Lista de resultados -->
                <div class="contraseñas-grid">
                    <?php foreach ($contraseñas as $password): ?>
                        <div class="password-card resultado-busqueda" data-id="<?php echo $password->getId(); ?>">
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
                                    <span class="selectable destacar-busqueda"><?php echo htmlspecialchars($password->getUsuario()); ?></span>
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
                                        <span class="destacar-busqueda"><?php echo nl2br(htmlspecialchars($password->getNotas())); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="campo-info fecha">
                                    <small>Creada: <?php echo date('d/m/Y H:i', strtotime($password->getFechaCreacion())); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="acciones-resultados">
                    <p>¿No encuentras lo que buscas?</p>
                    <div class="botones-acciones">
                        <a href="?pagina=listar" class="btn-secondary">📋 Ver Todas</a>
                        <a href="?pagina=agregar" class="btn-primary">➕ Agregar Nueva</a>
                    </div>
                </div>
            <?php endif; ?>
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
// Destacar términos de busqueda en los resultados
document.addEventListener('DOMContentLoaded', function() {
    const termino = '<?php echo addslashes($termino); ?>';
    if (termino.length > 0) {
        destacarTermino(termino);
    }
});

function destacarTermino(termino) {
    const elementos = document.querySelectorAll('.destacar-busqueda');
    elementos.forEach(elemento => {
        const texto = elemento.innerHTML;
        const regex = new RegExp(`(${termino})`, 'gi');
        elemento.innerHTML = texto.replace(regex, '<mark>$1</mark>');
    });
}

// Funciones reutilizadas
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

function confirmarEliminar(id, sitio) {
    if (confirm(`¿Estás seguro de eliminar la contraseña de "${sitio}"?\n\nEsta acción no se puede deshacer.`)) {
        document.getElementById('eliminar-id').value = id;
        document.getElementById('form-eliminar').submit();
    }
}

// Auto-focus en campo de busqueda
document.getElementById('campo-busqueda')?.focus();
</script>