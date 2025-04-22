<?php
// Public certificate verification page
require_once '../includes/db_connect.php';
$code = isset($_GET['code']) ? trim($_GET['code']) : '';
$cert = null;
if ($code) {
    $stmt = $pdo->prepare('SELECT c.*, u.name AS student_name, cr.course_name FROM certificates c JOIN users u ON c.user_id = u.id JOIN courses cr ON c.course_id = cr.id WHERE c.certificate_code = ?');
    $stmt->execute([$code]);
    $cert = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - Bonnie Computer Hub</title>
    <link rel="stylesheet" href="../../assets/css/bch-global.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <main class="container mx-auto px-4 py-12">
        <div class="max-w-lg mx-auto bg-white rounded-lg shadow-md p-8">
            <h1 class="text-2xl font-bold text-primary mb-6 text-center">Certificate Verification</h1>
            <form method="get" class="mb-6 flex gap-2">
                <input type="text" name="code" value="<?= htmlspecialchars($code) ?>" placeholder="Enter certificate code" class="flex-1 px-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-secondary" required>
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Verify</button>
            </form>
            <?php if ($code && !$cert): ?>
                <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">Certificate not found or invalid code.</div>
            <?php elseif ($cert): ?>
                <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">Certificate is valid!</div>
                <div class="mb-4">
                    <div class="font-semibold">Student:</div>
                    <div><?= htmlspecialchars($cert['student_name']) ?></div>
                </div>
                <div class="mb-4">
                    <div class="font-semibold">Course:</div>
                    <div><?= htmlspecialchars($cert['course_name']) ?></div>
                </div>
                <div class="mb-4">
                    <div class="font-semibold">Grade:</div>
                    <div><?= $cert['grade'] !== null ? number_format($cert['grade'],2).'%' : 'N/A' ?></div>
                </div>
                <div class="mb-4">
                    <div class="font-semibold">Status:</div>
                    <div class="capitalize">
                        <span class="inline-block px-2 py-1 rounded <?= $cert['status']==='issued' ? 'bg-green-100 text-green-700' : ($cert['status']==='revoked' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                            <?= htmlspecialchars($cert['status']) ?>
                        </span>
                    </div>
                </div>
                <div class="mb-4">
                    <div class="font-semibold">Issued At:</div>
                    <div><?= htmlspecialchars($cert['issued_at']) ?></div>
                </div>
                <div class="mb-4">
                    <div class="font-semibold">Certificate Code:</div>
                    <div class="font-mono text-xs text-gray-500"><?= htmlspecialchars($cert['certificate_code']) ?></div>
                </div>
                <?php if ($cert): ?>
                <div class="mb-8 flex flex-col gap-4 items-center">
                    <div class="font-semibold text-lg text-primary flex items-center gap-2">
                        <img src="../../images/BCH.jpg" alt="Bonnie Computer Hub Logo" class="h-12 w-auto rounded shadow" style="background:white;"> BCH Official Certificate Preview
                    </div>
                    <div class="rounded shadow-lg border border-gray-200 bg-white p-4">
                        <img src="../certificate.php?course_id=<?= $cert['course_id'] ?>&user_id=<?= $cert['user_id'] ?>&preview=1" alt="Certificate Preview" class="max-w-full h-auto">
                    </div>
                    <div class="flex flex-wrap gap-3 justify-center mt-4">
                        <a href="../certificate.php?course_id=<?= $cert['course_id'] ?>&user_id=<?= $cert['user_id'] ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow transition">Download PNG</a>
                        <?php if ($cert['pdf_path']): ?>
                        <a href="<?= $cert['pdf_path'] ?>" target="_blank" class="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded shadow transition">Download PDF</a>
                        <?php endif; ?>
                        <button onclick="window.print()" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow transition">Print</button>
                        <!-- LinkedIn Share Button -->
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('http://localhost/BonnieComputerHub/LMS/pages/verify_certificate.php?code=' . $cert['certificate_code']) ?>" target="_blank" class="bg-[#0077b5] hover:bg-[#005983] text-white px-4 py-2 rounded shadow flex items-center gap-2 transition"><svg xmlns='http://www.w3.org/2000/svg' fill='currentColor' viewBox='0 0 24 24' class='h-5 w-5'><path d='M19 0h-14c-2.76 0-5 2.24-5 5v14c0 2.76 2.24 5 5 5h14c2.76 0 5-2.24 5-5v-14c0-2.76-2.24-5-5-5zm-11 19h-3v-10h3v10zm-1.5-11.27c-.97 0-1.75-.79-1.75-1.76 0-.97.78-1.76 1.75-1.76s1.75.79 1.75 1.76c0 .97-.78 1.76-1.75 1.76zm13.5 11.27h-3v-5.6c0-1.34-.03-3.07-1.87-3.07-1.87 0-2.16 1.46-2.16 2.97v5.7h-3v-10h2.88v1.36h.04c.4-.75 1.38-1.54 2.85-1.54 3.05 0 3.62 2.01 3.62 4.62v5.56z'/></svg>Share on LinkedIn</a>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
