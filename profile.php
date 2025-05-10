<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $email = $_POST['email'];
        $bio = $_POST['bio'];
        
        // Handle profile photo upload
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            if (!in_array($_FILES['avatar']['type'], $allowed_types)) {
                $error_message = "Only JPG, PNG and GIF files are allowed.";
            } elseif ($_FILES['avatar']['size'] > $max_size) {
                $error_message = "File size must be less than 5MB.";
            } else {
                $upload_dir = 'uploads/avatars/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    // Delete old avatar if exists
                    $sql = "SELECT avatar FROM users WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $old_avatar = $result->fetch_assoc()['avatar'];
                    
                    if ($old_avatar && file_exists($old_avatar)) {
                        unlink($old_avatar);
                    }
                    
                    // Update avatar in database
                    $sql = "UPDATE users SET avatar = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $upload_path, $user_id);
                    $stmt->execute();
                } else {
                    $error_message = "Error uploading file.";
                }
            }
        }
        
        // Update other profile information
        if (empty($error_message)) {
            $sql = "UPDATE users SET first_name = ?, last_name = ?, email = ?, bio = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $first_name, $last_name, $email, $bio, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Error updating profile.";
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $sql = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("si", $hashed_password, $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Password changed successfully!";
                    } else {
                        $error_message = "Error changing password.";
                    }
                } else {
                    $error_message = "New password must be at least 6 characters long.";
                }
            } else {
                $error_message = "New passwords do not match.";
            }
        } else {
            $error_message = "Current password is incorrect.";
        }
    }
}

// Get user information
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profile - Simple CMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: #f4f8fb;
            margin: 0;
        }
        .topbar {
            background: #1565c0;
            color: #fff;
            padding: 0 32px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .topbar .title {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .topbar .user-info img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #fff;
        }
        .sidebar {
            width: 240px;
            background: #1976d2;
            color: #fff;
            min-height: 100vh;
            float: left;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 24px;
        }
        .sidebar .brand {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .sidebar .user-panel {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 24px;
        }
        .sidebar .user-panel img {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            margin-bottom: 8px;
        }
        .sidebar .user-panel .username {
            color: #fff;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .sidebar .menu {
            width: 100%;
        }
        .sidebar .menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .sidebar .menu li {
            width: 100%;
        }
        .sidebar .menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #fff;
            text-decoration: none;
            padding: 12px 32px;
            font-size: 1rem;
            border-left: 4px solid transparent;
            transition: background 0.2s, border 0.2s;
        }
        .sidebar .menu a.active, .sidebar .menu a:hover {
            background: #1565c0;
            border-left: 4px solid #fff;
        }
        .main-content {
            margin-left: 240px;
            padding: 32px 40px 24px 40px;
        }
        .breadcrumb {
            font-size: 0.95rem;
            color: #888;
            margin-bottom: 18px;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 24px;
        }
        .card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.10);
            padding: 28px 24px 20px 24px;
            margin-bottom: 32px;
        }
        .btn-primary, .btn-success, .btn-danger, .btn-info {
            background: #1976d2;
            color: #fff;
            border: none;
            font-weight: 500;
            border-radius: 8px;
            transition: background 0.2s;
            padding: 8px 18px;
        }
        .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-info:hover {
            background: #1565c0;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e3f2fd;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: #1976d2;
            outline: none;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        .profile-image-container {
            text-align: center;
            margin-bottom: 24px;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #1976d2;
            margin-bottom: 16px;
        }
        .profile-username {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 8px;
        }
        .nav-pills {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        .nav-pills .nav-link {
            color: #666;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .nav-pills .nav-link.active {
            background: #1976d2;
            color: #fff;
        }
        .tab-content {
            padding: 20px 0;
        }
        @media (max-width: 900px) {
            .main-content {
                margin-left: 0;
                padding: 16px 8px;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
                float: none;
                flex-direction: row;
                justify-content: space-between;
                padding: 8px 0;
            }
        }
    </style>
</head>
<body>
    <div class="topbar">
        <div class="title">Simple CMS</div>
        <div class="user-info">
            <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" alt="Avatar">
            <span><?php echo htmlspecialchars($user['username']); ?></span>
            <a href="logout.php" style="color:#fff;margin-left:12px;"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
    <div class="sidebar">
        <div class="brand">Simple CMS</div>
        <div class="user-panel">
            <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" alt="Avatar">
            <div class="username"><?php echo htmlspecialchars($user['username']); ?></div>
        </div>
        <nav class="menu">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="posts.php"><i class="fas fa-file-alt"></i> Posts</a></li>
                <li><a href="pages.php"><i class="fas fa-file"></i> Pages</a></li>
                <li><a href="media.php"><i class="fas fa-images"></i> Media</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="breadcrumb">
            <i class="fas fa-home"></i> Home &nbsp;>&nbsp; Profile
        </div>
        <div class="page-title">Profile</div>

        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="profile-image-container">
                        <img class="profile-image" src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" alt="Profile Image">
                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Photo
                            </button>
                        </form>
                    </div>
                    <h3 class="profile-username text-center"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h3>
                    <p class="text-center" style="color:#666;"><?php echo htmlspecialchars($user['role']); ?></p>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="nav-pills">
                        <a href="#profile" class="nav-link active" data-toggle="tab">Profile</a>
                        <a href="#password" class="nav-link" data-toggle="tab">Change Password</a>
                    </div>
                    <div class="tab-content">
                        <div class="tab-pane active" id="profile">
                            <form action="profile.php" method="post">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="bio">Bio</label>
                                    <textarea class="form-control" id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                                </div>
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                            </form>
                        </div>

                        <div class="tab-pane" id="password">
                            <form action="profile.php" method="post">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Handle tab switching
            $('.nav-link').click(function(e) {
                e.preventDefault();
                $('.nav-link').removeClass('active');
                $(this).addClass('active');
                $('.tab-pane').removeClass('active');
                $($(this).attr('href')).addClass('active');
            });
        });
    </script>
</body>
</html> 