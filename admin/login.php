<?php
session_start();
$error = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Default hardcoded admin credentials
    if ($username === 'admin' && $password === 'password123') {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid admin username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - StayNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login1.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">Admin Portal</h1>
                <p class="form-subtitle">Sign in to manage StayNest listings & bookings.</p>
            </div>

            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="login-form" action="login.php" method="POST">
                <div class="input-group">
                    <label for="username" class="input-label">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user-shield input-icon left"></i>
                        <input type="text" id="username" name="username" class="input-field" placeholder="Enter admin username" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <i class="fas fa-lock input-icon left"></i>
                        <input type="password" id="password" name="password" class="input-field" placeholder="Enter admin password" required>
                        <i class="fas fa-eye-slash input-icon right" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" name="login" class="login-btn">
                    <span>Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <p class="back-home">
                <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Homepage</a>
            </p>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>