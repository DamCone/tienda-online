<?php

namespace models;

class User
{
    private $id;
    private $username;
    private $password;
    private $nombre;
    private $apellidos;
    private $email;
    private $roles; // Array de strings, ej: ['USER', 'ADMIN']
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct(
        $id = null,
        $username = null,
        $password = null,
        $nombre = null,
        $apellidos = null,
        $email = null,
        array $roles = ['USER'],
        $createdAt = null,
        $updatedAt = null,
        $isDeleted = false
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->apellidos = $apellidos;
        $this->email = $email;
        $this->roles = $roles;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = (bool)$isDeleted;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    public function isAdmin(): bool
    {
        return in_array('ADMIN', $this->getRoles(), true);
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }
}