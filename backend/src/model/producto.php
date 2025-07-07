<?php
// filepath: c:\Users\ThePrinz\Desktop\PROYECTO WEB - PHP\backend\src\model\producto.php

class ProductoModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Generar código automático según categoría
    private function generarCodigo($categoria)
    {
        $prefijo = '';
        if ($categoria === 'EPPs') $prefijo = 'E';
        elseif ($categoria === 'Herramientas de Trabajo') $prefijo = 'H';
        elseif ($categoria === 'Materiales') $prefijo = 'M';
        else $prefijo = 'X';

        $stmt = $this->pdo->prepare("SELECT codigo FROM Producto WHERE categoria = ? AND codigo LIKE ? ORDER BY codigo DESC LIMIT 1");
        $stmt->execute([$categoria, $prefijo . '%']);
        $ultimo = $stmt->fetchColumn();

        if ($ultimo) {
            $num = intval(substr($ultimo, 1)) + 1;
        } else {
            $num = 1;
        }
        return $prefijo . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    // REGISTRAR PRODUCTO
    public function registrar($data)
    {
        $this->pdo->beginTransaction();
        try {
            // Generar código automáticamente
            $codigo = $this->generarCodigo($data['categoria']);

            $stmt = $this->pdo->prepare(
                "INSERT INTO Producto (codigo, nombre, categoria, descripcion, costo, usuario_id) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $codigo,
                $data['nombre'],
                $data['categoria'],
                $data['descripcion'],
                $data['costo'],
                $data['usuario_id']
            ]);
            $productoId = $this->pdo->lastInsertId();

            if ($data['categoria'] === 'EPPs') {
                $stmt = $this->pdo->prepare("INSERT INTO EPPs (producto_id, talla) VALUES (?, ?)");
                $stmt->execute([$productoId, $data['talla']]);
            } elseif ($data['categoria'] === 'Herramientas de Trabajo') {
                $stmt = $this->pdo->prepare("INSERT INTO Herramientas (producto_id, marca, modelo) VALUES (?, ?, ?)");
                $stmt->execute([$productoId, $data['marca'], $data['modelo']]);
            } elseif ($data['categoria'] === 'Materiales') {
                $stmt = $this->pdo->prepare("INSERT INTO Materiales (producto_id, medida) VALUES (?, ?)");
                $stmt->execute([$productoId, $data['medida']]);
            }

            $this->pdo->commit();
            return [
                'exito' => true,
                'mensaje' => 'Producto registrado correctamente.',
                'codigo' => $codigo // Devuelve el código generado
            ];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => 'Error al registrar producto.', 'error' => $e->getMessage()];
        }
    }

    // LISTAR PRODUCTOS POR CATEGORÍA
    public function listarPorCategoria($categoria)
    {
        if ($categoria === 'EPPs') {
            $stmt = $this->pdo->prepare("
                SELECT p.codigo, p.nombre, e.talla, p.descripcion, p.costo, p.fecha_registro
                FROM Producto p
                JOIN EPPs e ON p.id_producto = e.producto_id
                WHERE p.categoria = 'EPPs'
                ORDER BY p.fecha_registro DESC
            ");
            $stmt->execute();
        } elseif ($categoria === 'Herramientas de Trabajo') {
            $stmt = $this->pdo->prepare("
                SELECT p.codigo, p.nombre, h.marca, h.modelo, p.descripcion, p.costo, p.fecha_registro
                FROM Producto p
                JOIN Herramientas h ON p.id_producto = h.producto_id
                WHERE p.categoria = 'Herramientas de Trabajo'
                ORDER BY p.fecha_registro DESC
            ");
            $stmt->execute();
        } elseif ($categoria === 'Materiales') {
            $stmt = $this->pdo->prepare("
                SELECT p.codigo, p.nombre, m.medida, p.descripcion, p.costo, p.fecha_registro
                FROM Producto p
                JOIN Materiales m ON p.id_producto = m.producto_id
                WHERE p.categoria = 'Materiales'
                ORDER BY p.fecha_registro DESC
            ");
            $stmt->execute();
        } else {
            return [];
        }
        return $stmt->fetchAll();
    }

    // BUSCAR PRODUCTO POR CÓDIGO O NOMBRE Y CATEGORÍA
    public function buscar($busqueda, $categoria)
    {
        // Si el string de búsqueda es largo, asumimos que es nombre, si es corto y sin espacios, puede ser código
        $esCodigo = preg_match('/^[A-Z]\d{3}$/i', $busqueda);

        if ($categoria === 'EPPs') {
            if ($esCodigo) {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, e.talla, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN EPPs e ON p.id_producto = e.producto_id
                    WHERE p.categoria = 'EPPs' AND p.codigo = ?
                ");
                $stmt->execute([$busqueda]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, e.talla, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN EPPs e ON p.id_producto = e.producto_id
                    WHERE p.categoria = 'EPPs' AND LOWER(p.nombre) LIKE ?
                ");
                $stmt->execute(['%' . strtolower($busqueda) . '%']);
            }
        } elseif ($categoria === 'Herramientas de Trabajo') {
            if ($esCodigo) {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, h.marca, h.modelo, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN Herramientas h ON p.id_producto = h.producto_id
                    WHERE p.categoria = 'Herramientas de Trabajo' AND p.codigo = ?
                ");
                $stmt->execute([$busqueda]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, h.marca, h.modelo, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN Herramientas h ON p.id_producto = h.producto_id
                    WHERE p.categoria = 'Herramientas de Trabajo' AND LOWER(p.nombre) LIKE ?
                ");
                $stmt->execute(['%' . strtolower($busqueda) . '%']);
            }
        } elseif ($categoria === 'Materiales') {
            if ($esCodigo) {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, m.medida, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN Materiales m ON p.id_producto = m.producto_id
                    WHERE p.categoria = 'Materiales' AND p.codigo = ?
                ");
                $stmt->execute([$busqueda]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT p.codigo, p.nombre, m.medida, p.descripcion, p.costo, p.fecha_registro
                    FROM Producto p
                    JOIN Materiales m ON p.id_producto = m.producto_id
                    WHERE p.categoria = 'Materiales' AND LOWER(p.nombre) LIKE ?
                ");
                $stmt->execute(['%' . strtolower($busqueda) . '%']);
            }
        } else {
            return [];
        }
        return $stmt->fetchAll();
    }

    // ACTUALIZAR PRODUCTO
    public function actualizar($codigo, $data)
    {
        $this->pdo->beginTransaction();
        try {
            // 1. Obtener id_producto y categoría actual
            $stmt = $this->pdo->prepare("SELECT id_producto, categoria FROM Producto WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $row = $stmt->fetch();
            if (!$row) {
                $this->pdo->rollBack();
                return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
            }
            $id_producto = $row['id_producto'];

            // 2. Actualizar Producto
            $stmt = $this->pdo->prepare("UPDATE Producto SET nombre = ?, categoria = ?, descripcion = ?, costo = ? WHERE id_producto = ?");
            $stmt->execute([
                $data['nombre'],
                $data['categoria'],
                $data['descripcion'],
                $data['costo'],
                $id_producto
            ]);

            // 3. Eliminar de tablas hijas
            $this->pdo->prepare("DELETE FROM EPPs WHERE producto_id = ?")->execute([$id_producto]);
            $this->pdo->prepare("DELETE FROM Herramientas WHERE producto_id = ?")->execute([$id_producto]);
            $this->pdo->prepare("DELETE FROM Materiales WHERE producto_id = ?")->execute([$id_producto]);

            // 4. Insertar en tabla hija correspondiente
            if ($data['categoria'] === 'EPPs') {
                $this->pdo->prepare("INSERT INTO EPPs (producto_id, talla) VALUES (?, ?)")->execute([$id_producto, $data['talla']]);
            } elseif ($data['categoria'] === 'Herramientas de Trabajo') {
                $this->pdo->prepare("INSERT INTO Herramientas (producto_id, marca, modelo) VALUES (?, ?, ?)")->execute([$id_producto, $data['marca'], $data['modelo']]);
            } elseif ($data['categoria'] === 'Materiales') {
                $this->pdo->prepare("INSERT INTO Materiales (producto_id, medida) VALUES (?, ?)")->execute([$id_producto, $data['medida']]);
            }

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Producto actualizado correctamente.'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => 'Error al actualizar producto.', 'error' => $e->getMessage()];
        }
    }

    // ELIMINAR PRODUCTO
    public function eliminar($codigo)
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT id_producto, categoria FROM Producto WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $row = $stmt->fetch();
            if (!$row) {
                $this->pdo->rollBack();
                return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
            }
            $id_producto = $row['id_producto'];
            $categoria = $row['categoria'];

            // Eliminar de tabla hija
            $this->pdo->prepare("DELETE FROM EPPs WHERE producto_id = ?")->execute([$id_producto]);
            $this->pdo->prepare("DELETE FROM Herramientas WHERE producto_id = ?")->execute([$id_producto]);
            $this->pdo->prepare("DELETE FROM Materiales WHERE producto_id = ?")->execute([$id_producto]);

            // Eliminar de Producto
            $this->pdo->prepare("DELETE FROM Producto WHERE id_producto = ?")->execute([$id_producto]);

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Producto eliminado correctamente.'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => 'Error al eliminar producto.', 'error' => $e->getMessage()];
        }
    }
}