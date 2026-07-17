<?php
session_start();
include("db.php");
include("navbar.php");

// Mark the pending transaction as failed so it doesn't sit as PENDING forever.
// eSewa's failure_url usually carries no data payload at all, so we rely on
// the session value checkout_esewa.php stored right before redirecting.
if (isset($_SESSION['pending_esewa_txn'])) {
    $transaction_uuid = $_SESSION['pending_esewa_txn'];

    $stmt = $conn->prepare("UPDATE esewa_transactions SET status = 'FAILED' WHERE transaction_uuid = ? AND status = 'PENDING'");
    $stmt->bind_param("s", $transaction_uuid);
    $stmt->execute();
    $stmt->close();

    unset($_SESSION['pending_esewa_txn'], $_SESSION['pending_esewa_order']);
}

// If eSewa did send a data payload (some failure/cancel paths do), log it for debugging
if (isset($_GET['data'])) {
    $encoded_data = str_replace(' ', '+', $_GET['data']);
    $decoded = base64_decode($encoded_data);
    error_log("eSewa failure callback data: " . $decoded);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Cancelled</title>
    <style>
        body { font-family: sans-serif; background-color: #fef2f2; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .failure-box { background: white; padding: 40px; border-radius: 10px; width: 350px; text-align: center; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.1); }
        .icon { font-size: 50px; color: #ef4444; margin-bottom: 15px; }
        .btn-retry { display: inline-block; width: 100%; padding: 12px; background: #ef4444; color: white; text-align: center; text-decoration: none; border-radius: 6px; margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>

<div class="failure-box">
    <div class="icon">⚠</div>
    <h2>Payment Cancelled</h2>
    <p style="color: #666; font-size: 14px; line-height: 1.5;">
        The transaction was cancelled or could not be completed. No money was deducted.
    </p>
    <a href="order.php" class="btn-retry">Try Again</a>
</div>

</body>
</html>