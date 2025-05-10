<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Update session with user data
$_SESSION['avatar'] = $user['avatar'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CMS Dashboard</title>
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
            /* background: #e3f2fd; */
            background: transparent;
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
        .card, .small-box {
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(25, 118, 210, 0.10);
            border: none;
        }
        .small-box {
            background: #fff;
            color: #1976d2;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .small-box:hover {
            box-shadow: 0 8px 32px rgba(25, 118, 210, 0.18);
            transform: translateY(-2px) scale(1.03);
        }
        .small-box .icon {
            color: #1976d2;
            opacity: 0.2;
        }
        .small-box-footer {
            color: #1976d2 !important;
            font-weight: 500;
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
        @media (max-width: 900px) {
            .content-header h1 {
                font-size: 1.5rem;
            }
        }
        .sidebar .user-panel .info a {
            color: #fff !important;
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
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <img src="<?php echo !empty($user['avatar']) ? $user['avatar'] : 'https://via.placeholder.com/150'; ?>" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="profile.php" class="d-block"><?php echo htmlspecialchars($user['username']); ?></a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link active">
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
                    <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="users.php" class="nav-link">
                            <i class="nav-icon fas fa-users"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <?php endif; ?>
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
                        <h1 class="m-0">Dashboard</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Small boxes (Stat box) -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <?php
                                $sql = "SELECT COUNT(*) as count FROM posts";
                                $result = $conn->query($sql);
                                $posts_count = $result->fetch_assoc()['count'];
                                ?>
                                <h3><?php echo $posts_count; ?></h3>
                                <p>Posts</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <a href="posts.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <?php
                                $sql = "SELECT COUNT(*) as count FROM pages";
                                $result = $conn->query($sql);
                                $pages_count = $result->fetch_assoc()['count'];
                                ?>
                                <h3><?php echo $pages_count; ?></h3>
                                <p>Pages</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <a href="pages.php" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                        </div>
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