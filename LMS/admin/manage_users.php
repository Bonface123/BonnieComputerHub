<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Handle user addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    try {
        $insert_sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([$name, $email, $password, $role]);
        $_SESSION['success_msg'] = "User added successfully.";
        header("Location: manage_users.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = "Error adding user: " . $e->getMessage();
    }
}

// Fetch all users with role-based statistics
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM enrollments WHERE user_id = u.id) as enrollment_count,
        (SELECT COUNT(*) FROM submissions WHERE student_id = u.id) as submission_count
        FROM users u ORDER BY u.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BCH Learning</title>
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
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <img src="../images/BCH.jpg" alt="BCH Logo" class="h-12 w-12 rounded-full">
                    <div>
                        <a href="../index.php" class="text-xl font-bold text-secondary">Bonnie Computer Hub</a>
                        <p class="text-gray-300 text-sm">Admin Dashboard</p>
                    </div>
                </div>
                <nav class="hidden md:flex items-center space-x-6">
                    <a href="admin_dashboard.php" class="text-gray-300 hover:text-secondary transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </nav>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <!-- Page Title -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-2xl font-bold text-primary mb-2">Manage Users</h1>
            <p class="text-gray-600">Add, edit, and manage system users</p>
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

        <!-- Add User Form -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-primary mb-6">Add New User</h2>
            <form action="" method="POST" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="name">Full Name</label>
                        <input type="text" name="name" id="name" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter full name">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="email">Email Address</label>
                        <input type="email" name="email" id="email" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter email address">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="password">Password</label>
                        <input type="password" name="password" id="password" required
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent"
                               placeholder="Enter password">
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2" for="role">User Role</label>
                        <select name="role" id="role" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-secondary focus:border-transparent">
                            <option value="">Select role...</option>
                            <option value="student">Student</option>
                            <option value="instructor">Instructor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" name="add_user" 
                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition duration-300">
                        Add User
                    </button>
                </div>
            </form>
        </div>

        <!-- Users List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-primary mb-6">System Users</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stats</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" 
                                                 src="<?= $user['profile_image'] ?? '../images/default-avatar.png' ?>" 
                                                 alt="<?= htmlspecialchars($user['name']) ?>">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <?= htmlspecialchars($user['name']) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?= htmlspecialchars($user['email']) ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($user['role']) {
                                            case 'admin':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            case 'instructor':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            default:
                                                echo 'bg-blue-100 text-blue-800';
                                        }
                                        ?>">
                                        <?= ucfirst(htmlspecialchars($user['role'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php if ($user['role'] === 'student'): ?>
                                        <div>Enrollments: <?= $user['enrollment_count'] ?></div>
                                        <div>Submissions: <?= $user['submission_count'] ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="edit_user.php?id=<?= $user['id'] ?>" 
                                       class="text-primary hover:text-opacity-80 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="delete_user.php?id=<?= $user['id'] ?>" 
                                       onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')"
                                       class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Delete
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
</body>
</html>
