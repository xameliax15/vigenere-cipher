<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Edit Post";
$post = [
    'id' => '',
    'title' => '',
    'content' => '',
    'category_id' => '',
    'status' => 'draft'
];

// Fetch categories for dropdown
$categories = $conn->query("SELECT * FROM categories ORDER BY name");

// Handle post edit
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $post = $result->fetch_assoc();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category_id'] ?: null;
    $status = $_POST['status'];
    $slug = strtolower(str_replace(' ', '-', $title));
    
    if (isset($_POST['id'])) {
        // Update existing post
        $stmt = $conn->prepare("UPDATE posts SET title = ?, content = ?, category_id = ?, status = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("ssisss", $title, $content, $category_id, $status, $slug, $_POST['id']);
    } else {
        // Create new post
        $stmt = $conn->prepare("INSERT INTO posts (title, content, category_id, status, slug, author_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssisss", $title, $content, $category_id, $status, $slug, $_SESSION['user_id']);
    }
    
    if ($stmt->execute()) {
        header('Location: posts.php');
        exit();
    }
}

include 'includes/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            <?php echo $post['id'] ? 'Edit Post' : 'Add New Post'; ?>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <form method="post" action="post-edit.php<?php echo $post['id'] ? '?id=' . $post['id'] : ''; ?>">
                        <?php if ($post['id']): ?>
                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="box-body">
                            <div class="form-group">
                                <label for="title">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select class="form-control" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php while ($category = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $post['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="content">Content</label>
                                <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="draft" <?php echo $post['status'] == 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $post['status'] == 'published' ? 'selected' : ''; ?>>Published</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save Post</button>
                            <a href="posts.php" class="btn btn-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 