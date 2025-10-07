<?php

/**
 * ============================================================
 * ARCHIVO: models/User.php
 * DESCRIPCIÓN: Modelo para manejar operaciones relacionadas con
 * los usuarios del sistema (autenticación, búsqueda, sesión).
 * Se conecta a la base de datos usando la clase Database.
 * ============================================================
 */

class User
{
    // Propiedad para la conexión PDO
    private $conn;

    /**
     * Constructor: recibe la conexión a la base de datos
     * @param PDO $db
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }

/**
 * Verifica si el usuario y contraseña son válidos o si coincide con la contraseña temporal
 * @param string $identificador Nombre de usuario, correo o teléfono
 * @param string $password Contraseña ingresada por el usuario
 * @return array|null Datos del usuario si es válido, null si no
 */
public function login($identificador, $password)
{
    $query = "
        SELECT 
            alias_usuarios.id_usuario,
            alias_usuarios.nombre_usuario,
            alias_usuarios.password_usuario,
            alias_usuarios.password_temporal,
            alias_usuarios.expiracion_password_temporal,
            alias_usuarios.estado_usuario,
            alias_perfil.id_perfil,
            alias_perfil.descripcion_perfil,
            alias_persona.nombre_persona,
            alias_persona.apellido_persona
        FROM usuarios AS alias_usuarios
        INNER JOIN perfil AS alias_perfil 
            ON alias_usuarios.relacion_perfil = alias_perfil.id_perfil
        INNER JOIN persona AS alias_persona 
            ON alias_usuarios.relacion_persona = alias_persona.id_persona
        INNER JOIN detalle_contacto AS alias_detalle_contacto
            ON alias_persona.id_detalle_contacto = alias_detalle_contacto.id_detalle_contacto
        INNER JOIN tipo_contacto AS alias_tipo_contacto
            ON alias_detalle_contacto.id_tipo_contacto = alias_tipo_contacto.id_tipo_contacto
        WHERE (
            alias_usuarios.nombre_usuario = :identificador
            OR (
                alias_detalle_contacto.descripcion_contacto = :identificador2
                AND (
                    alias_tipo_contacto.nombre_tipo_contacto = 'Correo electrónico'
                    OR alias_tipo_contacto.nombre_tipo_contacto = 'Número de teléfono'
                )
            )
        )
        AND alias_usuarios.estado_usuario = 1
        LIMIT 1
    ";

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(':identificador', $identificador, PDO::PARAM_STR);
    $stmt->bindParam(':identificador2', $identificador, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 🔑 Verificación de contraseña normal
        if (!empty($user['password_usuario']) && password_verify($password, $user['password_usuario'])) {
            return $user;
        }

        // 🔑 Verificación de contraseña temporal (hash + expiración)
        if (
            !empty($user['password_temporal']) &&
            password_verify($password, $user['password_temporal']) &&
            !empty($user['expiracion_password_temporal']) &&
            strtotime($user['expiracion_password_temporal']) >= time()
        ) {
            // Marcar que el acceso fue con clave temporal
            $user['login_con_password_temporal'] = true;
            return $user;
        }
    }

    // ❌ Si no coincide nada
    return null;
}



    /**
     * Registra la sesión activa en la tabla sesion
     * @param int $idUsuario ID del usuario autenticado
     * @return bool True si se registró correctamente
     */
    public function registrarSesion($idUsuario)
    {
        // Desactiva sesiones anteriores
        $this->conn->prepare("UPDATE sesion SET activa = 0 WHERE id_usuario = :id")
            ->execute([':id' => $idUsuario]);

        // Inserta nueva sesión activa
        $query = "INSERT INTO sesion (id_usuario, activa) VALUES (:id_usuario, 1)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

/**
 * Busca un usuario por su correo electrónico (almacenado en detalle_contacto)
 */
public function buscarPorCorreo($correo)
{
    $sql = "
        SELECT 
            alias_usuarios.id_usuario,
            alias_usuarios.nombre_usuario,
            alias_usuarios.password_usuario,
            alias_usuarios.password_temporal,
            alias_usuarios.expiracion_password_temporal,
            alias_usuarios.relacion_perfil,
            alias_personas.nombre_persona,
            alias_personas.apellido_persona,
            alias_detalle_contacto.descripcion_contacto AS correo_usuario
        FROM usuarios AS alias_usuarios
        INNER JOIN persona AS alias_personas 
            ON alias_usuarios.relacion_persona = alias_personas.id_persona
        INNER JOIN detalle_contacto AS alias_detalle_contacto 
            ON alias_personas.id_detalle_contacto = alias_detalle_contacto.id_detalle_contacto
        INNER JOIN tipo_contacto AS alias_tipo_contacto 
            ON alias_detalle_contacto.id_tipo_contacto = alias_tipo_contacto.id_tipo_contacto
        WHERE alias_tipo_contacto.nombre_tipo_contacto = 'Correo electrónico'
          AND alias_detalle_contacto.descripcion_contacto = :correo
        LIMIT 1
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    /**
     * Método para generar la contraseña temporal para recuperar la contraseña
     */
public function guardarPasswordTemporal($id_usuario, $temporal, $expiracion)
{
    $hashTemporal = password_hash($temporal, PASSWORD_DEFAULT);

    $sql = "UPDATE usuarios 
            SET password_temporal = :temporal, expiracion_password_temporal = :expiracion 
            WHERE id_usuario = :id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':temporal', $hashTemporal);
    $stmt->bindParam(':expiracion', $expiracion);
    $stmt->bindParam(':id', $id_usuario);
    return $stmt->execute();
}


/**
 * Actualiza la contraseña del usuario, validando reglas de seguridad
 */
/**
 * Actualiza la contraseña del usuario, valida contra la actual y la temporal,
 * aplica reglas mínimas, hashea y limpia la temporal.
 * Devuelve true si se actualiza; lanza Exception con mensaje en caso de error.
 */
public function actualizarPassword($idUsuario, $nuevaPasswordPlano)
{
    // 1) Traer hashes actual y temporal
    $sqlSelect = "SELECT password_usuario, password_temporal FROM usuarios WHERE id_usuario = :id LIMIT 1";
    $stmtSelect = $this->conn->prepare($sqlSelect);
    $stmtSelect->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmtSelect->execute();
    $usuario = $stmtSelect->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception("Usuario no encontrado");
    }

    // 2) Validar que no sea igual a la contraseña actual
    if (!empty($usuario['password_usuario']) && password_verify($nuevaPasswordPlano, $usuario['password_usuario'])) {
        throw new Exception("La nueva contraseña no puede ser igual a la anterior");
    }

    // 3) Validar que no sea igual a la temporal (si existe)
    if (!empty($usuario['password_temporal']) && password_verify($nuevaPasswordPlano, $usuario['password_temporal'])) {
        throw new Exception("La nueva contraseña no puede ser igual a la temporal");
    }

    // 4) Reglas mínimas
    if (strlen($nuevaPasswordPlano) < 6) {
        throw new Exception("La contraseña debe tener al menos 6 caracteres");
    }
    if (!preg_match('/\d/', $nuevaPasswordPlano)) {
        throw new Exception("La contraseña debe contener al menos un número");
    }

    // 5) Hashear y actualizar + limpiar temporal
    $nuevoHash = password_hash($nuevaPasswordPlano, PASSWORD_DEFAULT);

    $sqlUpdate = "
        UPDATE usuarios
        SET password_usuario = :hash,
            password_temporal = NULL,
            expiracion_password_temporal = NULL
        WHERE id_usuario = :id
    ";
    $stmtUpdate = $this->conn->prepare($sqlUpdate);
    $stmtUpdate->bindParam(':hash', $nuevoHash, PDO::PARAM_STR);
    $stmtUpdate->bindParam(':id', $idUsuario, PDO::PARAM_INT);

    if (!$stmtUpdate->execute()) {
        throw new Exception("No se pudo actualizar la contraseña");
    }

    // ✔ El modelo no muestra mensajes; informa éxito
    return true;
}

/**
 * Método obtenerPorId sirve para consultar la contraseña vieja vs la nueva para no repetirlas
 * @param mixed $idUsuario
 */
public function obtenerPorId($idUsuario)
{
    $sql = "SELECT password_usuario, password_temporal FROM usuarios WHERE id_usuario = :id LIMIT 1";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $idUsuario);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function actualizarPasswordDefinitiva($idUsuario, $nuevoHash)
{
    $sql = "UPDATE usuarios SET password_usuario = :password, 
    password_temporal = NULL, expiracion_password_temporal = NULL WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':password', $nuevoHash);
    $stmt->bindParam(':id', $idUsuario);
    return $stmt->execute();
}

public function cerrarSesionActiva($idUsuario)
{
    $sql = "UPDATE sesion SET activa = 0 WHERE id_usuario = :id";
    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
}

}
