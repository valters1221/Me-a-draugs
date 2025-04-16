<?php
require_once '../admin/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/lang/handling.php';

// Get user's browser language (default to 'en')
$user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$language = in_array($user_language, ['en', 'lv']) ? $user_language : 'en';

// Get post slug from URL
$post_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($post_slug)) {
    header('Location: index.php');
    exit();
}

// Get post details with category information
$post_stmt = $conn->prepare("
    SELECT p.*, c.name as category_name, c.slug as category_slug
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE p.slug = ? 
    AND p.status = 'published'
    AND p.language = ?
");
$post_stmt->bind_param("ss", $post_slug, $language);
$post_stmt->execute();
$post = $post_stmt->get_result()->fetch_assoc();

if (!$post) {
    header('Location: index.php');
    exit();
}

// Get related posts from same category
$related_stmt = $conn->prepare("
    SELECT title, slug, created_at
    FROM blog_posts
    WHERE category_id = ?
    AND id != ?
    AND status = 'published'
    AND language = ?
    ORDER BY created_at DESC
    LIMIT 3
");
$related_stmt->bind_param("iis", $post['category_id'], $post['id'], $language);
$related_stmt->execute();
$related_posts = $related_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="author" content="">
    <title></title>
    <meta name="description" content="">

    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/script.js"></script>
    <link rel="stylesheet" href="/css/animastions.css">
    <script src="/js/animantion.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
    /* Remove Tailwind's reset for blog content */
    .disable-tailwind {
        all: revert !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .disable-tailwind h1 {
        font-size: 2em;
        font-weight: bold;
        margin: 0.67em 0;
    }

    .disable-tailwind h2 {
        font-size: 1.5em;
        font-weight: bold;
        margin: 0.83em 0;
    }

    .disable-tailwind h3 {
        font-size: 1.17em;
        font-weight: bold;
        margin: 1em 0;
    }

    .disable-tailwind h4 {
        font-weight: bold;
        margin: 1.33em 0;
    }

    .disable-tailwind h5 {
        font-size: 0.83em;
        font-weight: bold;
        margin: 1.67em 0;
    }

    .disable-tailwind h6 {
        font-size: 0.67em;
        font-weight: bold;
        margin: 2.33em 0;
    }

    .disable-tailwind ul {
        display: block;
        list-style-type: disc;
        padding-left: 40px;
        margin: 1em 0;
    }

    .disable-tailwind ol {
        display: block;
        list-style-type: decimal;
        padding-left: 40px;
        margin: 1em 0;
    }

    .disable-tailwind li {
        display: list-item;
        margin: 0.5em 0;
    }

    .disable-tailwind p {
        margin: 1em 0;
    }

    .disable-tailwind a {
        color: blue;
        text-decoration: underline;
    }

    .disable-tailwind table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        margin: 1em 0;
    }

    .disable-tailwind th,
    .disable-tailwind td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .disable-tailwind th {
        background-color: #f5f5f5;
    }

    .disable-tailwind blockquote {
        margin: 1em 40px;
        padding-left: 1em;
        border-left: 3px solid #ccc;
    }

    .disable-tailwind img {
        max-width: 100%;
        height: auto;
        margin: 1em 0;
    }

    .disable-tailwind pre {
        background-color: #f5f5f5;
        padding: 1em;
        overflow-x: auto;
        margin: 1em 0;
    }

    .disable-tailwind code {
        background-color: #f5f5f5;
        padding: 0.2em 0.4em;
        border-radius: 3px;
    }
    </style>
</head>

<body class="bg-black">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header.php'; ?>
    <main class=" px-4 py-8 mt-2 z-10 rounded-t-[30px] relative overflow-hidden bg-gray-100">
        <article class="max-w-4xl mx-auto pt-16">
            <!-- Breadcrumb -->
            <div class="text-sm text-gray-600 mb-6">
                <a href="index.php" class="hover:text-blue-600">Blog</a>
                <span class="mx-2">→</span>
                <a href="category.php?slug=<?php echo $post['category_slug']; ?>" class="hover:text-blue-600">
                    <?php echo htmlspecialchars($post['category_name']); ?>
                </a>
            </div>

            <!-- Article Header -->
            <header class="mb-8">
                <h1 class="text-4xl font-bold mb-4">
                    <?php echo htmlspecialchars($post['title']); ?>
                </h1>
                <div class="text-gray-600">
                    <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                </div>
            </header>

            <?php if ($post['featured_image']): ?>
            <div class="mb-8">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>"
                    alt="<?php echo htmlspecialchars($post['title']); ?>" class="w-full h-auto rounded-lg">
            </div>
            <?php endif; ?>

            <!-- Article Content -->
            <div class="disable-tailwind">
                <?php echo $post['content']; ?>
            </div>

            <!-- Related Posts -->
            <?php if ($related_posts->num_rows > 0): ?>
            <div class="border-t pt-8 mt-8">
                <h2 class="text-2xl font-bold mb-6">Related Posts</h2>
                <div class="grid md:grid-cols-3 gap-6">
                    <?php while ($related = $related_posts->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold mb-2">
                            <a href="post.php?slug=<?php echo $related['slug']; ?>" class="hover:text-blue-600">
                                <?php echo htmlspecialchars($related['title']); ?>
                            </a>
                        </h3>
                        <div class="text-sm text-gray-600">
                            <?php echo date('F j, Y', strtotime($related['created_at'])); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Navigation -->
            <div class="border-t pt-8 mt-8">
                <a href="/<?php echo $lang->getCurrentLang(); ?>/blog/list"
                    class="inline-block bg-green-li text-green-dark px-6 py-2 rounded-full hover:text-zinc-100 text-sm hover:bg-zinc-700 transition-colors">
                    ← Atpakaļ
                </a>
            </div>
        </article>
    </main>
</body>

</html>