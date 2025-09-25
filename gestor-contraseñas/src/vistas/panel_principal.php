<?php
/**
 * Panel principal - dashboard del usuario
 */

require_once 'utilidades/Autenticacion.php';

// Procesar logout
if (isset($_GET['logout'])) {
    Autenticacion::cerrarSesion();
}

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
        <h3>Estadisticas</h3>
        <!-- TODO: mostrar estadisticas reales -->
        <div class="stat-item">
            <span class="stat-number">0</span>
            <span class="stat-label">Contraseñas guardadas</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">100%</span>
            <span class="stat-label">Nivel de seguridad</span>
        </div>
    </div>
</div>