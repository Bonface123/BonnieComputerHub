<?php
session_start();
require_once '../includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Step 1: Handle Password Reset Request
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $success = '<div style="display:flex;align-items:center;justify-content:center;flex-direction:column;gap:16px;">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" style="margin-bottom:4px;"><circle cx="12" cy="12" r="12" fill="#E0F2FE"/><path d="M8.5 12.5l2 2 5-5" stroke="#1E40AF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        <div style="font-size:1.08rem;color:#1E40AF;font-weight:600;">Check your email</div>
        <div style="color:#374151;font-size:1rem;max-width:320px;line-height:1.6;">
            If that email address is registered, we&#39;ve sent a password reset link.<br>
            Please check your inbox and spam folder.<br><br>
            Didn&#39;t receive anything? <a href="#" id="bch-try-again" style="color:#1E40AF;font-weight:500;text-decoration:underline;">Try again</a><br>
            Still need help? <a href="mailto:support@bonniecomputerhub.com" style="color:#1E40AF;font-weight:500;text-decoration:underline;">Contact support</a>.
        </div>
        <script>document.addEventListener("DOMContentLoaded",function(){var t=document.getElementById("bch-try-again");if(t){t.addEventListener("click",function(e){e.preventDefault();window.location.href=window.location.pathname;});}});</script>
    </div>';
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        // Generate a secure token
        $token = bin2hex(random_bytes(50));
        $expires_at = date("Y-m-d H:i:s", strtotime('+1 hour'));
        // Insert token into password_resets table
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at, used) VALUES (?, ?, ?, 0)");
        $stmt->execute([$user['id'], $token, $expires_at]);
        // In production, send the reset link to the user's email address.
        $reset_link = "http://localhost/bonniecomputerhub/LMS/pages/reset_password.php?token=$token";
        // --- BCH SEND MAIL HELPER ---
        require_once __DIR__ . '/../includes/send_mail.php';
        $to = $email;
        $toName = '';
        $subject = 'Reset Your Password | BonnieComputerHub LMS';
        $logo_url = 'https://bonniecomputerhub.com/assets/bch-logo.png'; // Update with your real logo URL or local path
        $primary_color = '#1E40AF';
        $button_color = '#FFD700';
        $message_html = <<<HTML
        <div style="background:#f8f9fa;padding:32px 0;font-family:Inter,Arial,sans-serif;">
          <div style="max-width:480px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(30,64,175,0.08);padding:32px 24px 24px 24px;">
            <div style="text-align:center;margin-bottom:24px;">
              <img src="$logo_url" alt="BonnieComputerHub Logo" style="max-width:120px;max-height:60px;margin-bottom:12px;">
              <h2 style="color:$primary_color;font-size:1.5rem;margin:0 0 8px 0;">Password Reset Request</h2>
            </div>
            <p style="color:#222;font-size:1rem;margin-bottom:24px;">We received a request to reset your password for your BonnieComputerHub LMS account.</p>
            <div style="text-align:center;margin-bottom:24px;">
              <a href="$reset_link" style="background:$button_color;color:#002147;font-weight:600;padding:14px 32px;border-radius:8px;text-decoration:none;display:inline-block;font-size:1.1rem;box-shadow:0 2px 8px rgba(30,64,175,0.10);transition:background 0.2s;">Reset Password</a>
            </div>
            <p style="color:#444;font-size:0.98rem;">If you did not request this, you can safely ignore this email.<br><br>For security, this link will expire in 1 hour.</p>
            <hr style="margin:32px 0 16px 0;border:0;border-top:1px solid #e9ecef;">
            <div style="text-align:center;color:#6c757d;font-size:0.92rem;">&copy; 2025 BonnieComputerHub. All rights reserved.</div>
          </div>
        </div>
HTML;
        $message_text = "We received a request to reset your password for your BonnieComputerHub LMS account.\n\nReset your password using the link below (or copy and paste it into your browser):\n$reset_link\n\nIf you did not request this, you can ignore this email. For security, this link will expire in 1 hour.\n\nBonnieComputerHub Team";
        $mail_result = bch_send_mail($to, $toName, $subject, $message_html, $message_text);
        // Optionally, check $mail_result['success'] and log or show a generic error if false.
        // --- END BCH SEND MAIL HELPER ---
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'], $_POST['confirm_password'], $_POST['token'])) {
    // Step 2: Handle New Password Submission
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $token = $_POST['token'];
    // Validate passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Find the token in the database
        $stmt = $pdo->prepare("SELECT user_id, expires_at, used FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();
        if ($reset && strtotime($reset['expires_at']) > time() && !$reset['used']) {
            // Token is valid and unused, hash new password and update user record
            $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['user_id']]);
            // Mark token as used
            $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
            $stmt->execute([$token]);
            $success = "Password successfully reset. <a href='login.php'>Login here</a>";
        } elseif ($reset && $reset['used']) {
            $error = "This reset link has already been used.";
        } else {
            $error = "Invalid or expired token.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | BonnieComputerHub LMS</title>
    <link rel="stylesheet" href="/bonniecomputerhub/assets/design-system.css">
    <link rel="stylesheet" href="/bonniecomputerhub/assets/components.css">
    <link rel="stylesheet" href="/bonniecomputerhub/assets/utilities.css">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', Arial, sans-serif; background: #f4f6fa; }
        .bch-reset-card {
            max-width: 420px;
            margin: 48px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(30,64,175,0.09);
            padding: 40px 32px 32px 32px;
            border: 1px solid #e5e7eb;
        }
        .bch-logo {
            display: block;
            margin: 0 auto 16px auto;
            max-width: 120px;
            max-height: 60px;
        }
        .bch-reset-title {
            color: #1E40AF;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-align: center;
        }
        .bch-reset-desc {
            color: #374151;
            font-size: 1.02rem;
            margin-bottom: 28px;
            text-align: center;
        }
        .bch-reset-form label {
            font-weight: 500;
            color: #1E40AF;
        }
        .bch-reset-form input[type="email"],
        .bch-reset-form input[type="password"] {
            margin-bottom: 18px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            transition: border-color 0.2s;
        }
        .bch-reset-form input:focus {
            border-color: #1E40AF;
            outline: none;
            box-shadow: 0 0 0 2px #1E40AF22;
        }
        .bch-btn-primary {
            background: #1E40AF;
            color: #fff;
            font-weight: 600;
            border-radius: 8px;
            padding: 12px 0;
            font-size: 1.07rem;
            width: 100%;
            border: none;
            box-shadow: 0 2px 8px rgba(30,64,175,0.10);
            transition: background 0.2s;
        }
        .bch-btn-primary:hover, .bch-btn-primary:focus {
            background: #16308d;
        }
        .bch-reset-feedback {
            margin-bottom: 18px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 1rem;
            text-align: center;
        }
        .bch-reset-feedback-success {
            background: #e0f2fe;
            color: #0284c7;
            border: 1px solid #bae6fd;
        }
        .bch-reset-feedback-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        @media (max-width: 600px) {
            .bch-reset-card { padding: 24px 8px; }
        }
    </style>
    <link rel="stylesheet" href="/bonniecomputerhub/assets/components.css">
    <link rel="stylesheet" href="/bonniecomputerhub/assets/utilities.css">
    <link href="https://fonts.googleapis.com/css?family=Inter:400,500,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: var(--bch-bg-secondary);
            min-height: 100vh;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        main {
            max-width: 400px;
            margin: var(--bch-space-12) auto;
            background: var(--bch-bg-primary);
            border-radius: var(--bch-radius-lg);
            box-shadow: var(--bch-shadow-lg);
            padding: var(--bch-space-8) var(--bch-space-6);
            animation: bchFadeIn 0.5s;
        }
        h2 {
            color: var(--bch-blue);
            font-size: 2rem;
            margin-bottom: var(--bch-space-4);
            font-weight: 700;
            letter-spacing: 0.01em;
        }
        label {
            display: block;
            margin-bottom: var(--bch-space-2);
            color: var(--bch-gray-800);
            font-weight: 500;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: var(--bch-space-3);
            border: 1px solid var(--bch-gray-300);
            border-radius: var(--bch-radius-md);
            font-size: 1rem;
            margin-bottom: var(--bch-space-4);
            background: var(--bch-bg-secondary);
            transition: border-color var(--bch-transition-normal);
        }
        input[type="email"]:focus, input[type="password"]:focus {
            border-color: var(--bch-blue);
            outline: none;
            box-shadow: 0 0 0 2px var(--bch-blue-light);
        }
        button[type="submit"] {
            width: 100%;
            background: var(--bch-blue);
            color: var(--bch-white);
            padding: var(--bch-space-3);
            border: none;
            border-radius: var(--bch-radius-md);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--bch-transition-fast), box-shadow var(--bch-transition-fast);
            box-shadow: var(--bch-shadow-sm);
        }
        button[type="submit"]:hover, button[type="submit"]:focus {
            background: var(--bch-blue-dark);
            box-shadow: var(--bch-shadow-md);
        }
        .error {
            background: var(--bch-error);
            color: var(--bch-white);
            padding: var(--bch-space-3);
            border-radius: var(--bch-radius-md);
            margin-bottom: var(--bch-space-4);
            font-weight: 500;
            box-shadow: var(--bch-shadow-sm);
        }
        .success {
            background: var(--bch-success);
            color: var(--bch-white);
            padding: var(--bch-space-3);
            border-radius: var(--bch-radius-md);
            margin-bottom: var(--bch-space-4);
            font-weight: 500;
            box-shadow: var(--bch-shadow-sm);
        }
        a {
            color: var(--bch-blue);
            text-decoration: underline;
            font-weight: 500;
            transition: color var(--bch-transition-fast);
        }
        a:hover, a:focus {
            color: var(--bch-gold);
        }
        @media (max-width: 500px) {
            main {
                padding: var(--bch-space-6) var(--bch-space-2);
                margin: var(--bch-space-4);
            }
        }
        @keyframes bchFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
/* General styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body, html {
    font-family: 'Century Gothic', 'AppleGothic', sans-serif;
    background-color: #f4f4f9;
    color: #333;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

/* Main container styling */
main {
    max-width: 400px;
    width: 100%;
    background-color: #ffffff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* Headings */
h2 {
    color: #333;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Labels and inputs */
label {
    display: block;
    font-size: 16px;
    color: #666;
    margin-bottom: 8px;
    text-align: left;
}

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 20px;
    outline: none;
    transition: border-color 0.3s;
}

input[type="email"]:focus,
input[type="password"]:focus {
    border-color: #007bff;
}

/* Button styling */
button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    font-weight: bold;
    color: #ffffff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
}

button:hover {
    background-color: #0056b3;
}

/* Error message styling */
.error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    text-align: left;
    font-size: 14px;
    border: 1px solid #f5c6cb;
}

/* Link styling */
a {
    color: #007bff;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <main role="main" aria-labelledby="reset-password-heading">
        <?php if (isset($error)): ?>
            <div class="bch-reset-feedback bch-reset-feedback-error" role="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="bch-reset-feedback bch-reset-feedback-success" role="status"><?= $success ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['token']) && !isset($success)): ?>
            <!-- Form to Set New Password -->
            <form class="bch-reset-form" action="" method="POST" autocomplete="off" aria-label="Set new password form">
                <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
                <label for="new_password">New Password</label>
                <input type="password" name="new_password" id="new_password" required aria-required="true" aria-label="New password" minlength="6">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required aria-required="true" aria-label="Confirm new password" minlength="6">
                <button type="submit" class="bch-btn bch-btn-primary">Reset Password</button>
            </form>
        <?php elseif (!isset($_GET['token']) && !isset($success)): ?>
            <!-- Form to Request Password Reset -->
            <form class="bch-reset-form" method="post" autocomplete="off" aria-label="Password Reset Request">
                <label for="email">Email address</label>
                <input type="email" name="email" id="email" required aria-required="true" aria-label="Email address" autofocus />
                <button type="submit" class="bch-btn bch-btn-primary">Send Reset Link</button>
            </form>
        <?php endif; ?>
        </div>
    </main>
</body>
</html>
