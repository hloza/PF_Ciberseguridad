<?php
/**
 * Header/cabecera común para todas las páginas autenticadas
 */

if (!isset($_SESSION['usuario_autenticado'])) {
    return; // Solo mostrar si está autenticado
}

?>

<div class="barra-navegacion">
    <div class="nav-izquierda">
        <a href="?pagina=panel" class="logo-nav">
            🔐 <?php echo APP_NAME; ?>
        </a>
    </div>
    
    <div class="nav-centro">
        <nav class="menu-nav">
            <a href="?pagina=panel" class="<?php echo ($pagina == 'panel') ? 'activo' : ''; ?>">
                🏠 Panel
            </a>
            <a href="?pagina=listar" class="<?php echo ($pagina == 'listar') ? 'activo' : ''; ?>">
                📋 Ver Todas
            </a>
            <a href="?pagina=agregar" class="<?php echo ($pagina == 'agregar') ? 'activo' : ''; ?>">
                ➕ Agregar
            </a>
            <a href="?pagina=buscar" class="<?php echo ($pagina == 'buscar') ? 'activo' : ''; ?>">
                🔍 Buscar
            </a>
        </nav>
    </div>
    
    <div class="nav-derecha">
        <div class="usuario-info-nav">
            <span class="tiempo-sesion">
                Activo: <?php echo date('H:i', $_SESSION['tiempo_login']); ?>
            </span>
            <a href="?logout=1" class="btn-logout-nav">
                🚪 Salir
            </a>
        </div>
    </div>
</div>