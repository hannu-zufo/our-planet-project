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
    <meta name="description" content="Essays and analysis from the Our Planet Project Foundation on nuclear policy, atmospheric science, and the strategic implications of the Rolling the Dice thesis.">
    <meta name="author" content="John Ward">
    <link rel="canonical" href="https://ourplanetproject.com/blog/">
    <link rel="icon" href="/favicon.ico">

    <meta property="og:type" content="website">
    <meta property="og:url" content="https://ourplanetproject.com/blog/">
    <meta property="og:title" content="Blog | Our Planet Project Foundation">
    <meta property="og:description" content="Essays on nuclear policy, atmospheric science, and global stability.">
    <meta property="og:image" content="https://ourplanetproject.com/images/og-default.jpg">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Blog | Our Planet Project Foundation">
    <meta name="twitter:image" content="https://ourplanetproject.com/images/og-default.jpg">

    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/blog.css">

    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Blog",
  "name": "Our Planet Project Foundation Blog",
  "url": "https://ourplanetproject.com/blog/",
  "publisher": {
    "@type": "Organization",
    "name": "Our Planet Project Foundation",
    "logo": "https://ourplanetproject.com/images/logo.png"
  }
}
    </script>
</head>
<body>
    <nav class="editorial-nav">
        <div class="nav-container">
            <a href="../index.html" class="logo-link"><img src="../images/logo.png" alt="Logo" class="nav-logo"></a>
            <ul class="nav-links">
                <li><a href="../index.html">Thesis</a></li>
                <li><a href="../rolling-the-dice.html">The Book</a></li>
                <li><a href="../evidence.html">Evidence</a></li>
                <li><a href="../strategy.html">Strategy</a></li>
                <li><a href="../author.html">The Scholar</a></li>
                <li><a href="index.html" class="active">Blog</a></li>
                <li><a href="../author.html#contact">Contact</a></li>
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

    <aside class="book-promo">
        <div class="book-promo-inner">
            <a href="../rolling-the-dice.html" class="book-promo-cover">
                <img src="../images/rollingff.png" alt="Rolling the Dice with Nuclear Weapons by John Ward" loading="lazy">
            </a>
            <div class="book-promo-text">
                <span class="section-label">From the Author</span>
                <h3>Rolling the Dice with Nuclear Weapons</h3>
                <p>The full thesis — atmospheric science, archival case studies, and a policy blueprint for stability — in book form by John Ward.</p>
                <a href="../rolling-the-dice.html" class="book-promo-cta">Read more about the book &rarr;</a>
            </div>
        </div>
    </aside>

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
