<?php
class User {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function findByEmail($email) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ?", 
            [$email]
        );
    }
    
    public function findById($id) {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", 
            [$id]
        );
    }
    
    public function create($email, $password, $role = 'user') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        return $this->db->insert('users', [
            'email' => $email,
            'password_hash' => $hashedPassword,
            'role' => $role
        ]);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    public function authenticate($email, $password) {
    $user = $this->findByEmail($email);
    
    // Add debug info
    error_log("Debug: Email = $email");
    error_log("Debug: User found = " . ($user ? 'yes' : 'no'));
    
    if ($user) {
        error_log("Debug: Stored hash = " . $user['password_hash']);
        error_log("Debug: Password verify = " . ($this->verifyPassword($password, $user['password_hash']) ? 'true' : 'false'));
    }
    
    if ($user && $this->verifyPassword($password, $user['password_hash'])) {
        return $user;
    }
    
    return false;
}
}
?>