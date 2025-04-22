<?php
// Usage: mail_certificate($user_id, $course_id, $student_email, $student_name, $course_name, $cert_id)
function mail_certificate($user_id, $course_id, $student_email, $student_name, $course_name, $cert_id) {
    // Generate certificate as PNG in memory
    $width = 1200;
    $height = 850;
    $im = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($im, 255, 255, 255);
    $primary = imagecolorallocate($im, 0, 33, 71);
    $accent = imagecolorallocate($im, 255, 215, 0);
    $gray = imagecolorallocate($im, 100, 100, 100);
    imagefilledrectangle($im, 0, 0, $width, $height, $bg);
    imagesetthickness($im, 10);
    imagerectangle($im, 20, 20, $width-20, $height-20, $primary);
    // Add BCH logo at top center
    $logo_path = __DIR__ . '/../images/BCH.jpg';
    if (file_exists($logo_path)) {
        $logo = @imagecreatefromjpeg($logo_path);
        if ($logo) {
            $logo_w = imagesx($logo);
            $logo_h = imagesy($logo);
            $target_w = 180;
            $target_h = intval($logo_h * ($target_w / $logo_w));
            imagecopyresampled($im, $logo, intval(($width-$target_w)/2), 40, 0, 0, $target_w, $target_h, $logo_w, $logo_h);
            imagedestroy($logo);
        }
    }

    $font = __DIR__ . '/../assets/fonts/Inter-Bold.ttf';
    $font_regular = __DIR__ . '/../assets/fonts/Inter-Regular.ttf';
    $font_size = 48;
    $font_size_small = 28;
    $font_size_name = 56;
    $date = date('F j, Y');
    imagettftext($im, $font_size, 0, 340, 180, $primary, $font, 'Certificate of Completion');
    imagettftext($im, $font_size_small, 0, 340, 240, $gray, $font_regular, 'This certifies that');
    imagettftext($im, $font_size_name, 0, 340, 330, $accent, $font, $student_name);
    imagettftext($im, $font_size_small, 0, 340, 400, $primary, $font_regular, 'has successfully completed the course:');
    imagettftext($im, $font_size_small, 0, 340, 450, $primary, $font, $course_name);
    imagettftext($im, 22, 0, 340, 520, $gray, $font_regular, 'Date: ' . $date);
    imagettftext($im, 22, 0, 340, 560, $gray, $font_regular, 'Certificate ID: ' . $cert_id);
    // Add QR code for verification
    $verify_url = 'http://localhost/BonnieComputerHub/LMS/pages/verify_certificate.php?code=' . urlencode($cert_id);
    $qr_api = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($verify_url);
    $qr_img = @imagecreatefrompng($qr_api);
    if ($qr_img) {
        imagecopy($im, $qr_img, 80, 600, 0, 0, imagesx($qr_img), imagesy($qr_img));
        imagedestroy($qr_img);
        imagettftext($im, 18, 0, 80, 740, $gray, $font_regular, 'Verify:');
        imagettftext($im, 16, 0, 80, 765, $primary, $font_regular, $verify_url);
    } else {
        imagettftext($im, 18, 0, 80, 740, $gray, $font_regular, 'Verify at:');
        imagettftext($im, 16, 0, 80, 765, $primary, $font_regular, $verify_url);
    }
    imagettftext($im, 24, 0, 340, 650, $primary, $font_regular, 'Bonnie Computer Hub');
    // Add signature at bottom right
    $sig_path = __DIR__ . '/../images/Bonnie.jpg';
    if (file_exists($sig_path)) {
        $sig = @imagecreatefromjpeg($sig_path);
        if ($sig) {
            $sig_w = imagesx($sig);
            $sig_h = imagesy($sig);
            $target_sig_w = 180;
            $target_sig_h = intval($sig_h * ($target_sig_w / $sig_w));
            imagecopyresampled($im, $sig, $width-80-$target_sig_w, $height-80-$target_sig_h, 0, 0, $target_sig_w, $target_sig_h, $sig_w, $sig_h);
            imagedestroy($sig);
        }
    }
    ob_start();
    imagepng($im);
    $image_data = ob_get_clean();
    imagedestroy($im);
    $filename = 'BCH_Certificate_' . $cert_id . '.png';
    // Generate PDF certificate and attach if possible
    $pdf_data = null;
    $pdf_filename = 'BCH_Certificate_' . $cert_id . '.pdf';
    $pdf_path = null;
    $date = date('F j, Y');
    require_once __DIR__ . '/generate_pdf_certificate.php';
    if (function_exists('generate_pdf_certificate')) {
        $pdf_path = generate_pdf_certificate($user_id, $course_id, $student_name, $course_name, $cert_id, $date);
        if ($pdf_path && file_exists($pdf_path)) {
            $pdf_data = file_get_contents($pdf_path);
        }
    }
    $boundary = md5(time());
    $subject = "Your BCH Course Completion Certificate";
    $headers = "From: Bonnie Computer Hub <no-reply@bonniecomputerhub.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    $body = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= "Dear $student_name,\n\nCongratulations on completing the $course_name course at Bonnie Computer Hub!\n\nAttached are your official certificate files (PNG and PDF).\n\nYou or any third party can verify the authenticity of this certificate at:\n$verify_url\n\nKeep learning and growing!\n\nBest wishes,\nBonnie Computer Hub Team\n\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: image/png; name=\"$filename\"\r\n";
    $body .= "Content-Transfer-Encoding: base64\r\n";
    $body .= "Content-Disposition: attachment; filename=\"$filename\"\r\n\r\n";
    $body .= chunk_split(base64_encode($image_data));
    if ($pdf_data) {
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: application/pdf; name=\"$pdf_filename\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "Content-Disposition: attachment; filename=\"$pdf_filename\"\r\n\r\n";
        $body .= chunk_split(base64_encode($pdf_data));
    }
    $body .= "--$boundary--";
    mail($student_email, $subject, $body, $headers);
}
