<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch the course to be edited
if (isset($_GET['id'])) {
    $courseId = $_GET['id'];

    $sql = "SELECT * FROM courses WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$course) {
        echo "Course not found.";
        exit;
    }

    // Fetch instructors for dropdown
    $instructor_sql = "SELECT id, name FROM users WHERE role = 'instructor'";
    $instructor_stmt = $pdo->prepare($instructor_sql);
    $instructor_stmt->execute();
    $instructors = $instructor_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "Invalid course ID.";
    exit;
}

// Handle course update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $duration_weeks = $_POST['duration_weeks'];
    $price = $_POST['price'];
    $price_type = $_POST['price_type'];
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'self-paced';
    $next_intake_date = ($mode === 'instructor-led' && !empty($_POST['next_intake_date'])) ? $_POST['next_intake_date'] : null;
    $certification = $_POST['certification'];
    $instructor_id = $_POST['instructor_id'];
    $payment_approved = isset($_POST['payment_approved']) ? 1 : 0;
    $schedule_weeks = isset($_POST['schedule_weeks']) ? $_POST['schedule_weeks'] : [];
    $schedule_json = json_encode($schedule_weeks);
    // New fields
    $delivery_mode = isset($_POST['delivery_mode']) ? $_POST['delivery_mode'] : $course['delivery_mode'];
    $tools = isset($_POST['tools']) ? implode(',', $_POST['tools']) : $course['tools'];
    $tags = isset($_POST['tags']) ? $_POST['tags'] : $course['tags'];
    $outcomes = isset($_POST['outcomes']) ? $_POST['outcomes'] : $course['outcomes'];
    // Handle banner image upload
    $banner_image = $course['banner_image'] ?? '';
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/banners/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['banner_image']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $filePath)) {
            $banner_image = $fileName;
        }
    }
    // Handle thumbnail upload
    $thumbnail = $course['thumbnail'] ?? '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
        $filePath = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $filePath)) {
            $thumbnail = $fileName;
        }
    }

    $update_sql = "UPDATE courses SET course_name = ?, description = ?, duration_weeks = ?, price = ?, price_type = ?, mode = ?, next_intake_date = ?, certification = ?, instructor_id = ?, payment_approved = ?, schedule = ?, thumbnail = ?, delivery_mode = ?, tools = ?, tags = ?, outcomes = ?, banner_image = ? WHERE id = ?";
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $description, $duration_weeks, $price, $price_type, $mode, $next_intake_date, $certification, $instructor_id, $payment_approved, $schedule_json, $thumbnail, $delivery_mode, $tools, $tags, $outcomes, $banner_image, $courseId]);
// Update recommended courses
if (isset($_POST['recommended_courses'])) {
    $pdo->prepare("DELETE FROM recommended_courses WHERE course_id = ?")->execute([$courseId]);
    foreach ($_POST['recommended_courses'] as $rec_id) {
        $pdo->prepare("INSERT INTO recommended_courses (course_id, recommended_id) VALUES (?, ?)")->execute([$courseId, $rec_id]);
    }
}
    $_SESSION['success_msg'] = 'Course updated successfully.';
    header("Location: manage_courses.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
 
    <title>Edit Course</title>
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
    <script src="schedule_fields.js"></script>
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

    <script src="schedule_fields.js"></script>
</head>
<body class="bg-gray-50 font-sans">
    <?php include '../includes/header.php'; ?>
    <main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
            <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                <i class="fas fa-book-open text-primary text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1 tracking-tight">Edit Course</h1>
                <div class="text-xl md:text-2xl font-bold text-primary"> <?= htmlspecialchars($course['course_name']) ?> </div>
            </div>
        </div>
        <nav class="breadcrumb flex items-center gap-2 text-sm mb-4">
            <a href="../admin/admin_dashboard.php" class="text-primary hover:underline">Home</a>
            <span class="text-gray-400">&rsaquo;</span>
            <a href="manage_courses.php" class="text-primary hover:underline">Courses</a>
            <span class="text-gray-400">&rsaquo;</span>
            <span class="text-gray-600">Edit Course</span>
        </nav>
        <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-8 space-y-8 border border-gray-100">
    <!-- Onboarding and Certificate Emailing toggles -->
    <div class="flex gap-6 mb-4">
        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="enable_onboarding" id="enable_onboarding" class="form-checkbox" disabled>
                <span class="ml-2">Enable Automated Onboarding (coming soon)</span>
            </label>
        </div>
        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="auto_email_certificate" id="auto_email_certificate" class="form-checkbox" disabled>
                <span class="ml-2">Auto Email Certificate (coming soon)</span>
            </label>
        </div>
    </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2" for="mode">Course Mode</label>
                    <select name="mode" id="mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" onchange="toggleIntakeField()" required>
                        <option value="instructor-led" <?= ($course['mode'] === 'instructor-led') ? 'selected' : '' ?>>Instructor-led</option>
                        <option value="self-paced" <?= ($course['mode'] === 'self-paced') ? 'selected' : '' ?>>Self-paced</option>
                    </select>
                </div>
                <div class="md:col-span-2" id="intake-date-field" style="display:<?= ($course['mode'] === 'instructor-led') ? 'block' : 'none' ?>;">
                    <label class="block text-gray-700 font-medium mb-2" for="next_intake_date">Next Intake Date</label>
                    <input type="date" name="next_intake_date" id="next_intake_date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" value="<?= htmlspecialchars($course['next_intake_date'] ?? '') ?>" <?= ($course['mode'] === 'instructor-led') ? 'required' : 'disabled' ?>>
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
    <label class="block font-semibold mb-2 text-gray-700" for="name">Course Name</label>
    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" type="text" name="name" id="name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="delivery_mode">Delivery Mode</label>
                    <select name="delivery_mode" id="delivery_mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary">
                        <option value="Online" <?= ($course['delivery_mode'] ?? '') === 'Online' ? 'selected' : '' ?>>Online</option>
                        <option value="Blended" <?= ($course['delivery_mode'] ?? '') === 'Blended' ? 'selected' : '' ?>>Blended</option>
                        <option value="In-Person" <?= ($course['delivery_mode'] ?? '') === 'In-Person' ? 'selected' : '' ?>>In-Person</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="tools">Tools/Technologies</label>
                    <select name="tools[]" id="tools" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" multiple>
                        <?php $courseTools = isset($course['tools']) ? explode(',', $course['tools']) : []; ?>
                        <option value="HTML" <?= in_array('HTML', $courseTools) ? 'selected' : '' ?>>HTML</option>
                        <option value="CSS" <?= in_array('CSS', $courseTools) ? 'selected' : '' ?>>CSS</option>
                        <option value="JavaScript" <?= in_array('JavaScript', $courseTools) ? 'selected' : '' ?>>JavaScript</option>
                        <option value="React" <?= in_array('React', $courseTools) ? 'selected' : '' ?>>React</option>
                        <option value="VS Code" <?= in_array('VS Code', $courseTools) ? 'selected' : '' ?>>VS Code</option>
                        <option value="Git" <?= in_array('Git', $courseTools) ? 'selected' : '' ?>>Git</option>
                    </select>
                    <span class="text-xs text-gray-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</span>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="tags">Tags (comma-separated)</label>
                    <input type="text" name="tags" id="tags" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" placeholder="e.g. MDN,Frontend,Web Development" value="<?= htmlspecialchars($course['tags'] ?? '') ?>" />
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2 text-gray-700" for="outcomes">Learning Outcomes</label>
                    <textarea name="outcomes" id="outcomes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" placeholder="Describe the main outcomes for this course"><?= htmlspecialchars($course['outcomes'] ?? '') ?></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-2 text-gray-700" for="banner_image">Course Banner Image (optional)</label>
                    <input type="file" name="banner_image" id="banner_image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" />
                    <?php if (!empty($course['banner_image'])): ?>
                        <img src="../uploads/banners/<?= htmlspecialchars($course['banner_image']) ?>" alt="Banner Image" class="mt-2 rounded shadow h-24">
                    <?php endif; ?>
                </div>
                </div>
            <div class="col-span-1 md:col-span-2">
                <label class="block font-semibold mb-2 text-gray-700" for="description">Course Description</label>
                <div id="description-editor"></div>
                <input type="hidden" name="description" id="description-hidden" value="<?= htmlspecialchars($course['description']) ?>">
                <div class="mt-2 text-xs text-gray-400">You may use formatting: &lt;b&gt;, &lt;i&gt;, &lt;u&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;br&gt;, &lt;p&gt;</div>
                <script>
                $(document).ready(function() {
                    $('#description-editor').summernote({
                        placeholder: 'Write course description here...',
                        tabsize: 2,
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'underline', 'italic', 'clear']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        callbacks: {
                            onChange: function(contents) {
                                $('#description-hidden').val(contents);
                            }
                        }
                    });
                    // Set initial value
                    $('#description-editor').summernote('code', $('#description-hidden').val());
                    // Prevent form submit if empty
                    $('form').on('submit', function(e) {
                        var description = $('#description-editor').summernote('code');
                        if (description.trim() === '' || description.trim() === '<p><br></p>') {
                            alert('Please provide a course description');
                            e.preventDefault();
                            return false;
                        }
                    });
                });
                </script>
            </div>
            <div class="col-span-1 md:col-span-2">
                <label class="block font-semibold mb-2 text-gray-700" for="schedule">Course Schedule (Weeks & Topics)</label>
                <div id="schedule-fields">
                    <?php 
                    $schedule = isset($course['schedule']) ? json_decode($course['schedule'], true) : [];
                    $weekNum = 1;
                    if (!empty($schedule)) 
                        foreach ($schedule as $week => $topic) 
                    ?>
                    <div class="flex mb-2">
                        <input type="text" name="schedule_weeks[<?= htmlspecialchars($week) ?>]" value="<?= htmlspecialchars($topic) ?>" placeholder="Topic for <?= htmlspecialchars($week) ?>" class="w-full px-3 py-2 border rounded-l">
                        <button type="button" class="remove-week bg-red-500 text-white px-4 rounded-r hover:bg-red-600 transition">&minus;</button>
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
            <div class="col-span-1 md:col-span-2">
                    <label class="block font-semibold mb-2 text-gray-700" for="schedule">Course Schedule (Weeks & Topics)</label>
                    <div id="schedule-fields">
                        <?php 
                        $schedule = isset($course['schedule']) ? json_decode($course['schedule'], true) : [];
                        $weekNum = 1;
                        if (!empty($schedule)) {
                            foreach ($schedule as $week => $topic) {
                        ?>
                        <div class="flex mb-2">
                            <input type="text" name="schedule_weeks[<?= htmlspecialchars($week) ?>]" value="<?= htmlspecialchars($topic) ?>" placeholder="Topic for <?= htmlspecialchars($week) ?>" class="w-full px-3 py-2 border rounded-l">
                            <button type="button" class="remove-week bg-red-500 text-white px-4 rounded-r hover:bg-red-600 transition">&minus;</button>
                        </div>
                        <?php $weekNum++; }} else { ?>
                        <div class="flex mb-2">
                            <input type="text" name="schedule_weeks[Week 1]" placeholder="Topic for Week 1" class="w-full px-3 py-2 border rounded-l">
                            <button type="button" class="add-week bg-secondary text-white px-4 rounded-r hover:bg-yellow-400 transition">+</button>
                        </div>
                        <?php } ?>
                    </div>
                    <small class="text-gray-500">Add week-by-week topics for this course.</small>
                </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1">
                    <label class="block font-semibold mb-2 text-gray-700" for="duration_weeks">Duration (weeks)</label>
                    <input class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" type="number" name="duration_weeks" id="duration_weeks" min="1" value="<?= htmlspecialchars($course['duration_weeks']) ?>" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="price">Price</label>
                    <input class="input input-bordered w-full" type="number" step="0.01" name="price" id="price" min="0" value="<?= htmlspecialchars($course['price']) ?>" required>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="price_type">Price Type</label>
                    <select class="input input-bordered w-full" name="price_type" id="price_type" required>
                        <option value="free" <?= $course['price_type'] === 'free' ? 'selected' : '' ?>>Free</option>
                        <option value="paid" <?= $course['price_type'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block font-semibold mb-2 text-gray-700" for="certification">Certification</label>
                    <input class="input input-bordered w-full" type="text" name="certification" id="certification" value="<?= htmlspecialchars($course['certification']) ?>">
                </div>
            </div>
            <div>
                <label class="block font-semibold mb-2 text-gray-700" for="instructor_id">Assigned Instructor</label>
                <select class="input input-bordered w-full" name="instructor_id" id="instructor_id" required>
                    <option value="">Select Instructor</option>
                    <?php foreach ($instructors as $instructor): ?>
                        <option value="<?= htmlspecialchars($instructor['id']) ?>" <?= $course['instructor_id'] == $instructor['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($instructor['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="flex items-center mt-2">
                <input type="checkbox" name="payment_approved" id="payment_approved" value="1" <?= $course['payment_approved'] ? 'checked' : '' ?>>
                <label for="payment_approved" class="ml-2 text-gray-700">Payment Approved</label>
            </div>
            <div class="flex justify-end mt-8">
                <button type="submit" name="update_course" class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition duration-300 flex items-center gap-2">
                    <i class="fas fa-save"></i> Update Course
                </button>
            </div>
        </form>
        <div class="mt-8 flex justify-between items-center">
            <a href="manage_courses.php" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-lg font-semibold shadow hover:bg-blue-800 transition">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
            <span class="text-xs text-gray-400">&copy; <?= date('Y') ?> Bonnie Computer Hub</span>
        </div>
    </div>
</main>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>