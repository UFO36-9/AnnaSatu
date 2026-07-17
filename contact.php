<?php
session_start();
include("navbar.php"); 
include("db.php"); // Keeps database credentials separate and clean

$msg = "";
$msg_color = "";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize form inputs
    $name = mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($_POST['name']))));
    $email = mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($_POST['email']))));
    $subject = mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($_POST['subject']))));
    $message = mysqli_real_escape_string($conn, htmlspecialchars(strip_tags(trim($_POST['message']))));
    
    // Assign default value for your 'status' column
    $status = "Pending"; 

    // Server-side validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $msg = "Please fill all fields!";
        $msg_color = "red";
    } else {
        // Secure Prepared Statement matching your exact database columns
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $name, $email, $subject, $message, $status);

        if ($stmt->execute()) {
            $msg = "Message sent and saved successfully ✔";
            $msg_color = "green";
            
            // Clear variables so fields look empty after a successful submission
            $name = $email = $subject = $message = "";
        } else {
            $msg = "Database Error: Failed to save your message.";
            $msg_color = "red";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - Foodmandu</title>

<style>
*{
    margin:0;
    padding:0;
    font-family:'Segoe UI', sans-serif;
    box-sizing:border-box;
}

/* NAVBAR (WHITE PREMIUM) */
nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:white;
    padding:15px 40px;
    position:sticky;
    top:0;
    z-index:1000;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

nav h2{
    color:#ff3b3b;
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
}

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

/* PAGE BACKGROUND */
body{
    background:#ffffff;
}

/* HERO SECTION */
.hero{
    height:25vh;
    background:linear-gradient(45deg,#ff3b3b,#ff7a59);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
    font-size:40px;
    font-weight:bold;
    text-shadow:2px 2px 10px rgba(0,0,0,0.2);
}

/* CONTACT WRAPPER */
.contact-wrapper{
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:70vh;
    padding:40px;
}

/* FORM BOX */
.contact-box{
    width:380px;
    padding:30px;
    border-radius:15px;
    background:white;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
    border:1px solid #eee;
    transition:0.3s;
}

.contact-box:hover{
    transform:translateY(-5px);
}

.contact-box h2{
    text-align:center;
    margin-bottom:15px;
    color:#ff3b3b;
}

/* INPUTS */
.contact-box input,
.contact-box textarea{
    width:100%;
    padding:12px;
    margin:10px 0;
    border:1px solid #ddd;
    border-radius:8px;
    outline:none;
    transition:0.3s;
}

.contact-box input:focus,
.contact-box textarea:focus{
    border-color:#ff3b3b;
    box-shadow:0 0 8px rgba(255,59,59,0.2);
}

/* BUTTON */
.contact-box button{
    width:100%;
    padding:12px;
    background:#ff3b3b;
    color:white;
    border:none;
    border-radius:25px;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;
}

.contact-box button:hover{
    background:black;
    transform:scale(1.05);
}

/* MESSAGE */
#msg{
    text-align:center;
    margin-top:10px;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="hero">
    Contact Us 📞
</div>

<div class="contact-wrapper">
    <form class="contact-box" action="contact.php" method="POST">
        <h2>Get in Touch</h2>

        <input type="text" name="name" placeholder="Your Name" value="<?php echo isset($name) ? $name : ''; ?>" required>
        <input type="email" name="email" placeholder="Your Email" value="<?php echo isset($email) ? $email : ''; ?>" required>
        <input type="text" name="subject" placeholder="Subject" value="<?php echo isset($subject) ? $subject : ''; ?>" required>
        <textarea name="message" rows="5" placeholder="Your Message" required><?php echo isset($message) ? $message : ''; ?></textarea>

        <button type="submit">Send Message</button>

        <?php if (!empty($msg)): ?>
            <p id="msg" style="color: <?php echo $msg_color; ?>;"><?php echo $msg; ?></p>
        <?php endif; ?>
    </form>
</div>

</body>
</html>