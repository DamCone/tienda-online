<?php
// Iniciamos búfer de salida para permitir redirecciones limpias
ob_start();

// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

// Control de permisos: solo administradores
$session = SessionService::getInstance();
$session->requireAdmin();

// Recepción y validación del parámetro id
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de producto no válido.'));
    exit;
}

// Inicialización de servicios
$config = Config::getInstance();
$productosService = new ProductosService($config->db);

// Comprobar que el producto existe antes de intentar borrar
$producto = $productosService->findById($id);
if (!$producto) {
    header('Location: index.php?error=' . urlencode('El producto que intentas eliminar no existe.'));
    exit;
}

// 1. Gestión de la imagen física asociada (evitar archivos huérfanos)
if (!empty($producto->imagen)) {
    // Si la imagen no es un placeholder remoto, buscamos si existe en public/uploads/
    if (strpos($producto->imagen, 'placehold.co') === false) {
        $filename = basename(parse_url($producto->imagen, PHP_URL_PATH));
        $filePath = rtrim($config->uploadPath, '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!empty($filename) && file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
}

// 2. Eliminación del producto en la base de datos
$nombreProducto = $producto->marca . ' ' . $producto->modelo;
if ($productosService->deleteById($id)) {
    header('Location: index.php?msg=' . urlencode('El producto "' . $nombreProducto . '" ha sido eliminado correctamente.'));
    exit;
} else {
    header('Location: index.php?error=' . urlencode('No se pudo eliminar el producto de la base de datos.'));
    exit;
}

// En caso de renderizado por error de cabeceras, se incluye la plantilla común requerida
require_once 'header.php';
?>
<div class="alert alert-info">Procesando eliminación... Si no eres redirigido, <a href="index.php">pulsa aquí</a>.</div>
<?php
require_once 'footer.php';
ob_end_flush();
?>