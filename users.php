<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    // Don't allow deleting the last admin
    $sql = "SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin'";
    $result = $conn->query($sql);
    $admin_count = $result->fetch_assoc()['admin_count'];
    
    $sql = "SELECT role FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($admin_count > 1 || $user['role'] !== 'admin') {
        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
    }
}

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - SimpleCMS</title>
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
        .card, .table, .btn {
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
        .table thead th {
            background: #1976d2;
            color: #fff;
            border: none;
        }
        .table tbody tr {
            background: #fff;
            transition: box-shadow 0.2s;
        }
        .table tbody tr:hover {
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.08);
        }
        @media (max-width: 900px) {
            .content-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
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
                        <h1 class="m-0">User Management</h1>
                    </div>
                    <div class="col-sm-6">
                        <a href="user_edit.php" class="btn btn-primary float-right">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <table id="users-table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'editor' ? 'warning' : 'info'); ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                        <form action="users.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
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
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
$(document).ready(function() {
    $('#users-table').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
    });
});
</script>
</body>
</html> 