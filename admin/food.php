<?php
session_start();
include("../db.php");

// Simple authentication guard check
if(!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}

// ---- CSRF token setup ----
if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

function verify_csrf() {
    if(!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        die("Invalid or missing CSRF token.");
    }
}

// ---- Safe image upload helper ----
// Returns ['ok' => bool, 'path' => string, 'error' => string]
function handle_image_upload($file, $target_dir) {
    $allowed_ext = ["jpg", "jpeg", "png", "webp", "avif"];
    $allowed_mime = ["image/jpeg", "image/png", "image/webp", "image/avif"];
    $max_size = 5 * 1024 * 1024; // 5MB

    if($file['size'] > $max_size) {
        return ["ok" => false, "path" => "", "error" => "Image exceeds the 5MB size limit."];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed_ext)) {
        return ["ok" => false, "path" => "", "error" => "Invalid file type. Only JPG, JPEG, PNG, WEBP & AVIF files allowed."];
    }

    // Verify the file is actually an image (extension alone can't be trusted)
    $mime = mime_content_type($file['tmp_name']);
    if($mime === false || !in_array($mime, $allowed_mime)) {
        return ["ok" => false, "path" => "", "error" => "Uploaded file is not a valid image."];
    }

    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Fully random filename avoids collisions and strips any attacker-controlled name
    $file_name = bin2hex(random_bytes(16)) . "." . $ext;
    $target_file = $target_dir . $file_name;

    if(!move_uploaded_file($file['tmp_name'], $target_file)) {
        return ["ok" => false, "path" => "", "error" => "Failed to save the uploaded image file safely to folder storage."];
    }

    return ["ok" => true, "path" => "uploads/" . $file_name, "error" => ""];
}

// Normalize + validate a category string (free text, but keep it sane)
function clean_category($raw) {
    $cat = trim($raw ?? "");
    if($cat === "") return "Uncategorized";
    if(mb_strlen($cat) > 100) $cat = mb_substr($cat, 0, 100);
    return $cat;
}

$message = "";
$message_type = "";

// 1. ADD FOOD WITH FILE UPLOAD & CATEGORY
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])){
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $category = clean_category($_POST['category'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $image_path = "";

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload = handle_image_upload($_FILES['image'], "../uploads/");
        if($upload['ok']) {
            $image_path = $upload['path'];
        } else {
            $message = $upload['error'];
            $message_type = "error";
        }
    }

    if(empty($message) && $title !== '' && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO food (title, description, price, image, category, is_available) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $title, $description, $price, $image_path, $category, $is_available);

        if($stmt->execute()){
            $message = "New food item integrated successfully!";
            $message_type = "success";
        } else {
            $message = "Database processing error while adding the item.";
            $message_type = "error";
        }
        $stmt->close();
    } elseif(empty($message)) {
        $message = "Please insert an accurate food title and a price greater than zero.";
        $message_type = "error";
    }
}

// 2. UPDATE/EDIT FOOD ASSET PROCESSING
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])){
    verify_csrf();

    $id = intval($_POST['edit_id']);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $category = clean_category($_POST['category'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;

    // Fetch existing details to preserve current thumbnail if no replacement is uploaded
    $curr_stmt = $conn->prepare("SELECT image FROM food WHERE id = ?");
    $curr_stmt->bind_param("i", $id);
    $curr_stmt->execute();
    $curr_res = $curr_stmt->get_result()->fetch_assoc();
    $image_path = $curr_res ? $curr_res['image'] : "";
    $curr_stmt->close();

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload = handle_image_upload($_FILES['image'], "../uploads/");
        if($upload['ok']) {
            // Delete old file safely if it exists
            if(!empty($image_path) && file_exists("../" . $image_path)) {
                unlink("../" . $image_path);
            }
            $image_path = $upload['path'];
        } else {
            $message = $upload['error'];
            $message_type = "error";
        }
    }

    if(empty($message) && $title !== '' && $price > 0) {
        $stmt = $conn->prepare("UPDATE food SET title=?, description=?, price=?, image=?, category=?, is_available=? WHERE id=?");
        $stmt->bind_param("ssdssii", $title, $description, $price, $image_path, $category, $is_available, $id);

        if($stmt->execute()) {
            $message = "Food configuration altered successfully!";
            $message_type = "success";
        } else {
            $message = "Database update error while saving changes.";
            $message_type = "error";
        }
        $stmt->close();
    } elseif(empty($message)) {
        $message = "Please insert an accurate food title and a price greater than zero.";
        $message_type = "error";
    }
}

// 3. SECURE DELETE FOOD ACTION (POST + CSRF, not a bare GET link)
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])){
    verify_csrf();

    $id = intval($_POST['delete']);

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

    header("Location: food.php");
    exit();
}

// ---- Pull existing categories from the data itself for the datalist ----
$existing_categories = [];
$cat_res = $conn->query("SELECT DISTINCT category FROM food WHERE category IS NOT NULL AND category <> '' ORDER BY category ASC");
if($cat_res) {
    while($c = $cat_res->fetch_assoc()) {
        $existing_categories[] = $c['category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Foods - AnnSatu Admin</title>
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

        .main-content {
            margin-left: 270px;
            flex: 1;
            padding: 40px 50px;
        }

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

        .col-3 { grid-column: span 3; }
        .col-4 { grid-column: span 4; }
        .col-6 { grid-column: span 6; }
        .col-12 { grid-column: span 12; }

        .form-group label {
            font-size: 13px;
            font-weight: 700;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 13px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            outline: none;
            font-size: 15px;
            color: #2d3748;
            background: #f8fafc;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-group textarea { resize: vertical; min-height: 48px; }

        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
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

        .checkbox-group {
            flex-direction: row;
            align-items: center;
            gap: 10px !important;
            padding-bottom: 13px;
        }
        .checkbox-group input { width: auto; }

        .form-grid button, .modal-footer button {
            grid-column: span 3;
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
        .food-desc {
            display: block;
            font-size: 12px;
            color: #a0aec0;
            font-weight: 500;
            max-width: 260px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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

        .badge.available { background: rgba(46, 125, 50, 0.08); color: #2e7d32; }
        .badge.unavailable { background: rgba(160, 174, 192, 0.15); color: #718096; }

        .price-tag {
            color: #2d3748;
            font-weight: 700;
            font-size: 16px;
        }

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
            border: none;
            font-family: inherit;
        }

        .edit-btn {
            color: #3182ce;
            background: rgba(49, 130, 206, 0.06);
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
        .delete-form { display: inline; }

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
            max-height: 90vh;
            overflow-y: auto;
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
        <h2>AnnSatu</h2>
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
            <div class="alert <?= htmlspecialchars($message_type) ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="form-box">
            <h3>Add New Food Item</h3>
            <form method="POST" enctype="multipart/form-data" class="form-grid">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <div class="form-group col-4">
                    <label>Food Item Title</label>
                    <input type="text" name="title" placeholder="e.g., Crunchy Momo" required maxlength="255">
                </div>
                <div class="form-group col-3">
                    <label>Price (Rs)</label>
                    <input type="number" name="price" placeholder="Price" required min="0.01" step="0.01">
                </div>
                <div class="form-group col-3">
                    <label>Menu Category</label>
                    <input type="text" name="category" list="category-options" placeholder="e.g., Momo" maxlength="100">
                    <datalist id="category-options">
                        <?php foreach($existing_categories as $ec): ?>
                            <option value="<?= htmlspecialchars($ec) ?>">
                        <?php endforeach; ?>
                    </datalist>
                </div>
                <div class="form-group col-2 checkbox-group">
                    <input type="checkbox" name="is_available" id="add_available" checked>
                    <label for="add_available" style="text-transform:none;">Available</label>
                </div>
                <div class="form-group col-6">
                    <label>Description</label>
                    <textarea name="description" placeholder="Short description (optional)"></textarea>
                </div>
                <div class="form-group col-4">
                    <label>Product Thumbnail</label>
                    <input type="file" name="image" accept="image/*">
                </div>
                <button name="add" class="col-2">Add Food</button>
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
                        <th>Status</th>
                        <th style="width: 220px; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $res = $conn->query("SELECT * FROM food ORDER BY id DESC");
                    if($res && $res->num_rows > 0) {
                        while($row = $res->fetch_assoc()){
                            $img_src = !empty($row['image']) ? "../" . htmlspecialchars($row['image']) : "https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=150";
                            $cat_display = !empty($row['category']) ? $row['category'] : "Uncategorized";
                            $available = !empty($row['is_available']);
                            // Data passed to JS is JSON-encoded, avoiding fragile addslashes()+htmlspecialchars() escaping
                            $edit_payload = htmlspecialchars(json_encode([
                                "id" => $row['id'],
                                "title" => $row['title'],
                                "description" => $row['description'],
                                "price" => $row['price'],
                                "category" => $cat_display,
                                "is_available" => (int)$available
                            ]), ENT_QUOTES);
                            ?>
                            <tr>
                                <td><img src="<?= $img_src ?>" class="food-thumb" alt="Product View"></td>
                                <td>
                                    <span class="food-name"><?= htmlspecialchars($row['title']) ?></span>
                                    <?php if(!empty($row['description'])): ?>
                                        <span class="food-desc"><?= htmlspecialchars($row['description']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge"><?= htmlspecialchars($cat_display) ?></span></td>
                                <td><span class="price-tag">Rs <?= number_format((float)$row['price'], 2) ?></span></td>
                                <td>
                                    <span class="badge <?= $available ? 'available' : 'unavailable' ?>">
                                        <?= $available ? 'Available' : 'Unavailable' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <button type="button" class="action-btn edit-btn"
                                                data-food="<?= $edit_payload ?>"
                                                onclick="openEditModal(this)">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            Edit
                                        </button>
                                        <form method="POST" class="delete-form" onsubmit="return confirm('Permanently remove this food item asset from listing catalog?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="delete" value="<?= (int)$row['id'] ?>">
                                            <button type="submit" class="action-btn delete-btn">
                                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding:40px; color:#a0aec0; font-weight:600;'>No food menu assets registered inside the database catalog.</td></tr>";
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
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="edit_id" id="modal_edit_id">

                <div class="form-group">
                    <label>Food Item Title</label>
                    <input type="text" name="title" id="modal_edit_title" required maxlength="255">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="modal_edit_description" placeholder="Short description (optional)"></textarea>
                </div>

                <div class="form-group">
                    <label>Price (Rs)</label>
                    <input type="number" name="price" id="modal_edit_price" required min="0.01" step="0.01">
                </div>

                <div class="form-group">
                    <label>Menu Category</label>
                    <input type="text" name="category" id="modal_edit_category" list="category-options" maxlength="100">
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_available" id="modal_edit_available">
                    <label for="modal_edit_available" style="text-transform:none;">Available</label>
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

        function openEditModal(btn) {
            const food = JSON.parse(btn.dataset.food);
            document.getElementById('modal_edit_id').value = food.id;
            document.getElementById('modal_edit_title').value = food.title;
            document.getElementById('modal_edit_description').value = food.description || '';
            document.getElementById('modal_edit_price').value = food.price;
            document.getElementById('modal_edit_category').value = food.category;
            document.getElementById('modal_edit_available').checked = !!food.is_available;

            overlay.classList.add('active');
        }

        function closeEditModal() {
            overlay.classList.remove('active');
        }

        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeEditModal();
        });
    </script>
</body>
</html>