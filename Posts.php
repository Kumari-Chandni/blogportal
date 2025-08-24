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
        
        // Check for existing slugs
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $whereClause = "slug = ?";
            $params = [$slug];
            
            // Don't check current post when updating
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
        
        // Get total count for pagination
        $countSql = "
            SELECT COUNT(*) as total 
            FROM posts p 
            JOIN users u ON p.author_id = u.id 
            WHERE {$whereClause}
        ";
        array_pop($params); // Remove OFFSET
        array_pop($params); // Remove LIMIT
        
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
        $updateData = [];
        
        if (isset($data['title'])) {
            $updateData['title'] = $data['title'];
            $updateData['slug'] = $this->generateSlug($data['title'], $id);
        }
        
        if (isset($data['body'])) {
            $updateData['body'] = $data['body'];
        }
        
        if (isset($data['cover_media_url'])) {
            $updateData['cover_media_url'] = $data['cover_media_url'];
        }
        
        if (isset($data['media_type'])) {
            $updateData['media_type'] = $data['media_type'];
        }
        
        if (isset($data['media_attribution'])) {
            $updateData['media_attribution'] = $data['media_attribution'];
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->db->update('posts', $updateData, 'id = ?', [$id]);
    }
    
    public function softDelete($id) {
        return $this->db->update(
            'posts', 
            ['status' => 'deleted', 'updated_at' => date('Y-m-d H:i:s')], 
            'id = ?', 
            [$id]
        );
    }
    
    public function canUserEdit($postId, $userId, $userRole) {
        if ($userRole === 'admin') {
            return true;
        }
        
        $post = $this->getById($postId);
        return $post && $post['author_id'] == $userId;
    }
}
?>