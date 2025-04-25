<?php
// email_helper.php - helper for sending emails using PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendPaymentReceipt($to, $studentName, $courseName, $amount, $status, $mpesaRef, $date, $pdfPath = null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // TODO: Set your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your@email.com'; // TODO: Set your SMTP username
        $mail->Password = 'yourpassword'; // TODO: Set your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('no-reply@bonniecomputerhub.com', 'Bonnie Computer Hub');
        $mail->addAddress($to, $studentName);
        $mail->isHTML(true);
        $mail->Subject = 'Your Payment Receipt - Bonnie Computer Hub';
        $mail->Body = "<h2>Payment Receipt</h2>
            <b>Student:</b> $studentName<br>
            <b>Course:</b> $courseName<br>
            <b>Amount:</b> KES $amount<br>
            <b>Status:</b> $status<br>
            <b>MPESA Ref:</b> $mpesaRef<br>
            <b>Date:</b> $date<br>
            <br>Thank you for your payment!";
        if ($pdfPath && file_exists($pdfPath)) {
            $mail->addAttachment($pdfPath, 'Payment_Receipt.pdf');
        }
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Email error: ' . $mail->ErrorInfo);
        return false;
    }
}
