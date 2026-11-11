<?php
// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';
require_once __DIR__ . '/../src/services/CategoriasService.php';

use config\Config;
use models\Producto;
use services\CategoriasService;
use services\ProductosService;
use services\SessionService;

// Seguridad: Solo rol ADMIN puede acceder
$session = SessionService::getInstance();
$session->requireAdmin();

// Inicialización de servicios
$config = Config::getInstance();
$categoriasService = new CategoriasService($config->db);
$productosService = new ProductosService($config->db);

// Obtener todas las categorías para el desplegable
$categorias = $categoriasService->findAll();

// Variables para el formulario y control de errores
$errors = [];
$marca = '';
$modelo = '';
$descripcion = '';
$precio = '';
$stock = '';
$categoriaId = '';
$imagen = '';

// Procesamiento del formulario al enviar por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $marca = trim($_POST['marca'] ?? '');
    $modelo = trim($_POST['modelo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $categoriaId = trim($_POST['categoria_id'] ?? '');
    $imagen = trim($_POST['imagen'] ?? '');

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
        $errors['precio'] = 'Introduce un precio numérico válido (mayor o igual a 0).';
    }

    if ($stock === '' || filter_var($stock, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]) === false) {
        $errors['stock'] = 'Introduce un stock válido (número entero mayor o igual a 0).';
    }

    if (empty($categoriaId)) {
        $errors['categoria_id'] = 'Debes seleccionar una categoría.';
    }

    // Si no hay errores, se procede al alta
    if (empty($errors)) {
        $nuevoProducto = new Producto(
            null, // El ID se asigna con la secuencia de PostgreSQL
            null, // El UUID se generará automáticamente en el constructor
            $marca,
            $modelo,
            $descripcion,
            (float)$precio,
            (int)$stock,
            !empty($imagen) ? $imagen : Producto::IMAGEN_DEFAULT,
            $categoriaId
        );

        if ($productosService->save($nuevoProducto)) {
            header('Location: index.php?msg=' . urlencode('Producto "' . $marca . ' ' . $modelo . '" creado con éxito.'));
            exit;
        } else {
            $errors['general'] = 'Ocurrió un error al guardar el producto en la base de datos.';
        }
    }
}

// Inclusión de la cabecera común
require_once 'header.php';
?>

<!-- Migas de pan -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Productos</a></li>
        <li class="breadcrumb-item active" aria-current="page">Nuevo Producto</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h4 class="mb-0 text-dark">
                    <i class="bi bi-plus-circle text-success me-2"></i>Añadir Nuevo Producto
                </h4>
            </div>

            <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
                <?php endif; ?>

                <form method="POST" action="create.php" novalidate>
                    <div class="row g-3">
                        <!-- Marca -->
                        <div class="col-md-6">
                            <label for="marca" class="form-label fw-semibold">Marca <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control <?= isset($errors['marca']) ? 'is-invalid' : '' ?>" 
                                   id="marca" 
                                   name="marca" 
                                   value="<?= htmlspecialchars($marca) ?>" 
                                   placeholder="Ej: Nike, Adidas, Sony..." 
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
                                   value="<?= htmlspecialchars($modelo) ?>" 
                                   placeholder="Ej: Air Max, Runner 2.0..." 
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
                                    <option value="<?= htmlspecialchars($cat->id) ?>" <?= $categoriaId === $cat->id ? 'selected' : '' ?>>
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
                                       value="<?= htmlspecialchars($precio) ?>" 
                                       placeholder="0.00" 
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
                                   value="<?= htmlspecialchars($stock) ?>" 
                                   placeholder="0" 
                                   required>
                            <?php if (isset($errors['stock'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['stock']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- URL Imagen opcional -->
                        <div class="col-12">
                            <label for="imagen" class="form-label fw-semibold">URL de la imagen (Opcional)</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="imagen" 
                                   name="imagen" 
                                   value="<?= htmlspecialchars($imagen) ?>" 
                                   placeholder="Por defecto: https://placehold.co/150 (Podrás subir un archivo propio más tarde)">
                            <div class="form-text">Si se deja en blanco se utilizará la imagen por defecto.</div>
                        </div>

                        <!-- Descripción -->
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-semibold">Descripción del Producto</label>
                            <textarea class="form-control" 
                                      id="descripcion" 
                                      name="descripcion" 
                                      rows="4" 
                                      placeholder="Añade detalles, especificaciones o características..."><?= htmlspecialchars($descripcion) ?></textarea>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-lg me-1"></i>Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Inclusión del pie de página común
require_once 'footer.php';
?>