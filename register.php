<?php
include 'db.php';
$error = '';
$success = '';

if (isset($_POST['register'])) {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "This email is already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        if ($stmt->execute()) {
            $success = "Registration successful! You can now <a href='user_login.php' style='color:#0056D2; font-weight:bold;'>Login</a>.";
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Your Account - StayNest</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="register.css">
</head>
<body>

    <div class="main-wrapper">
        <div class="form-card">
            <div class="form-header">
                <h1 class="form-title">Create Your Account</h1>
                <p class="form-subtitle">Join StayNest and start your journey to unforgettable stays.</p>
            </div>

            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="alert"><?php echo $success; ?></div>
            <?php endif; ?>

            <form class="registration-form" action="register.php" method="POST">
                <div class="input-group">
                    <label for="full-name" class="input-label">Full Name</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon left"></i>
                        <input type="text" id="full-name" name="full_name" class="input-field" placeholder="Enter your full name" required>
                    </div>
                </div>

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
                        <input type="password" id="password" name="password" class="input-field" placeholder="Create a password" required>
                        <i class="fas fa-eye-slash input-icon right" id="togglePassword"></i>
                    </div>
                </div>

                <button type="submit" name="register" class="register-btn">
                    <span>Register</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="or-separator">
                <span>OR</span>
            </div>

            <p class="login-link">Already have an account? <a href="user_login.php">Login here</a></p>
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