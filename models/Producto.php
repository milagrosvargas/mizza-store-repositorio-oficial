<?php
// mizzastore/models/Producto.php
require_once __DIR__ . '/../config/database.php';

class Producto
{
    private PDO $pdo;
    private string $T = 'productos'; // tu tabla es "productos" (plural)

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /* --------- Listados FK (para selects) --------- */
    public function categorias(): array {
        $sql = "SELECT id_categoria, nombre_categoria
                  FROM categoria
              ORDER BY nombre_categoria";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function subCategorias(): array {
        $sql = "SELECT id_sub_categoria, nombre_sub_categoria, id_categoria
                  FROM sub_categoria
              ORDER BY nombre_sub_categoria";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function marcas(): array {
        $sql = "SELECT id_marca, nombre_marca
                  FROM marca
              ORDER BY nombre_marca";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function unidades(): array {
        // En la BD es "nombre_unidad_medida"; la aliasamos a "nombre_unidad"
        $sql = "SELECT id_unidad_medida, nombre_unidad_medida AS nombre_unidad
                  FROM unidad_medida
              ORDER BY nombre_unidad_medida";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function estados(): array {
        // En la BD es "nombre_estado"; la aliasamos a "nombre_estado_logico"
        $sql = "SELECT id_estado_logico, nombre_estado AS nombre_estado_logico
                  FROM estado_logico
              ORDER BY id_estado_logico";
        return $this->pdo->query($sql)->fetchAll();
    }

    /* --------- CRUD producto --------- */
    public function all(): array
    {
        // Alias para que las vistas lean llaves coherentes
        $sql = "SELECT p.id_producto, p.nombre_producto, p.descripcion_producto, p.imagen_producto,
                       p.precio_producto, p.stock_producto,
                       c.nombre_categoria,
                       sc.nombre_sub_categoria,
                       m.nombre_marca,
                       u.nombre_unidad_medida AS nombre_unidad,
                       e.nombre_estado       AS nombre_estado_logico
                  FROM {$this->T} p
             LEFT JOIN categoria      c  ON c.id_categoria = p.id_categoria
             LEFT JOIN sub_categoria  sc ON sc.id_sub_categoria = p.id_sub_categoria
             LEFT JOIN marca          m  ON m.id_marca = p.id_marca
             LEFT JOIN unidad_medida  u  ON u.id_unidad_medida = p.id_unidad_medida
             LEFT JOIN estado_logico  e  ON e.id_estado_logico = p.id_estado_logico
              ORDER BY p.id_producto DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->T}
                   (nombre_producto, descripcion_producto, imagen_producto,
                    precio_producto, stock_producto,
                    id_categoria, id_sub_categoria, id_marca, id_unidad_medida, id_estado_logico)
                VALUES
                   (:nombre, :desc, :img, :precio, :stock, :cat, :subcat, :marca, :unidad, :estado)";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':nombre' => $data['nombre_producto'],
            ':desc'   => $data['descripcion_producto'],
            ':img'    => $data['imagen_producto'],
            ':precio' => $data['precio_producto'],
            ':stock'  => $data['stock_producto'],
            ':cat'    => $data['id_categoria'],
            ':subcat' => $data['id_sub_categoria'],
            ':marca'  => $data['id_marca'],
            ':unidad' => $data['id_unidad_medida'],
            ':estado' => $data['id_estado_logico'],
        ]);
    }

    public function find(int $id): ?array
    {
        $st = $this->pdo->prepare("SELECT * FROM {$this->T} WHERE id_producto=:id LIMIT 1");
        $st->execute([':id'=>$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->T}
                   SET nombre_producto=:nombre,
                       descripcion_producto=:desc,
                       imagen_producto=:img,
                       precio_producto=:precio,
                       stock_producto=:stock,
                       id_categoria=:cat,
                       id_sub_categoria=:subcat,
                       id_marca=:marca,
                       id_unidad_medida=:unidad,
                       id_estado_logico=:estado
                 WHERE id_producto=:id";
        $st = $this->pdo->prepare($sql);
        return $st->execute([
            ':nombre'=>$data['nombre_producto'],
            ':desc'  =>$data['descripcion_producto'],
            ':img'   =>$data['imagen_producto'],
            ':precio'=>$data['precio_producto'],
            ':stock' =>$data['stock_producto'],
            ':cat'   =>$data['id_categoria'],
            ':subcat'=>$data['id_sub_categoria'],
            ':marca' =>$data['id_marca'],
            ':unidad'=>$data['id_unidad_medida'],
            ':estado'=>$data['id_estado_logico'],
            ':id'    =>$id,
        ]);
    }

    public function delete(int $id): bool
    {
        $st = $this->pdo->prepare("DELETE FROM {$this->T} WHERE id_producto=:id");
        return $st->execute([':id'=>$id]);
    }
}
