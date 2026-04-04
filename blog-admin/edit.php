<?php
require 'auth.php';

$slug = trim($_GET['slug'] ?? '');
$is_new = empty($slug);
$error = '';
$success = '';

$post = [
    'title' => '',
    'slug' => '',
    'date' => date('F d, Y'),
    'meta_title' => '',
    'meta_desc' => '',
    'excerpt' => '',
    'content' => ''
];

// Load existing post
if (!$is_new && $slug) {
    $filepath = POSTS_DIR . basename($slug) . '.html';
    if (file_exists($filepath)) {
        $html = file_get_contents($filepath);

        // Extract fields
        preg_match('/<title>(.*?) \| Our Planet/i', $html, $m);
        $post['title'] = html_entity_decode($m[1] ?? '');
        $post['meta_title'] = $post['title'];

        preg_match('/<meta name="description" content="(.*?)"/i', $html, $m);
        $post['meta_desc'] = html_entity_decode($m[1] ?? '');

        preg_match('/<p class="post-meta">(.*?)<\/p>/i', $html, $m);
        $post['date'] = $m[1] ?? date('F d, Y');

        preg_match('/<article class="prose-block post-content">(.*?)<\/article>/is', $html, $m);
        $post['content'] = trim($m[1] ?? '');

        $post['slug'] = $slug;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $raw_slug = trim($_POST['slug'] ?? '');
    $date = trim($_POST['date'] ?? date('F d, Y'));
    $meta_title = trim($_POST['meta_title'] ?? $title);
    $meta_desc = trim($_POST['meta_desc'] ?? '');
    $content = $_POST['content'] ?? '';

    if (empty($title)) {
        $error = 'Title is required.';
    } else {
        // Generate slug from title if empty
        if (empty($raw_slug)) {
            $raw_slug = strtolower($title);
            $raw_slug = preg_replace('/[^a-z0-9\s-]/', '', $raw_slug);
            $raw_slug = preg_replace('/[\s-]+/', '-', trim($raw_slug));
        }
        $new_slug = preg_replace('/[^a-z0-9-]/', '', strtolower($raw_slug));

        // If slug changed, delete old file
        if (!$is_new && $slug && $slug !== $new_slug) {
            $old_file = POSTS_DIR . basename($slug) . '.html';
            if (file_exists($old_file)) unlink($old_file);
        }

        $filepath = POSTS_DIR . $new_slug . '.html';

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$meta_title} | Our Planet Project Foundation</title>
    <meta name="description" content="{$meta_desc}">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/blog.css">
</head>
<body>
    <nav class="editorial-nav">
        <div class="nav-container">
            <a href="../../index.html" class="logo-link"><img src="../../images/logo.png" alt="Logo" class="nav-logo"></a>
            <ul class="nav-links">
                <li><a href="../../index.html">Thesis</a></li>
                <li><a href="../../evidence.html">Evidence</a></li>
                <li><a href="../../strategy.html">Strategy</a></li>
                <li><a href="../../author.html">The Author</a></li>
                <li><a href="../index.html" class="active">Blog</a></li>
            </ul>
        </div>
    </nav>

    <header class="post-header">
        <div class="container">
            <span class="section-label">Our Planet Project Foundation</span>
            <h1>{$title}</h1>
            <p class="post-meta">{$date}</p>
        </div>
    </header>

    <main class="container post-body">
        <article class="prose-block post-content">
            {$content}
        </article>
        <aside class="post-sidebar">
            <div class="sticky-sidebar">
                <div class="back-link">
                    <a href="../index.html">&larr; Back to Blog</a>
                </div>
            </div>
        </aside>
    </main>

    <footer class="site-footer"><p>&copy; 2026 Our Planet Project Foundation</p></footer>
    <script src="../../js/site.js"></script>
</body>
</html>
HTML;

        if (file_put_contents($filepath, $html) !== false) {
            // Regenerate blog index
            regenerate_index();
            header('Location: dashboard.php?saved=1');
            exit;
        } else {
            $error = 'Failed to save post. Check file permissions.';
        }
    }
}

function regenerate_index() {
    $posts = [];
    foreach (glob(POSTS_DIR . '*.html') as $file) {
        $slug = basename($file, '.html');
        $content = file_get_contents($file);
        preg_match('/<title>(.*?) \| Our Planet/i', $content, $tm);
        $title = html_entity_decode($tm[1] ?? $slug);
        preg_match('/<p class="post-meta">(.*?)<\/p>/i', $content, $dm);
        $date = $dm[1] ?? '';
        $posts[] = ['slug' => $slug, 'title' => $title, 'date' => $date, 'modified' => filemtime($file)];
    }
    usort($posts, fn($a, $b) => $b['modified'] - $a['modified']);

    $cards = '';
    foreach ($posts as $p) {
        $cards .= "        <article class=\"blog-card\">\n";
        $cards .= "            <div class=\"blog-card-meta\"><span class=\"section-label\">{$p['date']}</span></div>\n";
        $cards .= "            <h2><a href=\"posts/{$p['slug']}.html\">" . htmlspecialchars($p['title']) . "</a></h2>\n";
        $cards .= "        </article>\n";
    }

    $index = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog | Our Planet Project Foundation</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/blog.css">
</head>
<body>
    <nav class="editorial-nav">
        <div class="nav-container">
            <a href="../index.html" class="logo-link"><img src="../images/logo.png" alt="Logo" class="nav-logo"></a>
            <ul class="nav-links">
                <li><a href="../index.html">Thesis</a></li>
                <li><a href="../evidence.html">Evidence</a></li>
                <li><a href="../strategy.html">Strategy</a></li>
                <li><a href="../author.html">The Author</a></li>
                <li><a href="index.html" class="active">Blog</a></li>
            </ul>
        </div>
    </nav>
    <header class="evidence-header">
        <div class="container">
            <span class="section-label">Our Planet Project Foundation</span>
            <h1>The Blog</h1>
        </div>
    </header>
    <main class="container">
        <div class="blog-grid">
{$cards}
        </div>
    </main>
    <footer class="site-footer"><p>&copy; 2026 Our Planet Project Foundation</p></footer>
    <script src="../js/site.js"></script>
</body>
</html>
HTML;

    file_put_contents(BLOG_INDEX, $index);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_new ? 'New Post' : 'Edit Post'; ?> | Blog Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
<script src="https://cdn.tiny.cloud/1/ltqlue7zlrwe8q7r736f2r80fn9lrk31fxuu99922nesulne/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
    <style>
        :root { --ink:#1a1a1a; --paper:#fdfcfb; --accent:#A14068; --border:#e5e5e5; --serif:'Libre Baskerville',serif; --sans:'Inter',sans-serif; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { background:#f5f5f5; font-family:var(--sans); color:var(--ink); }
        .admin-nav { background:var(--ink); padding:0 40px; display:flex; align-items:center; justify-content:space-between; height:60px; }
        .admin-nav .brand { color:#fff; font-family:var(--serif); font-size:1.1rem; text-decoration:none; }
        .admin-nav .nav-right { display:flex; gap:20px; align-items:center; }
        .admin-nav a { color:#999; font-size:0.75rem; text-transform:uppercase; letter-spacing:1px; text-decoration:none; font-weight:600; }
        .admin-nav a:hover { color:#fff; }
        .edit-layout { display:flex; gap:0; min-height:calc(100vh - 60px); }
        .edit-main { flex:1; padding:40px; }
        .edit-sidebar { width:280px; background:#fff; border-left:1px solid var(--border); padding:30px; }
        .page-title { font-family:var(--serif); font-size:1.6rem; margin-bottom:30px; }
        .field { margin-bottom:20px; }
        .field label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#666; margin-bottom:8px; }
        .field input, .field textarea { width:100%; padding:10px 14px; border:1px solid var(--border); font-family:var(--sans); font-size:0.95rem; background:#fff; }
        .field input:focus, .field textarea:focus { outline:2px solid var(--accent); border-color:transparent; }
        .field textarea { height:80px; resize:vertical; }
        .field .hint { font-size:0.72rem; color:#999; margin-top:5px; }
        .editor-wrap { background:#fff; border:1px solid var(--border); }
        .section-divider { font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#999; border-top:1px solid var(--border); padding-top:20px; margin:25px 0 15px; }
        .btn-save { width:100%; padding:14px; background:var(--accent); color:#fff; border:none; font-family:var(--sans); font-weight:700; font-size:0.85rem; text-transform:uppercase; letter-spacing:1px; cursor:pointer; margin-bottom:12px; }
        .btn-save:hover { opacity:0.9; }
        .btn-cancel { display:block; text-align:center; color:#666; font-size:0.8rem; text-decoration:none; padding:8px; }
        .btn-cancel:hover { color:var(--ink); }
        .alert { padding:12px 16px; margin-bottom:20px; font-size:0.85rem; }
        .alert-error { background:#fde8e8; color:#c0392b; border-left:3px solid #c0392b; }
        .alert-success { background:#e8f5e9; color:#2e7d32; border-left:3px solid #2e7d32; }
    </style>
</head>
<body>
    <nav class="admin-nav">
        <a href="dashboard.php" class="brand">OPP Blog Admin</a>
        <div class="nav-right">
            <a href="dashboard.php">&larr; All Posts</a>
            <a href="logout.php">Sign Out</a>
        </div>
    </nav>

    <form method="POST" id="post-form">
    <div class="edit-layout">
        <div class="edit-main">
            <h1 class="page-title"><?php echo $is_new ? 'New Post' : 'Edit Post'; ?></h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="field">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required oninput="autoSlug(this.value)">
            </div>

            <div class="field">
                <label for="content">Content</label>
                <div class="editor-wrap">
                    <textarea id="content" name="content"><?php echo $post['content']; ?></textarea>
                </div>
            </div>
        </div>

        <div class="edit-sidebar">
            <button type="submit" class="btn-save">Save Post</button>
            <a href="dashboard.php" class="btn-cancel">Cancel</a>

            <div class="section-divider">Post Details</div>

            <div class="field">
                <label for="slug">URL Slug</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>">
                <p class="hint">Auto-generated from title. Leave blank to auto-fill.</p>
            </div>

            <div class="field">
                <label for="date">Display Date</label>
                <input type="text" id="date" name="date" value="<?php echo htmlspecialchars($post['date']); ?>">
            </div>

            <div class="section-divider">SEO</div>

            <div class="field">
                <label for="meta_title">Meta Title</label>
                <input type="text" id="meta_title" name="meta_title" value="<?php echo htmlspecialchars($post['meta_title'] ?: $post['title']); ?>">
            </div>

            <div class="field">
                <label for="meta_desc">Meta Description</label>
                <textarea id="meta_desc" name="meta_desc"><?php echo htmlspecialchars($post['meta_desc']); ?></textarea>
            </div>
        </div>
    </div>
    </form>

    <script>
    tinymce.init({
        selector: '#content',
        height: 600,
        menubar: true,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontsize | bold italic underline | link image | alignleft aligncenter alignright | bullist numlist | removeformat',
        content_style: 'body { font-family: Georgia, serif; font-size: 16px; line-height: 1.8; max-width: 800px; margin: 20px auto; }',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote',
    });

    function autoSlug(title) {
        const slugField = document.getElementById('slug');
        if (slugField.dataset.manual) return;
        slugField.value = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s]+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }

    document.getElementById('slug').addEventListener('input', function() {
        this.dataset.manual = 'true';
    });
    </script>
</body>
</html>
