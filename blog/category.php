<?php
include $_SERVER['DOCUMENT_ROOT'] . '/lang/handling.php'; 
require_once '../admin/includes/config.php';

// Get user's browser language (default to 'en')
$user_language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
$language = in_array($user_language, ['en', 'lv']) ? $user_language : 'en';

// Get category slug from URL
$category_slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($category_slug)) {
    header('Location: index.php');
    exit();
}

// Get category details
$cat_stmt = $conn->prepare("
    SELECT * FROM blog_categories 
    WHERE slug = ? AND language = ?
");
$cat_stmt->bind_param("ss", $category_slug, $language);
$cat_stmt->execute();
$category = $cat_stmt->get_result()->fetch_assoc();

if (!$category) {
    header('Location: index.php');
    exit();
}

// Pagination setup
$posts_per_page = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Get posts for this category
$post_stmt = $conn->prepare("
    SELECT p.*, c.name as category_name
    FROM blog_posts p
    LEFT JOIN blog_categories c ON p.category_id = c.id
    WHERE p.category_id = ? 
    AND p.status = 'published'
    ORDER BY p.created_at DESC
    LIMIT ? OFFSET ?
");
$post_stmt->bind_param("iii", $category['id'], $posts_per_page, $offset);
$post_stmt->execute();
$posts = $post_stmt->get_result();

// Get total posts for pagination
$count_stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM blog_posts 
    WHERE category_id = ? AND status = 'published'
");
$count_stmt->bind_param("i", $category['id']);
$count_stmt->execute();
$total_posts = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $posts_per_page);
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meža Draugas</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/script.js"></script>
    <link rel="stylesheet" href="/css/animastions.css">
    <script src="/js/animantion.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-black ">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header.php' ?>
    <main class="bg-green-md rounded-t-[30px] mt-2 z-10">

        <!-- Category Header -->
        <header class="mb-12 text-center pt-24">
            <h1 class="text-4xl font-bold mb-4 text-gray-100"><?php echo htmlspecialchars($category['name']); ?>
            </h1>
            <?php if (!empty($category['description'])): ?>
            <p class="text-gray-400 max-w-2xl mx-auto">
                <?php echo htmlspecialchars($category['description']); ?>
            </p>
            <?php endif; ?>
        </header>

        <!-- Posts Grid -->
        <section>
            <div class="flex justify-center items-center">
                <?php if ($posts->num_rows > 0): ?>
                <?php while ($post = $posts->fetch_assoc()): ?>
                <article class="overflow-hidden w-full">
                    <div class="lg:px-10 px-5 flex flex-row justify-center items-center">
                        <div class="w-full max-w-[1400px] border-b-2 border-green-dark pb-10">
                            <div class="text-zinc-200 text-sm mb-4 inline overflow-hidden">
                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </div>
                            <h3
                                class="md:text-5xl text-4xl text-white hover:text-green-lier font-semibold mb-2 transition-all duration-100">
                                <a href="post.php?slug=<?php echo $post['slug']; ?>" class="">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h3>
                            <div class="text-zinc-100 line-clamp-3 max-w-[800px] sm:pr-0 pr-10">
                                <?php 
                                $excerpt = strip_tags($post['content']);
                                echo strlen($excerpt) > 150 
                                    ? substr($excerpt, 0, 150) . '...' 
                                    : $excerpt;
                                ?>
                            </div>
                        </div>
                        <a href="post.php?slug=<?php echo $post['slug']; ?>"
                            class="bg-white rounded-xl p-3 w-12 h-12 flex items-center justify-center">
                            <svg class="arrow w-6 h-6 -rotate-45 transition-transform duration-300"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                    </div>
                </article>
                <?php endwhile; ?>
                <?php else: ?>
                <div class="text-center py-12 text-gray-500 w-full">
                    No posts found in this category.
                </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="flex justify-center space-x-2 my-8">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?slug=<?php echo $category_slug; ?>&page=<?php echo $i; ?>"
                class="px-4 py-2 border rounded-md <?php echo $page === $i ? 'bg-green-lier text-green-dark' : 'text-gray-300 hover:bg-green-dark/20'; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>

        <!-- Back Link -->
        <div class="text-center mt-8">
            <a href="/blog/list"
                class="inline-block bg-green-li text-green-dark px-6 py-2 rounded-full hover:text-zinc-100 text-sm hover:bg-zinc-700 transition-colors">
                ← Atpakaļ
            </a>
        </div>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/contact-form.php' ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php' ?>
    </main>

</body>

</html>