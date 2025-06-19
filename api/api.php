<?php
require_once __DIR__ . '/../config/Config.php';
require_once __DIR__ . '/../controllers/PlayerController.php';

header('Content-Type: application/json');

$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$controller = new PlayerController();

switch (true) {
    case preg_match('/\/api\/lyrics/', $request) && $method === 'GET':
        $trackId = $_GET['track_id'] ?? null;
        if ($trackId) {
            $controller->getLyrics($trackId);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Track ID is required']);
        }
        break;
        
    case preg_match('/\/api\/track\/play/', $request) && $method === 'POST':
        // Xử lý tracking play count
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['track_id'])) {
            // Verify CSRF token
            if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || 
                $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
                http_response_code(403);
                echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
                exit;
            }
            
            $result = $controller->trackPlay($data['track_id']);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
        break;
}
