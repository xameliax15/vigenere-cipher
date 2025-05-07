<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Categories";
include 'includes/header.php';

// Handle category deletion
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header('Location: categories.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $slug = strtolower(str_replace(' ', '-', $name));
    
    if (isset($_POST['id'])) {
        // Update existing category
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $slug, $_POST['id']);
    } else {
        // Create new category
        $stmt = $conn->prepare("INSERT INTO categories (name, description, slug) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $description, $slug);
    }
    
    if ($stmt->execute()) {
        header('Location: categories.php');
        exit();
    }
}

// Fetch all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Categories
            <small>Manage your categories</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Add New Category</h3>
                    </div>
                    <form method="post" action="categories.php">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save Category</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">All Categories</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Slug</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td><?php echo htmlspecialchars($category['slug']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-xs" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">Edit</button>
                                        <a href="categories.php?delete=<?php echo $category['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
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

<script>
function editCategory(category) {
    document.getElementById('name').value = category.name;
    document.getElementById('description').value = category.description;
    
    // Add hidden input for category ID
    let idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = category.id;
    document.querySelector('form').appendChild(idInput);
    
    // Change button text
    document.querySelector('.box-footer button').textContent = 'Update Category';
}
</script>

<?php include 'includes/footer.php'; ?> 