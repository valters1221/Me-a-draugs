<!-- Actual visible elements positioned to appear on the green background -->
<div class="absolute top-[20px] left-0 right-0 z-50">
    <div class="max-w-[1200px] mx-auto w-full px-4">
        <div class="flex justify-between items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="/index" class="logo-container relative w-14 h-14">
                    <img src="/images/logo.png" alt="logo" class="w-14 h-14 logo-normal absolute inset-0">
                    <img src="/images/logo-white.png" alt="logo"
                        class="w-14 h-14 logo-white absolute inset-0 opacity-0">
                </a>
            </div>
            <!-- Hamburger Button -->
            <div class="flex items-center">
                <?php 
                $current_page = $_SERVER['REQUEST_URI'];
                $button_class = (strpos($current_page, '/blog/post') !== false) ? 'text-black' : 'text-white';
                ?>
                <button
                    class="<?php echo $button_class; ?> menu-toggle-btn relative w-16 h-16 flex justify-center items-center">
                    <svg width="" height="" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="hamburger-icon absolute inset-0 m-auto">
                        <path d="M4 6H20M4 12H20M4 18H20"
                            stroke="<?php echo (strpos($current_page, '/blog/post') !== false) ? 'black' : 'white'; ?>"
                            stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <svg width="" height="" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="close-icon absolute inset-0 m-auto opacity-0">
                        <path d="M18 6L6 18M6 6L18 18" stroke="#EF4444" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden menu that pushes page down when revealed -->
<div class="menu-content overflow-hidden text-white absolute left-0 right-0 z-30">
    <!-- Menu content wrapper with overflow to hide text until animation completes -->
    <div class="menu-content-wrapper overflow-hidden">
        <div class="max-w-[1200px] mx-auto w-full px-4 pt-[9.5rem] pb-16">
            <div
                class="md:grid md:grid-cols-2 md:grid-rows-2 md:gap-12 gap-y-10 flex flex-col justify-evenly menu-items-container">
                <div class="flex items-center menu-item">
                    <a href="/index#Par-Mums"
                        class="text-4xl md:text-5xl  md:pl-16 pl-8 font-bold hover:text-green-li  transition-all duration-300">Par
                        Mums</a>
                </div>
                <div class="flex items-center menu-item">
                    <a href="/index#BUJ"
                        class="text-4xl md:text-5xl  md:pl-16 pl-8 font-bold hover:text-green-li  transition-all duration-300">BUJ?
                    </a>
                </div>
                <div class="flex items-center menu-item">
                    <a href="/blog/list"
                        class="text-4xl md:text-5xl  md:pl-16 pl-8 font-bold hover:text-green-li  transition-all duration-300">
                        Blogs</a>
                </div>
                <div class="flex items-center menu-item">
                    <a href="/index#Saznies-ar-mums"
                        class="text-4xl md:text-5xl  md:pl-16 pl-8 font-bold hover:text-green-li  transition-all duration-300">
                        Kontakti</a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
main {
    will-change: transform;
    transition: transform 0.7s cubic-bezier(0.23, 1, 0.32, 1);
}

/* When menu is open, push the main content down using transform */
body.menu-open main {
    transform: translateY(400px);
}

/* Logo transition */
.logo-normal,
.logo-white {
    transition: opacity 0.3s ease-in-out;
    will-change: opacity;
}

body.menu-open .logo-normal {
    opacity: 0;
}

body.menu-open .logo-white {
    opacity: 1;
}

/* Improved button swap animation */
.hamburger-icon,
.close-icon {
    transition: opacity 0.3s ease;
    will-change: opacity;
}

body.menu-open .hamburger-icon {
    opacity: 0;
}

body.menu-open .close-icon {
    opacity: 1;
}

/* Menu content with gradual reveal */
.menu-content {
    height: 0;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transition: all 0.7s cubic-bezier(0.23, 1, 0.32, 1);
}

/* Position menu items and stagger their reveal */
.menu-item {
    opacity: 0;
    transform: translateY(30px);
    transition: transform 0.5s ease, opacity 0.5s ease;
    will-change: transform, opacity;
}

/* Menu item containers */
.menu-items-container {
    width: 100%;
}

/* Staggered reveal from top to bottom */
body.menu-open .menu-item:nth-child(1) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.1s;
}

body.menu-open .menu-item:nth-child(2) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.2s;
}

body.menu-open .menu-item:nth-child(3) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.3s;
}

body.menu-open .menu-item:nth-child(4) {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.4s;
}

body.menu-open .menu-content {
    height: auto;
    max-height: 500px;
    opacity: 1;
}

/* Ensure menu fits on mobile */
@media (max-width: 767px) {
    body.menu-open main {
        transform: translateY(480px);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggleBtn = document.querySelector('.menu-toggle-btn');
    const body = document.body;

    menuToggleBtn.addEventListener('click', function() {
        body.classList.toggle('menu-open');
    });
});
</script>