<?php
/**
 * Panel principal - dashboard del usuario
 */

require_once 'utilidades/Autenticacion.php';
require_once 'controladores/ControladorContraseñas.php';

// Procesar logout
if (isset($_GET['logout'])) {
    Autenticacion::cerrarSesion();
}

// Obtener estadisticas
$estadisticas = ControladorContraseñas::obtenerEstadisticas();

?>

<div class="panel-principal">
    <div class="header-panel">
        <h2>Bienvenido al Gestor de Contraseñas</h2>
        <div class="user-info">
            <span>Sesion activa desde: <?php echo date('H:i:s', $_SESSION['tiempo_login']); ?></span>
            <a href="?logout=1" class="btn-logout">Cerrar Sesión</a>
        </div>
    </div>
    
    <div class="menu-principal">
        <div class="menu-item">
            <h3><a href="?pagina=agregar">🔐 Agregar Nueva Contraseña</a></h3>
            <p>Guarda una nueva contraseña de forma segura</p>
        </div>
        
        <div class="menu-item">
            <h3><a href="?pagina=listar">📋 Ver Mis Contraseñas</a></h3>
            <p>Lista y administra todas tus contraseñas guardadas</p>
        </div>
        
        <div class="menu-item">
            <h3><a href="?pagina=buscar">🔍 Buscar Contraseñas</a></h3>
            <p>Encuentra rapidamente una contraseña especifica</p>
        </div>
    </div>
    
    <div class="stats-panel">
        <h3>Estadisticas del Gestor</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <span class="stat-number"><?php echo $estadisticas['total_contraseñas']; ?></span>
                <span class="stat-label">Contraseñas guardadas</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo $estadisticas['sitios_unicos']; ?></span>
                <span class="stat-label">Sitios diferentes</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">
                    <?php 
                    if ($estadisticas['total_contraseñas'] > 0) {
                        echo "100%";
                    } else {
                        echo "N/A";
                    }
                    ?>
                </span>
                <span class="stat-label">Nivel de seguridad</span>
            </div>
        </div>
        
        <?php if ($estadisticas['ultima_agregada']): ?>
            <div class="info-adicional">
                <p><strong>Última contraseña agregada:</strong> 
                   <?php echo date('d/m/Y H:i', strtotime($estadisticas['ultima_agregada'])); ?>
                </p>
            </div>
        <?php endif; ?>
        
        <?php if ($estadisticas['total_contraseñas'] == 0): ?>
            <div class="info-inicial">
                <p>🎉 ¡Bienvenido! Aún no tienes contraseñas guardadas.</p>
                <p>Comienza <a href="?pagina=agregar" style="color: #3498db;">agregando tu primera contraseña</a></p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="acciones-rapidas">
        <h3>Acciones Rapidas</h3>
        <div class="botones-rapidos">
            <a href="?pagina=agregar" class="btn-accion">+ Nueva Contraseña</a>
            <?php if ($estadisticas['total_contraseñas'] > 0): ?>
                <a href="?pagina=listar" class="btn-accion">📋 Ver Todas</a>
                <a href="#" onclick="alert('Funcionalidad de backup pendiente')" class="btn-accion">💾 Backup</a>
            <?php endif; ?>
        </div>
    </div>
</div>