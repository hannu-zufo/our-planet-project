<?php
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slug = basename(trim($_POST['slug'] ?? ''));
    if ($slug) {
        $filepath = POSTS_DIR . $slug . '.html';
        if (file_exists($filepath)) {
            unlink($filepath);
            // Regenerate index
            require_once 'edit.php';
            // Redirect handled in edit.php only on POST, call function directly
        }
    }
}

// Regenerate blog index after delete
function regenerate_index_standalone() {
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

regenerate_index_standalone();
header('Location: dashboard.php?deleted=1');
exit;
