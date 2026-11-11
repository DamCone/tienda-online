<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Config.php';
require_once __DIR__ . '/../src/services/SessionService.php';
require_once __DIR__ . '/../src/services/UsersService.php';

use config\Config;
use services\SessionService;
use services\UsersService;

$session = SessionService::getInstance();

if ($session->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Por favor, rellena todos los campos.';
    } else {
        try {
            $config = Config::getInstance();
            $usersService = new UsersService($config->db);
            $user = $usersService->authenticate($username, $password);
            
            $session->login($user);
            header('Location: index.php?msg=' . urlencode('¡Bienvenido de nuevo, ' . $user->username . '!'));
            exit;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
}

require_once 'header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0"><i class="bi bi-lock me-2"></i>Iniciar Sesión</h4>
            </div>
            <div class="card-body p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="username" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Acceder
                    </button>
                </form>

                <div class="mt-4 p-3 bg-light rounded text-muted small">
                    <strong>Usuarios de prueba (database/init.sql):</strong>
                    <ul class="mb-0 ps-3">
                        <li><code>admin</code> / <code>Admin1</code> (Rol ADMIN)</li>
                        <li><code>user</code> / <code>User1234</code> (Rol USER)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>