<?php
namespace Src\Controller;

require_once __DIR__ . '/../model/user.php';
require_once __DIR__ . '/../model/conexion.php';

class UserController {
    public function mostrarLogin() {
        // Ruta absoluta al archivo HTML
        $ruta = realpath(__DIR__ . '/../../../frontend/login_index.html');
        if ($ruta && file_exists($ruta)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($ruta);
        } else {
            echo "No se encontró el archivo de login.";
        }
    }

    // Procesar login (para fetch/AJAX)
    public function login() {
        session_start();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['message' => 'Método no permitido']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = $input['nombre'] ?? '';
        $contraseña = $input['contraseña'] ?? '';

        // Inicializa variables de sesión para intentos y bloqueo
        if (!isset($_SESSION['intentos'])) $_SESSION['intentos'] = [];
        if (!isset($_SESSION['bloqueo'])) $_SESSION['bloqueo'] = [];

        // Verifica si el usuario está bloqueado
        if (isset($_SESSION['bloqueo'][$nombre]) && $_SESSION['bloqueo'][$nombre] > time()) {
            $desbloqueo = date('c', $_SESSION['bloqueo'][$nombre]);
            echo json_encode([
                'bloqueado' => true,
                'message' => 'Usuario bloqueado. Intente más tarde.',
                'desbloqueo' => $desbloqueo
            ]);
            return;
        }

        $userModel = new \UserModel($GLOBALS['pdo']);
        $usuario = $userModel->getUserByName($nombre);

        if (!$usuario || $usuario['contraseña'] !== $contraseña) {
            // Suma intento fallido
            $_SESSION['intentos'][$nombre] = ($_SESSION['intentos'][$nombre] ?? 0) + 1;

            if ($_SESSION['intentos'][$nombre] >= 3) {
                // Bloquea por 5 minutos
                $_SESSION['bloqueo'][$nombre] = time() + 5 * 60;
                $desbloqueo = date('c', $_SESSION['bloqueo'][$nombre]);
                echo json_encode([
                    'bloqueado' => true,
                    'message' => 'Usuario bloqueado por 5 minutos.',
                    'desbloqueo' => $desbloqueo
                ]);
                return;
            }

            http_response_code(401);
            echo json_encode(['message' => 'Usuario o contraseña incorrectos']);
            return;
        }

        // Login exitoso: limpia intentos y bloqueo
        unset($_SESSION['intentos'][$nombre]);
        unset($_SESSION['bloqueo'][$nombre]);
        $_SESSION['user'] = ['id' => $usuario['id'], 'nombre' => $usuario['nombre']];

        echo json_encode([
            'message' => 'Login exitoso',
            'usuario' => ['id' => $usuario['id'], 'nombre' => $usuario['nombre']]
        ]);
    }
}