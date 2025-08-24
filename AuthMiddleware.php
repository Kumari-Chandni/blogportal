<?php
class AuthMiddleware {
    
    public static function authenticate() {
        $headers = getallheaders();
        $token = null;
        
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token required']);
            exit;
        }
        
        $payload = JWTHelper::verifyToken($token);
        if (!$payload) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid or expired token']);
            exit;
        }
        
        return $payload;
    }
    
    public static function requireRole($requiredRole) {
        $payload = self::authenticate();
        
        if ($payload['role'] !== $requiredRole && $payload['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Insufficient permissions']);
            exit;
        }
        
        return $payload;
    }
    
    public static function getCurrentUser() {
        return self::authenticate();
    }
}
?>