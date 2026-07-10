<?php
session_start();
include("db.php");   // database connection
include("navbar.php");  // your navbar (optional)

// Place your fixed PHP here
if(isset($_POST['subscribe'])) {
    $email = mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($_POST['email']))));

    if(!empty($email)) {
        $stmt = $conn->prepare("INSERT INTO newsletter (email) VALUES (?)");
        $stmt->bind_param("s", $email);

        if($stmt->execute()) {
            echo "<script>alert('Subscribed successfully!');</script>";
        } else {
            echo "<script>alert('Database error!');</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Email is required!');</script>";
    }
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Foodmandu - Home</title>

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* NAVBAR CONTAINER */
nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: white;
    padding: 15px 40px;
    color: #333;
    position: sticky;
    top: 0;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-bottom: 1px solid #eee;
}

nav h2 {
    color: #ff3b3b;
}

/* NAV LINKS CONTAINER */
nav ul {
    display: flex;
    list-style: none;
    gap: 25px;
    align-items: center;
}

nav ul li {
    display: flex;
    align-items: center;
}

/* BASE STYLE FOR ALL ANCHOR LINKS */
nav ul li a {
    color: #333;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    padding: 5px 0;
    display: inline-block;
    transition: color 0.3s ease, transform 0.3s ease;
}

/* HOVER ANIMATION FOR STANDARD LINKS */
nav ul li a::after {
    content: "";
    position: absolute;
    width: 0%;
    height: 3px;
    left: 0;
    bottom: -5px;
    background: #ff3b3b;
    transition: width 0.4s ease;
}

nav ul li a:hover {
    color: #ff3b3b;
    transform: scale(1.05);
}

nav ul li a:hover::after {
    width: 100%;
}

/* ALIGNED USER PROFILE GREETING */
nav ul li .user-greeting {
    color: #333;
    font-weight: 700;
    font-size: 15px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 0;
    margin: 0;
    line-height: 1.2;
    border-bottom: 3px solid #ff3b3b;
}

/* LOGOUT BUTTON */
nav ul li a[href="logout.php"] {
    color: #ff3b3b;
}

nav ul li a[href="logout.php"]::after {
    background: #000000;
}

nav ul li a[href="logout.php"]:hover {
    color: #000000;
}

/* HERO */
.hero{
    height:90vh;
    background:
    linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
    url("https://images.unsplash.com/photo-1504674900247-0877df9cc836");
    background-size:cover;
    background-position:center;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    color:white;
}

.hero h1{
    font-size:60px;
}

.hero p{
    font-size:20px;
    margin:15px 0;
}

.hero button{
    padding:14px 30px;
    border:none;
    background:#ff3b3b;
    color:white;
    font-size:16px;
    cursor:pointer;
    border-radius:30px;
    transition:0.3s;
}

.hero button:hover{
    transform:scale(1.1);
    background:black;
}

/* FEATURES */
.features{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:25px;
    padding:60px;
    background:#f5f5f5;
}

.feature{
    width:260px;
    background:white;
    padding:25px;
    border-radius:15px;
    text-align:center;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
    transition:0.3s;
}

.feature:hover{
    transform:translateY(-10px);
}

.feature h3{
    color:#ff3b3b;
}

/* POPULAR FOOD */
.popular{
    padding:60px;
    text-align:center;
}

.popular h2{
    color:#ff3b3b;
}

.foods{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:25px;
    margin-top:30px;
}

/* POPULAR WRAPPER LINKS */
.food-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.food-card{
    width:230px;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
    transition:0.3s;
}

/* Scaling trigger shifts over to the card anchor link wrapper */
.food-card-link:hover .food-card{
    transform:scale(1.08);
}

.food-card img{
    width:100%;
    height:160px;
    object-fit:cover;
}

/* NEWSLETTER */
.newsletter{
    background:linear-gradient(45deg,#ff3b3b,#ff6a00);
    color:white;
    text-align:center;
    padding:50px;
}

.newsletter input{
    padding:12px;
    width:250px;
    border:none;
    border-radius:20px;
    margin-top:10px;
    outline: none;
}

.newsletter button{
    padding:12px 20px;
    border:none;
    border-radius:20px;
    background:black;
    color:white;
    cursor:pointer;
    margin-left:10px;
}

/* FOOTER */
.footer{
    background:#111;
    color:white;
    padding:50px 20px;
}

.footer-container{
    display:flex;
    justify-content:space-between;
    flex-wrap:wrap;
    max-width:1200px;
    margin:auto;
}

.footer-box{
    width:250px;
    margin:10px;
}

.footer-box h3{
    margin-bottom:15px;
    color:#ff3b3b;
}

.footer-box a{
    display:block;
    color:white;
    text-decoration:none;
    margin:5px 0;
}

.footer-box a:hover{
    color:#ffe600;
}

.bottom-footer{
    text-align:center;
    margin-top:30px;
    border-top:1px solid #333;
    padding-top:20px;
    font-size:14px;
    color:#aaa;
}
</style>
</head>

<body>

<?php if (isset($_SESSION['flash_message'])): ?>
    <script>
        alert(<?= json_encode($_SESSION['flash_message']) ?>);
    </script>
    <?php 
    unset($_SESSION['flash_message']); 
    unset($_SESSION['flash_type']); 
    ?>
<?php endif; ?>

<section class="hero">
    <h1>Delicious Food Delivered Fast 🍔</h1>
    <p>Order your favorite meals from top restaurants</p>

    <a href="menu.php">
        <button>Order Now</button>
    </a>
</section>

<section class="features">
    <div class="feature">
        <h3>⚡ Fast Delivery</h3>
        <p>Get your food within 30 minutes</p>
    </div>

    <div class="feature">
        <h3>🍕 Fresh Food</h3>
        <p>Hot & fresh meals every time</p>
    </div>

    <div class="feature">
        <h3>⭐ Top Quality</h3>
        <p>Best restaurants in your city</p>
    </div>
</section>

<section class="popular">
    <h2>🔥 Popular Foods</h2>

    <div class="foods">
        <a href="menu.php?category=momo" class="food-card-link">
            <div class="food-card">
                <img src="momo pic.jpg" alt="Momo">
            </div>
        </a>

        <a href="menu.php?category=chowmein" class="food-card-link">
            <div class="food-card">
                <img src="chowemin img.jfif" alt="Chowmein">
            </div>
        </a>

        <a href="menu.php?category=pizza" class="food-card-link">
            <div class="food-card">
                <img src="pizza pic.avif" alt="Pizza">
            </div>
        </a>

        <a href="menu.php?category=burger" class="food-card-link">
            <div class="food-card">
                <img src="burger img.jfif" alt="Burger">
            </div>
        </a>
    </div>
</section>

<section class="newsletter">
    <h2>Subscribe for Offers 🔥</h2>

    <form method="POST">
        <input type="email" name="email" placeholder="Enter your email" required>
        <button type="submit" name="subscribe">Subscribe</button>
    </form>
</section>

<footer class="footer">
    <div class="footer-container">

        <div class="footer-box">
            <h3>Foodmandu</h3>
            <p>Fast, fresh, and reliable food delivery.</p>
        </div>

        <div class="footer-box">
            <h3>Quick Links</h3>
            <a href="index.php">Home</a>
            <a href="menu.php">Menu</a>
            <a href="order.html">Order</a>
            <a href="contact.php">Contact</a>
        </div>

        <div class="footer-box">
            <h3>Contact</h3>
            <a href="#">📍 Kathmandu, Nepal</a>
            <a href="#">📞 +977 9800000000</a>
            <a href="#">📧 support@foodmandu.com</a>
        </div>

    </div>

    <div class="bottom-footer">
        © 2026 Foodmandu. All Rights Reserved.
    </div>
</footer>

</body>
</html>