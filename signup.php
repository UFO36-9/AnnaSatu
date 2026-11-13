<?php
session_start();
include("db.php"); // Ensure your database connection file path is correct

$message = "";
$message_type = "";

// Initialize variables to retain form inputs on error
$name = "";
$email = "";

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if (!empty($name) && !empty($email) && !empty($password) && !empty($confirm)) {
        
        // 1. Check if Passwords Match
        if ($password !== $confirm) {
            $message = "Passwords do not match! Please try again.";
            $message_type = "error";
        } else {
            // 2. Check if the Email is already registered
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

            if ($check_stmt->num_rows > 0) {
                $message = "An account with this email address already exists.";
                $message_type = "error";
                $check_stmt->close();
            } else {
                $check_stmt->close();

                // 3. Encrypt the password using modern secure hashing algorithms
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // 4. Securely Insert New Registration Row into Database Table
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);

                if ($stmt->execute()) {
                    // Fetch inserted ID to keep it consistent with your login architecture
                    $_SESSION['user_id'] = $stmt->insert_id; 
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;

                    // Safely encode string to prevent JavaScript break outs (XSS Protection)
                    $safe_name = json_encode($name);
                    echo "
                    <script>
                        alert(" . $safe_name . " + ', your account has been successfully created!');
                        window.location.href='index.php';
                    </script>
                    ";
                    exit();
                } else {
                    $message = "Database Error: Failed to complete registration.";
                    $message_type = "error";
                }
                $stmt->close();
            }
        }
    } else {
        $message = "Please fill in all required registration fields.";
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signup - AnnSatu</title>

<style>
* {
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
    box-sizing: border-box;
}

body {
    background: #ffffff;
}

/* CENTER WRAPPER */
.wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 90vh;
}

/* SIGNUP BOX */
.box {
    width: 370px;
    padding: 35px;
    border-radius: 15px;
    background: white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    text-align: center;
    border: 1px solid #eee;
    transition: 0.3s;
}

.box:hover {
    transform: translateY(-5px);
}

h2 {
    color: #ff3b3b;
    margin-bottom: 20px;
    font-size: 28px;
}

/* INPUTS */
input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    outline: none;
    transition: 0.3s;
}

input:focus {
    border-color: #ff3b3b;
    box-shadow: 0 0 8px rgba(255,59,59,0.2);
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;
    border: none;
    background: #ff3b3b;
    color: white;
    font-weight: bold;
    border-radius: 25px;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    background: black;
    transform: scale(1.05);
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

/* BASE STYLE FOR ALL NAV ANCHOR LINKS */
nav ul li a {
    color: #333;
    text-decoration: none;
    font-weight: 600;
    padding: 5px 0;
    transition: color 0.3s ease;
}

nav ul li a:hover {
    color: #ff3b3b;
}

/* BALANCED INLINE GREETING - MATCHES LOGIN SYSTEM HEIGHTS */
nav ul li .user-greeting {
    color: #555;
    font-weight: 400;
    font-size: 15px;
    display: inline-block;
    padding: 5px 0; 
    margin: 0;
    line-height: normal;
}

/* LOGOUT ICON POSITION FIX */
nav ul li a.logout-icon {
    color: #ff3b3b;
    font-size: 18px;
    padding: 5px 0;
    line-height: 1;
    display: inline-block;
}

nav ul li a.logout-icon:hover {
    color: #000000;
    transform: scale(1.15);
}

/* LINK UNDER BOX */
.box > a {
    display: block;
    margin-top: 12px;
    color: #ff3b3b;
    text-decoration: none;
    font-weight: 600;
}

.box > a:hover {
    color: black;
}

/* DYNAMIC STATUS ALERT BANNER UI */
.alert {
    padding: 11px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
}
.alert.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
</head>

<body>
<?php include("navbar.php"); ?>

<div class="wrapper">
    <div class="box">
        <h2>Create Account 🍔</h2>

        <?php if(!empty($message)): ?>
            <div class="alert <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form action="signup.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" value="<?= htmlspecialchars($name) ?>" required>
            <input type="email" name="email" placeholder="Email Address" value="<?= htmlspecialchars($email) ?>" required>
            <input type="password" name="password" placeholder="Password" required minlength="6">
            <input type="password" name="confirm" placeholder="Confirm Password" required>

            <button type="submit">Signup</button>
        </form>

        <a href="login.php">Already have an account? Login</a>
    </div>
</div>
 
</body>
</html>