<?php
class Posts {
    private $db;
    
    public function __construct(Database $db) {
        $this->db = $db;
    }
    
    public function generateSlug($title, $id = null) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $whereClause = "slug = ?";
            $params = [$slug];
            
            if ($id) {
                $whereClause .= " AND id != ?";
                $params[] = $id;
            }
            
            $existing = $this->db->fetchOne(
                "SELECT id FROM posts WHERE {$whereClause}",
                $params
            );
            
            if (!$existing) {
                break;
            }
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    public function getAll($page = 1, $perPage = 10, $search = '', $status = 'active') {
        $offset = ($page - 1) * $perPage;
        
        $whereClause = "p.status = ?";
        $params = [$status];
        
        if ($search) {
            $whereClause .= " AND (p.title LIKE ? OR u.email LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        $sql = "
            SELECT p.*, u.email as author_email 
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE {$whereClause}
            ORDER BY p.created_at DESC 
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        $posts = $this->db->fetchAll($sql, $params);
        
        $countSql = "
            SELECT COUNT(*) as total 
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE {$whereClause}
        ";
        array_pop($params); 
        array_pop($params); 
        
        $total = $this->db->fetchOne($countSql, $params)['total'];
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $perPage),
            'current_page' => $page
        ];
    }
    
    public function getBySlug($slug) {
        return $this->db->fetchOne(
            "SELECT p.*, u.email as author_email 
             FROM posts p 
             JOIN users u ON p.author_id = u.id 
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );
    }
    
    public function getById($id) {
        return $this->db->fetchOne(
            "SELECT p.*, u.email as author_email 
             FROM posts p 
             JOIN users u ON p.author_id = u.id 
             WHERE p.id = ?",
            [$id]
        );
    }
    
    public function create($data) {
        $slug = $this->generateSlug($data['title']);
        
        $postData = [
            'title' => $data['title'],
            'slug' => $slug,
            'body' => $data['body'],
            'author_id' => $data['author_id'],
            'cover_media_url' => $data['cover_media_url'] ?? null,
            'media_type' => $data['media_type'] ?? 'image',
            'media_attribution' => $data['media_attribution'] ?? null
        ];
        
        return $this->db->insert('posts', $postData);
    }
    


public function update($id, $data) {
    $fields = [];
    $params = [];
    
    if (isset($data['title'])) {
        $fields[] = 'title = :title';
        $params[':title'] = $data['title'];
    }
    
    if (isset($data['body'])) {
        $fields[] = 'body = :body';
        $params[':body'] = $data['body'];
    }
    
    if (isset($data['cover_media_url'])) {
        $fields[] = 'cover_media_url = :cover_media_url';
        $params[':cover_media_url'] = $data['cover_media_url'];
    }
    
    if (isset($data['media_type'])) {
        $fields[] = 'media_type = :media_type';
        $params[':media_type'] = $data['media_type'];
    }
    
    if (isset($data['media_attribution'])) {
        $fields[] = 'media_attribution = :media_attribution';
        $params[':media_attribution'] = $data['media_attribution'];
    }
    
    $fields[] = 'updated_at = NOW()';
    
    $params[':id'] = $id;
    
    if (empty($fields)) {
        return false; 
    }
    
    $sql = "UPDATE posts SET " . implode(', ', $fields) . " WHERE id = :id";
    
    try {
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("Update query failed: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        throw new Exception("Query failed: " . $e->getMessage());
    }
}
    
  public function softDelete($id) {
    $sql = "UPDATE posts SET status = :status, updated_at = NOW() WHERE id = :id";
    
    $params = [
        ':status' => 'deleted',
        ':id' => $id
    ];
    
    try {
        $stmt = $this->db->getConnection()->prepare($sql);
        return $stmt->execute($params);
    } catch (PDOException $e) {
        error_log("SoftDelete query failed: " . $e->getMessage());
        error_log("SQL: " . $sql);
        error_log("Params: " . print_r($params, true));
        throw new Exception("Query failed: " . $e->getMessage());
    }
}
    public function canUserEdit($postId, $userId, $userRole) {
    if ($userRole === 'admin') {
        return true;
    }
    
    $sql = "SELECT author_id FROM posts WHERE id = :id";
    $params = [':id' => $postId];
    
    try {
        $stmt = $this->db->getConnection()->prepare($sql);
        $stmt->execute($params);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $post && $post['author_id'] == $userId;
    } catch (PDOException $e) {
        error_log("canUserEdit query failed: " . $e->getMessage());
        throw new Exception("Query failed: " . $e->getMessage());
    }
}
}
?>