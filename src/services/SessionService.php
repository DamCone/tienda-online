<?php

namespace services;

use models\User;

class SessionService
{
    private static ?SessionService $instance = null;
    private int $expireAfterSeconds = 3600; // 1 hora de inactividad

    private function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->checkExpiration();
        $this->incrementVisits();
    }

    public static function getInstance(): SessionService
    {
        if (self::$instance === null) {
            self::$instance = new SessionService();
        }
        return self::$instance;
    }

    private function checkExpiration(): void
    {
        if (isset($_SESSION['last_activity'])) {
            $inactiveTime = time() - $_SESSION['last_activity'];
            if ($inactiveTime >= $this->expireAfterSeconds) {
                $this->logout();
            }
        }
        $_SESSION['last_activity'] = time();
    }

    private function incrementVisits(): void
    {
        $_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;
    }

    public function login(User $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['user_roles'] = $user->getRoles();
        $_SESSION['is_admin'] = $user->isAdmin();
        $_SESSION['last_login'] = date('d/m/Y H:i:s');
        $_SESSION['logged_in'] = true;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }
        session_destroy();
    }

    public function isLoggedIn(): bool
    {
        return !empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    public function isAdmin(): bool
    {
        return $this->isLoggedIn() && (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] === true);
    }

    public function getUsername(): string
    {
        return $_SESSION['username'] ?? 'Invitado';
    }

    public function getVisits(): int
    {
        return $_SESSION['visits'] ?? 1;
    }

    public function getLastLogin(): ?string
    {
        return $_SESSION['last_login'] ?? null;
    }

    public function requireAdmin(): void
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?error=' . urlencode('Acceso denegado: Se requiere rol de administrador.'));
            exit;
        }
    }

    public function requireAuth(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: login.php?error=' . urlencode('Debes iniciar sesión para acceder a esta sección.'));
            exit;
        }
    }
}