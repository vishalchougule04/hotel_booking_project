<?php
session_start();
// Explicitly point to login.php inside the admin folder
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

// Handle Delete Action
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    
    // 1. Get image paths to delete files from server folder
    $img_res = $conn->query("SELECT cover_image, image_url FROM rooms WHERE id = $del_id");
    if ($img_row = $img_res->fetch_assoc()) {
        // Delete cover image
        $cover_path = '../' . $img_row['cover_image'];
        if (!empty($img_row['cover_image']) && file_exists($cover_path)) {
            unlink($cover_path);
        }
        
        // Delete gallery images
        if (!empty($img_row['image_url'])) {
            $gallery_images = explode(',', $img_row['image_url']);
            foreach ($gallery_images as $img) {
                $img_path = '../' . trim($img);
                if (file_exists($img_path)) {
                    unlink($img_path);
                }
            }
        }
    }

    // 2. Delete associated bookings first to prevent foreign key constraint crash
    $conn->query("DELETE FROM bookings WHERE room_id = $del_id");

    // 3. Delete record from rooms table
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    
    // 4. Clean redirect back to self
    header("Location: manage_hotels.php?msg=deleted");
    exit();
}

$hotels = $conn->query("SELECT * FROM rooms ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hotels - Admin Panel</title>
    <link rel="stylesheet" href="manage_hotels.css">
    <style>
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-right: 5px;
        }
        .btn-edit { background-color: #0284c7; color: white; }
        .btn-edit:hover { background-color: #0369a1; }
        .btn-delete { background-color: #dc2626; color: white; }
        .btn-delete:hover { background-color: #b91c1c; }
    </style>
</head>
<body>
    <header>
        <div class="logo-area">Admin Dashboard</div>
        <nav>
            <a href="dashboard.php">Bookings</a>
            <a href="manage_hotels.php" class="active">Manage Hotels</a>
            <a href="manage_users.php">Users</a>
            <a href="add_room.php">Add Room</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Listed Hotels & Properties</h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div style="background:#22c55e; color:white; padding:10px; border-radius:4px; margin-bottom:15px; text-align:center;">
                Hotel listing and its bookings removed successfully!
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Cover Image</th>
                    <th>Hotel Name</th>
                    <th>City</th>
                    <th>Type</th>
                    <th>Price (₹)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $hotels->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><img src="../<?php echo htmlspecialchars($row['cover_image'] ?? $row['image_url']); ?>" alt="" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px;"></td>
                    <td><strong><?php echo htmlspecialchars($row['room_name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['city']); ?></td>
                    <td><?php echo htmlspecialchars($row['room_type']); ?></td>
                    <td>₹<?php echo number_format($row['price'], 0); ?></td>
                    <td>
                        <a href="edit_room.php?id=<?php echo $row['id']; ?>" class="action-btn btn-edit">Edit</a>
                        <a href="manage_hotels.php?delete_id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to remove this hotel and its bookings?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>