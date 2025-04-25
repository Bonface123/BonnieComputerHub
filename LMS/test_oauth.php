<?php
$consumer_key = 'f3fBAAviPGydq8sD1lhO3SBYVGSsBoTEK7MTP43hdpbfZeZt';
$consumer_secret = 'cfoRONhpu4co3jHfhSxBrZjK4ijeZaNnW3nAD6jjU7PpFaSjfijJdotECuTbNVBw';

$credentials = base64_encode($consumer_key . ':' . $consumer_secret);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic ' . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $httpcode\n";
if ($curl_error) {
    echo "cURL Error: $curl_error\n";
}
echo "Response: $response\n";
?>
