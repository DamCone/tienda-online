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

// Instancias de servicios mediante Singleton y Config
$session = SessionService::getInstance();
$config = Config::getInstance();
$productosService = new ProductosService($config->db);

// Comprobación de roles y estados
$isLoggedIn = $session->isLoggedIn();
$isAdmin = $session->isAdmin();

// Gestión del buscador (filtra por marca o modelo)
$search = trim($_GET['search'] ?? '');
$productos = $productosService->findAllWithCategoryName($search);

// Inclusión de la cabecera común
require_once 'header.php';
?>

<!-- Sección de Bienvenida -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <div>
        <h2 class="h3 mb-0 text-dark">Catálogo de Productos</h2>
        <p class="text-muted mb-0">
            <?php if ($isLoggedIn): ?>
                Bienvenido/a de nuevo, <strong><?= htmlspecialchars($session->getUsername()) ?></strong>.
            <?php else: ?>
                Bienvenido/a. Estás navegando como <em>Invitado</em>. Para ver detalles de productos, <a href="login.php">inicia sesión</a>.
            <?php endif; ?>
        </p>
    </div>

    <!-- Botón Crear Producto (Exclusivo ADMIN) -->
    <?php if ($isAdmin): ?>
        <a href="create.php" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg me-1"></i>Nuevo Producto
        </a>
    <?php endif; ?>
</div>

<!-- Barra de Búsqueda -->
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body">
        <form method="GET" action="index.php" class="row g-2 align-items-center">
            <div class="col-md-9 col-sm-8">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Buscar por marca o modelo..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Buscar</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Limpiar filtro">
                        <i class="bi bi-x-lg"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Productos -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th scope="col" style="width: 70px;">Imagen</th>
                    <th scope="col">Marca</th>
                    <th scope="col">Modelo</th>
                    <th scope="col">Categoría</th>
                    <th scope="col" class="text-end">Precio</th>
                    <th scope="col" class="text-center">Stock</th>
                    <th scope="col" class="text-center" style="min-width: 160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No se encontraron productos disponibles.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($productos as $producto): ?>
                        <tr>
                            <!-- Miniatura de imagen -->
                            <td>
                                <img src="<?= htmlspecialchars($producto->imagen) ?>" 
                                     alt="<?= htmlspecialchars($producto->modelo) ?>" 
                                     class="rounded object-fit-cover border"
                                     style="width: 50px; height: 50px;"
                                     onerror="this.src='https://placehold.co/50';">
                            </td>

                            <!-- Datos del producto -->
                            <td class="fw-semibold"><?= htmlspecialchars($producto->marca) ?></td>
                            <td><?= htmlspecialchars($producto->modelo) ?></td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary-emphasis border">
                                    <?= htmlspecialchars($producto->categoriaNombre ?? 'Sin categoría') ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold">
                                <?= number_format($producto->precio, 2, ',', '.') ?> €
                            </td>
                            <td class="text-center">
                                <?php if ($producto->stock > 5): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <?= (int)$producto->stock ?> uds.
                                    </span>
                                <?php elseif ($producto->stock > 0): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                                        <?= (int)$producto->stock ?> uds.
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        Agotado
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Botones de Acción según permisos -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <?php if ($isLoggedIn): ?>
                                        <!-- Ver Detalles (USER y ADMIN) -->
                                        <a href="details.php?id=<?= (int)$producto->id ?>" 
                                           class="btn btn-outline-info" 
                                           title="Ver detalles">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($isAdmin): ?>
                                        <!-- Cambiar Imagen (Solo ADMIN) -->
                                        <a href="update-image.php?id=<?= (int)$producto->id ?>" 
                                           class="btn btn-outline-secondary" 
                                           title="Cambiar imagen">
                                            <i class="bi bi-image"></i>
                                        </a>

                                        <!-- Editar Producto (Solo ADMIN) -->
                                        <a href="update.php?id=<?= (int)$producto->id ?>" 
                                           class="btn btn-outline-primary" 
                                           title="Modificar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <!-- Eliminar Producto (Solo ADMIN) -->
                                        <a href="delete.php?id=<?= (int)$producto->id ?>" 
                                           class="btn btn-outline-danger" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Seguro que deseas eliminar este producto (<?= addslashes(htmlspecialchars($producto->marca . ' ' . $producto->modelo)) ?>)?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php endif; ?>

                                    <?php if (!$isLoggedIn): ?>
                                        <span class="text-muted small">
                                            <em>Login requerido</em>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Información de sesión del usuario (visitas y último acceso) -->
<?php if ($isLoggedIn): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-white shadow-sm p-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center text-muted small">
                    <div>
                        <i class="bi bi-eye me-1"></i>
                        Número de visitas en esta sesión: <strong><?= (int)$session->getVisits() ?></strong>
                    </div>
                    <?php if ($session->getLastLogin()): ?>
                        <div>
                            <i class="bi bi-clock-history me-1"></i>
                            Último inicio de sesión: <strong><?= htmlspecialchars($session->getLastLogin()) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
// Inclusión del pie de página
require_once 'footer.php';
?>