<?php
session_start();
include 'db.php';

// Handle search query if submitted, grouped by room_name to prevent duplicate cards
$search_query = $_GET['destination'] ?? '';
if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT MIN(id) as id, room_name, city, MIN(price) as price, cover_image, image_url, distance_from_centre FROM rooms WHERE city LIKE ? OR room_name LIKE ? GROUP BY room_name");
    $searchTerm = "%" . $search_query . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT MIN(id) as id, room_name, city, MIN(price) as price, cover_image, image_url, distance_from_centre FROM rooms GROUP BY room_name");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StayNest - Find Your Perfect Stay</title>
    <link rel="stylesheet" href="indexstyle.css">
</head>
<body>
    <header>
        <div class="logo-area">
            🏨 StayNest <span style="font-size:0.8rem; color:#666; font-weight:normal;">Find Your Perfect Stay</span>
        </div>
        <nav>
            <a href="index.php" class="active">Home</a>
            <?php if(isset($_SESSION['user_name'])): ?>
                <span style="font-weight:600; color:#1a73e8;">👤 Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="user_logout.php" style="color:#dc2626;">Logout</a>
            <?php else: ?>
                <a href="user_login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
            <a href="admin/login.php">Admin Panel</a>
        </nav>
    </header>

    <!-- Hero Section with Search Bar -->
    <div class="hero-banner">
        <div>
            <h1>Find the right place to stay</h1>
            <p>Discover amazing hotels, resorts and homestays at the best prices.</p>
        </div>
        <form action="index.php" method="GET" class="search-container">
            <div class="search-box">
                <label>Where do you want to go?</label>
                <input type="text" name="destination" placeholder="Enter city or hotel name" value="<?php echo htmlspecialchars($search_query); ?>" required>
            </div>
            <div class="search-box">
                <label>Check in</label>
                <input type="date" name="check_in" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="search-box">
                <label>Check out</label>
                <input type="date" name="check_out" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>
            <button type="submit" class="search-btn">Search</button>
        </form>
    </div>

    <!-- Hotel Results Grid -->
    <div class="container">
        <h2 style="margin-bottom: 25px;"><?php echo !empty($search_query) ? "Search Results for '" . htmlspecialchars($search_query) . "'" : "Featured Properties"; ?></h2>
        
        <div class="room-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="room-card">
                        <!-- Uses cover_image for the homepage card display -->
                        <img src="<?php echo htmlspecialchars($row['cover_image'] ?? $row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['room_name']); ?>">
                        <div class="card-content">
                            <div class="card-header">
                                <h3><?php echo htmlspecialchars($row['room_name']); ?></h3>
                                <div class="stars">★★★★☆</div>
                            </div>
                            <div class="distance"><?php echo htmlspecialchars($row['distance_from_centre']); ?></div>
                            <div class="rating-badge">
                                <strong>4.8/5</strong> With honours <span style="color:#666; font-weight:normal; font-size:0.8rem;">(32 reviews)</span>
                            </div>
                            <div class="card-footer">
                                <div class="price">
                                    ₹<?php echo number_format($row['price'], 0); ?>
                                    <span>Per night</span>
                                </div>
                                <a href="book.php?room_id=<?php echo $row['id']; ?>" class="btn-book">Book Now</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No hotels found matching your search criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>