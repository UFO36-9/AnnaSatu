<?php
session_start();
include("navbar.php"); 
include("db.php"); // Connects to your foodmandu_old database

// 1. Fallback check: If no ID is passed in the URL, redirect back to the main page
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$member_id = intval($_GET['id']); // Securely convert ID to an integer to prevent SQL Injection

// 2. Query to fetch only this specific member's dataset
$query = $conn->prepare("SELECT * FROM team WHERE id = ?");
$query->bind_param("i", $member_id);
$query->execute();
$result = $query->get_result();

// 3. If the member exists, extract data. If not, show an error.
if ($result->num_rows > 0) {
    $member = $result->fetch_assoc();
} else {
    die("<h2>Team member not found!</h2>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $member['name']; ?> - Profile</title>
    <style>

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

        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            text-align: center;
            font-family: 'Segoe UI', sans-serif;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-radius: 12px;
            background: #fff;
        }
        .profile-container img {
            border-radius: 50%; /* Rounded profile image style */
            width: 200px;
            height: 200px;
            object-fit: cover;
            border: 4px solid #ff3b3b;
        }
        .profile-container h1 {
            color: #333;
            margin-top: 15px;
        }
        .profile-role {
            color: #ff3b3b;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .profile-bio {
            color: #666;
            line-height: 1.6;
        }
        .back-btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background: #ff3b3b;
            color: #fff;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            transition: 0.3s;
        }
        .back-btn:hover {
            background: #000;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <img src="<?php echo $member['image']; ?>" alt="<?php echo $member['name']; ?>">
    <h1><?php echo $member['name']; ?></h1>
    <div class="profile-role"><?php echo $member['role']; ?></div>
    
    <p class="profile-bio">
        <?php echo isset($member['bio']) ? $member['bio'] : 'No bio available for this team member.'; ?>
    </p>

    <a href="javascript:history.back()" class="back-btn">← Go Back</a>
</div>

</body>
</html>
<?php 
$query->close();
$conn->close();
?>