<?php
// Usage: generate_pdf_certificate($user_id, $course_id, $student_name, $course_name, $cert_id, $date)
// Requires mPDF (installed via Composer)
require_once __DIR__ . '/../../vendor/autoload.php';

function generate_pdf_certificate($user_id, $course_id, $student_name, $course_name, $cert_id, $date) {
    $mpdf = new \Mpdf\Mpdf(['format' => 'A4-L']);
    $verify_url = 'http://localhost/BonnieComputerHub/LMS/pages/verify_certificate.php?code=' . urlencode($cert_id);
    $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($verify_url);
    $logo_path = __DIR__ . '/../images/BCH.jpg';
    $sig_path = __DIR__ . '/../images/Bonnie.jpg';
    $logo_html = file_exists($logo_path) ? '<img src="' . $logo_path . '" style="height:60px;">' : '';
    $sig_html = file_exists($sig_path) ? '<img src="' . $sig_path . '" style="height:60px;">' : '';
    $html = '<div style="text-align:center; font-family:Inter,sans-serif; border:8px solid #1E40AF; padding:30px;">
        ' . $logo_html . '<br><br>
        <span style="font-size:32px; color:#1E40AF; font-weight:bold;">Certificate of Completion</span><br><br>
        <span style="font-size:20px;">Awarded to</span><br>
        <span style="font-size:28px; font-weight:bold; color:#FFD700;">' . htmlspecialchars($student_name) . '</span><br><br>
        <span style="font-size:18px;">for successfully completing the course</span><br>
        <span style="font-size:24px; color:#FFD700;">' . htmlspecialchars($course_name) . '</span><br><br>
        <span style="font-size:16px; color:#222;">Date: ' . htmlspecialchars($date) . '</span><br>
        <span style="font-size:16px; color:#888;">Certificate ID: ' . htmlspecialchars($cert_id) . '</span><br><br>
        <img src="' . $qr_url . '" alt="QR Code"><br>
        <span style="font-size:14px;">Verify at: ' . $verify_url . '</span><br><br>
        <div style="text-align:right; margin-top:40px;">' . $sig_html . '</div>
        <span style="font-size:18px; font-style:italic; color:#888;">Bonnie Computer Hub</span>
    </div>';
    $mpdf->WriteHTML($html);
    $pdf_path = __DIR__ . '/../uploads/certificates/BCH_Certificate_' . $cert_id . '.pdf';
    if (!is_dir(dirname($pdf_path))) {
        mkdir(dirname($pdf_path), 0777, true);
    }
    $mpdf->Output($pdf_path, \Mpdf\Output\Destination::FILE);
    return $pdf_path;
}
