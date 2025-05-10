<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['avatar'] = isset($user['avatar']) ? $user['avatar'] : null;
            header('Location: index.php');
            exit();
        }
    }
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SimpleCMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(120deg, #1976d2 0%, #64b5f6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }
        .container-login {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(25, 118, 210, 0.15);
            display: flex;
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }
        .login-form {
            flex: 1;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .login-form h2 {
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 8px;
        }
        .login-form p {
            color: #888;
            margin-bottom: 24px;
        }
        .login-form label {
            font-weight: 500;
            color: #1976d2;
            margin-bottom: 6px;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #b3c6e0;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 16px;
            background: #f7faff;
            transition: border 0.2s;
        }
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
        }
        .login-form .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .login-form .actions a {
            color: #1976d2;
            text-decoration: none;
            font-size: 14px;
        }
        .login-form .actions a:hover {
            text-decoration: underline;
        }
        .login-form button[type="submit"] {
            width: 100%;
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .login-form button[type="submit"]:hover {
            background: #1565c0;
        }
        .login-form .or {
            text-align: center;
            color: #aaa;
            margin: 18px 0 10px 0;
            font-size: 14px;
        }
        .login-form .social-login {
            display: flex;
            gap: 12px;
            justify-content: center;
        }
        .login-form .social-login button {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 10px 0;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s;
        }
        .login-form .social-login .google {
            background: #fff;
            color: #1976d2;
            border: 1px solid #e0e0e0;
        }
        .login-form .social-login .google:hover {
            background: #e3f2fd;
        }
        .login-form .social-login .facebook {
            background: #1877f3;
            color: #fff;
        }
        .login-form .social-login .facebook:hover {
            background: #1565c0;
        }
        .login-form .signup-link {
            text-align: center;
            margin-top: 18px;
            color: #888;
            font-size: 15px;
        }
        .login-form .signup-link a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        .login-form .signup-link a:hover {
            text-decoration: underline;
        }
        .login-form .error {
            color: #d32f2f;
            background: #ffeaea;
            border: 1px solid #ffcdd2;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .login-form .success {
            color: #388e3c;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .login-illustration {
            flex: 1;
            background: linear-gradient(120deg, #2196f3 0%, #e3f2fd 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
        }
        .login-illustration img {
            max-width: 90%;
            height: auto;
        }
        @media (max-width: 900px) {
            .container-login {
                flex-direction: column;
                max-width: 480px;
            }
            .login-illustration {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="container-login">
    <div class="login-form">
        <h2>Login</h2>
        <p>Don't have an account yet? <a href="register.php">Sign Up</a></p>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="username">Email Address</label>
            <input type="text" id="username" name="username" placeholder="you@example.com" required>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter 6 character or more" required>
            <div class="actions">
                <div>
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="font-size:14px;color:#888;">Remember me</label>
                </div>
                <a href="#">Forgot Password?</a>
            </div>
            <button type="submit">LOGIN</button>
        </form>
        <div class="or">or login with</div>
        <div class="social-login">
            <button class="google"><i class="fab fa-google"></i> Google</button>
            <button class="facebook"><i class="fab fa-facebook-f"></i> Facebook</button>
        </div>
        <div class="signup-link">
            or <a href="register.php">Sign Up</a> if you don't have an account
        </div>
    </div>
    <div class="login-illustration">
        <!-- Ganti dengan gambar lokal -->
        <img src="asset/login-illustration.jpg" alt="Login Illustration">
    </div>
</div>
</body>
</html> 