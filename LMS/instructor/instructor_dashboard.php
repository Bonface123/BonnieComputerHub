<?php
session_start();
require_once '../includes/db_connect.php';

// Check if the user is logged in and has instructor role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    header('Location: ../login.php');
    exit;
}

// Fetch the instructor's details from the database
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If the user doesn't exist, redirect to login
if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Fetch notifications for the instructor
$notifications = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifications->execute([$user_id]);
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <title>Instructor Dashboard</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fb;
            color: #333;
            line-height: 1.6;
        }

        /* Header */
        header {
            background-color: #002147;
            color: white;
            padding: 15px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 2.5rem;
        }

        /* Main Content */
        main {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        /* Breadcrumb */
        .breadcrumb {
            font-size: 1rem;
            color: #007bff;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            text-decoration: none;
            color: #007bff;
        }

        .breadcrumb span {
            color: #333;
        }

        /* Dashboard Title */
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #002147;
        }

        /* Dashboard Navigation */
        .dashboard-nav {
            margin-top: 20px;
            margin-bottom: 30px;
        }

        .dashboard-nav ul {
            list-style-type: none;
            padding: 0;
            display: flex;
            gap: 15px;
        }

        .dashboard-nav li {
            flex: 1;
        }

        .dashboard-nav a {
            display: block;
            padding: 12px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: #002147;
            text-align: center;
            border-radius: 6px;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .dashboard-nav a:hover {
            background-color: #007bff;
            color: white;
        }

        /* Section */
        section {
            margin-bottom: 40px;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #002147;
        }

        /* Notifications */
        .notifications ul {
            list-style-type: none;
            padding: 0;
        }

        .notifications li {
            padding: 12px;
            border: 1px solid #ddd;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .notifications li span {
            font-size: 0.9rem;
            color: #888;
        }

        .notifications p {
            text-align: center;
            color: #888;
        }

        /* Footer */
        footer {
            background-color: #333;
            color: white;
            padding: 20px 0;
            text-align: center;
        }

        footer a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }

        /* Media Queries for responsiveness */
        @media (max-width: 768px) {
            .breadcrumb {
                font-size: 0.9rem;
            }

            .dashboard-nav ul {
                flex-direction: column;
                gap: 10px;
            }

            .dashboard-nav a {
                padding: 10px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <main>
        <!-- Breadcrumb Navigation -->
        <div class="breadcrumb">
            <a href="../index.php">Home</a> &gt; 
            <span>Instructor Dashboard</span>
        </div>

        <h1>Instructor Dashboard</h1>

        <!-- Navigation Links for Dashboard Sections -->
        <nav class="dashboard-nav">
            <ul>
                <li><a href="manage_courses.php">Manage Courses</a></li>
                <li><a href="manage_assignments.php">Manage Assignments</a></li>
                <li><a href="view_progress.php">Track Student Progress</a></li>
            </ul>
        </nav>

        <section>
            <h2>Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
            <p>Use the links above to manage your courses, assignments, and track student progress.</p>
        </section>

        <section class="notifications">
            <h2>Notifications</h2>
            <?php if (count($notifications) > 0): ?>
                <ul>
                    <?php foreach ($notifications as $notification): ?>
                        <li>
                            <?= htmlspecialchars($notification['message']) ?> 
                            <span>(<?= htmlspecialchars($notification['created_at']) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No new notifications.</p>
            <?php endif; ?>
        </section>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
