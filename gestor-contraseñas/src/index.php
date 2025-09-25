<?php
/**
 * Archivo principal de la aplicacion
 * Maneja el enrutamiento basico y carga las vistas
 */

require_once 'config/configuracion.php';

// Simple enrutamiento basado en parametros GET
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : 'login';

// Verificar si esta autenticado para ciertas paginas
$paginasProtegidas = ['panel', 'agregar', 'listar', 'editar'];
if (in_array($pagina, $paginasProtegidas) && !isset($_SESSION['usuario_autenticado'])) {
    header('Location: index.php?pagina=login');
    exit();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>
    <?php 
    // Incluir cabecera si esta autenticado y no es login
    if (isset($_SESSION['usuario_autenticado']) && $pagina !== 'login') {
        include 'includes/cabecera.php';
    }
    ?>
    
    <div class="container">
        <?php
        // Incluir la vista correspondiente
        switch($pagina) {
            case 'login':
                include 'vistas/login.php';
                break;
            case 'panel':
                include 'vistas/panel_principal.php';
                break;
            case 'agregar':
                include 'vistas/agregar_contraseña.php';
                break;
            case 'listar':
                include 'vistas/listar_contraseñas.php';
                break;
            case 'editar':
                include 'vistas/editar_contraseña.php';
                break;
            case 'buscar':
                include 'vistas/buscar_contraseñas.php';
                break;
            default:
                echo "<h2>Pagina no encontrada</h2>";
                echo "<p>La pagina solicitada no existe.</p>";
                echo "<a href='?pagina=panel' class='btn-primary'>Ir al Panel Principal</a>";
                break;
        }
        ?>
    </div>
    
    <?php 
    // Incluir pie de página
    include 'includes/pie_pagina.php';
    ?>
    
    <script src="assets/js/funciones.js"></script>
</body>
</html>