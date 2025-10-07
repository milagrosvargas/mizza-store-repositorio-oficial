<?php
// Título dinámico y bandera para ocultar navbar/footer
$page_title = isset($page_title) ? $page_title . ' | MizzaStore' : 'MizzaStore';
$MZ_HIDE_CHROME = $MZ_HIDE_CHROME ?? false;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Fuentes / Iconos / CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">

  <style>
    body { font-family: 'Poppins', sans-serif; }
    .btn-primary { background:#890620; border-color:#890620; }
    .btn-primary:hover { background:#2C0703; border-color:#2C0703; }
    .dropdown-menu .dropdown-item.active, .dropdown-menu .dropdown-item:active { background:#890620; }

    .navbar .nav-link.active,
    .navbar .dropdown-toggle.active {
    pointer-events: auto !important;
    cursor: pointer;
    }

  </style>
</head>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<body>

<?php if (!$MZ_HIDE_CHROME): ?>
  <?php require_once __DIR__ . '/navbar.php'; ?>
<?php endif; ?>

<!-- ¡OJO! Abrimos main acá; se cierra en footer.php -->
<main class="<?= $MZ_HIDE_CHROME ? '' : 'mb-4' ?>">
