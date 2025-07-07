<?php
namespace Src\Model;

class StockModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Registrar entrada de stock
    public function registrarEntrada($input)
    {
        $codigo = $input['codigo'] ?? '';
        $cantidad = $input['cantidad'] ?? '';
        $usuario_id = $input['usuario_id'] ?? 1;

        $this->pdo->beginTransaction();
        try {
            // Buscar producto
            $stmt = $this->pdo->prepare("SELECT id_producto FROM Producto WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $prod = $stmt->fetch();
            if (!$prod) {
                return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
            }
            $producto_id = $prod['id_producto'];

            // Registrar movimiento (el trigger calcula el stock)
            $stmt = $this->pdo->prepare("INSERT INTO StockMovimiento (producto_id, tipo, cantidad, usuario_id) VALUES (?, 'entrada', ?, ?)");
            $stmt->execute([$producto_id, $cantidad, $usuario_id]);

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Entrada de stock registrada.'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => 'Error al registrar entrada de stock.', 'error' => $e->getMessage()];
        }
    }

    // Registrar salida de stock
    public function registrarSalida($input)
    {
        $codigo = $input['codigo'] ?? '';
        $cantidad = $input['cantidad'] ?? '';
        $usuario_id = $input['usuario_id'] ?? 1;

        $this->pdo->beginTransaction();
        try {
            // Buscar producto
            $stmt = $this->pdo->prepare("SELECT id_producto FROM Producto WHERE codigo = ?");
            $stmt->execute([$codigo]);
            $prod = $stmt->fetch();
            if (!$prod) {
                return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
            }
            $producto_id = $prod['id_producto'];

            // Verificar stock actual (opcional, para evitar negativos)
            $stmt = $this->pdo->prepare("SELECT IFNULL(SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE -cantidad END), 0) AS stock_actual FROM StockMovimiento WHERE producto_id = ?");
            $stmt->execute([$producto_id]);
            $stock_actual = $stmt->fetchColumn();
            if ($stock_actual < $cantidad) {
                return ['exito' => false, 'mensaje' => 'Stock insuficiente.'];
            }

            // Registrar movimiento (el trigger calcula el stock)
            $stmt = $this->pdo->prepare("INSERT INTO StockMovimiento (producto_id, tipo, cantidad, usuario_id) VALUES (?, 'salida', ?, ?)");
            $stmt->execute([$producto_id, $cantidad, $usuario_id]);

            $this->pdo->commit();
            return ['exito' => true, 'mensaje' => 'Salida de stock registrada.'];
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => 'Error al registrar salida de stock.', 'error' => $e->getMessage()];
        }
    }

    // Listar movimientos
    public function listarMovimientos()
    {
        $stmt = $this->pdo->query("
            SELECT 
                p.codigo,
                p.nombre,
                m.tipo,
                m.cantidad,
                m.fecha,
                m.stock
            FROM StockMovimiento m
            JOIN Producto p ON m.producto_id = p.id_producto
            ORDER BY m.fecha DESC, m.id_movimiento DESC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    // Buscar stock por código
    public function buscarPorCodigo($codigo)
    {
        // Buscar producto
        $stmt = $this->pdo->prepare("SELECT id_producto, categoria AS tipo FROM Producto WHERE codigo = ?");
        $stmt->execute([$codigo]);
        $prod = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$prod) {
            return ['exito' => false, 'mensaje' => 'Producto no encontrado.'];
        }
        $producto_id = $prod['id_producto'];
        $tipo = $prod['tipo'];

        // Movimientos (ahora incluye el campo codigo en cada movimiento)
        $stmt = $this->pdo->prepare("
            SELECT 
                ? AS codigo,
                m.tipo,
                m.cantidad,
                m.fecha,
                m.stock
            FROM StockMovimiento m
            WHERE m.producto_id = ?
            ORDER BY m.fecha DESC, m.id_movimiento DESC
        ");
        $stmt->execute([$codigo, $producto_id]);
        $movimientos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stock_actual = count($movimientos) > 0 ? $movimientos[0]['stock'] : 0;

        return [
            'exito' => true,
            'codigo' => $codigo,
            'tipo' => $tipo,
            'stock_actual' => $stock_actual,
            'movimientos' => $movimientos
        ];
    }

    // Listar stock actual de todos los productos (opcional, usando tu consulta avanzada)
    public function listarStockActual()
    {
        $stmt = $this->pdo->query("
            SELECT 
              p.codigo,
              p.nombre,
              p.categoria,
              p.costo,
              IFNULL(m.stock, 0) AS stock,
              m.fecha AS fecha_ultimo_movimiento,
              u.nombre AS usuario_registro,
              mu.nombre AS usuario_ultimo_movimiento
            FROM Producto p
            LEFT JOIN (
              SELECT m1.*
              FROM StockMovimiento m1
              INNER JOIN (
                SELECT producto_id, MAX(fecha) AS max_fecha
                FROM StockMovimiento
                GROUP BY producto_id
              ) m2 ON m1.producto_id = m2.producto_id AND m1.fecha = m2.max_fecha
            ) m ON p.id_producto = m.producto_id
            LEFT JOIN Usuario u ON p.usuario_id = u.id
            LEFT JOIN Usuario mu ON m.usuario_id = mu.id
            ORDER BY p.nombre ASC
        ");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}