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

// Ambil jumlah posts
$sql = "SELECT COUNT(*) as count FROM posts";
$result = $conn->query($sql);
$posts_count = $result->fetch_assoc()['count'];

// Ambil jumlah pages
$sql = "SELECT COUNT(*) as count FROM pages";
$result = $conn->query($sql);
$pages_count = $result->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simple CMS Dashboard</title>
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
        .dashboard-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1976d2;
            margin-bottom: 24px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(25, 118, 210, 0.10);
            padding: 28px 24px 20px 24px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: relative;
            min-height: 120px;
        }
        .stat-card .stat-icon {
            position: absolute;
            top: 18px;
            right: 18px;
            font-size: 2.2rem;
            opacity: 0.18;
        }
        .stat-card.posts { border-left: 6px solid #1976d2; }
        .stat-card.pages { border-left: 6px solid #43a047; }
        .stat-card.media { border-left: 6px solid #fbc02d; }
        .stat-card.users { border-left: 6px solid #e53935; }
        .stat-label {
            font-size: 1.1rem;
            color: #888;
            margin-bottom: 6px;
        }
        .stat-value {
            font-size: 2.1rem;
            font-weight: 700;
            color: #1976d2;
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
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
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
            <i class="fas fa-home"></i> Home &nbsp;>&nbsp; Dashboard
        </div>
        <div class="dashboard-title">Dashboard Control Panel</div>
        <div class="stats-grid">
            <div class="stat-card posts">
                <div class="stat-label">Posts</div>
                <div class="stat-value"><?php echo $posts_count; ?></div>
                <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
            </div>
            <div class="stat-card pages">
                <div class="stat-label">Pages</div>
                <div class="stat-value"><?php echo $pages_count; ?></div>
                <div class="stat-icon"><i class="fas fa-file"></i></div>
            </div>
            <div class="stat-card media">
                <div class="stat-label">Media</div>
                <div class="stat-value"><?php
                    $sql = "SELECT COUNT(*) as count FROM media";
                    $result = $conn->query($sql);
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-icon"><i class="fas fa-images"></i></div>
            </div>
            <div class="stat-card users">
                <div class="stat-label">Users</div>
                <div class="stat-value"><?php
                    $sql = "SELECT COUNT(*) as count FROM users";
                    $result = $conn->query($sql);
                    echo $result->fetch_assoc()['count'];
                ?></div>
                <div class="stat-icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php exit; ?> 