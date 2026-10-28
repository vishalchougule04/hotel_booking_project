<?php
session_start();
include 'db.php';
$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $name, $hashed_password);
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_name'] = $name;
            header("Location: index.php");
            exit();
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "No account found with this email!";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - StayNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="user_login.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">Welcome Back</h1>
                <p class="form-subtitle">Login to your StayNest account to continue.</p>
            </div>

            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form class="login-form" action="user_login.php" method="POST">
                <div class="input-group">
                    <label for="email" class="input-label">Email Address</label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon left"></i>
                        <input type="email" id="email" name="email" class="input-field" placeholder="Enter your email address" required>
                    </div>
                </div>

                <div class="input-group">
                    <label for="password" class="input-label">Password</label>
                    <div class="input-wrapper password-wrapper">
                        <i class="fas fa-lock input-icon left"></i>
                        <input type="password" id="password" name="password" class="input-field" placeholder="Enter your password" required>
                        <i class="fas fa-eye-slash input-icon right" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" name="login" class="login-btn">
                    <span>Login</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="or-separator">
                <span>OR</span>
            </div>

            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
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