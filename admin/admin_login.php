<?php
session_start();
include("../db.php"); // Keeps your database connection intact

// Check if your form has been submitted
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Replace this with your exact login check logic if different
    if ($username == 'admin' && $password == '1234') { 
        $_SESSION['admin'] = $username;
        header("Location: dashboard.php"); // Redirect to your admin dashboard panel
        exit();
    } else {
        $error = "Invalid Username or Password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background-color: #ffffff;
            padding: 40px 40px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #1a202c;
            font-size: 26px;
            font-weight: 700;
        }

        .login-header p {
            color: #718096;
            font-size: 14px;
            margin-top: 6px;
            font-weight: 500;
        }

        .error-message {
            background-color: #fff5f5;
            color: #e53e3e;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid #fed7d7;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            color: #4a5568;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 13px 16px;
            font-size: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background-color: #f8fafc;
            color: #2d3748;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #ff3b3b;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(255, 59, 59, 0.15);
        }

        /* ATTRACTIVE BRANDED RED-ORANGE GRADIENT BUTTON */
        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #ff3b3b, #ff6a00);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(255, 59, 59, 0.3);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 10px;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(255, 59, 59, 0.45);
            filter: brightness(1.05);
        }

        .btn-login:active {
            transform: translateY(1px);
            box-shadow: 0 3px 10px rgba(255, 59, 59, 0.3);
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>Admin Panel</h2>
        <p>Please sign in to continue</p>
    </div>

    <?php if(isset($error)): ?>
        <div class="error-message"><?= $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" name="login" class="btn-login">Login to Dashboard</button>
    </form>
</div>

</body>
</html>