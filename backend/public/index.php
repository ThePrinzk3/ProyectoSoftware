<?php
// filepath: backend/public/index.php

header('Access-Control-Allow-Origin: *');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Servir archivos estáticos de frontend
if (strpos($uri, '/frontend/') === 0) {
    $file = realpath(__DIR__ . '/../..' . $uri);
    if ($file && file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext === 'css') header('Content-Type: text/css');
        if ($ext === 'js') header('Content-Type: application/javascript');
        readfile($file);
        exit;
    } else {
        http_response_code(404);
        echo "Archivo no encontrado.";
        exit;
    }
}

// --- RUTAS API PRODUCTOS ---
if (strpos($uri, '/api/productos/') === 0) {
    require_once __DIR__ . '/../src/controller/productoController.php';
    $controller = new \Src\Controller\ProductoController();

    if ($uri === '/api/productos/listar' && $method === 'GET') {
        $controller->listarPorCategoria();
        exit;
    }
    if ($uri === '/api/productos/registrar' && $method === 'POST') {
        $controller->registrar();
        exit;
    }
    if ($uri === '/api/productos/buscar' && $method === 'GET') {
        $controller->buscar();
        exit;
    }
    if (preg_match('#^/api/productos/actualizar/([^/]+)$#', $uri, $m) && $method === 'PUT') {
        $controller->actualizar($m[1]);
        exit;
    }
    if (preg_match('#^/api/productos/eliminar/([^/]+)$#', $uri, $m) && $method === 'DELETE') {
        $controller->eliminar($m[1]);
        exit;
    }
    http_response_code(404);
    echo "API endpoint no encontrado.";
    exit;
}

// --- RUTAS API STOCK ---
if (strpos($uri, '/api/stock/') === 0) {
    require_once __DIR__ . '/../src/controller/stockController.php';
    $controller = new \Src\Controller\StockController($pdo); // Pasa tu conexión PDO

    if ($uri === '/api/stock/entrada' && $method === 'POST') {
        $controller->registrarEntrada();
        exit;
    }
    if ($uri === '/api/stock/salida' && $method === 'POST') {
        $controller->registrarSalida();
        exit;
    }
    if ($uri === '/api/stock/movimientos' && $method === 'GET') {
        $controller->listarMovimientos();
        exit;
    }
    if ($uri === '/api/stock/buscar' && $method === 'GET') {
        $controller->buscar();
        exit;
    }
    http_response_code(404);
    echo "API endpoint de stock no encontrado.";
    exit;
}

// --- RUTAS API REPORTE ---
if (strpos($uri, '/api/reporte/') === 0) {
    require_once __DIR__ . '/../src/controller/reporteController.php';
    $controller = new \Src\Controller\ReporteController();

    if ($uri === '/api/reporte/inventario' && $method === 'GET') {
        $controller->inventario();
        exit;
    }
    if ($uri === '/api/reporte/inventario/pdf' && $method === 'GET') {
        $controller->inventarioPDF();
        exit;
    }
    if ($uri === '/api/reporte/inventario/excel' && $method === 'GET') {
        $controller->inventarioExcel();
        exit;
    }
    http_response_code(404);
    echo "API endpoint de reporte no encontrado.";
    exit;
}

// --- RUTAS DE VISTAS Y OTROS CONTROLADORES ---
switch ($uri) {
    case '/':
    case '/login':
        require_once __DIR__ . '/../src/controller/userController.php';
        $controller = new \Src\Controller\UserController();
        if ($method === 'POST') {
            $controller->login();
        } else {
            $controller->mostrarLogin();
        }
        break;

    case '/menu.html':
    case '/menu':
        $ruta = realpath(__DIR__ . '/../../frontend/menu.html');
        if ($ruta && file_exists($ruta)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($ruta);
        } else {
            http_response_code(404);
            echo "Archivo no encontrado.";
        }
        break;

    case '/productos':
        require_once __DIR__ . '/../src/controller/productoController.php';
        $controller = new \Src\Controller\ProductoController();
        $controller->mostrarProductos();
        break;

    case '/stock':
        require_once __DIR__ . '/../src/controller/stockController.php';
        $controller = new \Src\Controller\StockController();
        $controller->mostrarStock();
        break;

    case '/reporte':
        require_once __DIR__ . '/../src/controller/reporteController.php';
        $controller = new \Src\Controller\ReporteController();
        $controller->mostrarReporte();
        break;

}



// cd "C:\Users\ThePrinz\Desktop\PROYECTO WEB - PHP\backend" 
// php -S localhost:8000 -t public
// ngrok http 8000
