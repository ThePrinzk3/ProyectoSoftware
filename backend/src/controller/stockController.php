<?php
namespace Src\Controller;

require_once __DIR__ . '/../model/stock.php';
require_once __DIR__ . '/../model/conexion.php';

class StockController {
    private $model;

    public function __construct()
    {
        $this->model = new \Src\Model\StockModel($GLOBALS['pdo']);
    }

    // Mostrar stock.html
    public function mostrarStock() {
        $ruta = realpath(__DIR__ . '/../../../frontend/stock.html');
        if ($ruta && file_exists($ruta)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($ruta);
        } else {
            echo "No se encontró el archivo de stock.";
        }
    }

    // Registrar entrada de stock (POST)
    public function registrarEntrada() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        // Asegúrate de tener el usuario_id en sesión
        $input['usuario_id'] = $_SESSION['user']['id'] ?? 1;
        $result = $this->model->registrarEntrada($input);
        echo json_encode($result);
    }

    // Registrar salida de stock (POST)
    public function registrarSalida() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $input['usuario_id'] = $_SESSION['user']['id'] ?? 1;
        $result = $this->model->registrarSalida($input);
        echo json_encode($result);
    }

    // Listar movimientos
    public function listarMovimientos()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $result = $this->model->listarMovimientos();
        echo json_encode(['exito' => true, 'movimientos' => $result]);
    }

    // Buscar stock por código
    public function buscar()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $codigo = $_GET['codigo'] ?? '';
        $result = $this->model->buscarPorCodigo($codigo);
        echo json_encode($result);
    }

    // (Opcional) Listar stock actual de todos los productos
    public function listarStockActual()
    {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $result = $this->model->listarStockActual();
        echo json_encode(['exito' => true, 'stock' => $result]);
    }
}