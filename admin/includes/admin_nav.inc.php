<?php 
$currRole = $_SESSION["role"];
?>
<nav class="navbar navbar-fixed-top" role="navigation">    
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="<?php echo BASE_URL; ?>" class="navbar-brand"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
    </div>
    <div class="navbar-content">
    <ul class="nav navbar-right top-nav">
        <li><a href="../">Home Site</a></li>
        <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> 
                <?php echo $_SESSION["firstname"] . " (" . $_SESSION['username'] . ")"; ?>
            <b class="caret"></b></a>
            <ul class="dropdown-menu">
                <li>
                    <a href="profile.php"><i class="fa fa-user-circle" aria-hidden="true"></i> Profile</a>
                </li>
                <li>
                <li class="divider"></li>
                <li>                    
                    <form id="signoutForm" method="post" action="">
                      <button name="sign_out" type="submit" id="admin-signout"><i class="fa fa-fw fa-power-off"></i> Sign Out</button>
                    </form>                    
                </li>
            </ul>
        </li>
    </ul>    
    <!-- Sidebar Menu Items - these collapse to the responsive navigation menu on small screens -->
    <div class="collapse navbar-collapse navbar-ex1-collapse">
        <ul class="nav navbar-nav side-nav">
            <li class="member-info">
                <a href="users.php?source=view_members&online=members">Members Online: <span class="onlinemembers"></span></a>
            </li>
            <li>
                <a href="javascript:;" data-toggle="collapse" data-target="#posts"><i class="fa fa-file-text" aria-hidden="true"></i> Posts 
                <i class="fa fa-fw fa-caret-down"></i></a>
                <ul id="posts" class="collapse">                    
                    <li>
                        <a href="posts.php?source=insert_post">Add Post</a>
                    </li>
                    <li>
                        <a href="posts.php">View All Posts</a>
                    </li>
                    <li>
                        <a href="
                        <?php if ($currRole == 'admin') {
                        echo 'posts.php?user=' . $currUname; // use $_SESSION['username'] in the remote file
                        } else {
                        echo 'authuser_posts.php';
                        } ?>">View Your Posts</a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="media.php" <?php if(THIS_PAGE == "media.php"){echo "id='active'";} ?>><i class="fa fa-film" aria-hidden="true"></i> Media</a>
            </li>
            <?php if ($currRole == "admin") { ?>
            <li>
                <a href="logo_banner.php" <?php if(THIS_PAGE == "logo_banner.php"){echo "id='active'";} ?>> <i class="fa fa-picture-o" aria-hidden="true"></i>  Logo &amp; Banner</a>
            </li>
            <?php } ?>
            <li>
                <a href="admin_categories.php" <?php if(THIS_PAGE == "admin_categories.php"){echo "id='active'";} ?>><i class="fa fa-th" aria-hidden="true"></i> Categories</a>
            </li>
            <li>
                <a href="comments.php" <?php if(THIS_PAGE == "comments.php"){echo "id='active'";} ?>><i class="fa fa-comments" aria-hidden="true"></i> Comments</a>
            </li>
            <?php
            if ($currRole == "admin") { ?>
            <li>
                <a href="javascript:;" data-toggle="collapse" data-target="#users"><i class="fa fa-users" aria-hidden="true"></i> Users 
                <i class="fa fa-fw fa-caret-down"></i></a>
                <ul id="users" class="collapse">                    
                    <?php
                    if ($currRole == "admin") { ?>
                    <li>
                        <a href="users.php?source=insert_user">Add New User</a>
                    </li>
                    <?php }                
                    if ($currRole == "admin") { ?>                    
                    <li>
                        <a href="users.php">View All Users</a>
                    </li>
                    <?php }                    
                    if ($currRole == "admin") { ?>
                    <li>  
                        <a href="users.php?role=member&lmt=1">View Members</a>
                    </li>
                    <?php } ?>                    
                </ul>
            </li>
            <?php } ?>
            <li>
                <a href="profile.php" <?php if(THIS_PAGE == "profile.php"){echo "id='active'";} ?>><i class="fa fa-user-circle" aria-hidden="true"></i> Profile</a>
            </li>            
        </ul>
    </div><!-- /.navbar-collapse -->
    </div>
</nav>