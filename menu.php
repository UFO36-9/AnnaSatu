<?php 
session_start();
include("db.php"); 
include("navbar.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Foodmandu - Menu</title>

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* HERO */
.menu-hero{
    height:45vh;
    background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url("https://images.unsplash.com/photo-1504674900247-0877df9cc836");
    background-size:cover;
    background-position:center;
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:45px;
    font-weight:bold;
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

/* FILTER BUTTONS */
.filters{
    text-align:center;
    padding:30px;
}

.filters button{
    padding:10px 18px;
    margin:8px;
    border:none;
    cursor:pointer;
    background:#ff3b3b;
    color:white;
    border-radius:20px;
    transition:0.3s;
}

.filters button:hover{
    background:black;
    transform:scale(1.05);
}

/* MENU GRID */
.menu-container{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:25px;
    padding:30px;
    background: #f5f5f5;
    min-height: 40vh;
}

/* CARD */
.card{
    width:240px;
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 5px 15px rgba(0,0,0,0.2);
    transition:0.3s;
}

.card:hover{
    transform:scale(1.07);
}

.card img{
    width:100%;
    height:170px;
    object-fit:cover;
}

/* CONTENT */
.card-content{
    padding:15px;
    text-align:center;
}

.price{
    color:#ff3b3b;
    font-weight:bold;
    margin:8px 0;
}

.rating{
    color:gold;
    margin-bottom:8px;
}

/* BUTTON */
.card-content button{
    width:100%;
    padding:10px;
    border:none;
    background:#ff3b3b;
    color:white;
    cursor:pointer;
    border-radius:20px;
    transition:0.3s;
}

.card-content button:hover{
    background:black;
    transform:scale(1.05);
}
</style>
</head>
<body>

<div class="menu-hero">
    Our Delicious Menu 🍽️
</div>

<div class="filters">
    <button onclick="filter('all')">All Items</button>
</div>

<div class="menu-container">

    <?php
    // 1. Querying the exact table name 'food' shown in your phpMyAdmin screenshot
    $query = "SELECT * FROM food"; 
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            
            // 2. Map directly to your actual columns: 'name', 'price', and 'image'
            $food_name = htmlspecialchars($row['name']);
            $price     = floatval($row['price']);
            $image     = htmlspecialchars($row['image']);
            
            // Safe fallback defaults since 'category' column is not found on your database schema
            $category  = "all"; 
            
            // If the image column is NULL or empty string, fallback to a clean placeholder food picture
            if(empty($image)) {
                $image = "https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=500";
            }
            ?>
            
            <div class="card <?php echo $category; ?>">
                <img src="<?php echo $image; ?>" alt="<?php echo $food_name; ?>">
                <div class="card-content">
                    <h3><?php echo $food_name; ?></h3>
                    <div class="rating">⭐⭐⭐⭐⭐</div>
                    <div class="price">Rs <?php echo number_format($price); ?></div>
                    <button onclick="addCart('<?php echo addslashes($food_name); ?>', <?php echo $price; ?>)">Add to Cart</button>
                </div>
            </div>

            <?php
        }
    } else {
        echo "<p style='color:gray; font-size:18px;'>Your `food` table is currently empty inside your database.</p>";
    }
    ?>

</div>

<script>
function addCart(name, price){
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    
    let existingItem = cart.find(item => item.name === name);
    if(existingItem) {
        existingItem.quantity = (existingItem.quantity || 1) + 1;
    } else {
        cart.push({name, price, quantity: 1});
    }
    
    localStorage.setItem("cart", JSON.stringify(cart));
    alert(name + " added to cart ✔");
}

function filter(category){
    let cards = document.querySelectorAll(".card");
    cards.forEach(card=>{
        if(category === "all"){
            card.style.display = "block";
        } else {
            if(card.classList.contains(category)){
                card.style.display = "block";
            }else{
                card.style.display = "none";
            }
        }
    });
}
</script>

</body>
</html>