<?php
session_start();
include("../db.php");

if(!isset($_SESSION['admin'])){
    header("Location: admin_login.php");
    exit();
}

// Handle Subscriber Deletion
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    // Secure deletion using prepared statements
    $stmt = $conn->prepare("DELETE FROM newsletter WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Subscriber Email Update Form Submission
if(isset($_POST['update_subscriber'])){
    $id    = intval($_POST['subscriber_id']);
    $email = $_POST['email'];

    $stmt = $conn->prepare("UPDATE newsletter SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $stmt->close();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch single subscriber details if "edit" parameter is triggered
$edit_sub = null;
if(isset($_GET['edit'])){
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM newsletter WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows > 0){
        $edit_sub = $res->fetch_assoc();
    }
    $stmt->close();
}

// Fetch all subscribers for the table view
$res = $conn->query("SELECT * FROM newsletter ORDER BY id DESC");
$total = $res ? $res->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AnnSatu - Subscribers</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR (matches dashboard.php) */
        .sidebar {
            width: 260px;
            background: #111;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            color: #ff3b3b;
            text-align: center;
            margin-bottom: 30px;
            font-size: 24px;
            padding: 0 20px;
        }

        .sidebar ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .sidebar ul li a {
            display: block;
            color: #ddd;
            text-decoration: none;
            padding: 15px 25px;
            font-weight: 600;
            transition: 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background: rgba(255, 59, 59, 0.1);
            color: #ff3b3b;
            border-left-color: #ff3b3b;
        }

        .sidebar ul li a.logout-link {
            margin-top: auto;
            color: #aaa;
        }

        .sidebar ul li a.logout-link:hover {
            background: rgba(255, 59, 59, 0.1);
            color: #ff3b3b;
        }

        /* MAIN CONTENT */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .header-bar h1 {
            font-size: 26px;
            color: #333;
        }

        .header-bar .icon-badge {
            font-size: 24px;
        }

        /* STAT CARD */
        .stat-strip {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 20px;
            border-left: 4px solid #ff3b3b;
            max-width: 300px;
        }

        .stat-strip .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,59,59,0.1);
            color: #ff3b3b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .stat-strip .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #222;
        }

        .stat-strip .stat-label {
            font-size: 13px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* TABLE CARD */
        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        thead tr {
            background: linear-gradient(45deg, #ff3b3b, #ff6a00);
        }

        th {
            padding: 16px 24px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 16px 24px;
            border-bottom: 1px solid #f0f0f0;
            color: #444;
            font-size: 14px;
        }

        tbody tr {
            transition: 0.2s;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover {
            background: #fff8f7;
        }

        .id-badge {
            display: inline-block;
            background: #f1f3f5;
            color: #666;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .email-cell {
            font-weight: 500;
            color: #222;
        }

        /* ACTION LINKS CONFIGURATION */
        .action-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            border-radius: 20px;
            transition: 0.2s;
        }

        .btn-edit {
            background: #007bff;
            color: #ffffff !important;
        }

        .btn-edit:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #dc3545;
            color: #ffffff !important;
        }

        .btn-delete:hover {
            background: #b8232f;
            transform: translateY(-1px);
        }

        .btn-action:active {
            transform: scale(0.96);
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }

        .empty-state .emoji {
            font-size: 40px;
            display: block;
            margin-bottom: 10px;
        }

        /* MODAL OVERLAY STYLING */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .modal-card {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            border: 1px solid #e0e0e0;
        }
        .modal-card h2 { font-size: 20px; color: #222; margin-bottom: 18px; font-weight: 700; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #555; margin-bottom: 6px; letter-spacing: 0.5px; }
        .form-group input { width: 100%; padding: 11px 14px; border: 1px solid #e0e0e0; background: #f8f9fa; border-radius: 8px; font-size: 14px; color: #333; outline: none; }
        .form-group input:focus { border-color: #007bff; background: white; }
        .modal-buttons { display: flex; gap: 12px; margin-top: 20px; }
        .modal-btn { flex: 1; padding: 12px; text-align: center; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; cursor: pointer; border: none; }
        .btn-save { background: #007bff; color: white; }
        .btn-save:hover { background: #0056b3; }
        .btn-cancel { background: #f1f3f5; color: #555; text-align: center; }
        .btn-cancel:hover { background: #e9ecef; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>AnnSatu Admin</h2>
        <ul>
            <li><a href="dashboard.php">📊 Dashboard Overview</a></li>
            <li><a href="food.php">🍔 Manage Foods</a></li>
            <li><a href="orders.php">📦 Manage Orders</a></li>
            <li><a href="subscribers.php" class="active">🔥 Subscribers</a></li>
            <li><a href="admin_messages.php">📩 Contact Messages</a></li>
            <li><a href="logout.php" class="logout-link" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="header-bar">
            <h1>Subscribers</h1>
            <span class="icon-badge">🔥</span>
        </div>

        <div class="stat-strip">
            <div class="stat-icon">👥</div>
            <div>
                <div class="stat-number"><?= $total ?></div>
                <div class="stat-label">Total Subscribers</div>
            </div>
        </div>

        <div class="table-card">
            <?php if ($total > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th style="width: 240px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td><span class="id-badge">#<?= $row['id'] ?></span></td>
                        <td class="email-cell"><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <div class="action-group">
                                <a href="?edit=<?= $row['id'] ?>" class="btn-action btn-edit">
                                    📝 Edit
                                </a>
                                <a href="?delete=<?= $row['id'] ?>"
                                   class="btn-action btn-delete"
                                   onclick="return confirm('Are you sure you want to delete this subscriber?');">
                                    🗑 Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
            <?php else: ?>
                <div class="empty-state">
                    <span class="emoji">📭</span>
                    No subscribers yet.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <?php if($edit_sub !== null): ?>
    <div class="modal-overlay">
        <div class="modal-card">
            <h2>Modify Subscriber #<?= $edit_sub['id'] ?></h2>
            <form method="POST" action="subscribers.php">
                <input type="hidden" name="subscriber_id" value="<?= $edit_sub['id'] ?>">
                
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($edit_sub['email']) ?>" required>
                </div>

                <div class="modal-buttons">
                    <a href="subscribers.php" class="modal-btn btn-cancel">Cancel</a>
                    <button type="submit" name="update_subscriber" class="modal-btn btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

</body>
</html>