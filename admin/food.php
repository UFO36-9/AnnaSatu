<?php
session_start();
include("../db.php");

// Simple authentication guard check
if(!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$message_type = "";

// 1. ADD FOOD WITH FILE UPLOAD & CATEGORY
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])){
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : "all"; 
    $image_path = ""; 

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = array("jpg", "jpeg", "png", "webp", "avif");
        if(in_array($image_file_type, $allowed_types)) {
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/" . $file_name;
            } else {
                $message = "Failed to save the uploaded image file safely to folder storage.";
                $message_type = "error";
            }
        } else {
            $message = "Invalid file type. Only JPG, JPEG, PNG, WEBP & AVIF files allowed.";
            $message_type = "error";
        }
    }

    if(empty($message) && !empty($name) && $price > 0) {
        $col_check = $conn->query("SHOW COLUMNS FROM food LIKE 'category'");
        
        if($col_check && $col_check->num_rows > 0) {
            $stmt = $conn->prepare("INSERT INTO food (name, price, image, category) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("siss", $name, $price, $image_path, $category);
        } else {
            $stmt = $conn->prepare("INSERT INTO food (name, price, image) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $name, $price, $image_path);
        }
        
        if($stmt->execute()){
            $message = "New food item integrated successfully!";
            $message_type = "success";
        } else {
            $message = "Database Processing Error: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    } elseif(empty($message)) {
        $message = "Please insert an accurate food description name and price range values.";
        $message_type = "error";
    }
}

// 2. UPDATE/EDIT FOOD ASSET PROCESSING
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])){
    $id = intval($_POST['edit_id']);
    $name = trim($_POST['name']);
    $price = intval($_POST['price']);
    $category = isset($_POST['category']) ? trim($_POST['category']) : "all";
    
    // Fetch existing details to preserve current thumbnail if no replacement is uploaded
    $curr_stmt = $conn->prepare("SELECT image FROM food WHERE id = ?");
    $curr_stmt->bind_param("i", $id);
    $curr_stmt->execute();
    $curr_res = $curr_stmt->get_result()->fetch_assoc();
    $image_path = $curr_res ? $curr_res['image'] : "";
    $curr_stmt->close();

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../uploads/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        $allowed_types = array("jpg", "jpeg", "png", "webp", "avif");
        if(in_array($image_file_type, $allowed_types)) {
            if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // Delete old file safely if it exists
                if(!empty($image_path) && file_exists("../" . $image_path)) {
                    unlink("../" . $image_path);
                }
                $image_path = "uploads/" . $file_name;
            }
        } else {
            $message = "Invalid image extension structure for update.";
            $message_type = "error";
        }
    }

    if(empty($message) && !empty($name) && $price > 0) {
        $col_check = $conn->query("SHOW COLUMNS FROM food LIKE 'category'");
        if($col_check && $col_check->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE food SET name=?, price=?, image=?, category=? WHERE id=?");
            $stmt->bind_param("sissi", $name, $price, $image_path, $category, $id);
        } else {
            $stmt = $conn->prepare("UPDATE food SET name=?, price=?, image=? WHERE id=?");
            $stmt->bind_param("sisi", $name, $price, $image_path, $id);
        }
        
        if($stmt->execute()) {
            $message = "Food configuration altered successfully!";
            $message_type = "success";
        } else {
            $message = "Database Update Error: " . $stmt->error;
            $message_type = "error";
        }
        $stmt->close();
    }
}

// 3. SECURE DELETE FOOD ACTION LOOP
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    
    $img_stmt = $conn->prepare("SELECT image FROM food WHERE id = ?");
    $img_stmt->bind_param("i", $id);
    $img_stmt->execute();
    $res = $img_stmt->get_result()->fetch_assoc();
    if($res && !empty($res['image']) && file_exists("../" . $res['image'])) {
        unlink("../" . $res['image']);
    }
    $img_stmt->close();

    $stmt = $conn->prepare("DELETE FROM food WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    
    echo "<script>window.location.href='food.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods - Foodmandu Admin</title>
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

        /* MAIN CONTENT AREA */
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

        /* NOTIFICATION ALERTS */
        .alert {
            padding: 16px 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 15px;
            animation: fadeIn 0.4s ease;
        }
        .alert.success { background: #dff0e3; color: #1e4620; border-left: 5px solid #2e7d32; }
        .alert.error { background: #fce8e6; color: #c53030; border-left: 5px solid #e53e3e; }

        /* FORM BOX */
        .form-box {
            background: white;
            padding: 35px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.02);
            margin-bottom: 40px;
            border: 1px solid #edf2f7;
        }

        .form-box h3 {
            margin-bottom: 25px;
            color: #1a202c;
            font-size: 20px;
            font-weight: 700;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
            align-items: flex-end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .col-4 { grid-column: span 4; }
        .col-2 { grid-column: span 2; }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 13px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            color: #2d3748;
            background: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-group input:focus, .form-group select:focus {
            border-color: #ff3b3b;
            background: white;
            box-shadow: 0 0 0 3px rgba(255, 59, 59, 0.1);
        }

        .form-group input[type="file"] {
            padding: 10px;
            background: transparent;
            border: 1px dashed #cbd5e0;
            cursor: pointer;
        }

        .form-grid button, .modal-footer button {
            grid-column: span 2;
            padding: 14px 24px;
            background: linear-gradient(135deg, #ff3b3b, #ff6a00);
            color: white;
            border: none;
            font-weight: 700;
            font-size: 15px;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(255, 59, 59, 0.2);
            height: 48px;
        }

        .form-grid button:hover, .modal-footer button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(255, 59, 59, 0.3);
        }

        /* DATA TABLE CARD CONTAINER */
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
            padding: 18px 24px;
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
        
        .food-thumb {
            width: 70px;
            height: 52px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            background: #edf2f7;
        }

        .food-name {
            font-weight: 700;
            color: #1a202c;
            font-size: 16px;
        }

        .badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 59, 59, 0.08);
            color: #ff3b3b;
        }

        .price-tag {
            color: #2d3748;
            font-weight: 700;
            font-size: 16px;
        }

        /* ACTION LINKS COMPONENT */
        .action-cell {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 13px;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .edit-btn {
            color: #3182ce;
            background: rgba(49, 130, 206, 0.06);
            border: none;
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

        /* PREMIUM DYNAMIC INTERFACE OVERLAY MODAL */
        .edit-modal-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 17, 21, 0.4);
            backdrop-filter: blur(4px);
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }
        .edit-modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .modal-card {
            background: white;
            width: 100%;
            max-width: 550px;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            transform: scale(0.9) translateY(15px);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .edit-modal-overlay.active .modal-card {
            transform: scale(1) translateY(0);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .modal-header h3 {
            font-size: 20px;
            color: #1a202c;
            font-weight: 700;
        }
        .close-modal {
            font-size: 24px;
            color: #a0aec0;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .close-modal:hover { color: #e53e3e; }
        .modal-form-stack {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .modal-footer {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }
        .cancel-btn {
            background: #edf2f7;
            color: #4a5568;
            border: none;
            padding: 14px 24px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        /* ANIMATIONS */
        .header-bar, .form-box, .table-container {
            animation: slideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .form-box { animation-delay: 0.1s; }
        .table-container { animation-delay: 0.2s; }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; } to { opacity: 1; }
        }

        /* RESPONSIVE STYLING */
        @media (max-width: 1200px) {
            .form-grid { display: flex; flex-direction: column; align-items: stretch; }
            .form-grid button { height: auto; }
        }
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
                <a href="food.php" class="active">
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
            <h1>Manage Menu Catalog</h1>
        </div>

        <?php if(!empty($message)): ?>
            <div class="alert <?= $message_type ?>"><?= $message ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h3>Add New Food Item</h3>
            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <div class="form-group col-4">
                    <label>Food Item Name</label>
                    <input type="text" name="name" placeholder="e.g., Crunchy Momo" required>
                </div>
                <div class="form-group col-2">
                    <label>Price (Rs)</label>
                    <input type="number" name="price" placeholder="Price" required min="1">
                </div>
                <div class="form-group col-2">
                    <label>Menu Category</label>
                    <select name="category">
                        <option value="momo">Momo</option>
                        <option value="pizza">Pizza</option>
                        <option value="burger">Burger</option>
                        <option value="drinks">Drinks</option>
                    </select>
                </div>
                <div class="form-group col-4">
                    <label>Product Thumbnail</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button name="add">Add Food</button>
            </form>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 120px;">Thumbnail</th>
                        <th>Food Item Name</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th style="width: 220px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM food ORDER BY id DESC");
                    if($res && $res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            $img_src = !empty($row['image']) ? "../" . $row['image'] : "https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=150";
                            $cat_display = isset($row['category']) ? htmlspecialchars($row['category']) : "all";
                            ?>
                            <tr>
                                <td><img src="<?= $img_src ?>" class="food-thumb" alt="Product View"></td>
                                <td><span class="food-name"><?= htmlspecialchars($row['name']) ?></span></td>
                                <td><span class="badge"><?= $cat_display ?></span></td>
                                <td><span class="price-tag">Rs <?= number_format($row['price']) ?></span></td>
                                <td>
                                    <div class="action-cell">
                                        <button class="action-btn edit-btn" 
                                                onclick="openEditModal(<?= $row['id'] ?>, '<?= addslashes(htmlspecialchars($row['name'])) ?>', <?= $row['price'] ?>, '<?= addslashes(htmlspecialchars($cat_display)) ?>')">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            Edit
                                        </button>
                                        <a href="food.php?delete=<?= $row['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Permanently remove this food item asset from listing catalog?')">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            Remove
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php 
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align:center; padding:40px; color:#a0aec0; font-weight:600;'>No food menu assets registered inside the database catalog.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="edit-modal-overlay" id="editModalOverlay">
        <div class="modal-card">
            <div class="modal-header">
                <h3>Edit Food Catalog Asset</h3>
                <button class="close-modal" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" class="modal-form-stack">
                <input type="hidden" name="edit_id" id="modal_edit_id">
                
                <div class="form-group">
                    <label>Food Item Name</label>
                    <input type="text" name="name" id="modal_edit_name" required>
                </div>
                
                <div class="form-group">
                    <label>Price (Rs)</label>
                    <input type="number" name="price" id="modal_edit_price" required min="1">
                </div>
                
                <div class="form-group">
                    <label>Menu Category</label>
                    <select name="category" id="modal_edit_category">
                        <option value="momo">Momo</option>
                        <option value="pizza">Pizza</option>
                        <option value="burger">Burger</option>
                        <option value="drinks">Drinks</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Replacement Thumbnail (Keep blank to retain original)</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="modal-footer">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="update">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const overlay = document.getElementById('editModalOverlay');
        
        function openEditModal(id, name, price, category) {
            document.getElementById('modal_edit_id').value = id;
            document.getElementById('modal_edit_name').value = name;
            document.getElementById('modal_edit_price').value = price;
            document.getElementById('modal_edit_category').value = category;
            
            overlay.classList.add('active');
        }

        function closeEditModal() {
            overlay.classList.remove('active');
        }

        // Close on overlay outside container tap background context
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeEditModal();
        });
    </script>
</body>
</html>