<?php include('../includes/header.php'); ?>
<!-- Contact Section -->
<section id="contact" class="py-20 bg-gradient-to-r from-blue-50 via-yellow-50 to-blue-100 rounded-2xl shadow-md mt-16 mb-12 font-inter">
    <div class="container mx-auto px-4">
        <h2 class="text-4xl sm:text-5xl font-extrabold text-primary text-center mb-12" data-aos="fade-up" aria-label="Contact Us">
            <span class="text-secondary">Let’s Connect</span>
        </h2>
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Contact Form -->
                <div class="bg-white rounded-xl shadow-md p-8" data-aos="fade-up">
                    <form action="https://formspree.io/f/xkgwarar" method="POST" class="space-y-6">
                        <div class="relative">
                            <input type="text" id="name" name="name" required placeholder="Your Name"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <input type="email" id="email" name="email" required placeholder="Your Email"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                            <div class="absolute top-3.5 left-4 text-gray-400">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>

                        <div class="relative">
                            <textarea id="message" name="message" rows="5" required placeholder="Your Message"
                                class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-300 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"></textarea>
                            <div class="absolute top-4 left-4 text-gray-400">
                                <i class="fas fa-comment-dots"></i>
                            </div>
                        </div>

                        <button type="submit"
                            class="w-full bg-[#1E40AF] text-white py-3 rounded-lg font-semibold text-base shadow-md hover:bg-[#FFD700] hover:text-[#1E40AF] focus:outline-none focus:ring-4 focus:ring-[#FFD700] transition duration-300">
                            Send Message
                        </button>

                        <div id="form-success" class="hidden text-green-500 text-center mt-4 font-medium">
                            ✅ Message sent successfully!
                        </div>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="bg-white rounded-xl p-8 text-[#1E3A8A] shadow-md" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold mb-6 text-[#1E40AF]">Get in Touch</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-white p-3 rounded-full shadow-md">
                                <i class="fas fa-map-marker-alt text-[#FFD700]"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1 text-[#1E40AF]">Location</h4>
                                <p class="text-[#1E3A8A]">Kenya</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-white p-3 rounded-full shadow-md">
                                <i class="fas fa-phone text-[#FFD700]"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1 text-[#1E40AF]">Phone</h4>
                                <a href="tel:+254729820689" class="text-[#1E3A8A] hover:text-[#FFD700]">+254 729 820 689</a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-white p-3 rounded-full shadow-md">
                                <i class="fas fa-envelope text-[#FFD700]"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1 text-[#1E40AF]">Email</h4>
                                <a href="mailto:bonniecomputerhub24@gmail.com" class="text-[#1E3A8A] hover:text-[#FFD700]">bonniecomputerhub24@gmail.com</a>
                            </div>
                        </div>

                        <div class="pt-6">
                            <h4 class="font-semibold mb-4 text-[#1E40AF]">Follow Us</h4>
                            <div class="flex space-x-4">
                                <a href="https://www.linkedin.com/in/bonniecomputerhub-273753307/" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-linkedin-in bg-white text-[#FFD700] p-3 rounded-full shadow-md group-hover:bg-[#FFD700] group-hover:text-white transition duration-300"></i>
                                    <span class="absolute hidden group-hover:block text-sm text-white bg-black px-2 py-1 rounded -top-10 left-1/2 -translate-x-1/2">LinkedIn</span>
                                </a>
                                <a href="https://github.com/bonniecomputerhub24" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-github bg-white text-[#FFD700] p-3 rounded-full shadow-md group-hover:bg-[#FFD700] group-hover:text-white transition duration-300"></i>
                                    <span class="absolute hidden group-hover:block text-sm text-white bg-black px-2 py-1 rounded -top-10 left-1/2 -translate-x-1/2">GitHub</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</section>
<?php include_once('../includes/footer.php'); ?>