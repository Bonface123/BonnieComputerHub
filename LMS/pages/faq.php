<?php
// pages/faq.php - Frequently Asked Questions for Bonnie Computer Hub

$pageTitle = "Frequently Asked Questions | Bonnie Computer Hub";
$faqs = [
    [
        'question' => 'What services does Bonnie Computer Hub offer?',
        'answer' => 'We provide web development, digital skills training, computer sales and repairs, software solutions, and ICT consultancy.'
    ],
    [
        'question' => 'How do I enroll in a course?',
        'answer' => 'Visit the Classes section, choose your desired course, and click the "Join Class" button. You will be guided through registration and payment.'
    ],
    [
        'question' => 'What payment methods are accepted?',
        'answer' => 'We accept M-Pesa, bank transfer, and major credit/debit cards. Contact support for more options.'
    ],
    [
        'question' => 'Can I get a certificate after completing a course?',
        'answer' => 'Yes! All our courses offer certificates upon successful completion.'
    ],
    [
        'question' => 'Where is Bonnie Computer Hub located?',
        'answer' => 'We are based in Nakuru, Kenya, but offer online services and courses nationwide.'
    ],
    [
        'question' => 'How do I contact support?',
        'answer' => 'Use the Contact page or email info@bonniecomputerhub.com. We respond within 24 hours.'
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body, html {
            font-family: 'Century Gothic', 'AppleGothic', sans-serif;
            font-size: 16px;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" href="../assets/css/bch-global.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">
    <?php include '../LMS/includes/header.php'; ?>
    <main class="flex-1 px-4 py-12 max-w-3xl mx-auto w-full">
        <div class="bg-bch-gray-100 rounded-xl shadow-lg p-8 border border-bch-blue-light mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-bch-blue mb-6 text-center">Frequently Asked Questions</h1>
            <div class="divide-y divide-bch-blue-light">
                <?php foreach ($faqs as $faq): ?>
                    <details class="py-4 group">
                        <summary class="cursor-pointer text-lg font-semibold text-bch-blue flex items-center justify-between">
                            <?= htmlspecialchars($faq['question']) ?>
                            <span class="ml-2 transition-transform group-open:rotate-180">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </summary>
                        <div class="mt-2 text-bch-gray-900 leading-relaxed">
                            <?= htmlspecialchars($faq['answer']) ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="text-center mt-6">
            <a href="../index.html" class="text-bch-blue hover:text-bch-gold-dark underline">&larr; Back to Home</a>
        </div>
    </main>
    <?php include '../LMS/includes/footer.php'; ?>
</body>
</html>
