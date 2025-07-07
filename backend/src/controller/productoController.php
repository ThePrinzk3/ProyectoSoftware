<?php
namespace Src\Controller;

require_once __DIR__ . '/../model/producto.php';
require_once __DIR__ . '/../model/conexion.php';

class ProductoController {
    private $model;

    public function __construct()
    {
        $this->model = new \ProductoModel($GLOBALS['pdo']);
    }

    // Mostrar producto.html
    public function mostrarProducto() {
        $ruta = realpath(__DIR__ . '/../../../frontend/producto.html');
        if ($ruta && file_exists($ruta)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($ruta);
        } else {
            echo "No se encontró el archivo de producto.";
        }
    }

    // Registrar producto (POST)
    public function registrar() {
        session_start();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        // Asegúrate de tener el usuario_id en sesión
        $input['usuario_id'] = $_SESSION['user']['id'] ?? 1;
        $result = $this->model->registrar($input);
        echo json_encode($result);
    }

    // Listar productos por categoría (GET)
    public function listarPorCategoria() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $categoria = $_GET['categoria'] ?? '';
        $productos = $this->model->listarPorCategoria($categoria);
        echo json_encode(['exito' => true, 'productos' => $productos]);
    }


    

    // Buscar producto por código o nombre y categoría (GET)
    public function buscar() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $busqueda = $_GET['busqueda'] ?? ''; // Cambia 'codigo' por 'busqueda'
        $categoria = $_GET['categoria'] ?? '';
        $productos = $this->model->buscar($busqueda, $categoria);
        echo json_encode(['exito' => true, 'productos' => $productos]);
    }




    // Actualizar producto (PUT)
    public function actualizar($codigo) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $input = json_decode(file_get_contents('php://input'), true);
        $result = $this->model->actualizar($codigo, $input);
        echo json_encode($result);
    }

    // Eliminar producto (DELETE)
    public function eliminar($codigo) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
            return;
        }
        $result = $this->model->eliminar($codigo);
        echo json_encode($result);
    }
}