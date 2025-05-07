<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Media";
include 'includes/header.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $filename = $file['name'];
    $filetype = $file['type'];
    $filesize = $file['size'];
    
    // Create uploads directory if it doesn't exist
    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }
    
    $filepath = 'uploads/' . time() . '_' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        $stmt = $conn->prepare("INSERT INTO media (filename, filepath, filetype, filesize, uploaded_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $filename, $filepath, $filetype, $filesize, $_SESSION['user_id']);
        $stmt->execute();
        header('Location: media.php');
        exit();
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Get file path before deletion
    $stmt = $conn->prepare("SELECT filepath FROM media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $media = $result->fetch_assoc();
    
    // Delete from database
    $stmt = $conn->prepare("DELETE FROM media WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Delete file from server
    if (file_exists($media['filepath'])) {
        unlink($media['filepath']);
    }
    
    header('Location: media.php');
    exit();
}

// Fetch all media files
$media = $conn->query("SELECT m.*, u.username as uploaded_by_name 
                      FROM media m 
                      LEFT JOIN users u ON m.uploaded_by = u.id 
                      ORDER BY m.created_at DESC");
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Media
            <small>Manage your media files</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload New File</h3>
                    </div>
                    <form method="post" action="media.php" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="file">Choose File</label>
                                <input type="file" id="file" name="file" required>
                                <p class="help-block">Select a file to upload</p>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Media Library</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php while ($file = $media->fetch_assoc()): ?>
                            <div class="col-sm-2">
                                <div class="box box-widget">
                                    <div class="box-body text-center">
                                        <?php if (strpos($file['filetype'], 'image/') === 0): ?>
                                            <img src="<?php echo htmlspecialchars($file['filepath']); ?>" class="img-responsive" alt="<?php echo htmlspecialchars($file['filename']); ?>">
                                        <?php else: ?>
                                            <i class="fa fa-file fa-3x"></i>
                                        <?php endif; ?>
                                        <p class="text-muted"><?php echo htmlspecialchars($file['filename']); ?></p>
                                        <p class="text-muted"><?php echo number_format($file['filesize'] / 1024, 2); ?> KB</p>
                                        <p class="text-muted">Uploaded by: <?php echo htmlspecialchars($file['uploaded_by_name']); ?></p>
                                        <div class="btn-group">
                                            <a href="<?php echo htmlspecialchars($file['filepath']); ?>" class="btn btn-info btn-xs" target="_blank">View</a>
                                            <a href="media.php?delete=<?php echo $file['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this file?')">Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 