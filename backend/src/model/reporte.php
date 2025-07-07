<?php
namespace Src\Model;

class ReporteModel
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Inventario general (con filtro opcional por categoría)
    public function obtenerInventario($categoria = null)
    {
        $params = [];
        $sql = "
            SELECT 
                p.codigo,
                p.nombre,
                p.categoria,
                p.costo,
                COALESCE(m.stock, 0) AS stock,
                m.fecha AS fecha_ultimo_movimiento
            FROM Producto p
            LEFT JOIN (
                SELECT sm1.producto_id, sm1.stock, sm1.fecha
                FROM StockMovimiento sm1
                INNER JOIN (
                    SELECT producto_id, MAX(fecha) AS max_fecha
                    FROM StockMovimiento
                    GROUP BY producto_id
                ) sm2 ON sm1.producto_id = sm2.producto_id AND sm1.fecha = sm2.max_fecha
            ) m ON p.id_producto = m.producto_id
        ";
        
        if ($categoria && $categoria !== 'todas') {
            $sql .= " WHERE p.categoria = ?";
            $params[] = $categoria;
        }
        $sql .= " ORDER BY p.nombre ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}