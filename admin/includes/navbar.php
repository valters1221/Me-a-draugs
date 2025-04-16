<nav
    class="bg-zinc-900 h-fit w-16 fixed left-3 top-[50%] rounded-xl translate-y-[-50%] flex flex-col items-center py-4 z-50">
    <!-- Logo at the top -->
    <div class="mb-8">
        <img src="https://spotthemark.com/images/logo.png" alt="Logo" class="w-10 h-10">
    </div>

    <!-- Blog List Button -->
    <div class="relative group mb-6">
        <a href="/admin/blog/list.php"
            class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-zinc-800 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1M19 20a2 2 0 002-2V8a2 2 0 00-2-2h-1M9 15L12 12M12 12L15 15M12 12V19" />
            </svg>
        </a>
        <div
            class="absolute left-16 top-1 scale-0 group-hover:scale-100 transition-transform origin-left duration-300 bg-zinc-900 text-white px-3 py-1 rounded-md whitespace-nowrap">
            Blog Posts
        </div>
    </div>

    <!-- Categories Button -->
    <div class="relative group">
        <a href="/admin/blog/categories/list.php"
            class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-zinc-800 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
            </svg>
        </a>
        <div
            class="absolute left-16 top-1 scale-0 group-hover:scale-100 transition-transform origin-left duration-300 bg-zinc-900 text-white px-3 py-1 rounded-md whitespace-nowrap">
            Categories
        </div>
    </div>
</nav>