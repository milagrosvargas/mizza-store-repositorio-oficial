<?php
// Helpers para “activo”
$controller = isset($_GET['controller']) ? strtolower($_GET['controller']) : 'home';
$action     = isset($_GET['action']) ? strtolower($_GET['action']) : 'dashboard';

function is_active($ctrls, $acts = null) {
    $c = strtolower($_GET['controller'] ?? 'home');
    $a = strtolower($_GET['action'] ?? 'dashboard');
    $ctrls = (array)$ctrls;
    $acts  = $acts ? (array)$acts : null;
    $byCtrl = in_array($c, $ctrls, true);
    if (!$acts) return $byCtrl ? 'active' : '';
    return ($byCtrl && in_array($a, $acts, true)) ? 'active' : '';
}

if (!function_exists('is_active')) {
  function is_active($ctrls, $acts = null) {
    $c = strtolower($_GET['controller'] ?? 'home');
    $a = strtolower($_GET['action'] ?? 'dashboard');
    $ctrls = (array)$ctrls; $acts = $acts ? (array)$acts : null;
    $byCtrl = in_array($c, $ctrls, true);
    return !$acts ? ($byCtrl ? 'active' : '') : ($byCtrl && in_array($a, $acts, true) ? 'active' : '');
  }
}
?>
<title>MizzaStore - Argentina</title>

<style>
  /* Barra superior con tu estética */
  .mz-topbar {
    background: radial-gradient(#fff,#ffd6d6);
    border-bottom: 1px solid #f2c9c9;
  }
  .navbar .nav-link { font-weight: 500; }
  .navbar .nav-link.active,
  .navbar .dropdown-item.active { color: #890620 !important; }
  .navbar .btn-primary { background:#890620; border-color:#890620; }
  .navbar .btn-primary:hover { background:#2C0703; border-color:#2C0703; }
  .navbar-brand img { height: 36px; }

  
</style>

<header class="mz-topbar">
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <!-- Logo -->
      <a class="navbar-brand d-flex align-items-center gap-2" href="index.php?controller=home&action=dashboard">
        <!-- Cambia la ruta si tu logo vive en otra carpeta -->
        <img src="assets/images/logo.png" alt="MizzaStore">
      </a>

      <!-- Toggler -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mzNav"
              aria-controls="mzNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Contenido -->
      <div class="collapse navbar-collapse" id="mzNav">
        <!-- Links izquierda -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link <?= is_active('home',['dashboard','index']) ?>"
               href="index.php?controller=home&action=dashboard">Inicio</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('usuarios') ?>"
               href="index.php?controller=usuarios&action=index">Usuarios</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('clientes') ?>"
               href="index.php?controller=clientes&action=index">Clientes</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('ventas') ?>"
               href="index.php?controller=ventas&action=index">Ventas</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('inventario') ?>"
               href="index.php?controller=inventario&action=index">Inventario</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('productos',['index']) ?>"
                href="productos/index.php?controller=productos&action=index">Productos</a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= is_active('pedidos') ?>"
               href="index.php?controller=pedidos&action=index">Pedidos</a>
          </li>

          <!-- Dropdown Configuración -->
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle <?= is_active('config') ?>" href="#" id="navbarConfig" role="button"
               data-bs-toggle="dropdown" aria-expanded="false">
              Configuración
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarConfig">
              <li><a class="dropdown-item <?= is_active('config',['tipo_documento']) ?>"
                     href="index.php?controller=config&action=tipo_documento">Tipos de Documento</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=estado_logico">Estado lógico</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=pais">País</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=provincia">Provincia</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=localidad">Localidad</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=barrio">Barrio</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=tipo_contacto">Tipos de contacto</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=genero">Género</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=marca">Marcas</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=unidad_medida">Unidades de medida</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=metodo_pago">Métodos de pago</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=tipo_nota">Tipos de nota</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=categoria">Categorías</a></li>
              <li><a class="dropdown-item" href="index.php?controller=config&action=sub_categoria">Sub‑categorías</a></li>
            </ul>
          </li>
        </ul>

        <!-- Búsqueda + íconos derecha -->
        <!-- Buscador -->
        <form class="d-flex align-items-center gap-2" role="search" action="index.php" method="get">
        <input type="hidden" name="controller" value="productos">
        <input type="hidden" name="action" value="buscar">
        <div class="search-bar-container">
            <input class="search-input" type="search" name="q" placeholder="Buscar productos, marcas…" aria-label="Buscar">
        </div>
        </form>

        <!-- Botones de sesión (FUERA del form) -->
        <div class="d-flex align-items-center ms-2">
        <?php if (!empty($_SESSION['usuario'])): ?>
            <a class="btn btn-primary d-none d-lg-inline"
            href="index.php?controller=sesion&action=logout">
            Cerrar sesión
            </a>
        <?php else: ?>
            <a class="btn btn-primary d-none d-lg-inline"
            href="index.php?controller=login&action=index">
            Iniciar sesión
            </a>
        <?php endif; ?>
        </div>

      </div>
    </div>
  </nav>
  
</header>
