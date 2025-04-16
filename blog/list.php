<?php include $_SERVER['DOCUMENT_ROOT'] . '/lang/handling.php' ; 

include $_SERVER['DOCUMENT_ROOT'] . '/lang/handling.php';
require_once '../admin/includes/config.php';

// Use the language from our handling system
$language = $lang->getCurrentLang();

// Fetch categories for current language
$cat_stmt = $conn->prepare("SELECT * FROM blog_categories WHERE language = ? ORDER BY name ASC");
$cat_stmt->bind_param("s", $language);
$cat_stmt->execute();
$categories = $cat_stmt->get_result();

// Fetch latest blog posts for current language
$post_stmt = $conn->prepare("
SELECT
p.*,
c.name as category_name
FROM blog_posts p
LEFT JOIN blog_categories c ON p.category_id = c.id
WHERE p.language = ?
AND p.status = 'published'
ORDER BY p.created_at DESC
LIMIT 6
");
$post_stmt->bind_param("s", $language);
$post_stmt->execute();
$posts = $post_stmt->get_result();
?>


<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meža Draugas</title>
    <link rel="stylesheet" href="/css/style.css?v=1.0.3">
    <script src="/js/script.js?v=1.0.3"></script>
    <link rel="stylesheet" href="/css/animastions.css">
    <script src="/js/animantion.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        media="print" onload="this.media='all'" crossorigin>
</head>

<body class="bg-black">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header.php'; ?>
    <main class="relative overflow-hidden bg-green-md rounded-t-[30px] mt-2 z-10">

        <section class="flex justify-center items-center relative h-fit py-52">
            <img src="/images/lines.svg" alt="background lines meža draugs"
                class="absolute width-svg min-[1000px]:h-[250vh] h-[1937px] w-[1924px] top-svg  top-[-60%] -translate-y-[60%] max-w-none">
            <div class="max-w-[1400px] w-full lg:px-10 px-5 z-20 relative animation-group">
                <h1
                    class="title-animate text-green-lier text-7xl min-[365px]:text-8xl lg:text-[10vw] font-bold lg:leading-tight lg:mb-0 mb-5 leading-[80px]">
                    <span class="w1">Blogs</span>
                </h1>
            </div>
        </section>

        <section class="mb-12 mt-16  flex justify-center items-center pb-16">
            <div class="max-w-[1400px] w-full lg:px-10 px-5 z-20 relative">
                <h2 class="text-5xl text-white md:text-6xl font-bold mb-8">Kategorijas</h2>
                <div class="flex flex-wrap gap-4">
                    <?php while ($category = $categories->fetch_assoc()): ?>
                    <a href=" category.php?slug=<?php echo $category['slug']; ?>"
                        class="block p-3 bg-green-lier w-fit rounded-lg shadow-md hover:shadow-2xl shadow-black transition-shadow ">
                        <h3 class="text-base text-green-dark font-semibold ">
                            <?php echo htmlspecialchars($category['name']); ?></h3>
                    </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </section>

        <section>
            <div class="flex justify-center items-center">
                <?php while ($post = $posts->fetch_assoc()): ?>
                <article class="overflow-hidden w-full">
                    <div class="lg:px-10 px-5 flex flex-row justify-center items-center">
                        <div class="w-full max-w-[1400px] border-b-2  border-green-dark pb-10">
                            <div class="text-zinc-200 text-sm mb-4 inline overflow-hidden">
                                <?php echo date('F j, Y', strtotime($post['created_at'])); ?>
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </div>
                            <h3
                                class="md:text-5xl text-4xl text-white hover:text-green-lier font-semibold mb-2 transition-all duration-100 ">
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
            </div>
        </section>

        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/contact-form.php'; ?>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/footer.php'; ?>
    </main>
</body>

</html>