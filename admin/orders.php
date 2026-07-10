<?php
session_start();
include("../db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// Handle Order Status Update
if(isset($_GET['complete'])){
    $id = intval($_GET['complete']);
    $stmt = $conn->prepare("UPDATE orders SET status = 'Completed' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php");
    exit();
}

// Handle Order Cancellation/Deletion
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php");
    exit();
}

// Handle Order Editing Form Submission
if(isset($_POST['update_order'])){
    $id            = intval($_POST['order_id']);
    $customer_name = $_POST['customer_name'];
    $phone         = $_POST['phone'];
    $address       = $_POST['address'];
    $ordered_items = $_POST['ordered_items'];
    $total_amount  = floatval($_POST['total_amount']);
    $status        = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET customer_name=?, phone=?, address=?, ordered_items=?, total_amount=?, status=? WHERE id=?");
    $stmt->bind_param("ssssdsi", $customer_name, $phone, $address, $ordered_items, $total_amount, $status, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: orders.php");
    exit();
}

// Fetch order details if "edit" parameter is triggered
$edit_order = null;
if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $edit_order = $res->fetch_assoc();
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Foodmandu Admin</title>
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

        /* SIDEBAR PANEL NAVIGATION */
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

        /* MAIN WORKSPACE WRAPPER */
        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 40px 50px;
        }

        /* HEADER TOP BAR */
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

        /* DATA RENDER MATRICES */
        .table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            border: 1px solid #edf2f7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 20px 24px;
            vertical-align: middle;
        }

        th {
            background: #f8fafc;
            color: #4a5568;
            font-weight: 700;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid #edf2f7;
        }

        td {
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
            font-size: 15px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #fdfdfd;
        }

        /* METRIC STYLING BASICS */
        .cust-name {
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
            display: block;
            margin-bottom: 4px;
        }

        .cust-meta {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }

        .order-items {
            font-weight: 600;
            color: #4a5568;
            line-height: 1.5;
        }

        .price-tag {
            font-weight: 700;
            font-size: 16px;
            color: #1a202c;
        }

        /* BADGES AND LABELS */
        .status {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status.pending { background: rgba(214, 158, 46, 0.12); color: #b7791f; }
        .status.completed { background: rgba(56, 161, 105, 0.12); color: #2f855a; }

        /* BUTTON ACTIONS GROUP */
        .action-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .action-link {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .complete-btn {
            color: #38a169;
            background: rgba(56, 161, 105, 0.05);
        }
        .complete-btn:hover {
            background: #38a169;
            color: white;
        }

        .edit-btn {
            color: #3182ce;
            background: rgba(49, 130, 206, 0.05);
        }
        .edit-btn:hover {
            background: #3182ce;
            color: white;
        }

        .delete-btn {
            color: #e53e3e;
            background: rgba(229, 62, 62, 0.05);
        }
        .delete-btn:hover {
            background: #e53e3e;
            color: white;
        }

        /* MODAL OVERLAY STYLING */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(15, 17, 21, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-card {
            background: white;
            width: 100%;
            max-width: 540px;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border: 1px solid #edf2f7;
            animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .modal-card h2 { font-size: 22px; color: #1a202c; margin-bottom: 20px; font-weight: 700; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #4a5568; margin-bottom: 6px; letter-spacing: 0.5px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 11px 14px; border: 1px solid #edf2f7; background: #f8fafc; border-radius: 10px; font-size: 14px; color: #2d3748; outline: none; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: #3182ce; background: white; }
        .modal-buttons { display: flex; gap: 12px; margin-top: 25px; }
        .modal-btn { flex: 1; padding: 12px; text-align: center; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; cursor: pointer; border: none; }
        .btn-save { background: #3182ce; color: white; }
        .btn-save:hover { background: #2b6cb0; }
        .btn-cancel { background: #edf2f7; color: #4a5568; text-align: center;}
        .btn-cancel:hover { background: #e2e8f0; }

        /* ANIMATION DELAYS */
        .header-bar, .table-container {
            animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .table-container { animation-delay: 0.1s; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* RESPONSIVE LAYOUT */
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
        <h2>Foodmandu</h2>
        <ul>
            <li>
                <a href="dashboard.php">
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
                <a href="orders.php" class="active">
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
                <a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor"><path d="M13 3h-2v10h2V3zm4.83 2.17l-1.42 1.42C17.99 7.86 19 9.81 19 12c0 3.87-3.13 7-7 7s-7-3.13-7-7c0-2.19 1.01-4.14 2.58-5.42L6.17 5.17C4.23 6.82 3 9.26 3 12c0 4.97 4.03 9 9 9s9-4.03 9-9c0-2.74-1.23-5.18-3.17-6.83z"/></svg>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main-content">
        
        <div class="header-bar">
            <h1>Live Customer Orders</h1>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Customer Details</th>
                        <th>Items Ordered</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th style="width: 280px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $table_check = $conn->query("SHOW TABLES LIKE 'orders'");
                    if($table_check && $table_check->num_rows > 0) {
                        $res = $conn->query("SELECT * FROM orders ORDER BY id DESC");
                        if($res->num_rows > 0){
                            while($row = $res->fetch_assoc()){
                                $status_val = htmlspecialchars($row['status']);
                                $status_class = (strtolower($status_val) == 'completed') ? 'completed' : 'pending';
                                ?>
                                <tr>
                                    <td>
                                        <span class="cust-name"><?= htmlspecialchars($row['customer_name']) ?></span>
                                        <span class="cust-meta">📞 <?= htmlspecialchars($row['phone']) ?> &nbsp;|&nbsp; 📍 <?= htmlspecialchars($row['address']) ?></span>
                                    </td>
                                    <td><span class="order-items"><?= htmlspecialchars($row['ordered_items']) ?></span></td>
                                    <td><span class="price-tag">Rs <?= number_format($row['total_amount']) ?></span></td>
                                    <td><span class="status <?= $status_class ?>"><?= $status_val ?></span></td>
                                    <td>
                                        <div class="action-container">
                                            <?php if(strtolower($status_val) != 'completed'): ?>
                                                <a href="orders.php?complete=<?= $row['id'] ?>" class="action-link complete-btn">
                                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                    Mark Ready
                                                </a>
                                            <?php endif; ?>

                                            <a href="orders.php?edit=<?= $row['id'] ?>" class="action-link edit-btn">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                Edit
                                            </a>

                                            <a href="orders.php?delete=<?= $row['id'] ?>" class="action-link delete-btn" onclick="return confirm('Remove this order entry?')">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                Remove
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color:#a0aec0; font-weight:600;'>No orders found in the system pipeline.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding: 40px; color:#e53e3e; font-weight:600;'>
                                <b>Notice:</b> The <code>orders</code> table does not exist yet. Create it inside phpMyAdmin to use this component!
                              </td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if($edit_order !== null): ?>
    <div class="modal-overlay">
        <div class="modal-card">
            <h2>Modify Order #<?= $edit_order['id'] ?></h2>
            <form method="POST" action="orders.php">
                <input type="hidden" name="order_id" value="<?= $edit_order['id'] ?>">
                
                <div class="form-group">
                    <label>Customer Name</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($edit_order['customer_name']) ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($edit_order['phone']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Delivery Address</label>
                    <textarea name="address" rows="2" required><?= htmlspecialchars($edit_order['address']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Ordered Items</label>
                    <textarea name="ordered_items" rows="2" required><?= htmlspecialchars($edit_order['ordered_items']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Total Price (Rs)</label>
                    <input type="number" name="total_amount" value="<?= htmlspecialchars($edit_order['total_amount']) ?>" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Order Status</label>
                    <select name="status">
                        <option value="Pending" <?= (strtolower($edit_order['status']) == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="Completed" <?= (strtolower($edit_order['status']) == 'completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>

                <div class="modal-buttons">
                    <a href="orders.php" class="modal-btn btn-cancel">Cancel</a>
                    <button type="submit" name="update_order" class="modal-btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>