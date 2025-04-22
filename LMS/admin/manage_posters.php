<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/helpers/course_functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}


// Handle CRUD actions (list, create, edit, delete, status)
$action = $_GET['action'] ?? 'list';
$message = '';

// Fetch courses for poster generator dropdown
$courses = get_courses($pdo, [], 'admin');

// Handle POST requests for create/update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle auto poster generation
    if (isset($_POST['auto_generate_poster'])) {
        $course_id = intval($_POST['course_id']);
        $headline = trim($_POST['headline']);
        $subtext = trim($_POST['subtext']);
        $cta = trim($_POST['cta']);
        $bg_color = $_POST['bg_color'] ?? '#1E40AF';
        $text_color = $_POST['text_color'] ?? '#FFFFFF';
        $cta_color = $_POST['cta_color'] ?? '#FFD700';

        // Get course name for poster
        $course = null;
        foreach ($courses as $c) {
            if ($c['id'] == $course_id) { $course = $c; break; }
        }
        if (!$course) {
            $message = 'Invalid course selected.';
        } else {
            $upload_dir = '../uploads/posters/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $filename = uniqid('autoposter_', true) . '.png';
            $filepath = $upload_dir . $filename;

            // Create base image
            $width = 800; $height = 1000;
            $im = imagecreatetruecolor($width, $height);
            // Colors
            $bg = sscanf($bg_color, "#%02x%02x%02x");
            $txt = sscanf($text_color, "#%02x%02x%02x");
            $cta_col = sscanf($cta_color, "#%02x%02x%02x");
            $bg_alloc = imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
            $txt_alloc = imagecolorallocate($im, $txt[0], $txt[1], $txt[2]);
            $cta_alloc = imagecolorallocate($im, $cta_col[0], $cta_col[1], $cta_col[2]);
            imagefilledrectangle($im, 0, 0, $width, $height, $bg_alloc);

            // Optional background image
            if (isset($_FILES['bg_image']) && $_FILES['bg_image']['error'] === UPLOAD_ERR_OK) {
                $bgimg_ext = strtolower(pathinfo($_FILES['bg_image']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($bgimg_ext, $allowed)) {
                    $bgimg_path = $_FILES['bg_image']['tmp_name'];
                    if ($bgimg_ext === 'png') {
                        $bgimg = imagecreatefrompng($bgimg_path);
                    } else {
                        $bgimg = imagecreatefromjpeg($bgimg_path);
                    }
                    imagecopyresampled($im, $bgimg, 0, 0, 0, 0, $width, $height, imagesx($bgimg), imagesy($bgimg));
                    imagedestroy($bgimg);
                }
            }

            // Headline
            $font = __DIR__ . '/../assets/fonts/Inter-Bold.ttf';
            if (!file_exists($font)) $font = __DIR__ . '/../assets/fonts/arial.ttf';
            imagettftext($im, 42, 0, 60, 160, $txt_alloc, $font, $headline);
            // Subtext
            imagettftext($im, 26, 0, 60, 230, $txt_alloc, $font, $subtext);
            // Course name
            imagettftext($im, 20, 0, 60, 290, $txt_alloc, $font, 'Course: ' . $course['course_name']);
            // CTA button
            imagefilledrectangle($im, 60, 340, 420, 410, $cta_alloc);
            imagettftext($im, 28, 0, 90, 390, $bg_alloc, $font, $cta);

            // Optional logo
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $logo_ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png'];
                if (in_array($logo_ext, $allowed)) {
                    $logo_path = $_FILES['logo']['tmp_name'];
                    if ($logo_ext === 'png') {
                        $logo = imagecreatefrompng($logo_path);
                    } else {
                        $logo = imagecreatefromjpeg($logo_path);
                    }
                    $logo_w = 120; $logo_h = 120;
                    imagecopyresampled($im, $logo, $width - $logo_w - 40, 40, 0, 0, $logo_w, $logo_h, imagesx($logo), imagesy($logo));
                    imagedestroy($logo);
                }
            }

            imagepng($im, $filepath);
            imagedestroy($im);

            // Insert into posters table
            $stmt = $pdo->prepare('INSERT INTO posters (title, description, image_path, cta_text, cta_link, status) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $headline,
                $subtext,
                $filename,
                $cta,
                '', // No link for generated posters
                'active'
            ]);
            $message = 'Poster generated and saved successfully!';
            $action = 'list';
        }
    }
    // End auto poster generation

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cta_text = trim($_POST['cta_text'] ?? '');
    $cta_link = trim($_POST['cta_link'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $poster_id = $_POST['id'] ?? null;
    $image_path = null;

    // Handle image upload (manual poster upload)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/posters/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $image_path = uniqid('poster_', true) . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_path);
        } else {
            $message = 'Invalid image file type.';
        }
    }

    if ($action === 'create' && $title && $image_path) {
        $stmt = $pdo->prepare('INSERT INTO posters (title, description, image_path, cta_text, cta_link, status) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title, $description, $image_path, $cta_text, $cta_link, $status]);
        $message = 'Poster created successfully!';
        $action = 'list';
    } elseif ($action === 'edit' && $poster_id) {
        $sql = 'UPDATE posters SET title=?, description=?, cta_text=?, cta_link=?, status=?';
        $params = [$title, $description, $cta_text, $cta_link, $status];
        if ($image_path) {
            $sql .= ', image_path=?';
            $params[] = $image_path;
        }
        $sql .= ' WHERE id=?';
        $params[] = $poster_id;
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $message = 'Poster updated successfully!';
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('DELETE FROM posters WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $message = 'Poster deleted.';
    $action = 'list';
}

// Fetch posters for list view
$posters = [];
if ($action === 'list') {
    $stmt = $pdo->query('SELECT * FROM posters ORDER BY created_at DESC');
    $posters = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch single poster for edit
$poster = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare('SELECT * FROM posters WHERE id = ?');
    $stmt->execute([$_GET['id']]);
    $poster = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Posters - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#002147',
                        secondary: '#FFD700',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <a href="#main-content" class="skip-link absolute left-0 top-0 bg-yellow-300 text-blue-900 px-4 py-2 z-50">Skip to main content</a>

    <!-- Poster Generator Modal/Form -->
    <main id="main-content" tabindex="-1">
    <section class="container mx-auto px-4 mt-8 mb-12">
        <div class="bg-white rounded-xl shadow-lg p-8 max-w-2xl mx-auto">
            <h2 class="text-2xl font-bold mb-6 text-primary">Auto-Generate Course Poster/Banner</h2>
            <?php if (!empty($message)): ?>
                <div class="mb-4 text-green-700 bg-green-100 px-4 py-2 rounded"> <?= htmlspecialchars($message) ?> </div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data" class="space-y-6" id="autoPosterForm" aria-label="Auto-generate course poster form">
    <input type="hidden" name="auto_generate_poster" value="1">
    <div>
        <label class="block font-semibold mb-2" for="course_id">Select Course</label>
        <select name="course_id" id="course_id" class="input input-bordered w-full" required aria-required="true">
            <option value="">-- Choose Course --</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>"> <?= htmlspecialchars($c['course_name']) ?> </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block font-semibold mb-2" for="headline">Headline</label>
        <input type="text" name="headline" id="headline" class="input input-bordered w-full" maxlength="60" placeholder="e.g. Enroll Now!" required aria-required="true">
    </div>
    <div>
        <label class="block font-semibold mb-2" for="subtext">Subtext</label>
        <input type="text" name="subtext" id="subtext" class="input input-bordered w-full" maxlength="100" placeholder="e.g. Limited slots for April intake!">
    </div>
    <div>
        <label class="block font-semibold mb-2" for="cta">Call to Action</label>
        <input type="text" name="cta" id="cta" class="input input-bordered w-full" maxlength="32" placeholder="e.g. Register Today!" required aria-required="true">
    </div>
    <div>
        <label class="block font-semibold mb-2" for="layout">Poster Layout Style</label>
        <select name="layout" id="layout" class="input input-bordered w-full" aria-label="Poster layout style">
            <option value="classic">Classic</option>
            <option value="modern">Modern</option>
            <option value="minimal">Minimalist</option>
        </select>
    </div>
    <div class="flex gap-4">
        <div>
            <label class="block font-semibold mb-2" for="bg_color">Background Color</label>
            <input type="color" name="bg_color" id="bg_color" value="#1E40AF" class="w-12 h-12 p-0 border-0" aria-label="Background color">
        </div>
        <div>
            <label class="block font-semibold mb-2" for="text_color">Text Color</label>
            <input type="color" name="text_color" id="text_color" value="#FFFFFF" class="w-12 h-12 p-0 border-0" aria-label="Text color">
        </div>
        <div>
            <label class="block font-semibold mb-2" for="cta_color">CTA Color</label>
            <input type="color" name="cta_color" id="cta_color" value="#FFD700" class="w-12 h-12 p-0 border-0" aria-label="CTA color">
        </div>
    </div>
    <div class="flex gap-4">
        <div>
            <label class="block font-semibold mb-2" for="logo">Optional Logo</label>
            <input type="file" name="logo" id="logo" accept="image/*" class="input input-bordered" aria-label="Logo image">
        </div>
        <div>
            <label class="block font-semibold mb-2" for="bg_image">Optional Background Image</label>
            <input type="file" name="bg_image" id="bg_image" accept="image/*" class="input input-bordered" aria-label="Background image">
        </div>
    </div>
    <div>
        <button type="button" onclick="showPosterPreview()" class="bch-btn bch-btn-secondary mr-4" aria-haspopup="dialog" aria-controls="autoPosterPreviewModal">Preview Poster</button>
        <button type="submit" class="bch-btn bch-btn-primary">Generate Poster</button>
    </div>
</form>
                <input type="hidden" name="auto_generate_poster" value="1">
                <div>
                    <label class="block font-semibold mb-2" for="course_id">Select Course</label>
                    <select name="course_id" id="course_id" class="input input-bordered w-full" required>
                        <option value="">-- Choose Course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"> <?= htmlspecialchars($c['course_name']) ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2" for="headline">Headline</label>
                    <input type="text" name="headline" id="headline" class="input input-bordered w-full" maxlength="60" placeholder="e.g. Enroll Now!" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2" for="subtext">Subtext</label>
                    <input type="text" name="subtext" id="subtext" class="input input-bordered w-full" maxlength="100" placeholder="e.g. Limited slots for April intake!">
                </div>
                <div>
                    <label class="block font-semibold mb-2" for="cta">Call to Action</label>
                    <input type="text" name="cta" id="cta" class="input input-bordered w-full" maxlength="32" placeholder="e.g. Register Today!" required>
                </div>
                <div class="flex gap-4">
                    <div>
                        <label class="block font-semibold mb-2" for="bg_color">Background Color</label>
                        <input type="color" name="bg_color" id="bg_color" value="#1E40AF" class="w-12 h-12 p-0 border-0">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2" for="text_color">Text Color</label>
                        <input type="color" name="text_color" id="text_color" value="#FFFFFF" class="w-12 h-12 p-0 border-0">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2" for="cta_color">CTA Color</label>
                        <input type="color" name="cta_color" id="cta_color" value="#FFD700" class="w-12 h-12 p-0 border-0">
                    </div>
                </div>
                <div class="flex gap-4">
                    <div>
                        <label class="block font-semibold mb-2" for="logo">Optional Logo</label>
                        <input type="file" name="logo" id="logo" accept="image/*" class="input input-bordered">
                    </div>
                    <div>
                        <label class="block font-semibold mb-2" for="bg_image">Optional Background Image</label>
                        <input type="file" name="bg_image" id="bg_image" accept="image/*" class="input input-bordered">
                    </div>
                </div>
                <div>
                    <button type="button" onclick="showPosterPreview()" class="bch-btn bch-btn-secondary mr-4">Preview Poster</button>
                    <button type="submit" class="bch-btn bch-btn-primary">Generate Poster</button>
                </div>
            </form>
            <!-- Poster Preview Modal -->
    </section>
    <!-- Poster List Section -->
    <section class="container mx-auto px-4 mb-12">
        <h2 class="text-2xl font-bold mb-6 text-primary">All Posters</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php
            // Fetch all posters
            $stmt = $pdo->query('SELECT * FROM posters ORDER BY id DESC');
            $all_posters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($all_posters as $poster): ?>
                <div class="bch-card bg-white rounded-xl shadow-lg hover:shadow-xl transition duration-300 flex flex-col h-full p-4">
                    <img src="../uploads/posters/<?= htmlspecialchars($poster['image_path']) ?>" alt="Poster: <?= htmlspecialchars($poster['title']) ?>" class="rounded-lg mb-4 w-full h-64 object-cover" loading="lazy">
                    <div class="flex-1">
                        <h3 class="font-bold text-lg text-bch-blue mb-1"> <?= htmlspecialchars($poster['title']) ?> </h3>
                        <p class="text-gray-600 mb-2"> <?= htmlspecialchars($poster['description']) ?> </p>
                        <span class="inline-block bg-yellow-100 text-yellow-800 px-3 py-1 rounded text-xs font-medium mb-2"> <?= htmlspecialchars($poster['cta_text']) ?> </span>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <a href="../uploads/posters/<?= htmlspecialchars($poster['image_path']) ?>" download class="bch-btn bch-btn-primary flex-1" aria-label="Download poster">Download</a>
                        <button onclick="navigator.clipboard.writeText(window.location.origin + '/bonniecomputerhub/LMS/uploads/posters/<?= htmlspecialchars($poster['image_path']) ?>');alert('Poster link copied!')" class="bch-btn bch-btn-secondary flex-1" aria-label="Copy poster link">Share</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    </main>
            <div id="autoPosterPreviewModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center" role="dialog" aria-modal="true" aria-labelledby="posterPreviewTitle">
                <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-lg w-full relative flex flex-col items-center">
                    <button onclick="closeAutoPreviewModal()" class="absolute top-2 right-2 bg-bch-blue text-bch-gold rounded-full p-2 hover:bg-bch-gold hover:text-bch-blue transition" aria-label="Close poster preview"><i class="fas fa-times"></i></button>
                    <h3 id="posterPreviewTitle" class="text-lg font-bold mb-2">Poster Preview</h3>
                    <canvas id="posterPreviewCanvas" width="360" height="450" class="rounded-xl border shadow-lg" aria-label="Poster preview image"></canvas>
                </div>
            </div>
        </div>
    </section>
    <script>
    // Live poster preview with layout options and accessibility
    function showPosterPreview() {
        const canvas = document.getElementById('posterPreviewCanvas');
        const ctx = canvas.getContext('2d');
        // Clear
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        // Get values
        const headline = document.getElementById('headline').value;
        const subtext = document.getElementById('subtext').value;
        const cta = document.getElementById('cta').value;
        const bg_color = document.getElementById('bg_color').value;
        const text_color = document.getElementById('text_color').value;
        const cta_color = document.getElementById('cta_color').value;
        const layout = document.getElementById('layout').value;
        // Layouts
        if (layout === 'classic') {
            ctx.fillStyle = bg_color;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = 'bold 28px Inter, Arial, sans-serif';
            ctx.fillStyle = text_color;
            ctx.fillText(headline, 24, 60);
            ctx.font = '18px Inter, Arial, sans-serif';
            ctx.fillText(subtext, 24, 110);
            ctx.font = 'italic 16px Inter, Arial, sans-serif';
            ctx.fillText('Bonnie Computer Hub', 24, 140);
            ctx.fillStyle = cta_color;
            ctx.fillRect(24, 170, 312, 50);
            ctx.fillStyle = bg_color;
            ctx.font = 'bold 22px Inter, Arial, sans-serif';
            ctx.fillText(cta, 44, 205);
        } else if (layout === 'modern') {
            ctx.fillStyle = bg_color;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.save();
            ctx.globalAlpha = 0.12;
            ctx.fillStyle = cta_color;
            ctx.beginPath();
            ctx.arc(280, 120, 120, 0, 2 * Math.PI);
            ctx.fill();
            ctx.restore();
            ctx.font = 'bold 30px Inter, Arial, sans-serif';
            ctx.fillStyle = text_color;
            ctx.fillText(headline, 24, 70);
            ctx.font = '18px Inter, Arial, sans-serif';
            ctx.fillText(subtext, 24, 120);
            ctx.fillStyle = cta_color;
            ctx.fillRect(24, 340, 312, 50);
            ctx.fillStyle = bg_color;
            ctx.font = 'bold 22px Inter, Arial, sans-serif';
            ctx.fillText(cta, 44, 375);
        } else if (layout === 'minimal') {
            ctx.fillStyle = bg_color;
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            ctx.font = 'bold 22px Inter, Arial, sans-serif';
            ctx.fillStyle = text_color;
            ctx.fillText(headline, 24, 60);
            ctx.font = '16px Inter, Arial, sans-serif';
            ctx.fillText(subtext, 24, 100);
            ctx.strokeStyle = cta_color;
            ctx.lineWidth = 3;
            ctx.strokeRect(24, 130, 312, 50);
            ctx.font = 'bold 18px Inter, Arial, sans-serif';
            ctx.fillStyle = cta_color;
            ctx.fillText(cta, 44, 162);
        }
        document.getElementById('autoPosterPreviewModal').classList.remove('hidden');
        document.getElementById('posterPreviewCanvas').focus();
    }
    function closeAutoPreviewModal() {
        document.getElementById('autoPosterPreviewModal').classList.add('hidden');
    }
    // Keyboard accessibility for modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeAutoPreviewModal();
    });
</script>

    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Admin Panel</p>
                    </div>
                </div>
                <div>
                    <a href="admin_dashboard.php" class="text-secondary hover:text-white font-semibold"><i class="fas fa-tachometer-alt mr-1"></i> Dashboard</a>
                </div>
            </div>
        </div>
    </header>
    <main class="container mx-auto px-4 py-10">
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Posters</h1>
            <p class="text-gray-600">Create, update, and organize marketing posters for the platform.</p>
        </div>
    <?php if ($message): ?>
        <div class="bg-bch-accent-green text-white px-4 py-2 rounded mb-4 shadow"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    <?php if ($action === 'list'): ?>
        <a href="?action=create" class="bg-bch-gold text-bch-blue font-bold px-5 py-2 rounded shadow hover:bg-bch-blue hover:text-bch-gold transition mb-6 inline-block">+ New Poster</a>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
    <?php foreach ($posters as $p): ?>
        <div class="bg-white rounded-xl shadow border p-4 flex flex-col">
            <div class="relative">
                <img src="../uploads/posters/<?= htmlspecialchars($p['image_path']) ?>" alt="Poster" class="h-48 w-full object-cover rounded mb-3">
                <button type="button" onclick="openPreviewModal('<?= htmlspecialchars($p['image_path']) ?>', '<?= htmlspecialchars(addslashes($p['title'])) ?>', '<?= htmlspecialchars(addslashes($p['description'])) ?>', '<?= htmlspecialchars(addslashes($p['cta_text'])) ?>', '<?= htmlspecialchars(addslashes($p['cta_link'])) ?>')" class="absolute top-2 right-2 bg-bch-gold text-bch-blue px-2 py-1 rounded shadow hover:bg-bch-blue hover:text-bch-gold transition text-xs font-bold z-10">Preview</button>
                <a href="../uploads/posters/<?= htmlspecialchars($p['image_path']) ?>" download class="absolute top-2 left-2 bg-bch-blue text-bch-gold px-2 py-1 rounded shadow hover:bg-bch-gold hover:text-bch-blue transition text-xs font-bold z-10"><i class="fas fa-download"></i></a>
            </div>
            <h2 class="font-bold text-lg text-bch-blue mb-1"> <?= htmlspecialchars($p['title']) ?> </h2>
            <p class="text-bch-gray-700 mb-2"> <?= htmlspecialchars($p['description']) ?> </p>
            <?php if ($p['cta_text']): ?>
                <a href="<?= htmlspecialchars($p['cta_link']) ?>" class="bg-bch-blue text-bch-gold font-semibold px-3 py-1 rounded shadow hover:bg-bch-gold hover:text-bch-blue transition mb-2 inline-block"> <?= htmlspecialchars($p['cta_text']) ?> </a>
            <?php endif; ?>
            <div class="flex gap-2 mt-auto">
                <a href="?action=edit&id=<?= $p['id'] ?>" class="bg-bch-gold text-bch-blue px-3 py-1 rounded font-bold hover:bg-bch-blue hover:text-bch-gold transition">Edit</a>
                <a href="?action=delete&id=<?= $p['id'] ?>" class="bg-bch-accent-red text-white px-3 py-1 rounded font-bold hover:bg-bch-blue hover:text-bch-gold transition" onclick="return confirm('Delete this poster?')">Delete</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Preview Modal with Controls -->
<div id="posterPreviewModal" class="fixed inset-0 bg-black bg-opacity-60 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-lg w-full relative flex flex-col items-center" id="posterModalContainer" style="aspect-ratio: 1/1; max-width: 480px;">
        <button onclick="closePreviewModal()" class="absolute top-2 right-2 bg-bch-blue text-bch-gold rounded-full p-2 hover:bg-bch-gold hover:text-bch-blue transition"><i class="fas fa-times"></i></button>
        <!-- Controls -->
        <div class="w-full flex flex-wrap gap-2 mb-4 justify-center">
            <label class="text-xs font-bold text-bch-blue">Aspect Ratio:
                <select id="aspectRatioSelect" class="border rounded px-2 py-1 ml-1">
                    <option value="1/1">1:1 Instagram</option>
                    <option value="4/5">4:5 Portrait</option>
                    <option value="9/16">9:16 Stories/Reels</option>
                    <option value="16/9">16:9 YouTube</option>
                    <option value="2/3">2:3 Facebook</option>
                    <option value="3/4">3:4 WhatsApp</option>
                </select>
            </label>
            <label class="text-xs font-bold text-bch-blue">BG Gradient:
                <input type="color" id="color1" value="#002147" class="w-6 h-6 border rounded ml-1">
                <input type="color" id="color2" value="#FFD700" class="w-6 h-6 border rounded ml-1">
            </label>
            <label class="text-xs font-bold text-bch-blue"><input type="checkbox" id="toggleLogo" checked class="mr-1">BCH Logo</label>
            <label class="text-xs font-bold text-bch-blue"><input type="checkbox" id="toggleOverlay" class="mr-1">Platform Overlay</label>
            <label class="text-xs font-bold text-bch-blue">Custom Text: <input type="text" id="customText" class="border rounded px-2 py-1 ml-1 text-xs" maxlength="30"></label>
        </div>
        <div class="w-full h-full flex flex-col items-center justify-between p-4 relative" id="posterPreviewFrame" style="background: linear-gradient(135deg,#002147 70%,#FFD700 100%); aspect-ratio: 1/1;">
            <img id="previewPosterImg" src="" alt="Poster Preview" class="rounded-xl object-contain w-full h-2/5 mb-4 bg-white" style="max-height: 220px;">
            <h3 id="previewPosterTitle" class="text-2xl font-bold text-bch-gold mb-2 text-center"></h3>
            <p id="previewPosterDesc" class="text-white mb-4 text-center"></p>
            <a id="previewPosterCta" href="#" target="_blank" class="hidden bg-bch-gold text-bch-blue font-bold px-6 py-2 rounded shadow hover:bg-bch-blue hover:text-bch-gold transition mb-2">CTA</a>
            <img id="bchLogoOverlay" src="../images/BCH.jpg" alt="BCH Logo" class="absolute left-4 top-4 w-12 h-12 rounded-full border-2 border-bch-gold shadow-lg">
            <div id="platformOverlay" class="absolute right-4 top-4 px-3 py-1 bg-bch-blue text-bch-gold font-bold rounded shadow-lg hidden">Social</div>
            <div id="customTextOverlay" class="absolute bottom-4 left-1/2 -translate-x-1/2 text-bch-gold font-bold text-lg drop-shadow-lg"></div>
            <div class="flex justify-center gap-2 mt-2">
                <a id="downloadModalBtn" href="#" download class="bg-bch-blue text-bch-gold px-4 py-2 rounded shadow hover:bg-bch-gold hover:text-bch-blue transition font-bold"><i class="fas fa-download mr-1"></i>Download</a>
            </div>
            <div class="text-xs text-white mt-3 text-center">Aspect ratios: Instagram, Facebook, Twitter, WhatsApp, Stories, YouTube</div>
        </div>
    </div>
</div>
<script>
let currentImg = '', currentTitle = '', currentDesc = '', currentCta = '', currentLink = '';
function openPreviewModal(img, title, desc, cta, link) {
    document.getElementById('posterPreviewModal').classList.remove('hidden');
    document.getElementById('previewPosterImg').src = '../uploads/posters/' + img;
    document.getElementById('previewPosterTitle').textContent = title;
    document.getElementById('previewPosterDesc').textContent = desc;
    const ctaBtn = document.getElementById('previewPosterCta');
    if (cta && link) {
        ctaBtn.textContent = cta;
        ctaBtn.href = link;
        ctaBtn.classList.remove('hidden');
    } else {
        ctaBtn.classList.add('hidden');
    }
    document.getElementById('downloadModalBtn').href = '../uploads/posters/' + img;
    currentImg = img; currentTitle = title; currentDesc = desc; currentCta = cta; currentLink = link;
    // Reset controls
    document.getElementById('aspectRatioSelect').value = '1/1';
    document.getElementById('color1').value = '#002147';
    document.getElementById('color2').value = '#FFD700';
    document.getElementById('toggleLogo').checked = true;
    document.getElementById('toggleOverlay').checked = false;
    document.getElementById('customText').value = '';
    updatePreviewControls();
}
function closePreviewModal() {
    document.getElementById('posterPreviewModal').classList.add('hidden');
}
function updatePreviewControls() {
    // Aspect ratio
    const ratio = document.getElementById('aspectRatioSelect').value;
    document.getElementById('posterModalContainer').style.aspectRatio = ratio;
    document.getElementById('posterPreviewFrame').style.aspectRatio = ratio;
    // BG Gradient
    const c1 = document.getElementById('color1').value;
    const c2 = document.getElementById('color2').value;
    document.getElementById('posterPreviewFrame').style.background = `linear-gradient(135deg,${c1} 70%,${c2} 100%)`;
    // Logo
    document.getElementById('bchLogoOverlay').style.display = document.getElementById('toggleLogo').checked ? 'block' : 'none';
    // Platform overlay
    document.getElementById('platformOverlay').style.display = document.getElementById('toggleOverlay').checked ? 'block' : 'none';
    // Custom text
    const txt = document.getElementById('customText').value;
    document.getElementById('customTextOverlay').textContent = txt;
}
document.getElementById('aspectRatioSelect').addEventListener('change', updatePreviewControls);
document.getElementById('color1').addEventListener('input', updatePreviewControls);
document.getElementById('color2').addEventListener('input', updatePreviewControls);
document.getElementById('toggleLogo').addEventListener('change', updatePreviewControls);
document.getElementById('toggleOverlay').addEventListener('change', updatePreviewControls);
document.getElementById('customText').addEventListener('input', updatePreviewControls);
window.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closePreviewModal();
});
</script>
    <?php else: ?>
        <form action="?action=<?= $action ?><?= $poster ? '&id=' . $poster['id'] : '' ?>" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-6 max-w-xl mx-auto">
            <input type="hidden" name="id" value="<?= $poster['id'] ?? '' ?>">
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="title">Title</label>
                <input type="text" name="title" id="title" value="<?= htmlspecialchars($poster['title'] ?? '') ?>" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="description">Description</label>
                <textarea name="description" id="description" rows="3" class="w-full border rounded px-3 py-2"><?= htmlspecialchars($poster['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="image">Poster Image</label>
                <input type="file" name="image" id="image" <?= $action === 'create' ? 'required' : '' ?> class="w-full">
                <?php if ($poster && $poster['image_path']): ?>
                    <div class="flex gap-2 items-center mt-2">
                        <img src="../uploads/posters/<?= htmlspecialchars($poster['image_path']) ?>" alt="Poster" class="h-32 rounded border">
                        <button type="button" onclick="openPreviewModal('<?= htmlspecialchars($poster['image_path']) ?>', '<?= htmlspecialchars(addslashes($poster['title'])) ?>', '<?= htmlspecialchars(addslashes($poster['description'])) ?>', '<?= htmlspecialchars(addslashes($poster['cta_text'])) ?>', '<?= htmlspecialchars(addslashes($poster['cta_link'])) ?>')" class="bg-bch-gold text-bch-blue px-3 py-1 rounded shadow hover:bg-bch-blue hover:text-bch-gold transition font-bold">Preview</button>
                        <a href="../uploads/posters/<?= htmlspecialchars($poster['image_path']) ?>" download class="bg-bch-blue text-bch-gold px-3 py-1 rounded shadow hover:bg-bch-gold hover:text-bch-blue transition font-bold"><i class="fas fa-download"></i></a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="cta_text">CTA Text</label>
                <input type="text" name="cta_text" id="cta_text" value="<?= htmlspecialchars($poster['cta_text'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="cta_link">CTA Link</label>
                <input type="url" name="cta_link" id="cta_link" value="<?= htmlspecialchars($poster['cta_link'] ?? '') ?>" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block font-semibold mb-1" for="status">Status</label>
                <select name="status" id="status" class="w-full border rounded px-3 py-2">
                    <option value="active" <?= (isset($poster['status']) && $poster['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                    <option value="draft" <?= (!isset($poster['status']) || $poster['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="archived" <?= (isset($poster['status']) && $poster['status'] === 'archived') ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <button type="submit" class="bg-bch-blue text-bch-gold font-bold px-6 py-2 rounded shadow hover:bg-bch-gold hover:text-bch-blue transition">Save Poster</button>
            <a href="?action=list" class="ml-4 text-bch-blue hover:underline">Cancel</a>
        </form>
    <?php endif; ?>
</div>
    </main>
    <!-- Footer -->
    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row items-center justify-between">
                <div class="mb-2 md:mb-0">
                    &copy; <?php echo date('Y'); ?> Bonnie Computer Hub. All rights reserved.
                </div>
                <div>
                    <a href="../index.php" class="text-secondary hover:underline">Back to Main Site</a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
