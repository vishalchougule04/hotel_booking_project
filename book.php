<?php
session_start();
include 'db.php';

$room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;

// 1. Get the hotel name from the clicked ID
$stmt = $conn->prepare("SELECT room_name FROM rooms WHERE id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$hotel_res = $stmt->get_result();

if ($hotel_res->num_rows === 0) {
    echo "<h2 style='text-align:center; margin-top:50px; font-family:Inter,sans-serif;'>Hotel not found! <a href='index.php'>Go back home</a></h2>";
    exit();
}
$hotel_data = $hotel_res->fetch_assoc();
$hotel_name = $hotel_data['room_name'];
$stmt->close();

// 2. Fetch ALL room types belonging to this hotel name
$rooms_stmt = $conn->prepare("SELECT * FROM rooms WHERE room_name = ?");
$rooms_stmt->bind_param("s", $hotel_name);
$rooms_stmt->execute();
$rooms_result = $rooms_stmt->get_result();
$first_room = $rooms_result->fetch_assoc(); 

// 3. Fetch gallery images from any row of this hotel if populated
$gallery_images = [];
$gallery_stmt = $conn->prepare("SELECT gallery_images FROM rooms WHERE room_name = ? AND gallery_images IS NOT NULL AND gallery_images != '' LIMIT 1");
$gallery_stmt->bind_param("s", $hotel_name);
$gallery_stmt->execute();
$gal_res = $gallery_stmt->get_result();
if ($gal_row = $gal_res->fetch_assoc()) {
    $gallery_images = json_decode($gal_row['gallery_images'], true);
}
$gallery_stmt->close();

// Reset statement execution to loop through all room options in the table
$rooms_stmt->execute();
$all_rooms = $rooms_stmt->get_result();

$success_msg = '';
$error_msg = '';

// Handle Booking Submission
if (isset($_POST['book_room'])) {
    $guest_name = trim($_POST['full_name']);
    $guest_email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    // Fallback to URL room_id if hidden input wasn't set
    $selected_room_id = !empty($_POST['room_id']) ? intval($_POST['room_id']) : $room_id;
    
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];

    if (!empty($guest_name) && !empty($guest_email) && !empty($check_in) && !empty($check_out) && $selected_room_id > 0) {
        
        // Fetch the exact price of the specifically selected room type
        $price_stmt = $conn->prepare("SELECT price FROM rooms WHERE id = ?");
        $price_stmt->bind_param("i", $selected_room_id);
        $price_stmt->execute();
        $price_res = $price_stmt->get_result();
        $room_price_data = $price_res->fetch_assoc();
        $total_price = $room_price_data['price'] ?? 0.00;
        $price_stmt->close();

        // Insert booking along with total_price
        $book_stmt = $conn->prepare("INSERT INTO bookings (room_id, guest_name, guest_email, phone, check_in, check_out, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $book_stmt->bind_param("isssssd", $selected_room_id, $guest_name, $guest_email, $phone, $check_in, $check_out, $total_price);
        
        if ($book_stmt->execute()) {
            $success_msg = "Reservation confirmed successfully!";
        } else {
            $error_msg = "Failed to complete reservation.";
        }
        $book_stmt->close();
    } else {
        $error_msg = "Please select a room option and fill in all required booking fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($hotel_name); ?> - StayNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="book.css">
</head>
<body>

    <header>
        <a href="index.php" class="logo">🏨 StayNest</a>
        <nav>
            <a href="index.php">Home</a>
            <?php if(isset($_SESSION['admin'])): ?>
                <a href="admin/dashboard.php" style="color: #38bdf8;">Admin Panel</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        
        <?php if($success_msg): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;"><?php echo $success_msg; ?></div>
        <?php endif; ?>
        <?php if($error_msg): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: 600;"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <!-- Hotel Title & Info -->
        <div style="margin-bottom: 20px;">
            <div style="color: #f59e0b; margin-bottom: 4px;">
                <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
            </div>
            <h1 style="font-size: 2.2rem; color: #0f172a; font-weight: 800;"><?php echo htmlspecialchars($hotel_name); ?></h1>
            <p style="color: #64748b; font-size: 0.95rem; margin-top: 4px;">
                <i class="fas fa-map-marker-alt" style="color: #ef4444;"></i> <?php echo htmlspecialchars($first_room['city']); ?> &bull; <?php echo htmlspecialchars($first_room['distance_from_centre'] ?? '1.2 km from downtown'); ?>
            </p>
        </div>

        <!-- Multi-Image Grid Layout -->
        <div class="hotel-photo-grid">
            <?php if (!empty($first_room['cover_image'])): ?>
                <img src="<?php echo htmlspecialchars($first_room['cover_image']); ?>" alt="Cover Image" class="main-grid-img">
            <?php endif; ?>
            <?php if (!empty($gallery_images)): ?>
                <?php foreach(array_slice($gallery_images, 0, 4) as $g_img): ?>
                    <img src="<?php echo htmlspecialchars($g_img); ?>" alt="Gallery Image">
                <?php endforeach; ?>
            <?php else: ?>
                <img src="<?php echo htmlspecialchars($first_room['cover_image']); ?>" alt="Gallery">
                <img src="<?php echo htmlspecialchars($first_room['cover_image']); ?>" alt="Gallery">
                <img src="<?php echo htmlspecialchars($first_room['cover_image']); ?>" alt="Gallery">
                <img src="<?php echo htmlspecialchars($first_room['cover_image']); ?>" alt="Gallery">
            <?php endif; ?>
        </div>

        <!-- Availability Table -->
        <h2 style="font-size: 1.5rem; margin-bottom: 15px; color: #0f172a;">Availability & Room Options</h2>
        
        <table class="availability-table">
            <thead>
                <tr>
                    <th>Room type</th>
                    <th>Today's price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($room = $all_rooms->fetch_assoc()): ?>
                <tr>
                    <td>
                        <strong style="color: #003580; font-size: 1.1rem;"><?php echo htmlspecialchars($room['room_type']); ?></strong><br>
                        <span style="font-size: 0.85rem; color: #475569;"><i class="fas fa-user-friends"></i> Sleeps: <?php echo htmlspecialchars($room['max_occupancy'] ?? '2 adults'); ?></span><br>
                        <span style="font-size: 0.85rem; color: #475569;"><i class="fas fa-bed"></i> Bed: <?php echo htmlspecialchars($room['bed_type'] ?? '1 large double bed'); ?></span>
                    </td>
                    <td>
                        <strong style="font-size: 1.2rem; color: #0f172a;">₹<?php echo number_format($room['price'], 2); ?></strong><br>
                        <span style="font-size: 0.75rem; color: #64748b;">+ taxes and charges</span>
                    </td>
                    <td>
                        <button type="button" class="reserve-btn" onclick="document.getElementById('selected_room_id').value = '<?php echo $room['id']; ?>'; document.getElementById('booking-form-section').scrollIntoView({behavior: 'smooth'});">I'll reserve</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Reservation Form Card -->
        <div id="booking-form-section" class="reservation-card">
            <h2 style="font-size: 1.4rem; color: #0f172a; margin-bottom: 20px; font-weight: 700;">Complete Your Reservation</h2>
            
            <form action="book.php?room_id=<?php echo $room_id; ?>" method="POST">
                <input type="hidden" name="room_id" id="selected_room_id" value="<?php echo $room_id; ?>" required>

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" placeholder="10-digit mobile number" required>
                    </div>
                    <div class="form-group" style="flex-direction: row; gap: 10px;">
                        <div style="flex: 1;">
                            <label>Check-in Date</label>
                            <input type="date" name="check_in" required>
                        </div>
                        <div style="flex: 1;">
                            <label>Check-out Date</label>
                            <input type="date" name="check_out" required>
                        </div>
                    </div>
                </div>

                <button type="submit" name="book_room" class="btn-confirm">Confirm Booking</button>
            </form>
        </div>

    </div>
</body>
</html>