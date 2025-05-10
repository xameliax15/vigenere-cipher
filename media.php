<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['media_file'])) {
    $file = $_FILES['media_file'];
    $filename = $file['name'];
    $filetype = $file['type'];
    $filesize = $file['size'];
    $upload_dir = 'uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $filepath = $upload_dir . time() . '_' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $sql = "INSERT INTO media (filename, filepath, filetype, filesize, uploaded_by) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $filename, $filepath, $filetype, $filesize, $_SESSION['user_id']);
        $stmt->execute();
    }
}

// Handle file deletion
if (isset($_POST['delete_media'])) {
    $media_id = $_POST['media_id'];
    
    // Get file path before deletion
    $sql = "SELECT filepath FROM media WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $media_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result->fetch_assoc();
    
    // Delete from database
    $sql = "DELETE FROM media WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $media_id);
    $stmt->execute();
    
    // Delete file from server
    if (file_exists($media['filepath'])) {
        unlink($media['filepath']);
    }
}

// Get all media files
$sql = "SELECT m.*, u.username as uploader_name 
        FROM media m 
        LEFT JOIN users u ON m.uploaded_by = u.id 
        ORDER BY m.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Media Management - CMS</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css">
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
                        <a href="media.php" class="nav-link active">
                            <i class="nav-icon fas fa-images"></i>
                            <p>Media</p>
                        </a>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
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
                        <h1 class="m-0">Media Management</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Upload Form -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Upload New Media</h3>
                    </div>
                    <div class="card-body">
                        <form action="media.php" method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="media_file">Choose File</label>
                                <input type="file" class="form-control-file" id="media_file" name="media_file" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>

                <!-- Media List -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Media Library</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($media = $result->fetch_assoc()): ?>
                            <div class="col-md-3 mb-4">
                                <div class="card">
                                    <?php if (strpos($media['filetype'], 'image/') === 0): ?>
                                    <img src="<?php echo htmlspecialchars($media['filepath']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($media['filename']); ?>">
                                    <?php else: ?>
                                    <div class="card-img-top bg-light text-center py-5">
                                        <i class="fas fa-file fa-3x"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($media['filename']); ?></h5>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Uploaded by: <?php echo htmlspecialchars($media['uploader_name']); ?><br>
                                                Size: <?php echo number_format($media['filesize'] / 1024, 2); ?> KB
                                            </small>
                                        </p>
                                        <div class="btn-group">
                                            <a href="<?php echo htmlspecialchars($media['filepath']); ?>" class="btn btn-sm btn-info" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <form action="media.php" method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                <input type="hidden" name="media_id" value="<?php echo $media['id']; ?>">
                                                <button type="submit" name="delete_media" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
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