<section class="flex items-center justify-center p-4 mt-10" id="Saznies-ar-mums">
    <div class="container max-w-[1400px] mx-auto grid md:grid-cols-2 gap-12 items-center ">
        <!-- Left Section -->
        <div class="text-white space-y-8 animation-group">
            <h1
                class="min-[399px]:text-7xl text-6xl text-green-lier font-bold mb-4 flex flex-col animate fast-[0.7] blur-in group-trigger ">
                <span class="whitespace-nowrap">Saznies ar</span>
                <span class="">mums!</span>
            </h1>
            <p class="text-gray-200 mb-8 animate fast-[0.7] delays-[0.05] blur-in group-trigger">
                Mēs labprāt uzklausītu Tavu viedokli, jautājumus vai idejas.</p>

            <div class="space-y-4 ">
                <div class="flex items-center gap-4 animate fast-[1] reveal-up group-trigger">
                    <div class="bg-green-lier p-2 rounded-lg">
                        <i class="fas fa-phone text-black"></i>
                    </div>
                    <span>+123 456 7890</span>
                </div>
                <div class="flex items-center gap-4 animate fast-[1] delays-[0.05] reveal-up group-trigger">
                    <div class="bg-green-lier p-2 rounded-lg">
                        <i class="fas fa-envelope text-black"></i>
                    </div>
                    <span>LoremIpsum@ipsum.com</span>
                </div>
            </div>
        </div>

        <!-- Right Section - Form -->
        <div class="flex justify-center">
            <div class="bg-green-dark p-6 rounded-xl shadow-lg animate fast-[0.7] blur-in max-w-2xl w-full">
                <form class="space-y-6">
                    <div>
                        <input type="text" placeholder="jhon doe"
                            class="w-full bg-gray-200 rounded-xl p-3 text-gray-800">
                        <p class="text-gray-300 text-sm mt-1">Please enter your full name here</p>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <input type="tel" placeholder="123 456 7890"
                                class="w-full bg-gray-200 rounded-md p-3 text-gray-800">
                            <p class="text-gray-300 text-sm mt-1">Your phone number</p>
                        </div>
                        <div>
                            <input type="email" placeholder="example@email.com"
                                class="w-full bg-gray-200 rounded-md p-3 text-gray-800">
                            <p class="text-gray-300 text-sm mt-1">Your email address</p>
                        </div>
                    </div>

                    <div>
                        <textarea placeholder="messege" rows="4"
                            class="w-full bg-gray-200 rounded-md p-3 text-gray-800"></textarea>
                        <p class="text-gray-300 text-sm mt-1">Enter your message details here</p>
                    </div>

                    <div class="text-right">
                        <button type="submit"
                            class="bg-[#d8e8a2] hover:bg-[#c9db89] text-gray-800 font-medium py-2 px-6 rounded-md transition">
                            Sūtīt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>