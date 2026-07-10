<?php
session_start();
include("db.php");
include("navbar.php");

// Get About content
$about = $conn->query("SELECT * FROM about_content LIMIT 1");

// Get Team members
$team = $conn->query("SELECT * FROM team");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Foodmandu - About</title>

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* NAVBAR */
nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:15px 40px;
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 2px 12px rgba(0,0,0,0.1);
}

nav h2{
    color:#ff3b3b;
    font-size:24px;
}

nav ul{
    display:flex;
    list-style:none;
    gap:25px;
}

nav ul li a{
    text-decoration:none;
    color:#333;
    font-weight:600;
    position:relative;
    transition:0.3s;
    padding:5px 0;
}

/* HOVER ANIMATION */
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

/* HERO */
.hero{
    height:55vh;
    background:
    linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),
    url("https://images.unsplash.com/photo-1504674900247-0877df9cc836");
    background-size:cover;
    background-position:center;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    color:white;
}

.hero h1{
    font-size:55px;
    text-shadow:2px 2px 10px black;
}

/* OUR STORY SECTION */
.story-section {
    padding: 70px 20px;
    text-align: center;
    background: #fff;
}

.story-section h2 {
    color: #ff3b3b;
    font-size: 36px;
    margin-bottom: 20px;
}

.story-content {
    max-width: 800px;
    margin: 0 auto;
}

.story-content p {
    font-size: 16px;
    color: #555;
    line-height: 1.8;
    margin-bottom: 20px;
    text-align: justify; 
    text-align-last: center; 
}

/* SECTION */
.section{
    padding:70px 20px;
    text-align:center;
}

.section h2{
    color:#ff3b3b;
    font-size:32px;
    margin-bottom:20px;
}

.section p{
    max-width:800px;
    margin:auto;
    color:#555;
    line-height:1.6;
}

/* CARDS */
.cards{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:30px;
    margin-top:40px;
}

.card{
    width:280px;
    background:rgba(255,255,255,0.9);
    backdrop-filter:blur(10px);
    padding:25px;
    border-radius:15px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    transition:0.3s;
    border:1px solid #eee;
}

.card:hover{
    transform:translateY(-10px);
}

/* BIG STATS RIBBON */
.stats{
    background:linear-gradient(45deg,#ff3b3b,#ff7b00);
    color:white;
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:70px;
    padding:60px;
    text-align:center;
}

.stat h3{
    font-size:45px;
}

/* TEAM CONTAINER */
.team{
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:30px;
    margin-top:30px;
}

/* TEAM MEMBER LINK */
.member-link{
    text-decoration:none;
    color:inherit;
    display:block;
    cursor:pointer;
}

.member{
    width:240px;
    background:white;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,0.2);
    transition:0.3s;
}

.member-link:hover .member{
    transform:scale(1.08);
}

.member img{
    width:100%;
    height:210px;
    object-fit:cover;
}

.member p{
    padding:15px;
    font-size:18px;
    text-align:center;
}

/* ANIMATION */
.card, .member{
    animation:fadeUp 0.8s ease-in-out;
}

@keyframes fadeUp{
    from{
        opacity:0;
        transform:translateY(30px);
    }
    to{
        opacity:1;
        transform:translateY(0);
    }
}
</style>
</head>

<body>

<!-- HERO IMAGE BANNER -->
<div class="hero">
    <h1>About Foodmandu 🍔</h1>
</div>

<!-- STORY LINES -->
<section class="story-section">
    <h2>Our Story</h2>
    <div class="story-content">
        <p>
            Founded with a passion for connecting food lovers with their favorite culinary destinations, 
            <strong>Foodmandu</strong> has grown into Nepal’s leading food delivery platform. We bridge the 
            gap between local food enthusiasts and the city's finest restaurants, bringing diverse, high-quality, 
            and authentic flavors straight to your doorstep.
        </p>
        <p>
            Whether you are craving traditional local delicacies like steaming MoMos, specialized fusion items, 
            or international comfort foods like piping hot pizzas and artisanal burgers, our goal remains the same: 
            delivering exceptional culinary experiences with speed, freshness, and care. 
        </p>
    </div>
</section>

<!-- COUNTER ACCENT BLOCK -->
<div class="stats">
    <div>
        <h3>10K+</h3>
        <p>Happy Customers</p>
    </div>

    <div>
        <h3>500+</h3>
        <p>Restaurants</p>
    </div>

    <div>
        <h3>1M+</h3>
        <p>Orders Delivered</p>
    </div>
</div>

<!-- MEMBERS PORTAL -->
<section class="section">
    <h2>Our Team</h2>

    <div class="team">
        <?php while($row = $team->fetch_assoc()) { ?>
            <a href="member_details.php?id=<?php echo $row['id']; ?>" class="member-link">
                <div class="member">
                    <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                    <p>
                        <b><?php echo $row['name']; ?></b><br>
                        <span style="font-size: 14px; color: #777;"><?php echo $row['role']; ?></span>
                    </p>
                </div>
            </a>
        <?php } ?>
    </div>
</section>

</body>
</html>