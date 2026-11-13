<?php
// Ensure session_start() is called once at the very top of your main pages
// (like index.php, menu.php, etc.) before including this navbar.
?>

<!-- Google Material Icons Library -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
/* Self-contained navbar styling - works even on pages that don't define
   their own nav CSS (e.g. checkout_esewa.php, success_esewa.php). */
.fm-navbar {
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
    font-family: 'Segoe UI', sans-serif;
    flex-wrap: wrap;
    gap: 15px;
}

.fm-navbar .fm-logo {
    text-decoration: none;
    display: flex;
    align-items: center;
}

.fm-navbar .fm-logo img {
    height: 45px;
}

.fm-navbar ul {
    display: flex;
    list-style: none;
    gap: 25px;
    align-items: center;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
}

.fm-navbar ul li {
    display: flex;
    align-items: center;
}

.fm-navbar ul li a {
    color: #333;
    text-decoration: none;
    font-weight: 600;
    position: relative;
    padding: 5px 0;
    display: inline-block;
    transition: color 0.3s ease, transform 0.3s ease;
}

.fm-navbar ul li a::after {
    content: "";
    position: absolute;
    width: 0%;
    height: 3px;
    left: 0;
    bottom: -5px;
    background: #ff3b3b;
    transition: width 0.4s ease;
}

.fm-navbar ul li a:hover {
    color: #ff3b3b;
    transform: scale(1.05);
}

.fm-navbar ul li a:hover::after {
    width: 100%;
}

.fm-navbar ul li .user-greeting {
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

.fm-navbar ul li a.logout-icon {
    color: #ff3b3b;
    display: inline-flex;
    align-items: center;
}

.fm-navbar ul li a.logout-icon::after {
    background: #000000;
}

.fm-navbar ul li a.logout-icon:hover {
    color: #000000;
}

.fm-navbar ul li a[title="Login"] {
    color: #333;
}

.fm-navbar ul li a[title="Signup"] {
    color: #ff3b3b;
}

.fm-navbar .material-icons {
    vertical-align: middle;
    font-size: 22px;
}
</style>

<nav class="fm-navbar">
    <a href="index.php" class="fm-logo">
        <img src="annsatu-logo.svg" alt="AnnSatu">
    </a>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="order.php">Order</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><span class="user-greeting">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <li>
                <a href="logout.php" class="logout-icon" title="Logout">
                    <span class="material-icons">logout</span>
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="login.php" class="logout-icon" title="Login">
                    <span class="material-icons">account_circle</span>
                </a>
            </li>
            <li>
                <a href="signup.php" class="logout-icon" title="Signup">
                    <span class="material-icons">person_add</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>