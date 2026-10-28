<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

// Handle Delete User Action (Optional)
if (isset($_GET['delete_id'])) {
    $del_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php?msg=deleted");
    exit();
}

$users = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin Panel</title>
    <link rel="stylesheet" href="manage_users.css">
    <style>
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .btn-delete { background-color: #dc2626; color: white; }
        .btn-delete:hover { background-color: #b91c1c; }
    </style>
</head>
<body>
    <header>
        <div class="logo-area">Admin Dashboard</div>
        <nav>
            <a href="dashboard.php">Bookings</a>
            <a href="manage_hotels.php">Manage Hotels</a>
            <a href="manage_users.php" class="active">Users</a>
            <a href="add_room.php">Add Room</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Registered Users</h2>
        
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div style="background:#22c55e; color:white; padding:10px; border-radius:4px; margin-bottom:15px; text-align:center;">
                User account removed successfully!
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email Address</th>
                    <th>Registered On</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users->num_rows > 0): ?>
                    <?php while($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a href="manage_users.php?delete_id=<?php echo $row['id']; ?>" class="action-btn btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #666;">No registered users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>