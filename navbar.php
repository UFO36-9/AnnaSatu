<?php
// Ensure session_start() is called once at the very top of your main pages 
// (like index.php, menu.php, etc.) before including this navbar.
?>

<!-- Google Material Icons Library -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<nav>
    <a href="index.php" style="text-decoration:none; display:flex; align-items:center;">
        <img src="logo (1).svg" alt="Foodmandu" style="height:45px;">
    </a>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="menu.php">Menu</a></li>
        <li><a href="order.php">Order</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><span class="user-greeting">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span></li>
            <!-- Google Material Logout Icon -->
            <li>
                <a href="logout.php" class="logout-icon" title="Logout">
                    <span class="material-icons" style="vertical-align: middle;">logout</span>
                </a>
            </li>
        <?php else: ?>
            <!-- Google Material Login Icon (Account Circle) -->
            <li>
                <a href="login.php" class="logout-icon" title="Login" style="color: #333;">
                    <span class="material-icons" style="vertical-align: middle;">account_circle</span>
                </a>
            </li>
            <!-- Google Material Signup Icon (Person Add) -->
            <li>
                <a href="signup.php" class="logout-icon" title="Signup" style="color: #ff3b3b;">
                    <span class="material-icons" style="vertical-align: middle;">person_add</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>