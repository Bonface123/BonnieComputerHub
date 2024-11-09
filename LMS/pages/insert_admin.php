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

    echo "Admin user added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
