<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

// Fetch bookings along with room details (including room_type and total_price)
$query = "SELECT bookings.*, rooms.room_name, rooms.city, rooms.room_type 
          FROM bookings 
          JOIN rooms ON bookings.room_id = rooms.id 
          ORDER BY bookings.id DESC";
$bookings = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bookings</title>
    <link rel="stylesheet" href="dashboard1.css">
</head>
<body>
    <header>
        <div class="logo-area">🏨 StayNest Admin</div>
        <nav>
            <a href="dashboard.php" class="active">Bookings</a>
            <a href="manage_hotels.php">Manage Hotels</a>
            <a href="manage_users.php">Users</a>
            <a href="add_room.php">Add Room</a>
            <a href="logout.php" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h2>Customer Reservations</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Guest Name</th>
                    <th>Email</th>
                    <th>Hotel / Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Price</th>
                    <th>Booked On</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings && $bookings->num_rows > 0): ?>
                    <?php while($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['guest_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['guest_email']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['room_name']); ?> <br>
                            <span style="font-size:0.8rem; color:#0284c7; font-weight:600;"><?php echo htmlspecialchars($row['room_type'] ?? ''); ?></span>
                            <span style="font-size:0.8rem; color:#64748b;">(<?php echo htmlspecialchars($row['city']); ?>)</span>
                        </td>
                        <td><?php echo $row['check_in']; ?></td>
                        <td><?php echo $row['check_out']; ?></td>
                        <td><strong style="color: #16a34a;">₹<?php echo number_format($row['total_price'] ?? 0, 2); ?></strong></td>
                        <td><?php echo $row['created_at'] ?? 'N/A'; ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">No customer bookings found yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>