<?php
/**
 * Vista para editar contraseña existente
 */

require_once 'controladores/ControladorContraseñas.php';

$mensaje = '';
$tipoMensaje = '';
$contraseña = null;

// Obtener ID de contraseña a editar
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: ?pagina=listar');
    exit();
}

// Procesar formulario si se envio
$resultado = ControladorContraseñas::procesarFormulario();
if ($resultado) {
    $mensaje = $resultado['mensaje'];
    $tipoMensaje = $resultado['exito'] ? 'exito' : 'error';
    
    // Si se actualizo exitosamente, redirigir a lista
    if ($resultado['exito']) {
        header('Location: ?pagina=listar&mensaje=editada');
        exit();
    }
}

// Obtener datos de la contraseña para editar
$contraseña = ControladorContraseñas::obtenerParaEditar($id);
if (!$contraseña) {
    header('Location: ?pagina=listar&error=no_encontrada');
    exit();
}

// Generar contraseña aleatoria si se solicito
$passwordGenerada = '';
if (isset($_GET['generar_password'])) {
    $passwordGenerada = ControladorContraseñas::generarPasswordSegura();
}

?>

<div class="editar-password">
    <div class="header-seccion">
        <h2>✏️ Editar Contraseña</h2>
        <div class="botones-header">
            <a href="?pagina=listar" class="btn-volver">← Volver a Lista</a>
            <a href="?pagina=panel" class="btn-volver">🏠 Panel</a>
        </div>
    </div>
    
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <div class="info-editando">
        <p><strong>Editando:</strong> <?php echo htmlspecialchars($contraseña->getSitio()); ?></p>
        <p><small>Creado: <?php echo date('d/m/Y H:i', strtotime($contraseña->getFechaCreacion())); ?></small></p>
    </div>
    
    <div class="form-container">
        <form method="POST" onsubmit="return validarFormulario(this)" autocomplete="off">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" value="<?php echo $contraseña->getId(); ?>">
            
            <div class="form-row">
                <div class="campo">
                    <label for="sitio">Sitio Web / Aplicación: *</label>
                    <input type="text" id="sitio" name="sitio" required 
                           placeholder="Ej: Facebook, Gmail, Banco..." 
                           value="<?php echo htmlspecialchars($_POST['sitio'] ?? $contraseña->getSitio()); ?>">
                </div>
                
                <div class="campo">
                    <label for="usuario">Usuario / Email: *</label>
                    <input type="text" id="usuario" name="usuario" required 
                           placeholder="Ej: usuario@email.com" 
                           value="<?php echo htmlspecialchars($_POST['usuario'] ?? $contraseña->getUsuario()); ?>">
                </div>
            </div>
            
            <div class="campo">
                <label for="password">Nueva Contraseña (dejar vacio para mantener actual):</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="password" 
                           placeholder="Solo cambiar si quieres nueva contraseña" 
                           value="<?php echo htmlspecialchars($passwordGenerada); ?>">
                    <button type="button" onclick="togglePassword('password')" class="btn-toggle">👁️</button>
                    <a href="?pagina=editar&id=<?php echo $id; ?>&generar_password=1" class="btn-generar">🎲 Generar</a>
                    <button type="button" onclick="mostrarPasswordActual()" class="btn-mostrar-actual">📄 Ver Actual</button>
                </div>
                <small class="password-strength" id="password-strength"></small>
                
                <div id="password-actual" style="display: none;" class="password-actual-container">
                    <strong>Contraseña actual:</strong> 
                    <span class="password-actual"><?php echo htmlspecialchars($contraseña->getPasswordDesencriptada()); ?></span>
                    <button type="button" onclick="copiarPasswordActual()" class="btn-copiar-pequeno">📋</button>
                </div>
            </div>
            
            <div class="campo">
                <label for="notas">Notas:</label>
                <textarea id="notas" name="notas" rows="3" 
                          placeholder="Notas adicionales, preguntas de seguridad, etc."><?php echo htmlspecialchars($_POST['notas'] ?? $contraseña->getNotas()); ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">💾 Actualizar Contraseña</button>
                <button type="button" onclick="resetearFormulario()" class="btn-secondary">🔄 Deshacer Cambios</button>
                <button type="button" onclick="confirmarEliminar()" class="btn-peligro">🗑️ Eliminar</button>
            </div>
        </form>
    </div>
    
    <div class="historial-cambios">
        <h3>📅 Información</h3>
        <ul>
            <li><strong>Creado:</strong> <?php echo date('d/m/Y H:i:s', strtotime($contraseña->getFechaCreacion())); ?></li>
            <?php if ($contraseña->getFechaCreacion() !== $contraseña->getFechaModificacion()): ?>
                <li><strong>Última modificación:</strong> <?php echo date('d/m/Y H:i:s', strtotime($contraseña->getFechaModificacion())); ?></li>
            <?php endif; ?>
            <li><strong>ID:</strong> #<?php echo $contraseña->getId(); ?></li>
        </ul>
    </div>
</div>

<!-- Form oculto para eliminar -->
<form method="POST" id="form-eliminar-editar" style="display: none;">
    <input type="hidden" name="accion" value="eliminar">
    <input type="hidden" name="id" value="<?php echo $contraseña->getId(); ?>">
</form>

<script>
// Valores originales para resetear
const valoresOriginales = {
    sitio: '<?php echo addslashes($contraseña->getSitio()); ?>',
    usuario: '<?php echo addslashes($contraseña->getUsuario()); ?>',
    notas: '<?php echo addslashes($contraseña->getNotas()); ?>'
};

// Validacion en tiempo real de fortaleza de contraseña
document.getElementById('password')?.addEventListener('input', function() {
    const password = this.value;
    const strengthDiv = document.getElementById('password-strength');
    
    if (password.length === 0) {
        strengthDiv.textContent = '';
        return;
    }
    
    const puntos = verificarFortaleza(password);
    let texto = '';
    let clase = '';
    
    switch(puntos) {
        case 0:
        case 1:
            texto = '🔴 Muy débil';
            clase = 'debil';
            break;
        case 2:
            texto = '🟡 Débil';
            clase = 'debil';
            break;
        case 3:
            texto = '🟠 Regular';
            clase = 'regular';
            break;
        case 4:
            texto = '🟢 Fuerte';
            clase = 'fuerte';
            break;
        case 5:
            texto = '🟢 Muy fuerte';
            clase = 'muy-fuerte';
            break;
    }
    
    strengthDiv.textContent = texto;
    strengthDiv.className = 'password-strength ' + clase;
});

// Mostrar contraseña actual
function mostrarPasswordActual() {
    const container = document.getElementById('password-actual');
    if (container.style.display === 'none') {
        container.style.display = 'block';
    } else {
        container.style.display = 'none';
    }
}

// Copiar contraseña actual
function copiarPasswordActual() {
    const passwordActual = document.querySelector('.password-actual').textContent;
    copiarAlPortapapeles(passwordActual);
    
    // Feedback visual
    const btn = event.target;
    const original = btn.textContent;
    btn.textContent = '✓';
    setTimeout(() => btn.textContent = original, 1500);
}

// Resetear formulario a valores originales
function resetearFormulario() {
    if (confirm('¿Deshacer todos los cambios y volver a los valores originales?')) {
        document.getElementById('sitio').value = valoresOriginales.sitio;
        document.getElementById('usuario').value = valoresOriginales.usuario;
        document.getElementById('notas').value = valoresOriginales.notas;
        document.getElementById('password').value = '';
        document.getElementById('password-strength').textContent = '';
    }
}

// Confirmar eliminacion
function confirmarEliminar() {
    const sitio = valoresOriginales.sitio;
    if (confirm(`¿Estás SEGURO de eliminar completamente la contraseña de "${sitio}"?\n\n⚠️ Esta acción NO se puede deshacer.\n\nEscribe "ELIMINAR" en mayúsculas para confirmar:`)) {
        const confirmacion = prompt('Escribe "ELIMINAR" para confirmar:');
        if (confirmacion === 'ELIMINAR') {
            document.getElementById('form-eliminar-editar').submit();
        } else {
            alert('Eliminación cancelada - texto de confirmación incorrecto');
        }
    }
}
</script>