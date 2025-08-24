<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'JWTHelper.php';
require_once 'AuthMiddleware.php';
require_once 'User.php';
require_once 'Posts.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$db = new Database();
$userModel = new User($db);
$postModel = new Posts($db);

$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['path']) ? $_GET['path'] : '';
$segments = explode('/', trim($path, '/'));

if (in_array('api.php', $segments)) {
    $segments = array_values(array_diff($segments, ['api.php']));
}

try {
    switch ($method) {
        case 'POST':
            if ($segments[0] === 'auth' && $segments[1] === 'login') {
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['email']) || !isset($input['password'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Email and password required']);
                    exit;
                }
                
                
                $user = $userModel->authenticate($input['email'], $input['password']);

                
                
                if (!$user) {
                    http_response_code(401);
                    echo json_encode(['error' => 'Invalid credentials']);
                    exit;
                }
                
                $payload = JWTHelper::createPayload($user['id'], $user['role']);
                $token = JWTHelper::generateToken($payload);
                
                echo json_encode([
                    'token' => $token,
                    'user' => [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ]);
                
            } elseif ($segments[0] === 'posts') {
                $currentUser = AuthMiddleware::getCurrentUser();
                $input = json_decode(file_get_contents('php://input'), true);
                
                if (!isset($input['title']) || !isset($input['body'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Title and body required']);
                    exit;
                }
                
                $input['author_id'] = $currentUser['sub'];
                $postId = $postModel->create($input);
                
                echo json_encode(['id' => $postId, 'message' => 'Post created successfully']);
                
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'GET':
            if ($segments[0] === 'posts') {
                if (isset($segments[1]) && is_numeric($segments[1])) {
                    $post = $postModel->getById($segments[1]);
                    
                    if (!$post) {
                        http_response_code(404);
                        echo json_encode(['error' => 'Post not found']);
                        exit;
                    }
                    
                    echo json_encode($post);
                    
                } else {
                    $page = $_GET['page'] ?? 1;
                    $search = $_GET['search'] ?? '';
                    $status = $_GET['status'] ?? 'active';
                    
                    if ($status !== 'active') {
                        AuthMiddleware::getCurrentUser();
                    }
                    
                    $result = $postModel->getAll($page, 10, $search, $status);
                    echo json_encode($result);
                }
                
            } elseif ($segments[0] === 'pixabay' && $segments[1] === 'search') {
                AuthMiddleware::getCurrentUser(); 
                
                $query = $_GET['q'] ?? '';
                $type = $_GET['type'] ?? 'photo'; 
                
                if (empty($query)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Query parameter required']);
                    exit;
                }
                
                $apiUrl = "https://pixabay.com/api/";
                if ($type === 'video') {
                    $apiUrl = "https://pixabay.com/api/videos/";
                }
                
                $params = [
                    'key' => '51948716-48160e10077ec275ad41f3dec',
                    'q' => urlencode($query),
                    'per_page' => 20,
                    'safesearch' => 'true',
                    'category' => 'all'
                ];
                
                $url = $apiUrl . '?' . http_build_query($params);
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                if ($httpCode === 200) {
                    echo $response;
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to fetch from Pixabay']);
                }
                
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'PUT':
            if ($segments[0] === 'posts' && isset($segments[1]) && is_numeric($segments[1])) {
                $currentUser = AuthMiddleware::getCurrentUser();
                $postId = $segments[1];
                
                if (!$postModel->canUserEdit($postId, $currentUser['sub'], $currentUser['role'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Not authorized to edit this post']);
                    exit;
                }
                
                $input = json_decode(file_get_contents('php://input'), true);
                $success = $postModel->update($postId, $input);
                
                if ($success) {
                    echo json_encode(['message' => 'Post updated successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to update post']);
                }
                
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        case 'DELETE':
            if ($segments[0] === 'posts' && isset($segments[1]) && is_numeric($segments[1])) {
                $currentUser = AuthMiddleware::getCurrentUser();
                $postId = $segments[1];
                
                if (!$postModel->canUserEdit($postId, $currentUser['sub'], $currentUser['role'])) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Not authorized to delete this post']);
                    exit;
                }
                
                $success = $postModel->softDelete($postId);
                
                if ($success) {
                    echo json_encode(['message' => 'Post deleted successfully']);
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to delete post']);
                }
                
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Endpoint not found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
            
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error: ' . $e->getMessage()]);
}
?>