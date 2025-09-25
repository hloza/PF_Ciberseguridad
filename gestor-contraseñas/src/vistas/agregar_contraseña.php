<?php
/**
 * Vista para agregar nueva contraseña
 */

require_once 'controladores/ControladorContraseñas.php';

$mensaje = '';
$tipoMensaje = '';
$mostrarFormulario = true;

// Procesar formulario si se envio
$resultado = ControladorContraseñas::procesarFormulario();
if ($resultado) {
    $mensaje = $resultado['mensaje'];
    $tipoMensaje = $resultado['exito'] ? 'exito' : 'error';
    
    // Si se guardo exitosamente, limpiar formulario
    if ($resultado['exito']) {
        $mostrarFormulario = false; // Mostrar mensaje de exito y boton para agregar otra
    }
}

// Generar contraseña aleatoria si se solicito
$passwordGenerada = '';
if (isset($_GET['generar_password'])) {
    $passwordGenerada = ControladorContraseñas::generarPasswordSegura();
}

?>

<div class="agregar-password">
    <div class="header-seccion">
        <h2>🔐 Agregar Nueva Contraseña</h2>
        <a href="?pagina=panel" class="btn-volver">← Volver al Panel</a>
    </div>
    
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($mostrarFormulario): ?>
        <div class="form-container">
            <form method="POST" onsubmit="return validarFormulario(this)" autocomplete="off">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="form-row">
                    <div class="campo">
                        <label for="sitio">Sitio Web / Aplicación: *</label>
                        <input type="text" id="sitio" name="sitio" required 
                               placeholder="Ej: Facebook, Gmail, Banco..." 
                               value="<?php echo htmlspecialchars($_POST['sitio'] ?? ''); ?>">
                    </div>
                    
                    <div class="campo">
                        <label for="usuario">Usuario / Email: *</label>
                        <input type="text" id="usuario" name="usuario" required 
                               placeholder="Ej: usuario@email.com" 
                               value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="campo">
                    <label for="password">Contraseña: *</label>
                    <div class="password-input-group">
                        <input type="password" id="password" name="password" required 
                               placeholder="Contraseña segura" 
                               value="<?php echo htmlspecialchars($passwordGenerada); ?>">
                        <button type="button" onclick="togglePassword('password')" class="btn-toggle">👁️</button>
                        <a href="?pagina=agregar&generar_password=1" class="btn-generar">🎲 Generar</a>
                    </div>
                    <small class="password-strength" id="password-strength"></small>
                </div>
                
                <div class="campo">
                    <label for="notas">Notas (opcional):</label>
                    <textarea id="notas" name="notas" rows="3" 
                              placeholder="Notas adicionales, preguntas de seguridad, etc."><?php echo htmlspecialchars($_POST['notas'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn-primary">💾 Guardar Contraseña</button>
                    <button type="button" onclick="limpiarFormulario()" class="btn-secondary">🔄 Limpiar</button>
                </div>
            </form>
        </div>
        
        <div class="tips-seguridad">
            <h3>💡 Consejos de Seguridad</h3>
            <ul>
                <li>Usa contraseñas diferentes para cada sitio</li>
                <li>Incluye mayúsculas, minúsculas, números y símbolos</li>
                <li>Evita información personal (fechas, nombres)</li>
                <li>Minimo 12 caracteres de longitud</li>
                <li>Cambia contraseñas periódicamente</li>
            </ul>
        </div>
    
    <?php else: ?>
        <!-- Mensaje de exito con opciones -->
        <div class="exito-container">
            <div class="exito-icono">✅</div>
            <h3>¡Contraseña Guardada!</h3>
            <p>La contraseña se ha guardado de forma segura y encriptada.</p>
            
            <div class="opciones-siguientes">
                <a href="?pagina=agregar" class="btn-primary">➕ Agregar Otra</a>
                <a href="?pagina=listar" class="btn-secondary">📋 Ver Todas</a>
                <a href="?pagina=panel" class="btn-secondary">🏠 Ir al Panel</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
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

function limpiarFormulario() {
    if (confirm('¿Estás seguro de que quieres limpiar el formulario?')) {
        document.querySelector('form').reset();
        document.getElementById('password-strength').textContent = '';
    }
}
</script>