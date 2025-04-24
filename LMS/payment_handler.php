<?php
// payment_handler.php: Centralized MPESA payment logic (simulated for now)
require_once 'includes/db_connect.php';
session_start();

// --- Helper Functions ---
function create_payment($user_id, $course_id, $amount, $method = 'MPESA') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->execute([$user_id, $course_id, $amount, $method]);
    return $pdo->lastInsertId();
}

function update_payment_status($payment_id, $status, $transaction_id = null, $error_message = null) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE payments SET status=?, transaction_id=?, error_message=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$status, $transaction_id, $error_message, $payment_id]);
}

// --- Main Payment Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mpesa_pay'])) {
    // CSRF token check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    if (!isset($_SESSION['user_id'])) {
        die('User not authenticated');
    }

    $user_id = $_SESSION['user_id'];
    $course_id = intval($_POST['course_id']);
    $amount = floatval($_POST['amount']);
    $phone = isset($_POST['mpesa_phone']) ? trim($_POST['mpesa_phone']) : null;
    if (!$phone || !preg_match('/^0[7-9][0-9]{8}$/', $phone)) {
        die('Invalid phone number format.');
    }

    // Insert payment as pending (store phone)
    $stmt = $pdo->prepare("INSERT INTO payments (user_id, course_id, amount, method, status, phone, created_at) VALUES (?, ?, ?, ?, 'pending', ?, NOW())");
    $stmt->execute([$user_id, $course_id, $amount, 'MPESA', $phone]);
    $payment_id = $pdo->lastInsertId();

    // === Safaricom Daraja STK Push ===
    $shortcode = '174379'; // Safaricom sandbox paybill
    $passkey = 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'; // App-specific sandbox passkey
    $consumer_key = 'f3fBAAviPGydq8sD1lhO3SBYVGSsBoTEK7MTP43hdpbfZeZt';
    $consumer_secret = 'cfoRONhpu4co3jHfhSxBrZjK4ijeZaNnW3nAD6jjU7PpFaSjfijJdotECuTbNVBw';
    $callback_url = 'https://60aa-197-232-123-164.ngrok-free.app/BonnieComputerHub/LMS/mpesa_callback.php';
    $amount_kes = (int)$amount;
    $timestamp = date('YmdHis');
    $password = base64_encode($shortcode.$passkey.$timestamp);
    $phone_international = '254' . substr($phone, 1); // Convert 07... to 2547...

    // Get OAuth token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Basic '.base64_encode($consumer_key.':'.$consumer_secret)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    curl_close($ch);
    $token = $result['access_token'] ?? null;
    if (!$token) {
        update_payment_status($payment_id, 'failed', null, 'Failed to get MPESA token');
        die('Failed to get MPESA token');
    }

    // Initiate STK Push
    $stk_data = [
        'BusinessShortCode' => $shortcode,
        'Password' => $password,
        'Timestamp' => $timestamp,
        'TransactionType' => 'CustomerPayBillOnline',
        'Amount' => $amount_kes,
        'PartyA' => $phone_international,
        'PartyB' => $shortcode,
        'PhoneNumber' => $phone_international,
        'CallBackURL' => $callback_url,
        'AccountReference' => 'testaccount',
        'TransactionDesc' => 'Course Payment',
    ];
    $ch = curl_init('https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($stk_data));
    $stk_response = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $request_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
    $curl_info = print_r(curl_getinfo($ch), true);
    $stk_result = json_decode($stk_response, true);
    curl_close($ch);

    // Log extra debug info
    file_put_contents('mpesa_stkpush_log.txt', date('c').
        "\nShortcode: $shortcode\nPasskey: $passkey\nTimestamp: $timestamp\nPassword: $password\n".
        "Request: ".json_encode($stk_data).
        "\nHTTP Status: $http_status\nResponse: $stk_response\nCurlError: $curl_error\nCurlInfo: $curl_info\n\n",
        FILE_APPEND);

    // Check if STK Push was accepted
    if (isset($stk_result['ResponseCode']) && $stk_result['ResponseCode'] == '0') {
        // Show pending, wait for callback
        header('Location: student/dashboard.php?payment=pending');
        exit;
    } else {
        $error_msg = 'STK Push failed.';
        if ($curl_error) {
            $error_msg .= ' Curl error: ' . $curl_error;
        } elseif (isset($stk_result['errorMessage'])) {
            $error_msg .= ' API error: ' . $stk_result['errorMessage'];
        } elseif (isset($stk_result['ResponseDescription'])) {
            $error_msg .= ' API error: ' . $stk_result['ResponseDescription'];
        } else {
            $error_msg .= ' Unknown error.';
        }
        update_payment_status($payment_id, 'failed', null, $error_msg);
        die('MPESA STK Push failed. '.htmlspecialchars($error_msg));
    }
}
?>