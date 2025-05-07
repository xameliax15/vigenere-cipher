<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Posts";
include 'includes/header.php';

// Handle post deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: posts.php');
    exit();
}

// Fetch all posts
$query = "SELECT p.*, c.name as category_name, u.username as author_name 
          FROM posts p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN users u ON p.author_id = u.id 
          ORDER BY p.created_at DESC";
$result = $conn->query($query);
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Posts
            <small>Manage your posts</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">All Posts</h3>
                        <div class="box-tools">
                            <a href="post-edit.php" class="btn btn-primary btn-sm">Add New Post</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Author</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($post = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                    <td><?php echo htmlspecialchars($post['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                    <td>
                                        <span class="label label-<?php echo $post['status'] == 'published' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($post['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                                    <td>
                                        <a href="post-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-info btn-xs">Edit</a>
                                        <a href="posts.php?delete=<?php echo $post['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this post?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 