<?php

namespace services;

use models\Producto;
use PDO;
use Ramsey\Uuid\Uuid;

class ProductosService
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllWithCategoryName(?string $searchTerm = null): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre 
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.is_deleted = false";

        $params = [];
        if (!empty($searchTerm)) {
            $sql .= " AND (p.marca ILIKE :search OR p.modelo ILIKE :search)";
            $params['search'] = '%' . trim($searchTerm) . '%';
        }

        $sql .= " ORDER BY p.id ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $productos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = new Producto(
                $row['id'],
                $row['uuid'],
                $row['marca'],
                $row['modelo'],
                $row['descripcion'],
                $row['precio'],
                $row['stock'],
                $row['imagen'],
                $row['categoria_id'],
                $row['categoria_nombre'],
                $row['created_at'],
                $row['updated_at'],
                $row['is_deleted']
            );
        }

        return $productos;
    }

    public function findById(int $id): ?Producto
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre 
                FROM productos p
                LEFT JOIN categorias c ON p.categoria_id = c.id
                WHERE p.id = :id AND p.is_deleted = false";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Producto(
            $row['id'],
            $row['uuid'],
            $row['marca'],
            $row['modelo'],
            $row['descripcion'],
            $row['precio'],
            $row['stock'],
            $row['imagen'],
            $row['categoria_id'],
            $row['categoria_nombre'],
            $row['created_at'],
            $row['updated_at'],
            $row['is_deleted']
        );
    }

    public function save(Producto $producto): bool
    {
        $sql = "INSERT INTO productos (uuid, marca, modelo, descripcion, precio, stock, imagen, categoria_id, created_at, updated_at, is_deleted)
                VALUES (:uuid, :marca, :modelo, :descripcion, :precio, :stock, :imagen, :categoria_id, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, false)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'uuid'         => $producto->uuid ?: Uuid::uuid4()->toString(),
            'marca'        => $producto->marca,
            'modelo'       => $producto->modelo,
            'descripcion'  => $producto->descripcion,
            'precio'       => $producto->precio,
            'stock'        => $producto->stock,
            'imagen'       => $producto->imagen ?: Producto::IMAGEN_DEFAULT,
            'categoria_id' => $producto->categoriaId
        ]);
    }

    public function update(Producto $producto): bool
    {
        $sql = "UPDATE productos 
                SET marca = :marca,
                    modelo = :modelo,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock = :stock,
                    categoria_id = :categoria_id,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id AND is_deleted = false";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'id'           => $producto->id,
            'marca'        => $producto->marca,
            'modelo'       => $producto->modelo,
            'descripcion'  => $producto->descripcion,
            'precio'       => $producto->precio,
            'stock'        => $producto->stock,
            'categoria_id' => $producto->categoriaId
        ]);
    }

    public function updateImage(int $id, string $imageUrl): bool
    {
        $sql = "UPDATE productos 
                SET imagen = :imagen, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'imagen' => $imageUrl,
            'id'     => $id
        ]);
    }

    public function deleteById(int $id): bool
    {
        // Borrado físico según especificación del proyecto
        $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}