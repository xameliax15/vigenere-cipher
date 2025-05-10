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
$resultUser = $stmt->get_result();
$user = $resultUser->fetch_assoc();

// Handle page deletion
if (isset($_POST['delete_page'])) {
    $page_id = $_POST['page_id'];
    $sql = "DELETE FROM pages WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $page_id);
    $stmt->execute();
}

// Get all pages
$sql = "SELECT * FROM pages ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pages - Simple CMS</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #1976d2;
            color: #fff;
            border: none;
        }
        tr {
            background: #fff;
            transition: box-shadow 0.2s;
        }
        tr:hover {
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.08);
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
                <li><a href="posts.php"><i class="fas fa-file-alt"></i> Posts</a></li>
                <li><a href="pages.php" class="active"><i class="fas fa-file"></i> Pages</a></li>
                <li><a href="media.php"><i class="fas fa-images"></i> Media</a></li>
                <?php if ($user['role'] === 'admin'): ?>
                <li><a href="users.php"><i class="fas fa-users"></i> Users</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="main-content">
        <div class="breadcrumb">
            <i class="fas fa-home"></i> Home &nbsp;>&nbsp; Pages
        </div>
        <div class="page-title">Pages</div>
        <div class="card">
            <a href="add_page.php" class="btn btn-primary" style="margin-bottom:18px;"><i class="fas fa-plus"></i> Add Page</a>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <a href="edit_page.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="pages.php" method="post" style="display:inline;">
                                <input type="hidden" name="page_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_page" class="btn btn-danger btn-sm" onclick="return confirm('Delete this page?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php exit; ?> 