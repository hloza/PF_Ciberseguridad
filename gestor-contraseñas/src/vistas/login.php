<?php
/**
 * Vista de login - aqui el usuario pone su contraseña maestra
 */

require_once 'utilidades/Autenticacion.php';

$mensaje = '';
$tipoMensaje = '';

// Procesar formulario si se envio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['accion'])) {
        
        if ($_POST['accion'] == 'login') {
            // Login existente
            $passwordMaestra = $_POST['password_maestra'] ?? '';
            
            if (Autenticacion::verificarContraseñaMaestra($passwordMaestra)) {
                Autenticacion::crearSesion($passwordMaestra);
                header('Location: index.php?pagina=panel');
                exit();
            } else {
                $mensaje = 'Contraseña maestra incorrecta';
                $tipoMensaje = 'error';
            }
            
        } elseif ($_POST['accion'] == 'crear_maestro') {
            // Crear contraseña maestra por primera vez
            $passwordNueva = $_POST['password_nueva'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            
            if (strlen($passwordNueva) < 8) {
                $mensaje = 'La contraseña debe tener al menos 8 caracteres';
                $tipoMensaje = 'error';
            } elseif ($passwordNueva !== $passwordConfirm) {
                $mensaje = 'Las contraseñas no coinciden';
                $tipoMensaje = 'error';
            } else {
                if (Autenticacion::crearUsuarioMaestro($passwordNueva)) {
                    $mensaje = 'Contraseña maestra creada exitosamente. Ahora puedes iniciar sesion';
                    $tipoMensaje = 'exito';
                } else {
                    $mensaje = 'Error al crear la contraseña maestra';
                    $tipoMensaje = 'error';
                }
            }
        }
    }
}

$existeUsuario = Autenticacion::existeUsuarioMaestro();
?>

<div class="login-container">
    <h1><?php echo APP_NAME; ?></h1>
    
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipoMensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($existeUsuario): ?>
        <!-- Formulario de login -->
        <div class="form-container">
            <h2>Acceso con Contraseña Maestra</h2>
            <form method="POST" onsubmit="return validarFormulario(this)">
                <input type="hidden" name="accion" value="login">
                
                <div class="campo">
                    <label for="password_maestra">Contraseña Maestra:</label>
                    <input type="password" id="password_maestra" name="password_maestra" required>
                </div>
                
                <button type="submit" class="btn-primary">Ingresar</button>
            </form>
        </div>
    
    <?php else: ?>
        <!-- Formulario para crear contraseña maestra -->
        <div class="form-container">
            <h2>Crear Contraseña Maestra</h2>
            <p>Es tu primera vez usando el gestor. Crea una contraseña maestra segura.</p>
            
            <form method="POST" onsubmit="return validarFormulario(this)">
                <input type="hidden" name="accion" value="crear_maestro">
                
                <div class="campo">
                    <label for="password_nueva">Nueva Contraseña Maestra:</label>
                    <input type="password" id="password_nueva" name="password_nueva" required minlength="8">
                    <small>Minimo 8 caracteres</small>
                </div>
                
                <div class="campo">
                    <label for="password_confirm">Confirmar Contraseña:</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>
                
                <button type="submit" class="btn-primary">Crear Contraseña Maestra</button>
            </form>
        </div>
    <?php endif; ?>
    
    <div class="info">
        <p><strong>Importante:</strong> La contraseña maestra no se puede recuperar. Asegurate de recordarla.</p>
    </div>
</div>