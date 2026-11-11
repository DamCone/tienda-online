<?php
ob_start();

// Carga de dependencias y servicios según la tabla de rutas
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/ProductosService.php';

use config\Config;
use services\ProductosService;
use services\SessionService;

// 1. Control de seguridad: solo peticiones POST de administradores
$session = SessionService::getInstance();
$session->requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php?error=' . urlencode('Método no permitido.'));
    exit;
}

// 2. Validación del ID de producto
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    header('Location: index.php?error=' . urlencode('Identificador de producto no válido.'));
    exit;
}

$config = Config::getInstance();
$productosService = new ProductosService($config->db);
$producto = $productosService->findById($id);

if (!$producto) {
    header('Location: index.php?error=' . urlencode('El producto no existe.'));
    exit;
}

// 3. Comprobación del archivo subido
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    header('Location: update-image.php?id=' . $id . '&error=' . urlencode('Error al subir el archivo o no se ha seleccionado ninguno.'));
    exit;
}

$fileTmpPath = $_FILES['imagen']['tmp_name'];
$fileName    = $_FILES['imagen']['name'];
$fileSize    = $_FILES['imagen']['size'];

// Validar tamaño máximo (2 MB = 2 * 1024 * 1024 bytes)
if ($fileSize > 2097152) {
    header('Location: update-image.php?id=' . $id . '&error=' . urlencode('El archivo supera el tamaño máximo permitido (2 MB).'));
    exit;
}

// Validar extensión
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if (!in_array($fileExtension, $allowedExtensions, true)) {
    header('Location: update-image.php?id=' . $id . '&error=' . urlencode('Extensión no permitida. Solo se admiten archivos JPG, JPEG o PNG.'));
    exit;
}

// Validar tipo MIME real del contenido
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $fileTmpPath);
finfo_close($finfo);

$allowedMimes = ['image/jpeg', 'image/png'];
if (!in_array($mimeType, $allowedMimes, true)) {
    header('Location: update-image.php?id=' . $id . '&error=' . urlencode('El contenido del archivo no es una imagen válida (debe ser JPEG o PNG).'));
    exit;
}

// 4. Preparación de la carpeta destino (public/uploads/)
$uploadDir = rtrim($config->uploadPath, '/\\') . DIRECTORY_SEPARATOR;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 5. Eliminar la imagen anterior si era un archivo físico local
if (!empty($producto->imagen) && strpos($producto->imagen, 'placehold.co') === false) {
    $oldFileName = basename(parse_url($producto->imagen, PHP_URL_PATH));
    $oldFilePath = $uploadDir . $oldFileName;
    if (file_exists($oldFilePath) && is_file($oldFilePath)) {
        unlink($oldFilePath);
    }
}

// 6. Generar nombre único basado en el UUID del producto y mover archivo
$newFileName = $producto->uuid . '.' . $fileExtension;
$destinationPath = $uploadDir . $newFileName;

if (!move_uploaded_file($fileTmpPath, $destinationPath)) {
    header('Location: update-image.php?id=' . $id . '&error=' . urlencode('No se pudo guardar la imagen en el servidor. Revisa los permisos de la carpeta uploads/.'));
    exit;
}

// 7. Actualizar la ruta en la base de datos (ruta relativa pública: uploads/nombre.ext)
$relativeDbPath = 'uploads/' . $newFileName;
if ($productosService->updateImage($producto->id, $relativeDbPath)) {
    header('Location: update-image.php?id=' . $id . '&msg=' . urlencode('Imagen actualizada correctamente.'));
    exit;
} else {
    header('Location: index.php?error=' . urlencode('La imagen se subió pero no se pudo actualizar el registro en la base de datos.'));
    exit;
}

ob_end_flush();