<?php
// mizzastore/controllers/LoginController.php

require_once __DIR__ . '/../config/database.php';

class LoginController
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getPDO();
    }

    /* ---------------- Helpers ---------------- */
    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    private function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    private function setFlash(string $type, string $title, string $message): void
    {
        $this->ensureSession();
        $_SESSION['flash'] = ['type'=>$type,'title'=>$title,'message'=>$message];
    }

    /* ---------------- Vistas ---------------- */
    public function index(): void
    {
        $this->ensureSession();
        $MZ_HIDE_CHROME = true;
        $page_title = 'Iniciar sesión';
        require __DIR__ . '/../views/home/login.php';
    }

    public function recuperar(): void
    {
        $this->ensureSession();
        $MZ_HIDE_CHROME = true;
        $page_title = 'Recuperar contraseña';
        require __DIR__ . '/../views/home/recuperar.php';
    }

    public function actualizarPassword(): void
    {
        $this->ensureSession();
        $MZ_HIDE_CHROME = true;
        $page_title = 'Actualizar contraseña';
        require __DIR__ . '/../views/home/actualizar_password.php';
    }

    /* ---------------- Acciones ---------------- */

    /**
     * POST desde el login.
     * Espera:
     *   - login_identificador  (usuario O email)
     *   - password_usuario
     */
    public function autenticar(): void
    {
        $this->ensureSession();

        $ident = trim($_POST['login_identificador'] ?? '');
        $clave = trim($_POST['password_usuario'] ?? '');

        if ($ident === '' || $clave === '') {
            $this->setFlash('error','Datos requeridos','Ingresá usuario (o email) y contraseña.');
            $this->redirect('index.php?controller=login&action=index');
        }

        // 1) Intentar por nombre de usuario
        $row = $this->getUsuarioPorNombre($ident);

        // 2) Si no está, y el identificador parece email, intentamos por email
        if (!$row && filter_var($ident, FILTER_VALIDATE_EMAIL)) {
            $row = $this->getUsuarioPorEmail($ident);
        }

        if (!$row) {
            $this->setFlash('error','Credenciales inválidas','Usuario/email o contraseña incorrectos.');
            $this->redirect('index.php?controller=login&action=index');
        }

        if ((int)$row['estado_usuario'] !== 1) {
            $this->setFlash('error','Cuenta inactiva','Tu usuario está inactivo. Contactá al administrador.');
            $this->redirect('index.php?controller=login&action=index');
        }

        if (!password_verify($clave, $row['password_usuario'])) {
            $this->setFlash('error','Credenciales inválidas','Usuario/email o contraseña incorrectos.');
            $this->redirect('index.php?controller=login&action=index');
        }

        // OK -> sesión
        $_SESSION['usuario'] = [
            'id_usuario'    => (int)$row['id_usuario'],
            'nombre_usuario'=> $row['nombre_usuario'],
            'id_perfil'     => (int)$row['relacion_perfil'],
            'id_persona'    => (int)$row['relacion_persona'],
            'autenticado'   => true,
            'momento_login' => date('Y-m-d H:i:s'),
        ];

        $this->setFlash('success','¡Bienvenida/o!','Accediste correctamente.');
        $this->redirect('index.php?controller=home&action=dashboard');
    }

    public function registrar(): void
    {
        $this->ensureSession();

        $usuario = trim($_POST['nombre_usuario'] ?? '');
        $clave   = trim($_POST['password_usuario'] ?? '');
        $confirm = trim($_POST['password_confirm'] ?? '');

        if ($usuario === '' || $clave === '' || $confirm === '') {
            $this->setFlash('error','Datos incompletos','Completá todos los campos.');
            $this->redirect('index.php?controller=login&action=index');
        }
        if ($clave !== $confirm) {
            $this->setFlash('error','Validación','Las contraseñas no coinciden.');
            $this->redirect('index.php?controller=login&action=index');
        }

        $existe = $this->pdo->prepare("SELECT 1 FROM usuarios WHERE nombre_usuario=:u LIMIT 1");
        $existe->execute([':u'=>$usuario]);
        if ($existe->fetchColumn()) {
            $this->setFlash('error','Ya existe','El nombre de usuario ya está registrado.');
            $this->redirect('index.php?controller=login&action=index');
        }

        $hash = password_hash($clave, PASSWORD_BCRYPT);
        $ins = $this->pdo->prepare(
            "INSERT INTO usuarios (nombre_usuario, password_usuario, estado_usuario, relacion_perfil, relacion_persona)
             VALUES (:u, :p, 1, 2, NULL)"
        );
        $ok = $ins->execute([':u'=>$usuario, ':p'=>$hash]);

        if (!$ok) {
            $this->setFlash('error','No se pudo registrar','Intentá nuevamente.');
            $this->redirect('index.php?controller=login&action=index');
        }

        $this->setFlash('success','Cuenta creada','Ya podés iniciar sesión.');
        $this->redirect('index.php?controller=login&action=index');
    }

    public function salir(): void
    {
        $this->redirect('index.php?controller=sesion&action=logout');
    }

    /* ---------------- Queries privadas ---------------- */
    private function getUsuarioPorNombre(string $usuario): ?array
    {
        $sql = "SELECT id_usuario, nombre_usuario, password_usuario, estado_usuario,
                       relacion_perfil, relacion_persona
                  FROM usuarios
                 WHERE nombre_usuario = :u
                 LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':u'=>$usuario]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    private function getUsuarioPorEmail(string $email): ?array
    {
        // Relación: usuarios -> persona (relacion_persona) -> detalle_contacto (id_detalle_contacto)
        // donde detalle_contacto.descripcion_contacto = email
        $sql = "SELECT u.id_usuario, u.nombre_usuario, u.password_usuario, u.estado_usuario,
                       u.relacion_perfil, u.relacion_persona
                  FROM usuarios u
             LEFT JOIN persona p ON p.id_persona = u.relacion_persona
             LEFT JOIN detalle_contacto dc ON dc.id_detalle_contacto = p.id_detalle_contacto
                 WHERE dc.descripcion_contacto = :e
                 LIMIT 1";
        $st = $this->pdo->prepare($sql);
        $st->execute([':e'=>$email]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
