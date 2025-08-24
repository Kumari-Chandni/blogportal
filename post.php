<?php
require_once 'config.php';
require_once 'Database.php';
require_once 'Posts.php';

$db = new Database();
$postModel = new Posts($db);

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit;
}

$post = $postModel->getBySlug($slug);

if (!$post) {
    header("HTTP/1.0 404 Not Found");
    echo "Post not found";
    exit;
}

// Escape output for security
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($post['title']); ?> - Blog</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .title { font-size: 2.5rem; font-weight: 700; margin-bottom: 15px; }
        .meta { color: #666; font-size: 0.9rem; }
        .meta span { margin: 0 10px; }
        .cover-media { margin: 30px 0; text-align: center; }
        .cover-media img, .cover-media video { max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .attribution { font-size: 0.8rem; color: #666; margin-top: 8px; font-style: italic; }
        .content { font-size: 1.1rem; line-height: 1.8; margin: 30px 0; }
        .content p { margin-bottom: 20px; }
        .back-link { display: inline-block; margin-top: 30px; color: #007bff; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .title { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <article>
            <header class="header">
                <h1 class="title"><?php echo e($post['title']); ?></h1>
                <div class="meta">
                    <span>By <?php echo e($post['author_email']); ?></span>
                    <span>•</span>
                    <span><?php echo formatDate($post['created_at']); ?></span>
                </div>
            </header>

            <?php if (!empty($post['cover_media_url'])): ?>
            <div class="cover-media">
                <?php if ($post['media_type'] === 'video'): ?>
                    <video controls>
                        <source src="<?php echo e($post['cover_media_url']); ?>" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                <?php else: ?>
                    <img src="<?php echo e($post['cover_media_url']); ?>" alt="<?php echo e($post['title']); ?>">
                <?php endif; ?>
                
                <?php if (!empty($post['media_attribution'])): ?>
                <div class="attribution"><?php echo e($post['media_attribution']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="content">
                <?php 
                // Simple formatting - convert line breaks to paragraphs
                $content = nl2br(e($post['body']));
                $paragraphs = explode("\n", $post['body']);
                foreach ($paragraphs as $paragraph) {
                    if (trim($paragraph)) {
                        echo '<p>' . e(trim($paragraph)) . '</p>';
                    }
                }
                ?>
            </div>
        </article>

        <a href="javascript:history.back()" class="back-link">← Back to Blog</a>
    </div>
</body>
</html>