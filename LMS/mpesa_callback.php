<?php
// mpesa_callback.php: Receives payment confirmation from Safaricom (Daraja API)
// This should be set as your callback URL in the Safaricom developer portal (using ngrok for testing)

require_once 'includes/db_connect.php';

// Get raw POST data
$data = file_get_contents('php://input');
file_put_contents('mpesa_callback_log.txt', $data . PHP_EOL, FILE_APPEND); // For debugging

$payload = json_decode($data, true);

if (!$payload || !isset($payload['Body']['stkCallback'])) {
    http_response_code(400);
    exit('Invalid callback payload');
}

$callback = $payload['Body']['stkCallback'];
$payment_id = null;
$mpesa_receipt = null;
$status = 'failed';
$error_message = null;

if (isset($callback['ResultCode']) && $callback['ResultCode'] == 0) {
    // Success
    $status = 'success';
    $mpesa_receipt = $callback['CallbackMetadata']['Item'][1]['Value'] ?? null; // Mpesa receipt number
    $phone = $callback['CallbackMetadata']['Item'][4]['Value'] ?? null;
    $amount = $callback['CallbackMetadata']['Item'][0]['Value'] ?? null;
    // Find payment by phone and amount, most recent pending
    $stmt = $pdo->prepare("SELECT id FROM payments WHERE status='pending' AND transaction_id IS NULL AND amount=? AND phone=? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$amount, $phone]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $payment_id = $row['id'];
        // Store payment_id in a file or cache for redirect after dashboard load
        file_put_contents('last_payment_success.txt', $payment_id);
    }
} else {
    $error_message = $callback['ResultDesc'] ?? 'Unknown error';
    // Optionally, find payment by phone/amount and mark as failed
}

if ($payment_id) {
    $stmt = $pdo->prepare("UPDATE payments SET status=?, transaction_id=?, error_message=?, updated_at=NOW() WHERE id=?");
    $stmt->execute([$status, $mpesa_receipt, $error_message, $payment_id]);
}

http_response_code(200);
echo json_encode(['result' => 'received']);
