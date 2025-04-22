<?php
// send_mail.php -- PHPMailer wrapper for BCH
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/env_loader.php';

/**
 * Sends an email using PHPMailer and SMTP
 * @param string $toEmail
 * @param string $toName
 * @param string $subject
 * @param string $body (plain text or HTML)
 * @param string|null $altBody (optional plain text alternative)
 * @param array $attachments (optional array of file paths)
 * @param array $smtpConfig (optional override of SMTP config)
 * @return array ['success' => bool, 'error' => string]
 */
function bch_send_mail($toEmail, $toName, $subject, $body, $altBody = '', $attachments = [], $smtpConfig = []) {
    $mail = new PHPMailer(true);
    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'] ?? $_ENV['SMTP_USER'] ?? 'bonniecomputerhub24@gmail.com';
        $mail->Password = $smtpConfig['password'] ?? $_ENV['SMTP_PASS'] ?? '';
        $mail->SMTPSecure = $smtpConfig['secure'] ?? PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $smtpConfig['port'] ?? 587;
        $mail->setFrom($smtpConfig['from'] ?? 'bonniecomputerhub24@gmail.com', 'Bonnie Computer Hub');

        $mail->addAddress($toEmail, $toName);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(false);
        if ($altBody) {
            $mail->AltBody = $altBody;
        }
        // Attachments
        if (!empty($attachments)) {
            foreach ($attachments as $file) {
                $mail->addAttachment($file);
            }
        }
        $mail->send();
        return ['success' => true, 'error' => ''];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}
