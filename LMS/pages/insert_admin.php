// pages/register_process.php
<?php
include '../includes/db_connect.php';

// Admin details
$name = 'Bonface';
$email = 'ondusobonface9@gmail.com';
$password = password_hash('THE first321', PASSWORD_DEFAULT); // Set your desired password
$role = 'admin';

try {
    // Insert admin into the users table
    $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $email, $password, $role]);

    require_once '../includes/send_mail.php';
    $subject = "Welcome to Bonnie Computer Hub (Admin)!";
    $body = "Hello $name,\n\nYour admin account has been created for Bonnie Computer Hub LMS. You can now log in and manage the platform.\n\nBest regards,\nBonnie Computer Hub Team";
    $result = bch_send_mail($email, $name, $subject, $body);
    if ($result['success']) {
        echo "Admin user added successfully and welcome email sent!";
    } else {
        echo "Admin user added, but failed to send welcome email: " . htmlspecialchars($result['error']);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
