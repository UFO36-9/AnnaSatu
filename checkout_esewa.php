<?php
session_start();
include("db.php");

// Must be logged in, same as order.php requires to place the order
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];

if (!isset($_GET['order_id']) || !ctype_digit((string) $_GET['order_id'])) {
    die("Invalid order.");
}
$order_id = (int) $_GET['order_id'];

// Fetch the order (including the customer_name entered at checkout) and
// confirm it actually belongs to this logged-in user.
$stmt = $conn->prepare("SELECT id, customer_name, total_amount, payment_status FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    die("Order not found.");
}

if ($order['payment_status'] === 'paid') {
    die("This order has already been paid for.");
}

// Use the name the customer typed in at checkout, rather than session data.
$customer_name = $order['customer_name'];

// Sandbox configuration
$epay_url = "https://rc-epay.esewa.com.np/api/epay/main/v2/form";
$merchant_id = "EPAYTEST";
$secret_key = "8gBm/:&EnhH.1/q";

// orders.total_amount is decimal(10,2); format to 2dp string for eSewa
$formatted_amount = number_format((float) $order['total_amount'], 2, '.', '');
$tax_amount = "0.00";
$service_charge = "0.00";
$delivery_charge = "0.00";
// No extra charges in this app, so total_amount == amount
$total_amount = $formatted_amount;

// Unique transaction UUID tied to this order
$transaction_uuid = "ORD{$order_id}-" . time() . "-" . rand(1000, 9999);

// Log the pending transaction attempt
$insert = $conn->prepare("INSERT INTO esewa_transactions (order_id, transaction_uuid, customer_name, amount, tax_amount, service_charge, delivery_charge, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'PENDING')");
if (!$insert) {
    die("SQL Setup Failure on esewa_transactions insert: " . $conn->error);
}
$insert->bind_param(
    "issddddd",
    $order_id,
    $transaction_uuid,
    $customer_name,
    $formatted_amount,
    $tax_amount,
    $service_charge,
    $delivery_charge,
    $total_amount
);
if (!$insert->execute()) {
    die("Database Execution Error on esewa_transactions insert: " . $insert->error);
}
$insert->close();

// Remember which transaction we just sent the user off to pay for. Both
// failure_esewa.php and success_esewa.php use this to recover gracefully
// if eSewa's redirect back to us doesn't carry the usual "data" payload.
$_SESSION['pending_esewa_txn'] = $transaction_uuid;
$_SESSION['pending_esewa_order'] = $order_id;

// Signature for initiating a payment uses these 3 fields, in this order
$signature_data = "total_amount={$total_amount},transaction_uuid={$transaction_uuid},product_code={$merchant_id}";
$signature = base64_encode(hash_hmac('sha256', $signature_data, $secret_key, true));

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['REQUEST_URI']), '/');

// IMPORTANT: these URLs must NOT contain their own query string.
// eSewa appends its own "?data=..." onto whatever success_url/failure_url
// you give it, WITHOUT checking whether one is already present. If we add
// "?order_id=..&txn=.." here, eSewa turns it into "...&txn=XXX?data=YYY",
// which corrupts the URL (two "?" marks) and "data" ends up merged into
// the "txn" value instead of being its own parameter. Order/txn
// correlation is handled via $_SESSION['pending_esewa_txn'] instead.
$success_url = $base_url . "/success_esewa.php";
$failure_url = $base_url . "/failure_esewa.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Redirecting to eSewa...</title>
</head>
<body onload="document.getElementById('esewaForm').submit();">
    <p style="text-align: center; margin-top: 50px; font-family: sans-serif; color: #555;">
        Preparing secure payment connection, please wait...
    </p>

    <form id="esewaForm" action="<?php echo htmlspecialchars($epay_url); ?>" method="POST">
        <input type="hidden" name="amount" value="<?php echo htmlspecialchars($formatted_amount); ?>">
        <input type="hidden" name="tax_amount" value="<?php echo htmlspecialchars($tax_amount); ?>">
        <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars($total_amount); ?>">
        <input type="hidden" name="transaction_uuid" value="<?php echo htmlspecialchars($transaction_uuid); ?>">
        <input type="hidden" name="product_code" value="<?php echo htmlspecialchars($merchant_id); ?>">
        <input type="hidden" name="product_service_charge" value="<?php echo htmlspecialchars($service_charge); ?>">
        <input type="hidden" name="product_delivery_charge" value="<?php echo htmlspecialchars($delivery_charge); ?>">
        <input type="hidden" name="success_url" value="<?php echo htmlspecialchars($success_url); ?>">
        <input type="hidden" name="failure_url" value="<?php echo htmlspecialchars($failure_url); ?>">
        <input type="hidden" name="signed_field_names" value="total_amount,transaction_uuid,product_code">
        <input type="hidden" name="signature" value="<?php echo htmlspecialchars($signature); ?>">
    </form>
</body>
</html>