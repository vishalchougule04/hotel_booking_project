<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}
include '../db.php';

$success = '';
$error = '';

if (isset($_POST['add_room'])) {
    $hotel_name = trim($_POST['room_name']);
    $city = trim($_POST['city']);
    $distance = trim($_POST['distance_from_centre']);
    
    // Combine checked checkboxes into a comma-separated string
    $amenities_array = $_POST['amenities'] ?? [];
    $amenities = implode(', ', $amenities_array);

    // 1. Main Cover Image Upload
    $image_url = '';
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === 0) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        $file_name = time() . '_' . basename($_FILES['room_image']['name']);
        if (move_uploaded_file($_FILES['room_image']['tmp_name'], $target_dir . $file_name)) {
            $image_url = 'uploads/' . $file_name;
        }
    }

    // 2. Multiple Gallery Images Upload
    $gallery_paths = [];
    if (isset($_FILES['gallery_images'])) {
        $target_dir = "../uploads/";
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['gallery_images']['error'][$key] === 0) {
                $g_name = time() . '_' . rand(1000,9999) . '_' . basename($_FILES['gallery_images']['name'][$key]);
                if (move_uploaded_file($tmp_name, $target_dir . $g_name)) {
                    $gallery_paths[] = 'uploads/' . $g_name;
                }
            }
        }
    }
    $gallery_json = !empty($gallery_paths) ? json_encode($gallery_paths) : null;

    $room_types = $_POST['room_type'] ?? [];
    $prices = $_POST['price'] ?? [];
    $capacities = $_POST['capacity'] ?? [];

    if (!empty($hotel_name) && !empty($image_url) && count($room_types) > 0) {
        $stmt = $conn->prepare("INSERT INTO rooms (room_name, room_type, city, price, distance_from_centre, cover_image, gallery_images, max_occupancy, amenities) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $inserted = true;
        for ($i = 0; $i < count($room_types); $i++) {
            $r_type = trim($room_types[$i]);
            $r_price = trim($prices[$i]);
            $r_cap = trim($capacities[$i]);

            if (!empty($r_type) && !empty($r_price)) {
                $stmt->bind_param("sssdsssss", $hotel_name, $r_type, $city, $r_price, $distance, $image_url, $gallery_json, $r_cap, $amenities);
                if (!$stmt->execute()) { 
                    $inserted = false; 
                }
            }
        }
        $stmt->close();

        if ($inserted) {
            $success = "Hotel listing added successfully with selectable amenities!";
        } else {
            $error = "Failed to save some room types.";
        }
    } else {
        $error = "Please fill in all required fields and upload a cover image.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Hotel Listing - StayNest Admin</title>
    <link rel="stylesheet" href="dashboard1.css">
</head>
<body>
    <header>
        <div class="logo-area">🏨 StayNest Admin</div>
        <nav>
            <a href="dashboard.php">Bookings</a>
            <a href="manage_hotels.php">Manage Hotels</a>
            <a href="manage_users.php">Users</a>
            <a href="add_room.php" class="active">Add Room</a>
            <a href="logout.php" style="color: #fca5a5;">Logout</a>
        </nav>
    </header>

    <div class="container" style="max-width: 800px; margin: 40px auto; background: white; padding: 35px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
        <h2 style="margin-bottom: 25px; color: #0f172a;">Add New Hotel Listing</h2>

        <?php if($success): ?>
            <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-weight: 600;"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="add_room.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Hotel Name:</label>
                <input type="text" name="room_name" placeholder="Enter hotel name" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">City / Location:</label>
                    <input type="text" name="city" placeholder="e.g. Kolhapur" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                </div>
                <div style="flex: 1;">
                    <label style="display: block; font-weight: 600; margin-bottom: 6px;">Distance from Centre:</label>
                    <input type="text" name="distance_from_centre" placeholder="e.g. 1.2 km from downtown" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px;">
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;">

            <h3>Room Types & Pricing Options</h3>
            <div id="room-container" style="display: flex; flex-direction: column; gap: 15px;">
                <div class="room-row" style="display: flex; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="flex: 2;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Room Type:</label>
                        <input type="text" name="room_type[]" placeholder="e.g. Deluxe Double Room" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Price (₹):</label>
                        <input type="number" step="0.01" name="price[]" placeholder="1999" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                    <div style="flex: 1;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Capacity:</label>
                        <input type="text" name="capacity[]" placeholder="2 adults" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>
            </div>

            <button type="button" id="add-room-btn" onclick="addRoomRow()" style="background: #e2e8f0; border: none; padding: 10px; border-radius: 6px; font-weight: 600; cursor: pointer; color: #334155;">+ Add Another Room Type</button>

            <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 10px 0;">

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Main Cover Image (Shown on Homepage Card):</label>
                <input type="file" name="room_image" accept="image/*" required style="padding: 10px; background: #f8fafc; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 6px;">Gallery Images (Multiple photos for details page grid):</label>
                <input type="file" name="gallery_images[]" accept="image/*" multiple style="padding: 10px; background: #f8fafc; width: 100%; border: 1px solid #cbd5e1; border-radius: 8px;">
            </div>

            <!-- Selectable Amenities Checkboxes -->
            <div>
                <label style="display: block; font-weight: 600; margin-bottom: 10px;">Select Amenities:</label>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Free Wi-Fi"> Free Wi-Fi
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Swimming Pool"> Swimming Pool
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Air Conditioning"> Air Conditioning
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Free Parking"> Free Parking
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Restaurant"> Restaurant
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Room Service"> Room Service
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Family Rooms"> Family Rooms
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; cursor: pointer;">
                        <input type="checkbox" name="amenities[]" value="Tea/Coffee Maker"> Tea/Coffee Maker
                    </label>
                </div>
            </div>

            <button type="submit" name="add_room" style="background: #0071c2; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 1rem; font-weight: 700; cursor: pointer; margin-top: 10px;">Save Hotel Listing</button>
        </form>
    </div>

    <script>
        function addRoomRow() {
            const container = document.getElementById('room-container');
            const row = document.createElement('div');
            row.className = 'room-row';
            row.style.cssText = 'display: flex; gap: 15px; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0;';
            row.innerHTML = `
                <div style="flex: 2;">
                    <input type="text" name="room_type[]" placeholder="e.g. Standard Room Non-AC" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div style="flex: 1;">
                    <input type="number" step="0.01" name="price[]" placeholder="1499" required style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
                <div style="flex: 1;">
                    <input type="text" name="capacity[]" placeholder="2 adults" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>