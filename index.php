<?php include $_SERVER['DOCUMENT_ROOT'] . '/lang/handling.php' ; ?>
<!DOCTYPE html>
<html lang="<?php echo $lang->getCurrentLang(); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meža Draugas</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body class="bg-black ">
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header.php'; ?>
    <main class="relative overflow-hidden bg-green-md rounded-t-[30px] mt-1 -z-30">

        <section class="flex justify-center items-center relative h-fit py-52">
            <img src="/images/lines.svg" alt="background lines meža draugs"
                class="absolute width-svg min-[1000px]:h-[250vh] h-[1937px] w-[1924px] top-svg  top-[-50%] -translate-y-[40%] max-w-none">
            <div class="max-w-[1400px] w-full lg:px-10 px-5 z-20 relative">
                <h1
                    class="text-green-lier text-7xl min-[365px]:text-8xl lg:text-[10vw] font-bold lg:leading-tight lg:mb-0 mb-5 leading-[80px]">
                    Meža
                    Draugs
                </h1>
                <p class="text-zinc-200 text-base lg:text-lg max-w-[700px] mb-8  ">
                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus ut ipsum maximus. Etiam magna in,
                    suscipit massa. Nam in velit molestie turpis a pharetra mattis. Suspendisse bibendum lobortis.
                </p>
                <div class="flex items-center gap-4">
                    <a href="#"
                        class="bg-green-dark font-semibold text-lg text-zinc-100 px-6 py-3 rounded-xl hover:shadow-md hover:shadow-green-li transition-all duration-100">Sazinies
                        ar mums</a>
                    <button class="bg-zinc-200 p-2 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-black text-lg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                    </button>
                </div>
            </div>
        </section>

        <section class="flex justify-center items-center ">
            <div class="max-w-[1500px] w-full lg:px-10 px-5 z-20 relative">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="md:w-1/2 ">
                        <h1 class="text-white text-[20vw] sm:text-7xl leading-[95px] font-bold mb-3">Kas mēs esam!</h1>
                        <p class="text-zinc-200 text-base md:max-w-[90%]">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique, ad eum obcaecati minima
                            ipsa illo beatae labore cum voluptate maiores, a magni sint repellendus dicta quidem, libero
                            molestiae veniam aperiam explicabo placeat optio fugit consequatur odit. Neque, iure,
                            molestiae atque illum recusandae harum rerum quos quis ad voluptas dignissimos excepturi?
                        </p>
                    </div>
                    <div class="md:w-1/2 flex justify-center items-center">
                        <div
                            class="w-[300px] h-[300px] sm:w-[500px] sm:h-[500px] bg-green-li shadow-lg shadow-green-lier/50 rounded-full relative mt-10 sm:mt-0">
                            <img src="images/tree.png" alt="tree meža draugs"
                                class="absolute -top-16 -left-10 sm:-left-16 w-full h-full object-cover">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="flex justify-center items-center mt-20">
            <div class="max-w-[1500px] w-full lg:px-10 px-5 z-20 relative">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="md:w-1/2 flex justify-center items-center order-2 md:order-1">
                        <video src="images/mairis-briedis.mp4" autoplay loop muted
                            class="h-[50%] max-h-[600px] rounded-[30px] border-[10px] border-zinc-800">
                        </video>
                    </div>
                    <div class="md:w-1/2 flex flex-col order-1 md:order-2">
                        <h1 class="text-white text-[20vw] sm:text-8xl leading-[80px] font-bold mb-3">Seko Mums...</h1>
                        <p class="text-zinc-200 text-base mb-8">
                            Lorem ipsum dolor sit amet consectetur adipisicing elit. Similique, ad eum obcaecati ad eum
                            obcaecati
                        </p>
                        <div class="flex flex-col gap-4 w-full">
                            <a href="#"
                                class="bg-green-li px-7 py-4 rounded-xl hover:shadow-md hover:shadow-green-li transition-all duration-100 text-green-dark font-semibold text-lg flex items-center justify-between max-w-[350px] w-full">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                    </svg>
                                    Instagram
                                </div>
                                <div class="bg-zinc-200 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -rotate-45" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </a>
                            <a href="#"
                                class="bg-green-li px-7 py-4 rounded-xl hover:shadow-md hover:shadow-green-li transition-all duration-100 text-green-dark font-semibold text-lg flex items-center justify-between max-w-[350px] w-full">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z" />
                                    </svg>
                                    YouTube
                                </div>
                                <div class="bg-zinc-200 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -rotate-45" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </a>
                            <a href="#"
                                class="bg-green-li px-7 py-4 rounded-xl hover:shadow-md hover:shadow-green-li transition-all duration-100 text-green-dark font-semibold text-lg flex items-center justify-between max-w-[350px] w-full">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z" />
                                    </svg>
                                    Facebook
                                </div>
                                <div class="bg-zinc-200 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -rotate-45" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </a>
                            <a href="#"
                                class="bg-green-li px-7 py-4 rounded-xl hover:shadow-md hover:shadow-green-li transition-all duration-100 text-green-dark font-semibold text-lg flex items-center justify-between max-w-[350px] w-full">
                                <div class="flex items-center gap-2">
                                    <img src="images/tiktok.svg" alt="tiktok" class="w-6 h-6 mr-2">
                                    TikTok
                                </div>
                                <div class="bg-zinc-200 rounded-lg p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 -rotate-45" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                            d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                    </svg>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="my-20 relative">
            <img src="/images/lines.svg" alt="background lines meža draugs "
                class="absolute lg:h-[1700px] lg:w-[100vw] h-[1000px] w-[200vw] -z-10 top-[-70%] lg:right-[-60vw] right-[-150vw]  max-w-none">
            <div class="faq-container container mx-auto px-4">
                <h1
                    class="min-[1040px]:text-[6vw] text-4xl md:text-7xl font-bold text-white mb-16 min-[1040px]:leading-[110px] flex flex-col">
                    <div class="whitespace-nowrap">Viss biežāk uzdotie</div>
                    <span class="text-green-li">jautājumi?</span>
                </h1>

                <div class="space-y-6">
                    <!-- FAQ Item 1 -->
                    <div class="faq-item">
                        <div class="faq-question flex justify-between items-center cursor-pointer">
                            <div class="flex items-center">
                                <span class="text-green-lier text-7xl font-bold mr-6 md:block hidden">01</span>
                                <span class="text-white md:text-zinc-200 md:text-5xl text-4xl">Lorom ispisum?</span>
                            </div>
                            <div class="bg-white rounded-xl p-3 w-12 h-12 flex items-center justify-center">
                                <svg class="arrow w-6 h-6 rotate-45" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="faq-answer text-white text-xl">

                        </div>
                    </div>
                    <hr class="border-zinc-200 rounded-full">
                    <div class="faq-item">
                        <div class="faq-question flex justify-between items-center cursor-pointer">
                            <div class="flex items-center">
                                <span class="text-green-lier text-7xl font-bold mr-6 md:block hidden">02</span>
                                <span class="text-white md:text-zinc-200 md:text-5xl text-4xl">Lorom ispisum?</span>
                            </div>
                            <div class="bg-white rounded-xl p-3 w-12 h-12 flex items-center justify-center">
                                <svg class="arrow w-6 h-6 rotate-45" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="faq-answer text-white text-xl">

                        </div>
                    </div>
                    <hr class="border-zinc-200 rounded-full">
                    <div class="faq-item">
                        <div class="faq-question flex justify-between items-center cursor-pointer">
                            <div class="flex items-center">
                                <span class="text-green-lier text-7xl font-bold mr-6 md:block hidden">03</span>
                                <span class="text-white md:text-zinc-200 md:text-zinc-200 md:text-5xl text-4xl">Lorom
                                    ispisum?</span>
                            </div>
                            <div class="bg-white rounded-xl p-3 w-12 h-12 flex items-center justify-center">
                                <svg class="arrow w-6 h-6 rotate-45" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="faq-answer text-white text-xl">

                        </div>
                    </div>
                    <hr class="border-zinc-200 rounded-full">
                    <div class="faq-item">
                        <div class="faq-question flex justify-between items-center cursor-pointer">
                            <div class="flex items-center">
                                <span class="text-green-lier text-7xl font-bold mr-6 md:block hidden">04</span>
                                <span class="text-white md:text-zinc-200 md:text-5xl text-4xl">Lorom ispisum?</span>
                            </div>
                            <div class="bg-white rounded-xl p-3 w-12 h-12 flex items-center justify-center">
                                <svg class="arrow w-6 h-6 rotate-45" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </div>
                        </div>
                        <div class="faq-answer text-white text-xl">

                        </div>
                    </div>
                    <hr class="border-zinc-200 rounded-full">
                </div>
            </div>
        </section>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/contact-form.php'; ?>
    </main>
</body>

</html>