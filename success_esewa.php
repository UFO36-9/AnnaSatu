<?php
session_start();
include("db.php");

$secret_key = "8gBm/:&EnhH.1/q";

$txn                 = null;
$db_error            = null;
$amount_mismatch     = false;
$is_signature_valid  = false;
$is_payment_complete = false;
$reconstructed_payload = '';
$transaction_code    = 'N/A';
$total_amount        = '';
$transaction_uuid    = '';

if (isset($_GET['data'])) {
    // ---- Fresh callback from eSewa: decode, verify signature, update DB ----

    // PHP converts '+' in URLs to ' ' (spaces). Convert them back before decoding.
    $encoded_data = str_replace(' ', '+', $_GET['data']);
    $json_decoded = base64_decode($encoded_data);
    $response = json_decode($json_decoded, true);

    if (!$response) {
        die("Failed to decode receipt details.");
    }

    $status                      = $response['status'] ?? '';
    $total_amount                = $response['total_amount'] ?? '';
    $transaction_uuid            = $response['transaction_uuid'] ?? '';
    $product_code                = $response['product_code'] ?? '';
    $returned_signature          = $response['signature'] ?? '';
    $transaction_code            = $response['transaction_code'] ?? 'N/A';
    $returned_signed_field_names = $response['signed_field_names'] ?? '';

    function esewa_sign($payload, $key) {
        return base64_encode(hash_hmac('sha256', $payload, $key, true));
    }

    // Reconstruct the payload dynamically from eSewa's own signed_field_names -
    // this is the format confirmed to work during testing.
    $field_values = [
        'transaction_code'   => $transaction_code,
        'status'              => $status,
        'total_amount'        => $total_amount,
        'transaction_uuid'    => $transaction_uuid,
        'product_code'        => $product_code,
        'signed_field_names'  => $returned_signed_field_names,
    ];

    if ($returned_signed_field_names) {
        $parts = [];
        foreach (explode(',', $returned_signed_field_names) as $f) {
            $f = trim($f);
            $parts[] = $f . '=' . ($field_values[$f] ?? '');
        }
        $reconstructed_payload = implode(',', $parts);
    }

    $expected_signature  = $reconstructed_payload ? esewa_sign($reconstructed_payload, $secret_key) : '';
    $is_signature_valid  = $reconstructed_payload && hash_equals($expected_signature, $returned_signature);
    $is_payment_complete = (strtoupper($status) === 'COMPLETE');

    if ($is_signature_valid && $is_payment_complete) {
        // Look up the esewa_transaction row we created at checkout time
        $stmt = $conn->prepare("SELECT id, order_id, total_amount, status FROM esewa_transactions WHERE transaction_uuid = ?");
        if (!$stmt) {
            die("SQL Setup Failure looking up esewa_transactions: " . $conn->error);
        }
        $stmt->bind_param("s", $transaction_uuid);
        if ($stmt->execute()) {
            $txn = $stmt->get_result()->fetch_assoc();
        } else {
            $db_error = $conn->error;
        }
        $stmt->close();

        if ($txn) {
            $expected_amount = number_format((float) $txn['total_amount'], 2, '.', '');
            $returned_amount = number_format((float) $total_amount, 2, '.', '');

            if ($expected_amount !== $returned_amount) {
                $amount_mismatch = true;
            } else {
                if ($txn['status'] !== 'COMPLETE') {
                    $update_txn = $conn->prepare("UPDATE esewa_transactions SET status = 'COMPLETE' WHERE id = ?");
                    $update_txn->bind_param("i", $txn['id']);
                    $update_txn->execute();
                    $update_txn->close();
                    $txn['status'] = 'COMPLETE';
                }

                if ($txn['order_id']) {
                    $update_order = $conn->prepare("UPDATE orders SET payment_status = 'paid', payment_mode = 'esewa' WHERE id = ?");
                    $update_order->bind_param("i", $txn['order_id']);
                    if (!$update_order->execute()) {
                        $db_error = $conn->error;
                    }
                    $update_order->close();
                }

                // Remember this confirmed transaction for this browser session.
                // Some browsers / eSewa redirect flows re-request the success
                // URL a second time without the "data" query parameter attached.
                // When that happens (handled below), we reuse this instead of
                // showing "Invalid access."
                $_SESSION['last_esewa_txn'] = $transaction_uuid;
            }
        } elseif (!$db_error) {
            $db_error = "No matching esewa_transactions found for transaction_uuid: " . htmlspecialchars($transaction_uuid);
        }
    }

    unset($_SESSION['pending_esewa_txn'], $_SESSION['pending_esewa_order']);

} elseif (isset($_SESSION['last_esewa_txn'])) {
    // ---- Reload / duplicate request with no "data" param ----
    // This transaction was already verified and recorded above on the
    // original callback; we just re-display its saved status here rather
    // than treating the reload as an invalid request.
    $transaction_uuid = $_SESSION['last_esewa_txn'];

    $stmt = $conn->prepare("SELECT id, order_id, total_amount, status FROM esewa_transactions WHERE transaction_uuid = ?");
    if ($stmt) {
        $stmt->bind_param("s", $transaction_uuid);
        if ($stmt->execute()) {
            $txn = $stmt->get_result()->fetch_assoc();
        }
        $stmt->close();
    }

    if ($txn && $txn['status'] === 'COMPLETE') {
        $is_signature_valid  = true;
        $is_payment_complete = true;
        $total_amount        = number_format((float) $txn['total_amount'], 2, '.', '');
    } else {
        // Nothing confirmed to show - fall through to the failure view below.
        $txn = null;
    }

} else {
    // Nothing usable arrived at all - log exactly what this request looked
    // like so we can see why, instead of guessing blind next time.
    error_log("success_esewa.php invalid access - GET: " . json_encode($_GET) . " | SESSION: " . json_encode($_SESSION ?? []));
    die("Invalid access. No transaction data found. GET params received: " . htmlspecialchars(json_encode($_GET)));
}

$show_success = $is_signature_valid && $is_payment_complete && $txn && !$amount_mismatch;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
    <style>
        body { font-family: sans-serif; background-color: #f3f4f6; margin: 0; }
        .page-content { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 70px); flex-direction: column; padding: 20px; }
        .receipt { background: #fff; padding: 30px; border-radius: 10px; width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .status-success { color: #10b981; font-weight: bold; font-size: 22px; }
        .status-failed { color: #ef4444; font-weight: bold; font-size: 22px; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .details-table td { padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-home { display: inline-block; width: 100%; padding: 12px; background: #3b82f6; color: white; text-align: center; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: bold; box-sizing: border-box; }
        .debug-box { margin-top: 20px; background: #fee2e2; border: 1px solid #fca5a5; padding: 15px; border-radius: 8px; width: 100%; max-width: 500px; font-family: monospace; font-size: 11px; color: #991b1b; overflow-x: auto; box-sizing: border-box; word-break: break-all; }
    </style>
</head>
<body>

<?php include("navbar.php"); ?>

<div class="page-content">
<div class="receipt">
    <?php if ($show_success): ?>
        <div class="status-success" style="text-align: center;">Payment Successful!</div>
        <p style="text-align: center; color: #666; font-size: 13px;">Your order has been verified and confirmed.</p>

        <table class="details-table">
            <tr><td><strong>Order ID:</strong></td><td style="text-align: right;">#<?php echo htmlspecialchars($txn['order_id']); ?></td></tr>
            <tr><td><strong>Transaction Code:</strong></td><td style="text-align: right;"><?php echo htmlspecialchars($transaction_code); ?></td></tr>
            <tr><td><strong>UUID:</strong></td><td style="text-align: right;"><code><?php echo htmlspecialchars($transaction_uuid); ?></code></td></tr>
            <tr><td><strong>Total Paid:</strong></td><td style="text-align: right; color:#10b981; font-weight: bold;">NPR <?php echo htmlspecialchars($total_amount); ?></td></tr>
            <tr><td><strong>Status:</strong></td><td style="text-align: right;"><span style="background: #e6fbf4; color: #10b981; padding: 2px 8px; border-radius: 4px; font-size: 12px;">Paid</span></td></tr>
        </table>

    <?php else: ?>
        <div class="status-failed" style="text-align: center;">Verification Failed</div>
        <p style="text-align: center; color: #ef4444;">
            <?php if (!$is_signature_valid): ?>
                The callback signature is either invalid or incomplete. This transaction could be a forgery.
            <?php elseif (!$is_payment_complete): ?>
                Payment status was not COMPLETE.
            <?php elseif ($amount_mismatch): ?>
                The confirmed amount didn't match the transaction total.
            <?php elseif (!$txn): ?>
                We couldn't find a matching transaction record.
            <?php else: ?>
                Something went wrong recording your payment.
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <a href="index.php" class="btn-home">Back to Store</a>
</div>

<?php if (!$show_success): ?>
<div class="debug-box">
    <strong>[DEBUG]</strong><br>
    signature_valid: <?php echo $is_signature_valid ? 'yes' : 'no'; ?><br>
    payment_complete: <?php echo $is_payment_complete ? 'yes' : 'no'; ?><br>
    amount_mismatch: <?php echo $amount_mismatch ? 'yes' : 'no'; ?><br>
    transaction_found: <?php echo $txn ? 'yes' : 'no'; ?><br>
    <?php if ($db_error): ?>db_error: <?php echo htmlspecialchars($db_error); ?><br><?php endif; ?>
    reconstructed_payload: <?php echo htmlspecialchars($reconstructed_payload); ?><br>
    get_params: <?php echo htmlspecialchars(json_encode($_GET)); ?><br>
    <?php if (isset($response)): ?>raw_decoded_response: <?php echo htmlspecialchars(json_encode($response)); ?><br><?php endif; ?>
</div>
<?php endif; ?>
</div>

</body>
</html>