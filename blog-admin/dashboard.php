<?php
require 'auth.php';

// Get all posts
$posts = [];
if (is_dir(POSTS_DIR)) {
    foreach (glob(POSTS_DIR . '*.html') as $file) {
        $slug = basename($file, '.html');
        $content = file_get_contents($file);
        
        // Extract title
        preg_match('/<title>(.*?) \| Our Planet/i', $content, $title_match);
        $title = $title_match[1] ?? $slug;
        
        // Extract date from post-meta
        preg_match('/<p class="post-meta">(.*?)<\/p>/i', $content, $date_match);
        $date = $date_match[1] ?? '';
        
        $posts[] = [
            'slug' => $slug,
            'title' => html_entity_decode($title),
            'date' => $date,
            'file' => $file,
            'modified' => filemtime($file)
        ];
    }
}

// Sort by file modified time descending
usort($posts, fn($a, $b) => $b['modified'] - $a['modified']);

$search = trim($_GET['search'] ?? '');
if ($search) {
    $posts = array_filter($posts, fn($p) => stripos($p['title'], $search) !== false);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Blog Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#1a1a1a; --paper:#fdfcfb; --accent:#A14068; --border:#e5e5e5; --serif:'Libre Baskerville',serif; --sans:'Inter',sans-serif; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:var(--paper); font-family:var(--sans); color:var(--ink); }
        .admin-nav { background:var(--ink); padding:0 40px; display:flex; align-items:center; justify-content:space-between; height:60px; }
        .admin-nav .brand { color:#fff; font-family:var(--serif); font-size:1.1rem; text-decoration:none; }
        .admin-nav .nav-right { display:flex; gap:20px; align-items:center; }
        .admin-nav a { color:#999; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; text-decoration:none; font-weight:600; }
        .admin-nav a:hover { color:#fff; }
        .admin-nav .btn-new { background:var(--accent); color:#fff !important; padding:8px 16px; }
        .container { max-width:1100px; margin:0 auto; padding:0 40px; }
        .page-header { padding:40px 0 20px; border-bottom:1px solid var(--border); margin-bottom:30px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:20px; }
        .page-header h1 { font-family:var(--serif); font-size:2rem; }
        .search-bar input { padding:10px 16px; border:1px solid var(--border); font-family:var(--sans); font-size:0.9rem; width:280px; }
        .search-bar input:focus { outline:2px solid var(--accent); }
        .post-count { font-size:0.8rem; color:#666; margin-bottom:20px; }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:#666; border-bottom:2px solid var(--ink); padding:10px 12px; }
        td { padding:14px 12px; border-bottom:1px solid var(--border); vertical-align:middle; }
        td.title { font-family:var(--serif); font-size:1rem; }
        td.title a { color:var(--ink); text-decoration:none; }
        td.title a:hover { color:var(--accent); }
        td.date { font-size:0.8rem; color:#666; white-space:nowrap; }
        td.actions { white-space:nowrap; }
        .btn-edit, .btn-delete, .btn-view { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; padding:6px 12px; text-decoration:none; display:inline-block; margin-right:6px; }
        .btn-edit { background:var(--ink); color:#fff; }
        .btn-edit:hover { background:var(--accent); }
        .btn-view { background:#f0f0f0; color:var(--ink); }
        .btn-view:hover { background:#e0e0e0; }
        .btn-delete { background:transparent; color:#c0392b; border:1px solid #c0392b; cursor:pointer; font-family:var(--sans); }
        .btn-delete:hover { background:#c0392b; color:#fff; }
        .alert { padding:14px 16px; margin-bottom:20px; font-size:0.9rem; }
        .alert-success { background:#e8f5e9; color:#2e7d32; border-left:3px solid #2e7d32; }
        .alert-error { background:#fde8e8; color:#c0392b; border-left:3px solid #c0392b; }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php" class="brand">OPP Blog Admin</a>
        <div class="nav-right">
            <a href="../index.html" target="_blank">View Site</a>
            <a href="../blog/index.html" target="_blank">View Blog</a>
            <a href="edit.php" class="btn-new">+ New Post</a>
            <a href="logout.php">Sign Out</a>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>All Posts</h1>
            <form class="search-bar" method="GET">
                <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
            </form>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">Post deleted successfully.</div>
        <?php endif; ?>
        <?php if (isset($_GET['saved'])): ?>
            <div class="alert alert-success">Post saved successfully.</div>
        <?php endif; ?>

        <p class="post-count"><?php echo count($posts); ?> post<?php echo count($posts) !== 1 ? 's' : ''; ?><?php echo $search ? ' matching "' . htmlspecialchars($search) . '"' : ''; ?></p>

        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td class="title"><a href="edit.php?slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></td>
                    <td class="date"><?php echo htmlspecialchars($post['date']); ?></td>
                    <td class="actions">
                        <a href="edit.php?slug=<?php echo urlencode($post['slug']); ?>" class="btn-edit">Edit</a>
                        <a href="../blog/posts/<?php echo urlencode($post['slug']); ?>.html" target="_blank" class="btn-view">View</a>
                        <form method="POST" action="delete.php" style="display:inline;" onsubmit="return confirm('Delete this post?')">
                            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>">
                            <button type="submit" class="btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
