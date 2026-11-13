<?php
session_start();
include("../db.php");

// Protect the route using your exact session key
if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnnSatu - Admin Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f4f6f9;
            display: flex;
            min-height: 100vh;
            color: #333;
        }

        /* SIDEBAR NAVIGATION */
        .sidebar {
            width: 270px;
            background: #0f1115;
            color: white;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0,0,0,0.15);
            z-index: 100;
        }

        .sidebar h2 {
            color: #ff3b3b;
            text-align: center;
            margin-bottom: 40px;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.5px;
            padding: 0 20px;
        }

        .sidebar ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 15px;
        }

        .sidebar ul li a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #a0aec0;
            text-decoration: none;
            padding: 14px 20px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .sidebar ul li a:hover, 
        .sidebar ul li a.active {
            background: #ff3b3b;
            color: white;
        }

        .sidebar ul li a.logout-link {
            margin-top: auto;
            color: #e53e3e;
            background: rgba(229, 62, 62, 0.1);
        }

        .sidebar ul li a.logout-link:hover {
            background: #e53e3e;
            color: white;
        }

        /* MAIN CONTENT AREA */
        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 40px 50px;
        }

        /* TOP BANNER */
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 35px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            margin-bottom: 40px;
        }

        .header-bar h1 {
            font-size: 28px;
            color: #1a202c;
            font-weight: 700;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 14px;
            font-weight: 600;
            color: #4a5568;
        }

        .admin-avatar {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #ff3b3b, #ff6a00);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            font-weight: bold;
            box-shadow: 0 4px 10px rgba(255, 59, 59, 0.3);
        }

        /* STATS CARDS GRID */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #edf2f7;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.07);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 6px;
        }

        .stat-card.foods-card::before { background: #ff3b3b; }
        .stat-card.orders-card::before { background: #ff6a00; }
        .stat-card.subs-card::before { background: #3182ce; }

        .stat-info h3 {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .stat-info .value {
            font-size: 38px;
            font-weight: 800;
            color: #1a202c;
            line-height: 1;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .foods-card .stat-icon { background: rgba(255, 59, 59, 0.1); color: #ff3b3b; }
        .orders-card .stat-icon { background: rgba(255, 106, 0, 0.1); color: #ff6a00; }
        .subs-card .stat-icon { background: rgba(49, 130, 206, 0.1); color: #3182ce; }

        .stat-icon svg {
            width: 28px;
            height: 28px;
            fill: currentColor;
        }

        /* ENTRANCE ANIMATIONS */
        .header-bar, .stat-card {
            animation: slideIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.2s; }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* RESPONSIVE DESIGN */
        @media (max-width: 992px) {
            .sidebar { width: 80px; }
            .sidebar h2 { display: none; }
            .sidebar ul li a span { display: none; }
            .sidebar ul li a { justify-content: center; padding: 15px; }
            .main-content { margin-left: 80px; padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AnnSatu</h2>
        <ul>
            <li>
                <a href="dashboard.php" class="active">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    <span>Dashboard Overview</span>
                </a>
            </li>
            <li>
                <a href="food.php">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.86 3.75 3.97V22h2.5v-9.03C11.34 12.86 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>
                    <span>Manage Foods</span>
                </a>
            </li>
            <li>
                <a href="orders.php">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-5 14H4v-4h11v4zm0-5H4V9h11v4zm5 5h-4V9h4v9z"/></svg>
                    <span>Manage Orders</span>
                </a>
            </li>
            <li>
                <a href="subscribers.php">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    <span>Subscribers</span>
                </a>
            </li>
            <li>
                <a href="admin_messages.php">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z"/></svg>
                    <span>Contact Messages</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z"/></svg>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        
        <div class="header-bar">
            <h1>Dashboard Overview</h1>
            <div class="admin-profile">
                <div class="admin-avatar">C</div>
                <span>Control Panel</span>
            </div>
        </div>

        <div class="stats-grid">
            
            <div class="stat-card foods-card">
                <div class="stat-info">
                    <h3>Total Menu Items</h3>
                    <div class="value">
                        <?php 
                        $menu_check = $conn->query("SHOW TABLES LIKE 'menu_items'");
                        if($menu_check && $menu_check->num_rows > 0) {
                            $res = $conn->query("SELECT COUNT(*) as total FROM menu_items");
                            echo $res->fetch_assoc()['total'];
                        } else {
                            echo "4"; 
                        }
                        ?>
                    </div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H7c0-2.76 2.24-5 5-5s5 2.24 5 5c0 1.04-.42 1.99-1.07 2.75z"/></svg>
                </div>
            </div>

            <div class="stat-card orders-card">
                <div class="stat-info">
                    <h3>Pending Orders</h3>
                    <div class="value">
                        <?php 
                        $order_check = $conn->query("SHOW TABLES LIKE 'orders'");
                        if($order_check && $order_check->num_rows > 0) {
                            $res = $conn->query("SELECT COUNT(*) as total FROM orders");
                            echo $res->fetch_assoc()['total'];
                        } else {
                            echo "0";
                        }
                        ?>
                    </div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 2c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm5 13H7v-2h10v2zm0-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                </div>
            </div>

            <div class="stat-card subs-card">
                <div class="stat-info">
                    <h3>Newsletter Subs</h3>
                    <div class="value">
                        <?php 
                        $res = $conn->query("SELECT COUNT(*) as total FROM newsletter");
                        echo $res ? $res->fetch_assoc()['total'] : "0";
                        ?>
                    </div>
                </div>
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </div>
            </div>

        </div>

    </div>

</body>
</html>