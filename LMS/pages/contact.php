<?php include('../includes/header.php'); ?>
<!-- Contact Section -->
<section id="contact" class="py-20 bg-cover bg-center relative" style="background-image: url('../images/');">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm"></div>
    <div class="relative container mx-auto px-4 z-10">
        <h2 class="text-5xl font-bold text-white text-center mb-12" data-aos="fade-up">
            Let’s Connect
        </h2>

        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                
                <!-- Contact Form -->
                <div class="bg-white/30 backdrop-blur-xl rounded-xl shadow-2xl p-8" data-aos="fade-up">
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
                            class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary transition duration-300 transform hover:scale-105">
                            Send Message
                        </button>

                        <div id="form-success" class="hidden text-green-500 text-center mt-4 font-medium">
                            ✅ Message sent successfully!
                        </div>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="bg-white/20 backdrop-blur-xl rounded-xl p-8 text-white" data-aos="fade-up" data-aos-delay="200">
                    <h3 class="text-2xl font-bold mb-6">Get in Touch</h3>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="bg-primary text-white p-3 rounded-full">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Location</h4>
                                <p>Kenya</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-primary text-white p-3 rounded-full">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Phone</h4>
                                <a href="tel:+254729820689" class="hover:text-primary">+254 729 820 689</a>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="bg-primary text-white p-3 rounded-full">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="font-semibold mb-1">Email</h4>
                                <a href="mailto:bonniecomputerhub24@gmail.com" class="hover:text-primary">bonniecomputerhub24@gmail.com</a>
                            </div>
                        </div>

                        <div class="pt-6">
                            <h4 class="font-semibold mb-4">Follow Us</h4>
                            <div class="flex space-x-4">
                                <a href="https://www.linkedin.com/in/bonniecomputerhub-273753307/" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-linkedin-in bg-primary text-white p-3 rounded-full group-hover:bg-secondary transition duration-300"></i>
                                    <span class="absolute hidden group-hover:block text-sm text-white bg-black px-2 py-1 rounded -top-10 left-1/2 -translate-x-1/2">LinkedIn</span>
                                </a>
                                <a href="https://github.com/bonniecomputerhub24" target="_blank"
                                   class="group relative">
                                    <i class="fab fa-github bg-primary text-white p-3 rounded-full group-hover:bg-secondary transition duration-300"></i>
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