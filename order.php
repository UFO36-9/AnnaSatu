<?php 
session_start();
include("db.php"); 
include("navbar.php"); 

// Handle asynchronous AJAX database tracking
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['api']) && $_GET['api'] == 'place_order') {
    ob_clean();
    header('Content-Type: application/json');

    // Read payload from javascript stream
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "No order dataset transmitted."]);
        exit;
    }

    // Require login to place an order, so we can tie it to a real user_id.
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Please log in before placing an order."]);
        exit;
    }
    $user_id = $_SESSION['user_id'];

    $customer_name  = trim($data['customer_name']);
    $phone          = trim($data['phone']);
    $address        = trim($data['address']);
    $ordered_items  = trim($data['ordered_items']);
    $total_amount   = intval(round(floatval($data['total_amount']))); 
    $status         = "Pending";

    // Validate variables
    if (empty($customer_name) || empty($phone) || empty($address) || empty($ordered_items) || $total_amount <= 0) {
        echo json_encode(["status" => "error", "message" => "Validation check failed inside backend engine."]);
        exit;
    }

    // customer_name (typed at checkout) is saved here on the order itself.
    // checkout_esewa.php reads it back from this same orders table so the
    // esewa_transactions log reflects the real customer name instead of a
    // session-based guest email.
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, address, ordered_items, total_amount, status, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "SQL Setup Failure: " . $conn->error]);
        exit;
    }

    $stmt->bind_param("ssssisi", $customer_name, $phone, $address, $ordered_items, $total_amount, $status, $user_id);

    if ($stmt->execute()) {
        $new_order_id = $conn->insert_id;
        // Send the new order's id back so the browser can send the customer
        // to eSewa to pay for this specific order.
        echo json_encode([
            "status"  => "success",
            "message" => "Order placed successfully ✔",
            "order_id" => $new_order_id
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Execution Error: " . $stmt->error]);
    }

    $stmt->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AnnSatu - Order</title>

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* MAIN CONTAINER */
.container{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:30px;
    padding:40px;
    background:#f5f5f5;
}
/* NAVBAR */
nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:15px 40px;
    color:#333;
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
    border-bottom:1px solid #eee;
}

nav h2{
    color:#ff3b3b;
}

/* NAV LINKS */
nav ul{
    display:flex;
    list-style:none;
    gap:25px;
}

nav ul li a{
    color:#333;
    text-decoration:none;
    font-weight:600;
    position:relative;
    padding:5px 0;
    transition:0.3s;
}

/* 🔥 HOVER EFFECT */
nav ul li a::after{
    content:"";
    position:absolute;
    width:0%;
    height:3px;
    left:0;
    bottom:-5px;
    background:#ff3b3b;
    transition:0.4s;
}

nav ul li a:hover{
    color:#ff3b3b;
    transform:scale(1.05);
}

nav ul li a:hover::after{
    width:100%;
}

/* CARD STYLE */
.cart-box, .checkout{
    width:400px;
    background:rgba(255,255,255,0.95);
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    transition:0.3s;
}

.cart-box:hover, .checkout:hover{
    transform:translateY(-5px);
}

.cart-box h2, .checkout h2{
    text-align:center;
    margin-bottom:15px;
    color:#ff3b3b;
}

/* ITEMS */
.item{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:10px;
    border-bottom:1px solid #ddd;
}

/* BUTTONS */
.qty-btn{
    padding:5px 10px;
    margin:0 5px;
    border:none;
    cursor:pointer;
    background:#ff3b3b;
    color:white;
    border-radius:5px;
}

.qty-btn:hover{
    background:black;
}

.remove{
    background:black;
    color:white;
    border:none;
    padding:5px 8px;
    cursor:pointer;
    border-radius:5px;
}

.remove:hover{
    background:#ff3b3b;
}

/* TOTAL */
.total{
    text-align:right;
    font-size:18px;
    margin-top:15px;
    font-weight:bold;
}

/* CHECKOUT */
.checkout input, .checkout textarea{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ccc;
    border-radius:8px;
}

.checkout button{
    width:100%;
    padding:12px;
    background:#ff3b3b;
    color:white;
    border:none;
    cursor:pointer;
    border-radius:25px;
    transition:0.3s;
}

.checkout button:hover{
    background:black;
    transform:scale(1.05);
}

.empty{
    text-align:center;
    padding:20px;
    color:gray;
}
</style>
</head>

<body>

<div class="order-hero">
    Your Order 🛒
</div>

<div class="container">

    <div class="cart-box">
        <h2>Your Cart</h2>
        <div id="cartItems"></div>
        <div class="total" id="total">Total: Rs 0</div>
    </div>

    <div class="checkout">
        <h2>Checkout</h2>

        <input type="text" id="name" placeholder="Full Name">
        <input type="text" id="phone" placeholder="Phone Number">
        <textarea id="address" placeholder="Delivery Address"></textarea>

        <button onclick="placeOrder()">Pay with eSewa 💳</button>
    </div>

</div>

<script>
let cart = JSON.parse(localStorage.getItem("cart")) || [];
let orderTotal = 0;

function displayCart(){
    let container = document.getElementById("cartItems");
    let totalBox = document.getElementById("total");

    container.innerHTML = "";

    if(cart.length === 0){
        container.innerHTML = "<p class='empty'>Cart is empty</p>";
        totalBox.innerHTML = "Total: Rs 0";
        orderTotal = 0;
        return;
    }

    orderTotal = 0;

    cart.forEach((item, index)=>{
        let itemQty = item.quantity || 1;
        orderTotal += (item.price * itemQty);

        container.innerHTML += `
        <div class="item">
            <div>
                <b>${item.name}</b><br>
                Rs ${item.price}
            </div>

            <div>
                <button class="qty-btn" onclick="adjustQty(${index}, -1)">-</button>
                <span>${itemQty}</span>
                <button class="qty-btn" onclick="adjustQty(${index}, 1)">+</button>
            </div>

            <button class="remove" onclick="deleteItem(${index})">X</button>
        </div>
        `;
    });

    totalBox.innerHTML = "Total: Rs " + orderTotal;
}

function adjustQty(index, change) {
    if(!cart[index].quantity) cart[index].quantity = 1;
    cart[index].quantity += change;
    
    if(cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    
    localStorage.setItem("cart", JSON.stringify(cart));
    displayCart();
}

function deleteItem(index){
    cart.splice(index,1);
    localStorage.setItem("cart", JSON.stringify(cart));
    displayCart();
}

function placeOrder(){
    let name = document.getElementById("name").value.trim();
    let phone = document.getElementById("phone").value.trim();
    let address = document.getElementById("address").value.trim();

    if(name=="" || phone=="" || address==""){
        alert("Please fill all details!");
        return;
    }

    if(cart.length === 0){
        alert("Cart is empty!");
        return;
    }

    // Compile cart items into a clean comma-separated list
    let foodNames = cart.map(item => {
        let q = item.quantity || 1;
        return `${item.name} (x${q})`;
    }).join(", ");

    // Clean data structural payload mapping
    let payload = {
        customer_name: name,
        phone: phone,
        address: address,
        ordered_items: foodNames,
        total_amount: orderTotal
    };

    // Transmit directly to your database pipeline endpoint
    fetch("order.php?api=place_order", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === "success"){
            // Order is saved in the database. Now clear the cart and
            // send the customer to eSewa to actually pay for it.
            localStorage.removeItem("cart");
            window.location.href = "checkout_esewa.php?order_id=" + data.order_id;
        } else {
            alert("System Error: " + data.message);
        }
    })
    .catch(err => {
        console.error("Pipeline failure trace:", err);
        alert("Failed to submit entry to active endpoint processing queue.");
    });
}

displayCart();
</script>

</body>
</html>