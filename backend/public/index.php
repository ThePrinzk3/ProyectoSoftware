<?php
// filepath: backend/public/index.php

header('Access-Control-Allow-Origin: *'); // Permite peticiones CORS desde cualquier origen. Útil para APIs en desarrollo; en producción restringir el origen por seguridad.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Obtiene solo la ruta (path) de la URL solicitada (ej. "/api/productos/listar"), sin query string.
$method = $_SERVER['REQUEST_METHOD']; // Método HTTP de la petición (GET, POST, PUT, DELETE, etc.).

// Servir archivos estáticos de frontend
if (strpos($uri, '/frontend/') === 0) { // Si la ruta comienza con "/frontend/", consideramos que es un archivo estático del frontend.
    $file = realpath(__DIR__ . '/../..' . $uri); // Construye la ruta absoluta al archivo: __DIR__ = backend/public, sube 2 niveles y concatena $uri.
    if ($file && file_exists($file)) { // Verifica que realpath devolvió una ruta y que el archivo existe.
        $ext = pathinfo($file, PATHINFO_EXTENSION); // Obtiene la extensión del archivo (css, js, html, png, etc.).
        if ($ext === 'css') header('Content-Type: text/css'); // Si es .css, envía el header correcto.
        if ($ext === 'js') header('Content-Type: application/javascript'); // Si es .js, envía el header correcto.
        readfile($file); // Lee el archivo y lo envía directamente al cliente.
        exit; // Termina la ejecución del script (respuesta enviada).
    } else {
        http_response_code(404); // Si no existe, responde 404 Not Found.
        echo "Archivo no encontrado.";
        exit; // Termina la ejecución.
    }
}

// --- RUTAS API PRODUCTOS ---
if (strpos($uri, '/api/productos/') === 0) { // Si la ruta pertenece al prefijo /api/productos/
    require_once __DIR__ . '/../src/controller/productoController.php'; // Incluye el controlador de productos.
    $controller = new \Src\Controller\ProductoController(); // Crea una instancia del controller (namespace \Src\Controller).

    if ($uri === '/api/productos/listar' && $method === 'GET') { // GET /api/productos/listar
        $controller->listarPorCategoria(); // Llama al método que lista productos por categoría.
        exit;
    }
    if ($uri === '/api/productos/registrar' && $method === 'POST') { // POST /api/productos/registrar
        $controller->registrar(); // Llama al método que registra un nuevo producto.
        exit;
    }
    if ($uri === '/api/productos/buscar' && $method === 'GET') { // GET /api/productos/buscar?...
        $controller->buscar(); // Llama al método que busca productos (probablemente usa query params).
        exit;
    }
    if (preg_match('#^/api/productos/actualizar/([^/]+)$#', $uri, $m) && $method === 'PUT') { // PUT /api/productos/actualizar/{id}
        $controller->actualizar($m[1]); // $m[1] contiene la parte capturada por la regex (p. ej. el id). Llama a actualizar con ese parámetro.
        exit;
    }
    if (preg_match('#^/api/productos/eliminar/([^/]+)$#', $uri, $m) && $method === 'DELETE') { // DELETE /api/productos/eliminar/{id}
        $controller->eliminar($m[1]); // Llama a eliminar con el id pasado en la URL.
        exit;
    }
    http_response_code(404); // Si no se matcheó ninguna ruta/producto válida, devuelve 404 para este prefijo.
    echo "API endpoint no encontrado.";
    exit;
}

// --- RUTAS API STOCK ---
if (strpos($uri, '/api/stock/') === 0) { // Si la ruta pertenece al prefijo /api/stock/
    require_once __DIR__ . '/../src/controller/stockController.php'; // Incluye el controlador de stock.
    $controller = new \Src\Controller\StockController($pdo); // Crea el controller de stock y le pasa $pdo (la conexión a BD).
    // NOTA: Asegúrate de que $pdo esté inicializado antes de este archivo (p. ej. include de db.php). Aquí se asume que existe.

    if ($uri === '/api/stock/entrada' && $method === 'POST') { // POST /api/stock/entrada
        $controller->registrarEntrada(); // Registra una entrada de stock.
        exit;
    }
    if ($uri === '/api/stock/salida' && $method === 'POST') { // POST /api/stock/salida
        $controller->registrarSalida(); // Registra una salida de stock.
        exit;
    }
    if ($uri === '/api/stock/movimientos' && $method === 'GET') { // GET /api/stock/movimientos
        $controller->listarMovimientos(); // Lista los movimientos de stock.
        exit;
    }
    if ($uri === '/api/stock/buscar' && $method === 'GET') { // GET /api/stock/buscar?...
        $controller->buscar(); // Busca movimientos o productos en stock según parámetros.
        exit;
    }
    http_response_code(404); // Endpoint no reconocido dentro de /api/stock/
    echo "API endpoint de stock no encontrado.";
    exit;
}

// --- RUTAS API REPORTE ---
if (strpos($uri, '/api/reporte/') === 0) { // Si la ruta pertenece al prefijo /api/reporte/
    require_once __DIR__ . '/../src/controller/reporteController.php'; // Incluye el controlador de reportes.
    $controller = new \Src\Controller\ReporteController(); // Instancia el controller de reportes.

    if ($uri === '/api/reporte/inventario' && $method === 'GET') { // GET /api/reporte/inventario
        $controller->inventario(); // Genera un reporte de inventario (probablemente JSON).
        exit;
    }
    if ($uri === '/api/reporte/inventario/pdf' && $method === 'GET') { // GET /api/reporte/inventario/pdf
        $controller->inventarioPDF(); // Genera o envía un PDF del inventario.
        exit;
    }
    if ($uri === '/api/reporte/inventario/excel' && $method === 'GET') { // GET /api/reporte/inventario/excel
        $controller->inventarioExcel(); // Genera o envía un Excel del inventario.
        exit;
    }
    http_response_code(404); // Endpoint no reconocido en /api/reporte/
    echo "API endpoint de reporte no encontrado.";
    exit;
}

// --- RUTAS DE VISTAS Y OTROS CONTROLADORES ---
switch ($uri) { // Switch basado en la ruta para servir vistas HTML u otros controladores.
    case '/':
    case '/login':
        require_once __DIR__ . '/../src/controller/userController.php'; // Incluye controlador de usuarios.
        $controller = new \Src\Controller\UserController(); // Instancia el controlador de usuario.
        if ($method === 'POST') { // Si se envió formulario (login)
            $controller->login(); // Procesa el login (autenticación).
        } else {
            $controller->mostrarLogin(); // Muestra la vista/HTML del formulario de login.
        }
        break;

    case '/menu.html':
    case '/menu':
        $ruta = realpath(__DIR__ . '/../../frontend/menu.html'); // Construye la ruta absoluta al archivo menu.html del frontend.
        if ($ruta && file_exists($ruta)) { // Si existe el archivo
            header('Content-Type: text/html; charset=utf-8'); // Header para HTML con codificación UTF-8.
            readfile($ruta); // Envía el contenido del archivo HTML.
        } else {
            http_response_code(404); // Si no existe el archivo, responde 404.
            echo "Archivo no encontrado.";
        }
        break;

    case '/productos':
        require_once __DIR__ . '/../src/controller/productoController.php'; // Incluye el controlador de productos.
        $controller = new \Src\Controller\ProductoController(); // Instancia el controller.
        $controller->mostrarProductos(); // Llama al método que muestra la vista/listado de productos.
        break;

    case '/stock':
        require_once __DIR__ . '/../src/controller/stockController.php'; // Incluye el controlador de stock.
        $controller = new \Src\Controller\StockController(); // Instancia el controller. (Aquí no se pasa $pdo; revisa la firma del constructor)
        $controller->mostrarStock(); // Muestra la vista de stock.
        break;

    case '/reporte':
        require_once __DIR__ . '/../src/controller/reporteController.php'; // Incluye controlador de reportes.
        $controller = new \Src\Controller\ReporteController(); // Instancia el controller.
        $controller->mostrarReporte(); // Muestra la vista de reportes.
        break;

} // Fin del switch




// cd "C:\Users\ThePrinz\Desktop\PROYECTO WEB - PHP\backend" 
// php -S localhost:8000 -t public
// ngrok http 8000
