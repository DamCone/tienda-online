<?php
// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';
require_once __DIR__ . '/../src/services/CategoriasService.php';

use config\Config;
use services\CategoriasService;
use services\ProductosService;
use services\SessionService;

// Seguridad: Solo administradores
$session = SessionService::getInstance();
$session->requireAdmin();

// Validar identificador recibido por GET o POST
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
}

if (!$id || $id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de producto no válido.'));
    exit;
}

// Inicializar servicios y cargar datos actuales
$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$categoriasService = new CategoriasService($config->db);

$producto = $productosService->findById($id);
if (!$producto) {
    header('Location: index.php?error=' . urlencode('El producto que intentas editar no existe.'));
    exit;
}

$categorias = $categoriasService->findAll();

// Valores iniciales tomados del producto existente
$errors = [];
$marca = $producto->marca;
$modelo = $producto->modelo;
$descripcion = $producto->descripcion ?? '';
$precio = $producto->precio;
$stock = $producto->stock;
$categoriaId = $producto->categoriaId;

// Procesamiento de la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $categoriaId = trim($_POST['categoria_id'] ?? '');

    // Validaciones
    if (empty($marca)) {
        $errors['marca'] = 'La marca es obligatoria.';
    } elseif (strlen($marca) > 255) {
        $errors['marca'] = 'La marca no puede superar los 255 caracteres.';
    }

    if (empty($modelo)) {
        $errors['modelo'] = 'El modelo es obligatorio.';
    } elseif (strlen($modelo) > 255) {
        $errors['modelo'] = 'El modelo no puede superar los 255 caracteres.';
    }

    if ($precio === '' || !is_numeric($precio) || (float)$precio < 0) {
        $errors['precio'] = 'Introduce un precio válido (mayor o igual a 0).';
    }

    if ($stock === '' || filter_var($stock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $errors['stock'] = 'Introduce un stock válido (entero mayor o igual a 0).';
    }

    if (empty($categoriaId)) {
        $errors['categoria_id'] = 'Debes seleccionar una categoría.';
    }

    // Si todo es correcto, actualizar
    if (empty($errors)) {
        $producto->marca = $marca;
        $producto->modelo = $modelo;
        $producto->descripcion = $descripcion;
        $producto->precio = (float)$precio;
        $producto->stock = (int)$stock;
        $producto->categoriaId = $categoriaId;

        if ($productosService->update($producto)) {
            header('Location: index.php?msg=' . urlencode('Producto "' . $marca . ' ' . $modelo . '" modificado correctamente.'));
            exit;
        } else {
            $errors['general'] = 'Hubo un problema al actualizar el producto en la base de datos.';
        }
    }
}

// Inclusión de la cabecera
require_once 'header.php';
?>

<!-- Migas de pan -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Productos</a></li>
        <li class="breadcrumb-item"><a href="details.php?id=<?= (int)$producto->id ?>" class="text-decoration-none"><?= htmlspecialchars($producto->modelo) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page">Modificar</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-dark">
                    <i class="bi bi-pencil-square text-primary me-2"></i>Modificar Producto #<?= (int)$producto->id ?>
                </h4>
                <a href="update-image.php?id=<?= (int)$producto->id ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-image me-1"></i>Cambiar Imagen
                </a>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form method="POST" action="update.php?id=<?= (int)$producto->id ?>" novalidate>
                    <input type="hidden" name="id" value="<?= (int)$producto->id ?>">

                    <div class="row g-3">
                        <!-- Vista previa de la imagen actual (Solo lectura) -->
                        <div class="col-12 text-center p-3 bg-light rounded border mb-2">
                            <span class="d-block small text-muted mb-2">Imagen actual (para cambiarla usa el botón superior)</span>
                            <img src="<?= htmlspecialchars($producto->imagen) ?>" 
                                 alt="<?= htmlspecialchars($producto->modelo) ?>" 
                                 class="rounded border" 
                                 style="max-height: 120px;"
                                 onerror="this.src='https://placehold.co/120';">
                        </div>

                        <!-- Marca -->
                        <div class="col-md-6">
                            <label for="marca" class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['marca']) ? 'is-invalid' : '' ?>" 
                                   id="marca" 
                                   name="marca" 
                                   value="<?= htmlspecialchars((string)$marca) ?>" 
                                   required>
                            <?php if (isset($errors['marca'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['marca']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Modelo -->
                        <div class="col-md-6">
                            <label for="modelo" class="form-label fw-semibold">Modelo <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['modelo']) ? 'is-invalid' : '' ?>" 
                                   id="modelo" 
                                   name="modelo" 
                                   value="<?= htmlspecialchars((string)$modelo) ?>" 
                                   required>
                            <?php if (isset($errors['modelo'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['modelo']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Categoría -->
                        <div class="col-md-6">
                            <label for="categoria_id" class="form-label fw-semibold">Categoría <span class="text-danger">*</span></label>
                            <select class="form-select <?= isset($errors['categoria_id']) ? 'is-invalid' : '' ?>" 
                                    id="categoria_id" 
                                    name="categoria_id" 
                                    required>
                                <option value="">-- Selecciona una categoría --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat->id) ?>" <?= (string)$categoriaId === (string)$cat->id ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat->nombre) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['categoria_id'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['categoria_id']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Precio -->
                        <div class="col-md-3">
                            <label for="precio" class="form-label fw-semibold">Precio (€) <span class="text-danger">*</span></label>
                            <div class="input-group has-validation">
                                <input type="number" 
                                       step="0.01" 
                                       min="0" 
                                       class="form-control <?= isset($errors['precio']) ? 'is-invalid' : '' ?>" 
                                       id="precio" 
                                       name="precio" 
                                       value="<?= htmlspecialchars((string)$precio) ?>" 
                                       required>
                                <span class="input-group-text">€</span>
                                <?php if (isset($errors['precio'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['precio']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Stock -->
                        <div class="col-md-3">
                            <label for="stock" class="form-label fw-semibold">Stock <span class="text-danger">*</span></label>
                            <input type="number" 
                                   min="0" 
                                   step="1" 
                                   class="form-control <?= isset($errors['stock']) ? 'is-invalid' : '' ?>" 
                                   id="stock" 
                                   name="stock" 
                                   value="<?= htmlspecialchars((string)$stock) ?>" 
                                   required>
                            <?php if (isset($errors['stock'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['stock']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold">Descripción del Producto</label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4"><?= htmlspecialchars((string)$descripcion) ?></textarea>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Inclusión del pie de página
require_once 'footer.php';
?>