<?php

namespace models;

use Ramsey\Uuid\Uuid;

class Producto
{
    public const IMAGEN_DEFAULT = 'https://placehold.co/150';

    private $id;
    private $uuid;
    private $marca;
    private $modelo;
    private $descripcion;
    private $precio;
    private $stock;
    private $imagen;
    private $categoriaId;
    private $categoriaNombre;
    private $createdAt;
    private $updatedAt;
    private $isDeleted;

    public function __construct(
        $id = null,
        $uuid = null,
        $marca = null,
        $modelo = null,
        $descripcion = null,
        $precio = 0.0,
        $stock = 0,
        $imagen = self::IMAGEN_DEFAULT,
        $categoriaId = null,
        $categoriaNombre = null,
        $createdAt = null,
        $updatedAt = null,
        $isDeleted = false
    ) {
        $this->id = $id;
        $this->uuid = $uuid ?? $this->generateUUID();
        $this->marca = $marca;
        $this->modelo = $modelo;
        $this->descripcion = $descripcion;
        $this->precio = (float)$precio;
        $this->stock = (int)$stock;
        $this->imagen = empty($imagen) ? self::IMAGEN_DEFAULT : $imagen;
        $this->categoriaId = $categoriaId;
        $this->categoriaNombre = $categoriaNombre;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->isDeleted = (bool)$isDeleted;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    private function generateUUID(): string
    {
        return Uuid::uuid4()->toString();
    }
}