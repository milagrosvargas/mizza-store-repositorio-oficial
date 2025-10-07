<?php
// mizzastore/index.php
declare(strict_types=1);

// ------------------------------------
// Bootstrap básico
// ------------------------------------
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', '1');

define('BASE_PATH', __DIR__);

// Sanitizar parámetros de ruta
$controller = strtolower($_GET['controller'] ?? 'home');
$action     = strtolower($_GET['action'] ?? 'dashboard');
$controller = preg_replace('/[^a-z0-9_]/', '', $controller);
$action     = preg_replace('/[^a-z0-9_]/', '', $action);

// Helper centralizado para cargar controladores con ruta segura
function require_controller(string $name): void {
    $file = BASE_PATH . '/controllers/' . $name . '.php';
    if (!is_file($file)) {
        http_response_code(404);
        exit("Controlador no encontrado: {$name}");
    }
    require_once $file;
}

// ------------------------------------
// Ruteo por controlador
// ------------------------------------
if (!function_exists('require_controller')) {
    function require_controller(string $name): void {
        $file = __DIR__ . '/controllers/' . $name . '.php';
        if (!is_file($file)) {
            http_response_code(404);
            exit("Controlador no encontrado: {$name}");
        }
        require_once $file;
    }
}

/* ---------- Productos ---------- */
if ($controller === 'productos') {
    require_once __DIR__ . '/controllers/ProductosController.php';
    $ctrl = new ProductosController();
    $allowed = ['index','store','update','delete'];
    if (!in_array($action, $allowed, true)) { $action = 'index'; }
    $ctrl->$action();
    exit;
}
// CONFIG (usa whitelist completa)
if ($controller === 'config') {
    /** @noinspection PhpUndefinedClassInspection */
    require_controller('ConfigController');
    /** @var ConfigController $ctrl */
    $ctrl = new ConfigController();

    $allowed = [
        // tipo_documento
        'tipo_documento','tipo_documento_store','tipo_documento_update','tipo_documento_delete',
        // estado_logico
        'estado_logico','estado_logico_store','estado_logico_update','estado_logico_delete',
        // pais
        'pais','pais_store','pais_update','pais_delete',
        // provincia
        'provincia','provincia_store','provincia_update','provincia_delete',
        // localidad
        'localidad','localidad_store','localidad_update','localidad_delete',
        // barrio
        'barrio','barrio_store','barrio_update','barrio_delete',
        // tipo_contacto
        'tipo_contacto','tipo_contacto_store','tipo_contacto_update','tipo_contacto_delete',
        // genero
        'genero','genero_store','genero_update','genero_delete',
        // categoria
        'categoria','categoria_store','categoria_update','categoria_delete',
        // sub_categoria
        'sub_categoria','sub_categoria_store','sub_categoria_update','sub_categoria_delete',
        // marca
        'marca','marca_store','marca_update','marca_delete',
        // unidad_medida
        'unidad_medida','unidad_medida_store','unidad_medida_update','unidad_medida_delete',
        // metodo_pago
        'metodo_pago','metodo_pago_store','metodo_pago_update','metodo_pago_delete',
        // tipo_nota
        'tipo_nota','tipo_nota_store','tipo_nota_update','tipo_nota_delete',
    ];

    if (!in_array($action, $allowed, true)) {
        $action = 'tipo_documento';
    }

    $ctrl->$action();
    exit;
}

// SESION (index, logout)
if ($controller === 'sesion') {
    /** @noinspection PhpUndefinedClassInspection */
    require_controller('SesionController');
    /** @var SesionController $ctrl */
    $ctrl = new SesionController();

    $allowed = ['index','logout'];
    if (!in_array($action, $allowed, true)) {
        $action = 'index';
    }

    $ctrl->$action();
    exit;
}

// LOGIN (pantallas de login/recupero)
if ($controller === 'login') {
    /** @noinspection PhpUndefinedClassInspection */
    require_controller('LoginController');
    /** @var LoginController $ctrl */
    $ctrl = new LoginController();

    if (!method_exists($ctrl, $action)) {
        $action = 'index';
    }

    $ctrl->$action();
    exit;
}

// HOME (dashboard o index)
if ($controller === 'home') {
    /** @noinspection PhpUndefinedClassInspection */
    require_controller('HomeController');
    /** @var HomeController $ctrl */
    $ctrl = new HomeController();

    if (!method_exists($ctrl, $action)) {
        $action = method_exists($ctrl, 'dashboard') ? 'dashboard' : 'index';
    }

    $ctrl->$action();
    exit;
}

// ------------------------------------
// Fallback global → HomeController
// ------------------------------------
/** @noinspection PhpUndefinedClassInspection */
require_controller('HomeController');
/** @var HomeController $ctrl */
$ctrl = new HomeController();
$action = method_exists($ctrl, 'dashboard') ? 'dashboard' : 'index';
$ctrl->$action();
exit;
