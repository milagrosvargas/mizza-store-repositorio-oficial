<?php
// mizzastore/controllers/ProductosController.php
require_once __DIR__ . '/../models/Producto.php';

class ProductosController
{
    private Producto $model;

    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->model = new Producto();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        }
    }

    private function base(): string
    {
        // Evita 404 por rutas relativas
        return $_SERVER['SCRIPT_NAME']; // típicamente /mizzastore_v2/mizzastore/index.php
    }

    private function csrfCheck(): void
    {
        $t = $_POST['csrf_token'] ?? '';
        if (!$t || !hash_equals($_SESSION['csrf_token'], $t)) {
            http_response_code(403);
            exit('CSRF token inválido');
        }
    }

    private function flash(string $type, string $title, string $msg): void
    {
        $_SESSION['flash'] = ['type'=>$type,'title'=>$title,'message'=>$msg];
    }

    private function redirectList(): never
    {
        header('Location: ' . $this->base() . '?controller=productos&action=index');
        exit;
    }

    /* ======= VISTA LISTADO + FORM ======= */
    public function index(): void
    {
        $items       = $this->model->all();
        $categorias  = $this->model->categorias();
        $subcats     = $this->model->subCategorias();
        $marcas      = $this->model->marcas();
        $unidades    = $this->model->unidades();
        $estados     = $this->model->estados();
        $csrf_token  = $_SESSION['csrf_token'];
        require __DIR__ . '/../views/productos/index.php';
    }

    /* ======= CREATE ======= */
    public function store(): void
    {
        $this->csrfCheck();

        $nombre  = trim($_POST['nombre_producto'] ?? '');
        $desc    = trim($_POST['descripcion_producto'] ?? '');
        $precio  = (float)($_POST['precio_producto'] ?? 0);
        $stock   = (int)($_POST['stock_producto'] ?? 0);
        $cat     = (int)($_POST['id_categoria'] ?? 0);
        $subcat  = (int)($_POST['id_sub_categoria'] ?? 0);
        $marca   = (int)($_POST['id_marca'] ?? 0);
        $unidad  = (int)($_POST['id_unidad_medida'] ?? 0);
        $estado  = (int)($_POST['id_estado_logico'] ?? 1);

        if ($nombre === '' || $precio <= 0 || $cat <= 0 || $marca <= 0 || $unidad <= 0) {
            $this->flash('error','Datos inválidos','Completá los campos requeridos.');
            $this->redirectList();
        }

        // Imagen
        $imgPath = null;
        if (!empty($_FILES['imagen_producto']['name'])) {
            $imgPath = $this->guardarImagen($_FILES['imagen_producto']);
            if ($imgPath === null) {
                $this->flash('error','Imagen','Formato no permitido (jpg, jpeg, png, webp) o error de subida.');
                $this->redirectList();
            }
        }

        $ok = $this->model->create([
            'nombre_producto'      => $nombre,
            'descripcion_producto' => $desc,
            'imagen_producto'      => $imgPath,
            'precio_producto'      => $precio,
            'stock_producto'       => $stock,
            'id_categoria'         => $cat,
            'id_sub_categoria'     => $subcat ?: null,
            'id_marca'             => $marca,
            'id_unidad_medida'     => $unidad,
            'id_estado_logico'     => $estado ?: 1,
        ]);

        $this->flash($ok ? 'success' : 'error',
                     $ok ? 'Guardado' : 'Error',
                     $ok ? 'Producto creado correctamente.' : 'No se pudo crear el producto.');
        $this->redirectList();
    }

    /* ======= UPDATE ======= */
    public function update(): void
    {
        $this->csrfCheck();

        $id      = (int)($_POST['id_producto'] ?? 0);
        $nombre  = trim($_POST['nombre_producto'] ?? '');
        $desc    = trim($_POST['descripcion_producto'] ?? '');
        $precio  = (float)($_POST['precio_producto'] ?? 0);
        $stock   = (int)($_POST['stock_producto'] ?? 0);
        $cat     = (int)($_POST['id_categoria'] ?? 0);
        $subcat  = (int)($_POST['id_sub_categoria'] ?? 0);
        $marca   = (int)($_POST['id_marca'] ?? 0);
        $unidad  = (int)($_POST['id_unidad_medida'] ?? 0);
        $estado  = (int)($_POST['id_estado_logico'] ?? 1);
        $imgOld  = $_POST['imagen_actual'] ?? null;

        if ($id<=0 || $nombre==='' || $precio<=0 || $cat<=0 || $marca<=0 || $unidad<=0) {
            $this->flash('error','Datos inválidos','Revisá los campos.');
            $this->redirectList();
        }

        $imgPath = $imgOld;
        if (!empty($_FILES['imagen_producto']['name'])) {
            $imgPath = $this->guardarImagen($_FILES['imagen_producto']);
            if ($imgPath === null) {
                $this->flash('error','Imagen','Formato no permitido (jpg, jpeg, png, webp) o error de subida.');
                $this->redirectList();
            }
        }

        $ok = $this->model->update($id, [
            'nombre_producto'      => $nombre,
            'descripcion_producto' => $desc,
            'imagen_producto'      => $imgPath,
            'precio_producto'      => $precio,
            'stock_producto'       => $stock,
            'id_categoria'         => $cat,
            'id_sub_categoria'     => $subcat ?: null,
            'id_marca'             => $marca,
            'id_unidad_medida'     => $unidad,
            'id_estado_logico'     => $estado ?: 1,
        ]);

        $this->flash($ok ? 'success' : 'error',
                     $ok ? 'Actualizado' : 'Error',
                     $ok ? 'Producto actualizado.' : 'No se pudo actualizar.');
        $this->redirectList();
    }

    /* ======= DELETE ======= */
    public function delete(): void
    {
        $this->csrfCheck();
        $id = (int)($_POST['id_producto'] ?? 0);
        if ($id<=0) { $this->flash('error','ID inválido',''); $this->redirectList(); }

        $ok = $this->model->delete($id);
        $this->flash($ok ? 'success' : 'error',
                     $ok ? 'Eliminado' : 'Error',
                     $ok ? 'Producto eliminado.' : 'No se pudo eliminar (FK).');
        $this->redirectList();
    }

    /* ======= Util imagen ======= */
    private function guardarImagen(array $file): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) return null;

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed, true)) return null;

        $dir = __DIR__ . '/../assets/images/productos';
        if (!is_dir($dir)) mkdir($dir, 0775, true);

        $name = uniqid('prd_') . '.' . $ext;
        $dest = $dir . '/' . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

        // Ruta pública relativa al index.php
        return 'assets/images/productos/' . $name;
    }
}
