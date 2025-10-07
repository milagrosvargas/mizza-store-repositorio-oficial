<?php

/**
 * ============================================================
 * ARCHIVO: controllers/HomeController.php
 * DESCRIPCIÓN: Controlador inicial de la aplicación MizzaStore.
 * Controla la vista principal y realiza una prueba de conexión a la base de datos.
 * ============================================================
 */

// Asegura que la clase Database esté disponible
require_once 'config/database.php';

class HomeController
{
    /**
     * Método por defecto al acceder a ?controller=home&action=index
     * Carga la vista de inicio (landing pública del sitio).
     */
    public function index()
    {
        // Incluye la vista de la página principal
        require_once 'views/home/inicio.php';
    }

    public function dashboard()
    {

        require_once 'views/layout/header.php';
        require_once 'views/home/dashboard_principal.php';
        require_once 'views/layout/footer.php';
    }
}
