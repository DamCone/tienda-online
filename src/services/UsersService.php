<?php

namespace services;

use Exception;
use models\User;
use PDO;

class UsersService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function findUserByUsername(string $username): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = :username AND is_deleted = false");
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        // Obtener roles del usuario
        $rolesStmt = $this->db->prepare("SELECT roles FROM user_roles WHERE user_id = :user_id");
        $rolesStmt->execute(['user_id' => $data['id']]);
        $roles = $rolesStmt->fetchAll(PDO::FETCH_COLUMN) ?: ['USER'];

        return new User(
            $data['id'],
            $data['username'],
            $data['password'],
            $data['nombre'],
            $data['apellidos'],
            $data['email'],
            $roles,
            $data['created_at'],
            $data['updated_at'],
            $data['is_deleted']
        );
    }

    public function authenticate(string $username, string $password): User
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            throw new Exception("Usuario o contraseña incorrectos.");
        }

        if (!password_verify($password, $user->password)) {
            throw new Exception("Usuario o contraseña incorrectos.");
        }

        return $user;
    }
}