<?php
/**
 * Footer/pie de página común
 */
?>

<footer class="pie-pagina">
    <div class="footer-contenido">
        <div class="footer-seccion">
            <h4>🔐 <?php echo APP_NAME; ?></h4>
            <p>Gestor de contraseñas seguro</p>
            <small>Versión <?php echo APP_VERSION; ?></small>
        </div>
        
        <div class="footer-seccion">
            <h4>📊 Estadísticas</h4>
            <?php 
            if (isset($_SESSION['usuario_autenticado'])):
                $stats = ControladorContraseñas::obtenerEstadisticas();
            ?>
                <p>Contraseñas: <?php echo $stats['total_contraseñas']; ?></p>
                <p>Sitios: <?php echo $stats['sitios_unicos']; ?></p>
            <?php else: ?>
                <p>Inicia sesión para ver estadísticas</p>
            <?php endif; ?>
        </div>
        
        <div class="footer-seccion">
            <h4>🔒 Seguridad</h4>
            <p>• Cifrado AES-256-CBC</p>
            <p>• Hash SHA-256 + Salt</p>
            <p>• Sin almacenamiento en texto plano</p>
        </div>
        
        <div class="footer-seccion">
            <h4>ℹ️ Información</h4>
            <p>Proyecto de ciberseguridad</p>
            <p>Desarrollado en PHP + SQLite</p>
            <small>© 2025 - Uso educativo</small>
        </div>
    </div>
    
    <div class="footer-creditos">
        <p>
            <strong>Importante:</strong> Este gestor almacena las contraseñas de forma segura usando encriptación. 
            No olvides tu contraseña maestra ya que no se puede recuperar.
        </p>
    </div>
</footer>