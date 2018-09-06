<?php
$subpage;
// Get the source or status (subpage)
if (isset($_GET["source"])) {
    $subpage = $conn->real_escape_string($_GET["source"]);
} elseif (isset($_GET["status"])) {
    $subpage = $conn->real_escape_string($_GET["status"]);
} elseif (isset($_GET["role"])) {
    $subpage = $conn->real_escape_string($_GET["role"]);
} elseif (isset($_GET["user"])) {
    $subpage = $conn->real_escape_string($_GET["user"]);
}

// Get the name of the page
$pageName   = basename(THIS_PAGE, ".php");
$currUname  = $_SESSION["username"];
$currFname  = $_SESSION["firstname"];
$getImageId = $conn->query("SELECT * FROM auth_profile WHERE username = '{$currUname}'"); confirmQuery($getImageId);

if ($getImageId->num_rows != 0) {
    while ($row = $getImageId->fetch_assoc()) {
        $uimg_id = $row["image_id"];
    }
} else {
    $getImageId = $conn->query("SELECT * FROM users WHERE username = '{$currUname}'"); confirmQuery($getImageId);
    while ($row = $getImageId->fetch_assoc()) {
        $uimg_id = $row["image_id"];
    }
}

$getFilename = $conn->query("SELECT filename FROM user_images WHERE image_id = {$uimg_id}"); confirmQuery($getFilename);
$row         = $getFilename->fetch_array();
$filename    = $row["filename"];
?>
<div class="jumbotron">
<div class="container">
<div class="row">
<div class="col-md-6" style="padding-left: 0; padding-right: 0;">
<div id="page-details">
<?php    
if ($pageName == "index") {
    echo "<h2 class='page-header text-center'>Welcome, ";   
    echo "<small>" . $_SESSION['firstname'] . "!</small></h2>";
?>  
    <div class="current-user-details">
        <div class="col-sm-7">
            <ul>
            <li>Username: <?php echo $currUname; ?></li>
            <li>Email: <?php echo $_SESSION["email"]; ?></li>
            <li>Role: <?php echo ucwords($_SESSION["role"]); ?></li>
            </ul>
        </div>
        <div class="col-sm-5">
            <a href="profile.php#profile">
            <?php if (isset($filename) && !empty($filename)) { ?>
            <img src="images/user_images/<?php echo $filename; ?>" class="img-responsive img-circle" alt="Profile Image" style="margin: auto;" height="120" width="120">
            <?php } else { ?>
            <img src="images/user_images/defaultuser.png" class="img-responsive img-circle" alt="Profile Image" style="margin: auto;" height="120" width="120">
            <?php } ?>
            </a>
        </div>
        <p><a href="profile.php#profile">View Profile</a> for more details</p>
    </div>    
<?php
} elseif ($pageName == "logo_banner") {
    echo "<h2 class='page-header'>Logo &amp; Banner</h2>";
    echo "<p class='page-descript'>Edit the home page's welcome banner.</p>";
} elseif ($pageName == "admin_categories") {
    echo "<h2 class='page-header'>Categories</h2>";
    echo "<p class='page-descript'>Manage all categories: view, add, edit, and delete categories.</p>";
} elseif ($pageName == "comments" && empty($subpage)) {
    if ($currRole == "admin") {
        echo "<h2 class='page-header'>All Comments</h2>";
        echo "<p class='page-descript'>Manage all comments: view, approve, deny, and delete comments.</p>";
    } else {
        echo "<h2 class='page-header'>Your Posts' Comments</h2>";
        echo "<p class='page-descript'>Manage all your posts' comments: view, approve, deny, and delete your posts' comments.</p>";
    }
} elseif ($pageName == "posts" && empty($subpage)) {
    echo "<h2 class='page-header'>All Posts</h2>";
    echo "<p class='page-descript'>Manage all published and unpublished posts: view, approve, deny, edit, and delete posts. Set the featured post.</p>";
} elseif ($pageName == "authuser_posts" && empty($subpage)) {
    echo "<h2 class='page-header'>{$currFname}'s Posts</h2>";
    echo "<p class='page-descript'>Manage your own posts: view, approve, deny, edit, and delete posts.</p>"; 
} elseif ($pageName == "users" && empty($subpage)) {
    echo "<h2 class='page-header'>All Users</h2>";
    echo "<p class='page-descript'>Manage all users: view, edit, and delete all users. Change a user's role.</p>";
} elseif (!empty($subpage)) {
    if ($subpage == "insert_post") {
        echo "<h2 class='page-header'>Add Post</h2>";
        echo "<p class='page-descript'>Create a new published post or post draft.</p>"; 
    }    
    if ($subpage == "update_post") {
        echo "<h2 class='page-header'>Update Post</h2>";
        echo "<p class='page-descript'>Edit an existing blog post.</p>";
    }
    if ($subpage == "published") {
        echo "<h2 class='page-header'>Published Posts</h2>";
        echo "<p class='page-descript'>Manage published posts.</p>";
    }
    if ($subpage == "draft") {
        echo "<h2 class='page-header'>Pending Posts</h2>";
        echo "<p class='page-descript'>Manage post drafts.</p>";
    }
    if ($subpage == "approved") {
        echo "<h2 class='page-header'>Approved Comments</h2>";
        echo "<p class='page-descript'>Manage approved comments.</p>";
    }
    if ($subpage == "unapproved") {
        echo "<h2 class='page-header'>Pending Comments</h2>";
        echo "<p class='page-descript'>Manage unapproved comments.</p>";
    }
    if ($subpage == "insert_user") {
        echo "<h2 class='page-header'>Add Admin or Author</h2>";
        echo "<p class='page-descript'>Add a new user with admin or author privileges.</p>";
    }
    if ($subpage == "update_user") {
        echo "<h2 class='page-header'>Update User</h2>";
        echo "<p class='page-descript'>Edit a current admin or author user.</p>";
    }
    if ($subpage == $currUname) {
        echo "<h2 class='page-header'>{$currFname}'s Posts</h2>";
        echo "<p class='page-descript'>Manage your own posts: view, approve, deny, edit, and delete posts.</p>";
    }
    if ($subpage == "view_admin") {
        echo "<h2 class='page-header'>Admin</h2>";
        echo "<p class='page-descript'>View all admin users.</p>";
    }    
    if ($subpage == "view_authors") {
        echo "<h2 class='page-header'>Authors</h2>";
        echo "<p class='page-descript'>View all author users.</p>";
    }    
    if ($subpage == "view_members" && $currRole == "author")  {
        echo "<h2 class='page-header'>Members</h2>";
        echo "<p class='page-descript'>View all member users.</p>";
    }
    if ($subpage == "member" && $currRole != "author")  {
        echo "<h2 class='page-header'>Members</h2>";
        echo "<p class='page-descript'>Manage member users.</p>";
    }
} elseif ($pageName == "profile") {
    echo "<h2 class='page-header'>" . $_SESSION['firstname'] . "'s " . $pageName . "</h2>";
    echo "<p class='page-descript'>View " . $_SESSION['firstname'] . "'s current profile details.</p>";
    
} elseif ($pageName == "update_profile") {
    echo "<h2 class='page-header'>Update Profile</h2>";
    echo "<p class='page-descript'>Edit your profile: update your profile image and bio.</p>";
} else {                
    echo "<h2 class='page-header'>" . ucwords($pageName) . "</h2>";
}
?>
</div>
</div>
<div class="col-md-6" style="padding-left: 0; padding-right: 0;">
    <div class="quick-list">
        <h4>Quick Options:</h4>
        <div class="row">
            <div class="col-xs-6 col-sm-5 col-md-6">
                <ul class="list-unstyled">
                    <li><a href="posts.php?source=insert_post">Add New Post</a></li>
                    <li><a href="posts.php">View Posts</a></li>
                    <li><a href="admin_categories.php">Add New Category</a></li>
                    <?php if ($currRole == "admin") { ?>
                    <li><a href="users.php?source=insert_user">Add Admin User</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-xs-6 col-sm-7 col-md-6">
                <ul class="list-unstyled">
                    <li><a href="comments.php">Comments</a></li>
                    <li><a href="../">View Site</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<!-- BREADCRUMB -->
<ol class="breadcrumb">
    <li><a href="<?php echo BASE_URL; ?>"><i class="fa fa-dashboard"></i> Dashboard</a></li>
    <li class="active">
        <?php
        if (isset($pageName) && isset($subpage)) {
            if ($pageName == "posts" && $subpage == "insert_post") {
                echo "<i class='fa fa-file'></i> Add Post";
            } elseif ($pageName == "posts" && $subpage == "update_post") {
                echo "<i class='fa fa-file'></i> Update Post";
            } elseif ($pageName == "posts" && $subpage == "published") {
                echo "<i class='fa fa-file'></i> " . ucwords($subpage) . " Posts";
            } elseif ($pageName == "posts" && $subpage == "draft") {
                echo "<i class='fa fa-file'></i> Pending Posts";
            } elseif ($pageName == "posts" && $subpage == $currUname) {
                echo "<i class='fa fa-file'></i> {$currFname}'s Posts";                    
            } elseif ($pageName == "users" && $subpage == "insert_user") {
                echo "<i class='fa fa-file'></i> Add Admin or Author";  
            } elseif ($pageName == "users" && $subpage == "update_user") {
                echo "<i class='fa fa-file'></i> Update User";
            } elseif ($pageName == "users" && $subpage == "member" || $subpage == "view_members") {
                echo "<i class='fa fa-file'></i> Members";
            } elseif ($pageName == "comments" && $subpage == "approved") {
                echo "<i class='fa fa-file'></i> Approved Comments";
            } elseif ($pageName == "comments" && $subpage == "unapproved") {
                echo "<i class='fa fa-file'></i> Pending Comments";  
            } 
        } else {
            switch($pageName) {
                case "index";
                echo "<i class='fa fa-file'></i> Home";
                break;

                case "posts";
                echo "<i class='fa fa-file'></i> All Posts";
                break;

                case "authuser_posts";
                echo "<i class='fa fa-file'></i> {$currFname}'s Posts";
                break;

                case "users";
                echo "<i class='fa fa-file'></i> All Users";
                break;
                    
                case "comments";
                if ($currRole == "admin") {
                    echo "<i class='fa fa-file'></i> All Comments";
                } else {
                    echo "<i class='fa fa-file'></i> Your Posts' Comments";
                }
                break;

                case "logo_banner";
                echo "<i class='fa fa-file'></i> Logo &amp; Banner";
                break;

                case "update_profile";
                echo "<i class='fa fa-file'></i> Update Profile";
                break;

                default:
                $pageName = str_replace("_", " ", $pageName); // replaces the underscore with space            
                echo "<i class='fa fa-file'></i> " . ucwords($pageName);
            }
        }        
        ?>
    </li>
</ol>