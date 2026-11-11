<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/services/SessionService.php';

use services\SessionService;

$session = SessionService::getInstance();
$isLoggedIn = $session->isLoggedIn();
$isAdmin = $session->isAdmin();
$username = $session->getUsername();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda Online - Gestión Integral</title>

    <!-- Icono para la pestaña del navegador -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <!-- Logo con favicon y nombre de la tienda -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
            <img src="/favicon.ico" alt="Logo" width="24" height="24" class="me-2 d-inline-block align-text-top">
            TiendaDAW
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-box-seam me-1"></i>Productos</a>
                </li>
                <?php if ($isAdmin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="create.php"><i class="bi bi-plus-circle me-1"></i>Nuevo Producto</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-3">
                <span class="text-light">
                    <i class="bi bi-person-circle me-1"></i>
                    <strong><?= htmlspecialchars($username) ?></strong>
                    <?php if ($isAdmin): ?>
                        <span class="badge bg-danger ms-1">ADMIN</span>
                    <?php elseif ($isLoggedIn): ?>
                        <span class="badge bg-info text-dark ms-1">USER</span>
                    <?php else: ?>
                        <span class="badge bg-secondary ms-1">Invitado</span>
                    <?php endif; ?>
                </span>

                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<main class="container my-4 flex-grow-1">
    <?php if (!empty($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>