<?php
// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

// Control de acceso: solo ADMIN
$session = SessionService::getInstance();
$session->requireAdmin();

// Validar ID recibido por GET
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de producto no válido.'));
    exit;
}

// Consultar producto existente
$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$producto = $productosService->findById($id);

if (!$producto) {
    header('Location: index.php?error=' . urlencode('El producto seleccionado no existe.'));
    exit;
}

require_once 'header.php';
?>

<!-- Migas de pan -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Productos</a></li>
        <li class="breadcrumb-item"><a href="details.php?id=<?= (int)$producto->id ?>" class="text-decoration-none"><?= htmlspecialchars($producto->modelo) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Cambiar Imagen</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-7 col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h4 class="mb-0 text-dark">
                    <i class="bi bi-card-image text-primary me-2"></i>Actualizar Imagen del Producto
                </h4>
            </div>

            <div class="card-body p-4">
                <!-- Información resumida del producto -->
                <div class="text-center mb-4">
                    <h5 class="fw-bold mb-1"><?= htmlspecialchars($producto->marca . ' ' . $producto->modelo) ?></h5>
                    <p class="text-muted small mb-3">UUID: <code><?= htmlspecialchars($producto->uuid) ?></code></p>
                    
                    <div class="p-3 bg-light rounded border d-inline-block">
                        <span class="d-block small text-muted mb-2">Imagen actual</span>
                        <img src="<?= htmlspecialchars($producto->imagen) ?>" 
                             alt="<?= htmlspecialchars($producto->modelo) ?>" 
                             class="rounded border img-fluid"
                             style="max-height: 180px; object-fit: contain;"
                             onerror="this.src='https://placehold.co/180';">
                    </div>
                </div>

                <!-- Formulario de Subida -->
                <form action="update_image_file.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= (int)$producto->id ?>">

                    <div class="mb-3">
                        <label for="imagen" class="form-label fw-semibold">Seleccionar nuevo archivo de imagen</label>
                        <input class="form-control" 
                               type="file" 
                               id="imagen" 
                               name="imagen" 
                               accept="image/jpeg,image/png,image/jpg" 
                               required>
                        <div class="form-text">
                            Formatos permitidos: <strong>JPG, JPEG, PNG</strong>. Tamaño máximo recomendado: 2 MB.
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <a href="index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i>Subir y Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'footer.php';
?>