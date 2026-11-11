<?php
// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';
require_once __DIR__ . '/../src/services/CategoriasService.php';

use config\Config;
use services\SessionService;
use services\ProductosService;

// Comprobar autenticación (Invitados NO pueden ver detalles)
$session = SessionService::getInstance();
$session->requireAuth();

// Validar parámetro GET id
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de producto no válido.'));
    exit;
}

// Obtener datos del producto
$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$producto = $productosService->findById($id);

// Si el producto no existe en la base de datos
if (!$producto) {
    header('Location: index.php?error=' . urlencode('El producto solicitado no existe o ha sido eliminado.'));
    exit;
}

$isAdmin = $session->isAdmin();

// Inclusión de la cabecera
require_once 'header.php';
?>

<!-- Migas de pan / Navegación superior -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Productos</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($producto->marca . ' ' . $producto->modelo) ?></li>
    </ol>
</nav>

<!-- Tarjeta de Detalle del Producto -->
<div class="card shadow-sm border-0 overflow-hidden mb-4">
    <div class="row g-0">
        <!-- Columna de Imagen -->
        <div class="col-md-5 bg-white d-flex align-items-center justify-content-center p-4 border-end">
            <img src="<?= htmlspecialchars($producto->imagen) ?>" 
                 alt="<?= htmlspecialchars($producto->modelo) ?>" 
                 class="img-fluid rounded shadow-sm object-fit-contain"
                 style="max-height: 380px; width: 100%;"
                 onerror="this.src='https://placehold.co/300';">
        </div>

        <!-- Columna de Información -->
        <div class="col-md-7">
            <div class="card-body p-4 d-flex flex-column h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                        <?= htmlspecialchars($producto->categoriaNombre ?? 'Sin Categoría') ?>
                    </span>
                    <span class="text-muted small">ID: #<?= (int)$producto->id ?></span>
                </div>

                <h2 class="card-title fw-bold text-dark mb-1">
                    <?= htmlspecialchars($producto->marca) ?> - <?= htmlspecialchars($producto->modelo) ?>
                </h2>

                <!-- Precio y Stock -->
                <div class="my-3 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center">
                    <div>
                        <span class="text-muted small d-block">Precio unitario</span>
                        <span class="fs-3 fw-bold text-primary">
                            <?= number_format($producto->precio, 2, ',', '.') ?> €
                        </span>
                    </div>
                    <div class="text-end">
                        <span class="text-muted small d-block">Disponibilidad</span>
                        <?php if ($producto->stock > 5): ?>
                            <span class="badge bg-success fs-6">
                                <i class="bi bi-check-circle me-1"></i>En Stock (<?= (int)$producto->stock ?> uds.)
                            </span>
                        <?php elseif ($producto->stock > 0): ?>
                            <span class="badge bg-warning text-dark fs-6">
                                <i class="bi bi-exclamation-triangle me-1"></i>Últimas unidades (<?= (int)$producto->stock ?>)
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">
                                <i class="bi bi-x-circle me-1"></i>Agotado
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="mb-4">
                    <h6 class="fw-bold text-dark mb-2">Descripción del producto</h6>
                    <p class="card-text text-muted">
                        <?= !empty($producto->descripcion) ? nl2br(htmlspecialchars($producto->descripcion)) : '<em>Sin descripción detallada.</em>' ?>
                    </p>
                </div>

                <!-- Metadatos técnicos (Lista de definición) -->
                <div class="mt-auto pt-3 border-top">
                    <dl class="row mb-0 small text-muted">
                        <dt class="col-sm-4">Identificador UUID:</dt>
                        <dd class="col-sm-8 mb-1"><code><?= htmlspecialchars($producto->uuid) ?></code></dd>

                        <dt class="col-sm-4">Fecha de registro:</dt>
                        <dd class="col-sm-8 mb-1"><?= htmlspecialchars($producto->createdAt ?? 'N/D') ?></dd>

                        <dt class="col-sm-4">Última modificación:</dt>
                        <dd class="col-sm-8 mb-0"><?= htmlspecialchars($producto->updatedAt ?? 'N/D') ?></dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <!-- Barra de Acciones / Pie de la tarjeta -->
    <div class="card-footer bg-white border-top p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Volver al Catálogo
        </a>

        <?php if ($isAdmin): ?>
            <div class="d-flex gap-2">
                <a href="update-image.php?id=<?= (int)$producto->id ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-image me-1"></i>Cambiar Imagen
                </a>
                <a href="update.php?id=<?= (int)$producto->id ?>" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Modificar
                </a>
                <a href="delete.php?id=<?= (int)$producto->id ?>" 
                   class="btn btn-danger"
                   onclick="return confirm('¿Confirmas que deseas eliminar definitivamente este producto?');">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Inclusión del pie de página común
require_once 'footer.php';
?>