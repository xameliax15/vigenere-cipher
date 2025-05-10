<?php
session_start();
require_once 'config/database.php';

$new_password = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Generate new random password
        $new_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $hashed_password, $user['id']);
        if ($stmt->execute()) {
            $success = "Password baru Anda: <strong>" . htmlspecialchars($new_password) . "</strong> Silakan login dan segera ganti password Anda.";
        } else {
            $error = "Gagal mengupdate password. Silakan coba lagi.";
        }
    } else {
        $error = "Email tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - SimpleCMS</title>
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
        .forgot-box {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(25, 118, 210, 0.15);
            max-width: 400px;
            width: 100%;
            padding: 40px 32px;
        }
        h2 {
            color: #1976d2;
            font-weight: 700;
            margin-bottom: 18px;
        }
        label {
            color: #1976d2;
            font-weight: 500;
        }
        input[type="email"] {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #b3c6e0;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 16px;
            background: #f7faff;
            transition: border 0.2s;
        }
        input[type="email"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
        }
        button[type="submit"] {
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
        button[type="submit"]:hover {
            background: #1565c0;
        }
        .error {
            color: #d32f2f;
            background: #ffeaea;
            border: 1px solid #ffcdd2;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        .success {
            color: #388e3c;
            background: #e8f5e9;
            border: 1px solid #a5d6a7;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 18px;
            font-size: 15px;
        }
        a {
            color: #1976d2;
            text-decoration: none;
            font-size: 15px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="forgot-box">
    <h2>Forgot Password</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="forgot_password.php" method="post">
        <label for="email">Enter your email address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required>
        <button type="submit">Reset Password</button>
    </form>
    <div style="text-align:center; margin-top:18px;">
        <a href="login.php">Back to Login</a>
    </div>
</div>
</body>
</html> 