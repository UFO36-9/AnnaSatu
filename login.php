<?php
session_start();
include("db.php"); 

$message = "";
$message_type = "";

// 1. ALL BACKEND LOGIC PROCESSING FIRST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, name, email, password, is_admin FROM users WHERE email = ?");

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Password verification check
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                // Flag admin accounts so admin/esewa_transactions.php (and any
                // other admin-only page) can check $_SESSION['is_admin']
                $_SESSION['is_admin'] = ((int)$user['is_admin'] === 1);

                $_SESSION['flash_message'] = "Login successful! Welcome back, " . $user['name'] . ".";
                $_SESSION['flash_type'] = "success";

                header("Location: index.php");
                exit();
            } else {
                $message = "Incorrect password! Please try again.";
                $message_type = "error";
            }
        } else {
            $message = "No account found with that email address.";
            $message_type = "error";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Foodmandu</title>
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
            height: 85vh;
        }

        /* LOGIN BOX */
        .box {
            width: 360px;
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

        /* PLAIN INLINE GREETING - NO ICON, NO UNDERLINE, NO BOLD */
        nav ul li .user-greeting {
            color: #555;
            font-weight: 400;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            padding: 0;
            margin: 0;
            line-height: 1.2;
        }

        /* LOGOUT AS A SMALL ICON ONLY */
        nav ul li a.logout-icon {
            color: #ff3b3b;
            font-size: 18px;
            padding: 0;
            line-height: 1;
        }

        nav ul li a.logout-icon:hover {
            color: #000000;
            transform: scale(1.15);
        }

        /* Signup link below the login box only */
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

        /* ALERT BANNER SYSTEM */
        .alert {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
        }
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>

<?php include("navbar.php"); ?>

<div class="wrapper">
    <div class="box">
        <h2>Login 🍔</h2>

        <?php if(!empty($message)): ?>
            <div class="alert <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>

        <a href="signup.php">Don't have an account? Signup</a>
    </div>
</div>

</body>
</html>
