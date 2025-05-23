<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$user = [
    'id' => '',
    'username' => '',
    'email' => '',
    'role' => 'author'
];

// If editing existing user
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    // Check if username or email already exists
    $sql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $username, $email, $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Username or email already exists";
    } else {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            // Update existing user
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $_POST['id']);
            } else {
                $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $username, $email, $role, $_POST['id']);
            }
        } else {
            // Create new user
            if (empty($password)) {
                $error = "Password is required for new users";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (username, email, role, password) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $username, $email, $role, $hashed_password);
            }
        }
        
        if (!isset($error) && $stmt->execute()) {
            header('Location: users.php');
            exit();
        } else if (!isset($error)) {
            $error = "Error saving user: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit User - SimpleCMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:400,500,700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(120deg, #e3f2fd 0%, #bbdefb 100%);
            min-height: 100vh;
        }
        .main-header, .main-footer {
            background: #1976d2 !important;
            color: #fff !important;
            border: none;
        }
        .main-sidebar {
            background: #1565c0 !important;
        }
        .brand-link {
            background: #1976d2 !important;
            color: #fff !important;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .sidebar .user-panel {
            background: #e3f2fd;
            border-radius: 12px;
            margin-bottom: 18px;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .sidebar .user-panel .image img {
            border-radius: 50%;
            border: 2px solid #1976d2;
        }
        .sidebar .nav-link.active, .sidebar .nav-link:hover {
            background: #1976d2 !important;
            color: #fff !important;
            border-radius: 8px;
        }
        .sidebar .nav-link {
            color: #e3f2fd !important;
            font-weight: 500;
            margin-bottom: 4px;
            transition: background 0.2s;
        }
        .content-wrapper {
            background: transparent;
        }
        .card, .btn, .form-control, .custom-file-input, .custom-file-label {
            border-radius: 18px !important;
            box-shadow: 0 4px 24px rgba(25, 118, 210, 0.10);
            border: none;
        }
        .btn-primary, .btn-success, .btn-danger, .btn-info {
            background: #1976d2;
            color: #fff;
            border: none;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-primary:hover, .btn-success:hover, .btn-danger:hover, .btn-info:hover {
            background: #1565c0;
        }
        .main-footer {
            background: #1976d2 !important;
            color: #fff !important;
            border-top: none;
            border-radius: 0 0 18px 18px;
        }
        h1, h3, h2 {
            color: #1976d2;
            font-weight: 700;
        }
        label {
            color: #1976d2;
            font-weight: 500;
        }
        @media (max-width: 900px) {
            .content-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="index.php" class="brand-link">
            <span class="brand-text font-weight-light">Simple CMS</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="posts.php" class="nav-link">
                            <i class="nav-icon fas fa-file-alt"></i>
                            <p>Posts</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="pages.php" class="nav-link">
                            <i class="nav-icon fas fa-file"></i>
                            <p>Pages</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="media.php" class="nav-link">
                            <i class="nav-icon fas fa-images"></i>
                            <p>Media</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link active">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?php echo empty($user['id']) ? 'New User' : 'Edit User'; ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="user_edit.php" method="post">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password<?php echo empty($user['id']) ? '' : ' (leave blank to keep current)'; ?></label>
                                <input type="password" class="form-control" id="password" name="password" <?php echo empty($user['id']) ? 'required' : ''; ?>>
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role">
                                    <option value="author" <?php echo $user['role'] === 'author' ? 'selected' : ''; ?>>Author</option>
                                    <option value="editor" <?php echo $user['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Save User</button>
                                <a href="users.php" class="btn btn-default">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-block">
            <b>Version</b> 1.0.0
        </div>
        <strong>Copyright &copy; 2024 <a href="#">Simple CMS</a>.</strong> All rights reserved.
    </footer>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html> 