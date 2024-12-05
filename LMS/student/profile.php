<?php
session_start();
require_once '../includes/db_connect.php';

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../pages/login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$user_query = $pdo->prepare("
    SELECT name, email, profile_image, bio
    FROM users 
    WHERE id = ?
");
$user_query->execute([$user_id]);
$user = $user_query->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $bio = htmlspecialchars(trim($_POST['bio']));
    
    // Handle profile image upload
    $profile_image = $user['profile_image']; // Keep existing image by default
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file = $_FILES['profile_image'];
        
        if (in_array($file['type'], $allowed_types)) {
            $upload_dir = '../uploads/profile_images/';
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $user_id . '_' . time() . '.' . $extension;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $profile_image = $filename;
                
                // Delete old profile image if exists
                if ($user['profile_image'] && file_exists($upload_dir . $user['profile_image'])) {
                    unlink($upload_dir . $user['profile_image']);
                }
            }
        }
    }
    
    // Handle password change
    $password_updated = false;
    if (!empty($_POST['new_password'])) {
        if (empty($_POST['current_password'])) {
            $error = "Current password is required to change password.";
        } else {
            // Verify current password
            $verify_query = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $verify_query->execute([$user_id]);
            $current_hash = $verify_query->fetchColumn();
            
            if (password_verify($_POST['current_password'], $current_hash)) {
                $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                $password_updated = true;
            } else {
                $error = "Current password is incorrect.";
            }
        }
    }
    
    if (!isset($error)) {
        // Update user data
        $update_query = $pdo->prepare("
            UPDATE users 
            SET name = ?, 
                email = ?, 
                profile_image = ?,
                bio = ?,
                " . ($password_updated ? ", password = ?" : "") . "
            WHERE id = ?
        ");
        
        $params = [$name, $email, $profile_image, $bio];
        if ($password_updated) {
            $params[] = $new_password;
        }
        $params[] = $user_id;
        
        if ($update_query->execute($params)) {
            $success = "Profile updated successfully!";
            // Refresh user data
            $user_query->execute([$user_id]);
            $user = $user_query->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update profile.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - BCH Learning</title>
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
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-primary shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-secondary hover:text-white transition">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-primary mb-8">My Profile</h1>

            <?php if (isset($success)): ?>
                <div class="bg-green-50 text-green-600 p-4 rounded-lg mb-6">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <!-- Profile Header -->
                <div class="bg-primary p-6">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <img src="<?= $user['profile_image'] ? '../uploads/profile_images/' . htmlspecialchars($user['profile_image']) : 'https://via.placeholder.com/150' ?>" 
                                 alt="Profile Picture" 
                                 class="w-24 h-24 rounded-full object-cover border-4 border-white">
                            <label for="profile_image" 
                                   class="absolute bottom-0 right-0 bg-secondary text-primary p-2 rounded-full cursor-pointer hover:bg-white transition">
                                <i class="fas fa-camera"></i>
                            </label>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-white"><?= htmlspecialchars($user['name']) ?></h2>
                            <p class="text-gray-300"><?= htmlspecialchars($user['email']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Profile Form -->
                <form method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*">

                    <!-- Personal Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-gray-700 font-medium mb-2">Full Name</label>
                            <input type="text" id="name" name="name" 
                                   value="<?= htmlspecialchars($user['name']) ?>" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                        </div>
                        <div>
                            <label for="email" class="block text-gray-700 font-medium mb-2">Email Address</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email']) ?>" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                        </div>
                    </div>

                    <!-- Bio -->
                    <div>
                        <label for="bio" class="block text-gray-700 font-medium mb-2">Bio</label>
                        <textarea id="bio" name="bio" rows="4" 
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition"
                                  placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <!-- Change Password -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-bold text-primary mb-4">Change Password</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="current_password" class="block text-gray-700 font-medium mb-2">Current Password</label>
                                <input type="password" id="current_password" name="current_password"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                            </div>
                            <div>
                                <label for="new_password" class="block text-gray-700 font-medium mb-2">New Password</label>
                                <input type="password" id="new_password" name="new_password"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="border-t pt-6">
                        <button type="submit" 
                                class="w-full bg-primary text-white py-3 rounded-lg hover:bg-secondary hover:text-primary transition duration-300">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-gray-400 mb-4">
                &copy; <?= date("Y") ?> Bonnie Computer Hub. All Rights Reserved.
            </p>
            <p class="text-secondary italic">
                "I can do all things through Christ who strengthens me." - Philippians 4:13
            </p>
        </div>
    </footer>

    <script>
        // Preview profile image before upload
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('img[alt="Profile Picture"]').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html> 