<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

$instructor_id = $_SESSION['user_id'];

// Check if course ID is provided and fetch the course details
if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    
    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$course_id]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    // If course not found, redirect
    if (!$course) {
        $_SESSION['flash_message'] = "Course not found.";
        header("Location: manage_courses.php");
        exit;
    }
} else {
    header("Location: manage_courses.php");
    exit;
}

// Handle course update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $price_type = $_POST['price_type'];
    $price = $price_type === 'paid' ? (float)$_POST['price'] : 0.00;
    $enrollment_status = $_POST['enrollment_status'];
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'self-paced';
    $next_intake_date = ($mode === 'instructor-led' && !empty($_POST['next_intake_date'])) ? $_POST['next_intake_date'] : null;
    $enrollment_deadline = $_POST['enrollment_deadline'];
    $schedule = json_encode($_POST['schedule'] ?? []);

    // Handle thumbnail upload
    $thumbnail = $course['thumbnail'] ?? '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $filePath = "{$uploadDir}{$fileName}";
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $filePath)) {
            $thumbnail = $fileName;
        }
    }

    $update_sql = "UPDATE courses SET course_name=?, description=?, price_type=?, price=?, enrollment_status=?, mode=?, next_intake_date=?, enrollment_deadline=?, schedule=?, thumbnail=? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([
        $course_name, $description, $price_type, $price, $enrollment_status, $mode, $next_intake_date, $enrollment_deadline, $schedule, $thumbnail, $course_id
    ]);

    $_SESSION['flash_message'] = "Course updated successfully.";
    header("Location: manage_courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1E40AF',
                        secondary: '#FFD700',
                        accent: '#F7A700'
                    },
                    fontFamily: {
                        sans: ['Inter', 'Arial', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <script>
    // Thumbnail preview
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('thumbnail');
        const preview = document.getElementById('thumbnail-preview');
        if (input && preview) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.src = ev.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <?php include '../includes/header.php'; ?>
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
                <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                    <i class="fas fa-book-open text-primary text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1 tracking-tight">Edit Course</h2>
                    <div class="text-xl md:text-2xl font-bold text-primary"> <?= htmlspecialchars($course['course_name']) ?> </div>
                </div>
            </div>
            <nav class="breadcrumb flex items-center gap-2 text-sm mb-4">
                <a href="instructor_dashboard.php" class="text-primary hover:underline">Instructor Dashboard</a>
                <span class="text-gray-400">&rsaquo;</span>
                <a href="manage_courses.php" class="text-primary hover:underline">Manage Courses</a>
                <span class="text-gray-400">&rsaquo;</span>
                <span class="text-gray-600">Edit Course</span>
            </nav>
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
                    <?= htmlspecialchars($_SESSION['flash_message']) ?>
                    <?php unset($_SESSION['flash_message']); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-8 space-y-8 border border-gray-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1">
                        <label class="block font-semibold mb-2 text-gray-700" for="mode">Course Mode</label>
                        <select name="mode" id="mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" onchange="toggleIntakeField()" required>
                            <option value="instructor-led" <?= ($course['mode'] === 'instructor-led') ? 'selected' : '' ?>>Instructor-led</option>
                            <option value="self-paced" <?= ($course['mode'] === 'self-paced') ? 'selected' : '' ?>>Self-paced</option>
                        </select>
                    </div>
                    <div class="col-span-1" id="intake-date-field" style="display:<?= ($course['mode'] === 'instructor-led') ? 'block' : 'none' ?>;">
                        <label class="block font-semibold mb-2 text-gray-700" for="next_intake_date">Next Intake Date</label>
                        <input id="next_intake_date" type="date" name="next_intake_date" value="<?= htmlspecialchars($course['next_intake_date'] ?? '') ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Next Intake Date" <?= ($course['mode'] === 'instructor-led') ? 'required' : 'disabled' ?>>
                        <div class="mt-1 text-xs text-gray-400">Leave blank for self-paced courses.</div>
                    </div>
                    <script>
                    function toggleIntakeField() {
                        var mode = document.getElementById('mode').value;
                        var intakeField = document.getElementById('intake-date-field');
                        var intakeInput = document.getElementById('next_intake_date');
                        if (mode === 'instructor-led') {
                            intakeField.style.display = 'block';
                            intakeInput.disabled = false;
                            intakeInput.required = true;
                        } else {
                            intakeField.style.display = 'none';
                            intakeInput.disabled = true;
                            intakeInput.required = false;
                            intakeInput.value = '';
                        }
                    }
                    document.addEventListener('DOMContentLoaded', toggleIntakeField);
                    </script>
                    <div class="col-span-1">
                        <label class="block font-semibold mb-2 text-gray-700" for="courseName">Course Name</label>
                        <input id="courseName" type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Course Name">
                    </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="priceType">Pricing</label>
                    <select name="price_type" id="priceType" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" onchange="togglePriceField()" aria-label="Pricing Type">
                        <option value="free" <?= ($course['price_type']==='free')?'selected':'' ?>>Free</option>
                        <option value="paid" <?= ($course['price_type']==='paid')?'selected':'' ?>>Paid</option>
                    </select>
                    <input type="number" step="0.01" min="0" name="price" id="priceField" value="<?= htmlspecialchars($course['price']) ?>" class="mt-2 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans <?= ($course['price_type']==='paid')?'':'hidden' ?>" placeholder="Enter price" aria-label="Course Price">
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2" for="description">Description <span class="text-xs text-gray-400">(You may use formatting: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;br&gt;, &lt;p&gt;)</span></label>
                    <textarea id="description" name="description" required rows="4" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Course Description"><?= strip_tags($course['description'], '<p><br><ul><ol><li><b><strong><i><em><u>') ?></textarea>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block font-semibold mb-2 text-gray-700" for="thumbnail">Course Thumbnail</label>
                    <div class="flex items-center gap-4 mb-2">
                        <?php if (!empty($course['thumbnail'])): ?>
                            <img id="thumbnail-preview" src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" class="h-16 w-16 rounded object-cover border transition-all duration-200">
                        <?php else: ?>
                            <img id="thumbnail-preview" src="#" alt="No Image" class="h-16 w-16 rounded object-cover border hidden">
                            <div class="h-16 w-16 flex items-center justify-center bg-gray-200 rounded border text-gray-400">No Image</div>
                        <?php endif; ?>
                        <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Course Thumbnail">
                    </div>
                    <span class="text-xs text-gray-400">Accepted formats: JPG, PNG, GIF. Max 2MB.</span>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="enrollmentStatus">Enrollment Status</label>
                    <select name="enrollment_status" id="enrollmentStatus" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Enrollment Status">
                        <option value="open" <?= ($course['enrollment_status']==='open')?'selected':'' ?>>Open</option>
                        <option value="closed" <?= ($course['enrollment_status']==='closed')?'selected':'' ?>>Closed</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                        <label class="block font-semibold mb-2 text-gray-700" for="thumbnail">Course Thumbnail</label>
                        <div class="flex items-center gap-4 mb-2">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <img id="thumbnail-preview" src="../uploads/thumbnails/<?= htmlspecialchars($course['thumbnail']) ?>" alt="Thumbnail" class="h-16 w-16 rounded object-cover border transition-all duration-200">
                            <?php else: ?>
                                <img id="thumbnail-preview" src="#" alt="No Image" class="h-16 w-16 rounded object-cover border hidden">
                                <div class="h-16 w-16 flex items-center justify-center bg-gray-200 rounded border text-gray-400">No Image</div>
                            <?php endif; ?>
                            <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Course Thumbnail">
                        </div>
                        <span class="text-xs text-gray-400">Accepted formats: JPG, PNG, GIF. Max 2MB.</span>
                    </div>

                <div class="grid md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="intakeStart">Intake Start</label>
                        <input id="intakeStart" type="date" name="intake_start" value="<?= ($course['intake_start'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $course['intake_start'])) ? htmlspecialchars($course['intake_start']) : '' ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Intake Start">
                        <span class="block text-xs text-gray-400 mt-1">Format: YYYY-MM-DD</span>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="intakeEnd">Intake End</label>
                        <input id="intakeEnd" type="date" name="intake_end" value="<?= ($course['intake_end'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $course['intake_end'])) ? htmlspecialchars($course['intake_end']) : '' ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Intake End">
                        <span class="block text-xs text-gray-400 mt-1">Format: YYYY-MM-DD</span>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="enrollmentDeadline">Enrollment Deadline</label>
                        <input id="enrollmentDeadline" type="date" name="enrollment_deadline" value="<?= ($course['enrollment_deadline'] && preg_match('/^\d{4}-\d{2}-\d{2}$/', $course['enrollment_deadline'])) ? htmlspecialchars($course['enrollment_deadline']) : '' ?>" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Enrollment Deadline">
                        <span class="block text-xs text-gray-400 mt-1">Format: YYYY-MM-DD</span>
                    </div>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Weekly Schedule</label>
                    <div id="scheduleFields">
                        <?php 
                        $schedule = json_decode($course['schedule'] ?? '[]', true);
                        for ($w = 1; $w <= 8; $w++): ?>
                            <div class="mb-2">
                                <label class="block text-gray-500 text-sm">Week <?= $w ?> Topic</label>
                                <input type="text" name="schedule[<?= $w ?>]" value="<?= htmlspecialchars($schedule[$w] ?? '') ?>" class="w-full px-3 py-1 border rounded font-sans">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="flex justify-end gap-2 mt-6">
                    <a href="manage_courses.php" class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 focus:ring-2 focus:ring-secondary transition font-sans" aria-label="Cancel Edit">Cancel</a>
                    <button type="submit" name="update_course" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary/90 focus:ring-2 focus:ring-secondary transition font-sans" aria-label="Save Course Changes">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
    <script>
        function togglePriceField() {
            var type = document.getElementById('priceType').value;
            var priceField = document.getElementById('priceField');
            if (type === 'paid') {
                priceField.classList.remove('hidden');
            } else {
                priceField.classList.add('hidden');
            }
        }
        // Ensure correct field visibility on load
        document.addEventListener('DOMContentLoaded', togglePriceField);
    </script>
</body>
</html>
