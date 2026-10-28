<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

$hotel_id = $_GET['id'] ?? 0;
$success = "";
$error = "";

// Fetch existing hotel data
$stmt = $conn->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$hotel = $stmt->get_result()->fetch_assoc();

if (!$hotel) {
    header("Location: manage_hotels.php");
    exit();
}

// Handle Update Submission
if (isset($_POST['update_hotel'])) {
    $room_name = $_POST['room_name'];
    $city = $_POST['city'];
    $room_type = $_POST['room_type'];
    $price = $_POST['price'];
    $capacity = $_POST['capacity'];
    $distance = $_POST['distance'];
    
    $facilities_array = $_POST['facilities'] ?? [];
    $facilities = implode(', ', $facilities_array);

    $db_image_path = $hotel['image_url']; // keep existing by default

    // Check if new image was uploaded
    if (isset($_FILES['hotel_image']) && $_FILES['hotel_image']['error'] === 0) {
        $file_tmp = $_FILES['hotel_image']['tmp_name'];
        $unique_image_name = time() . '_' . basename($_FILES['hotel_image']['name']);
        $upload_path = '../uploads/' . $unique_image_name;
        $db_image_path = 'uploads/' . $unique_image_name;

        if (getimagesize($file_tmp) !== false) {
            move_uploaded_file($file_tmp, $upload_path);
        }
    }

    $update_stmt = $conn->prepare("UPDATE rooms SET room_name = ?, city = ?, room_type = ?, price = ?, capacity = ?, distance_from_centre = ?, image_url = ?, facilities = ? WHERE id = ?");
    $update_stmt->bind_param("sssdidssi", $room_name, $city, $room_type, $price, $capacity, $distance, $db_image_path, $facilities, $hotel_id);
    
    if ($update_stmt->execute()) {
        $success = "Hotel details updated successfully!";
        // Refresh hotel data
        $stmt->execute();
        $hotel = $stmt->get_result()->fetch_assoc();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Hotel - Admin Panel</title>
    <link rel="stylesheet" href="edit_room.css">
</head>
<body>
    <header>
        <div class="logo-area">Admin Dashboard</div>
        <nav>
            <a href="dashboard.php">Bookings</a>
            <a href="manage_hotels.php">Manage Hotels</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container" style="margin-top: 40px; max-width: 700px;">
        <h2>Edit Hotel Listing</h2>
        
        <?php if($success): ?><div class="alert"><?php echo $success; ?></div><?php endif; ?>
        <?php if($error): ?><div class="alert" style="background:#dc2626;"><?php echo $error; ?></div><?php endif; ?>

        <form action="edit_room.php?id=<?php echo $hotel_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Hotel Name:</label>
                <input type="text" name="room_name" value="<?php echo htmlspecialchars($hotel['room_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>City / Location:</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($hotel['city']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Room Type:</label>
                <input type="text" name="room_type" value="<?php echo htmlspecialchars($hotel['room_type']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Price per Night (₹):</label>
                <input type="number" step="0.01" name="price" value="<?php echo $hotel['price']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Capacity (Guests):</label>
                <input type="number" name="capacity" value="<?php echo $hotel['capacity']; ?>" required>
            </div>
            
            <div class="form-group">
                <label>Distance from Centre:</label>
                <input type="text" name="distance" value="<?php echo htmlspecialchars($hotel['distance_from_centre']); ?>" required>
            </div>
            
            <div class="form-group">
                <label>Current Image:</label><br>
                <img src="../<?php echo $hotel['image_url']; ?>" alt="" style="width: 120px; height: 80px; object-fit: cover; border-radius: 6px; margin-bottom: 8px;">
            </div>

            <div class="form-group">
                <label>Upload New Image (Optional):</label>
                <input type="file" name="hotel_image" accept="image/*" style="padding: 6px; background: #fff;">
            </div>
            
            <div class="form-group">
                <label style="margin-bottom: 8px;">Most Popular Facilities:</label>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; background: #f8fafc; padding: 15px; border-radius: 6px; border: 1px solid #cbd5e1;">
                    <?php 
                    $current_facs = explode(', ', $hotel['facilities']);
                    $all_facs = ['Indoor swimming pool', 'Free parking', 'Family rooms', 'Free WiFi', 'Restaurants', 'Airport shuttle', 'Room service', 'Tea/coffee maker', 'Bar', 'Fabulous breakfast'];
                    foreach($all_facs as $f) {
                        $checked = in_array($f, $current_facs) ? 'checked' : '';
                        echo '<label style="display:flex; align-items:center; gap:8px; font-size:0.9rem; cursor:pointer;"><input type="checkbox" name="facilities[]" value="'.$f.'" '.$checked.' style="width:16px; height:16px;"> '.$f.'</label>';
                    }
                    ?>
                </div>
            </div>
            
            <button type="submit" name="update_hotel" class="btn">Update Listing</button>
        </form>
    </div>
</body>
</html>