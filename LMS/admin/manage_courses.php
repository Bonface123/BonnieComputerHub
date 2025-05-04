<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Fetch instructors for dropdown
$instructor_sql = "SELECT id, name FROM users WHERE role = 'instructor'";
$instructor_stmt = $pdo->prepare($instructor_sql);
$instructor_stmt->execute();
$instructors = $instructor_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle course addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $createdBy = $_SESSION['user_id'];
    $duration_weeks = $_POST['duration_weeks'];
    $price = $_POST['price'];
    $price_type = $_POST['price_type'];
    $certification = $_POST['certification'];
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'self-paced';
    $next_intake_date = ($mode === 'instructor-led' && !empty($_POST['next_intake_date'])) ? $_POST['next_intake_date'] : null;
    $instructor_id = isset($_POST['instructor_id']) ? $_POST['instructor_id'] : null;
    $payment_approved = isset($_POST['payment_approved']) ? 1 : 0;
    $schedule_weeks = isset($_POST['schedule_weeks']) ? $_POST['schedule_weeks'] : [];
    $schedule_json = json_encode($schedule_weeks);
    // New fields
    $delivery_mode = isset($_POST['delivery_mode']) ? $_POST['delivery_mode'] : 'Online';
    $tools = isset($_POST['tools']) ? implode(',', $_POST['tools']) : '';
    $tags = isset($_POST['tags']) ? $_POST['tags'] : '';
    $outcomes = isset($_POST['outcomes']) ? $_POST['outcomes'] : '';

    // Handle banner image upload
    $banner_image = '';
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
    $thumbnail = '';
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

    // Validate instructor selection
    if (empty($instructor_id)) {
        $_SESSION['error_msg'] = 'Please select an instructor.';
        header('Location: manage_courses.php');
        exit;
    }

    try {
        $insert_sql = "INSERT INTO courses (course_name, description, created_by, duration_weeks, price, price_type, certification, instructor_id, payment_approved, schedule, thumbnail, delivery_mode, tools, tags, outcomes, banner_image, mode, next_intake_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$name, $description, $createdBy, $duration_weeks, $price, $price_type, $certification, $instructor_id, $payment_approved, $schedule_json, $thumbnail, $delivery_mode, $tools, $tags, $outcomes, $banner_image, $mode, $next_intake_date]);
        $_SESSION['success_msg'] = "Course added successfully.";
        header("Location: manage_courses.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding course: " . $e->getMessage();
    }
}

// Fetch all courses with creator information
$sql = "SELECT c.*, u.name as creator_name 
        FROM courses c 
        LEFT JOIN users u ON c.created_by = u.id 
        ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses - BCH Learning</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
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
    <script src="schedule_fields.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white shadow-md transition-all duration-300">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <img src="../images/BCH.jpg" alt="Bonnie Computer Hub Logo" class="h-12 w-12 rounded-full object-cover" />
            <div class="flex flex-col">
                <a href="../index.php" class="text-xl font-bold text-yellow-600 hover:text-primary transition duration-300">
                    BONNIE COMPUTER HUB
                </a>
                <span class="text-sm text-gray-500 font-medium tracking-wide ml-1">Admin Dashboard</span>
            </div>
        </div>
        <nav class="hidden md:flex items-center space-x-6">
            <a href="../index.html" class="text-gray-800 hover:text-primary transition font-medium hover:underline underline-offset-4">Home</a>
            <a href="../LMS/pages/courses.php" class="text-gray-600 hover:text-primary transition font-medium hover:underline underline-offset-4">Classes</a>
            <a href="admin_dashboard.php" class="ml-4 bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-full font-medium transition duration-300 transform hover:scale-105">Back to Dashboard</a>
        </nav>
        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100" id="mobile-menu-button" aria-label="Toggle mobile menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>
    <!-- Optional: visually consistent mobile menu placeholder, not functional for now -->
    <nav class="hidden md:hidden px-4 pb-4" id="mobile-menu" aria-label="Mobile navigation">
        <div class="flex flex-col space-y-4 mt-4">
            <a href="../index.html" class="text-gray-800 hover:text-primary transition">Home</a>
            <a href="../LMS/pages/courses.php" class="text-gray-600 hover:text-primary transition">Classes</a>
            <a href="admin_dashboard.php" class="mt-2 inline-block bg-primary hover:bg-blue-700 text-white px-4 py-2 rounded-full font-medium transition">Back to Dashboard</a>
        </div>
    </nav>
</header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-gradient-to-r from-primary via-blue-100 to-white rounded-xl shadow-lg p-6 mb-8 flex items-center gap-4 border border-blue-200">
            <div class="flex-shrink-0 bg-white rounded-full p-4 shadow">
                <i class="fas fa-graduation-cap text-primary text-3xl"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-800 mb-1">Manage Courses</h1>
                <div class="text-base md:text-lg text-primary">Create and manage courses in the learning management system</div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                <?= $_SESSION['success_msg'] ?>
                <?php unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <?= $_SESSION['error_msg'] ?>
                <?php unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Create Course Form -->
        <div class="bg-white rounded-xl shadow p-8 mb-10 border border-gray-100">
            <h2 class="text-lg font-semibold text-primary mb-4 flex items-center gap-2"><i class="fas fa-plus-circle"></i> Create New Course</h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <!-- Thumbnail Upload -->
<div>
    <label class="block font-semibold mb-2 text-gray-700" for="thumbnail">Course Thumbnail</label>
    <div class="flex items-center gap-4 mb-2">
        <div class="h-16 w-16 flex items-center justify-center bg-gray-200 rounded border text-gray-400">No Image</div>
        <input type="file" name="thumbnail" id="thumbnail" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent font-sans" aria-label="Course Thumbnail">
    </div>
    <span class="text-xs text-gray-400">Accepted formats: JPG, PNG, GIF. Max 2MB.</span>
</div>
<div class="grid md:grid-cols-2 gap-6">
    <div class="md:col-span-2">
        <label class="block text-gray-700 font-medium mb-2" for="mode">Course Mode</label>
        <select name="mode" id="mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" onchange="toggleIntakeField()" required>
            <option value="instructor-led">Instructor-led</option>
            <option value="self-paced">Self-paced</option>
        </select>
    </div>
    <div class="md:col-span-2" id="intake-date-field" style="display:none;">
        <label class="block text-gray-700 font-medium mb-2" for="next_intake_date">Next Intake Date</label>
        <input type="date" name="next_intake_date" id="next_intake_date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" disabled>
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
    <div class="md:col-span-2">
        <label class="block text-gray-700 font-medium mb-2" for="name">Course Name</label>
        <input type="text" name="name" id="name" required
               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent" />
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2" for="delivery_mode">Delivery Mode</label>
        <select name="delivery_mode" id="delivery_mode" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary">
            <option value="Online">Online</option>
            <option value="Blended">Blended</option>
            <option value="In-Person">In-Person</option>
        </select>
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2" for="tools">Tools/Technologies</label>
        <select name="tools[]" id="tools" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" multiple>
            <option value="HTML">HTML</option>
            <option value="CSS">CSS</option>
            <option value="JavaScript">JavaScript</option>
            <option value="React">React</option>
            <option value="VS Code">VS Code</option>
            <option value="Git">Git</option>
        </select>
        <span class="text-xs text-gray-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</span>
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2" for="tags">Tags (comma-separated)</label>
        <input type="text" name="tags" id="tags" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" placeholder="e.g. MDN,Frontend,Web Development" />
    </div>
    <div class="md:col-span-2">
        <label class="block text-gray-700 font-medium mb-2" for="outcomes">Learning Outcomes</label>
        <textarea name="outcomes" id="outcomes" rows="3" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" placeholder="Describe the main outcomes for this course"></textarea>
    </div>
    <div class="md:col-span-2">
        <label class="block text-gray-700 font-medium mb-2" for="banner_image">Course Banner Image (optional)</label>
        <input type="file" name="banner_image" id="banner_image" accept="image/*" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary" />
    </div>
                               placeholder="Enter course name">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2" for="description">Course Description</label>
                        <div id="description"></div>
                        <input type="hidden" name="description" id="description-hidden">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-gray-700 font-medium mb-2" for="schedule">Course Schedule (Weeks & Topics)</label>
                        <div id="schedule-fields">
                            <div class="flex mb-2">
                                <input type="text" name="schedule_weeks[Week 1]" placeholder="Topic for Week 1" class="w-full px-3 py-2 border rounded-l">
                                <button type="button" class="add-week bg-secondary text-white px-4 rounded-r">+</button>
                            </div>
                        </div>
                        <small class="text-gray-500">Add week-by-week topics for this course.</small>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="duration_weeks">Duration (weeks)</label>
                        <input type="number" name="duration_weeks" id="duration_weeks" min="1" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="e.g. 8">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="price">Price</label>
                        <input type="number" step="0.01" name="price" id="price" min="0" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="e.g. 99.99">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="price_type">Price Type</label>
                        <select name="price_type" id="price_type" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option value="free">Free</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="certification">Certification</label>
                        <input type="text" name="certification" id="certification"
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="e.g. Certificate of Completion">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="instructor_id">Assigned Instructor</label>
                        <select name="instructor_id" id="instructor_id" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?= htmlspecialchars($instructor['id']) ?>">
                                    <?= htmlspecialchars($instructor['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="flex items-center mt-2 md:col-span-2">
                        <input type="checkbox" name="payment_approved" id="payment_approved"
                               class="mr-2 rounded border-gray-300 focus:ring-secondary">
                        <label for="payment_approved" class="text-gray-700 font-medium">Payment Approved</label>
                    </div>
                </div>
                <div class="flex justify-end mt-6">
                    <button type="submit" name="add_course" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition duration-300 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Create Course
                    </button>
                </div>
            </form>
        </div>

        <!-- Courses List -->
        <?php if (isset($_SESSION['success_msg'])): ?>
            <div class="mb-4 text-green-800 bg-green-100 border border-green-200 px-4 py-3 rounded">
                <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="mb-4 text-red-800 bg-red-100 border border-red-200 px-4 py-3 rounded">
                <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Courses</h1>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($courses as $course): ?>
                            <tr class="hover:bg-blue-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-base font-semibold text-primary flex items-center gap-2">
                                        <i class="fas fa-graduation-cap"></i>
                                        <?= htmlspecialchars($course['course_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-h-20 overflow-y-auto">
                                        <?= $course['description'] ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= htmlspecialchars($course['creator_name']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        <?= date('M j, Y', strtotime($course['created_at'])) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium flex flex-col gap-2 md:flex-row md:gap-0">
                                    <a href="manage_modules.php?course_id=<?= $course['id'] ?>" 
                                       class="inline-flex items-center text-green-600 hover:text-green-800 mr-3 gap-1">
                                        <i class="fas fa-folder-open"></i> <span class="hidden md:inline">Modules</span>
                                    </a>
                                    <a href="edit_course.php?id=<?= $course['id'] ?>" 
                                       class="inline-flex items-center text-primary hover:text-primary-dark mr-3 gap-1">
                                        <i class="fas fa-edit"></i> <span class="hidden md:inline">Edit</span>
                                    </a>
                                    <a href="delete_course.php?id=<?= $course['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this course? This action cannot be undone.')"
                                       class="inline-flex items-center text-red-600 hover:text-red-900 gap-1">
                                        <i class="fas fa-trash"></i> <span class="hidden md:inline">Delete</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white mt-12">
        <div class="container mx-auto px-4 py-6">
            <div class="text-center">
                <p>&copy; <?= date('Y') ?> Bonnie Computer Hub. All rights reserved.</p>
                <div class="mt-2">
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Privacy Policy</a>
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Terms of Service</a>
                    <a href="#" class="text-secondary hover:text-opacity-80 mx-2">Contact Us</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        $(document).ready(function() {
            // Initialize Summernote
            $('#description').summernote({
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

            // Form validation
            $('form').on('submit', function(e) {
                var description = $('#description').summernote('code');
                if (description.trim() === '' || description.trim() === '<p><br></p>') {
                    alert('Please provide a course description');
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</body>
</html>
