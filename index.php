<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$page_title = "Dashboard";
include 'includes/header.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Dashboard
            <small>Control panel</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3>Posts</h3>
                        <p>Manage your posts</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-file-text"></i>
                    </div>
                    <a href="posts.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3>Categories</h3>
                        <p>Manage categories</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-folder"></i>
                    </div>
                    <a href="categories.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3>Media</h3>
                        <p>Manage media files</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-picture-o"></i>
                    </div>
                    <a href="media.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
            
            <div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3>Users</h3>
                        <p>Manage users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <a href="users.php" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?> 