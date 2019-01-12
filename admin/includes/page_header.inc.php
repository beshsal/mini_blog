<?php
// This file is responsible for customizing the jumbotron and breadcrumb titles.

$subpage;
// Get the "source", "status", "catid", "role", or "user" (subpage) parameter from the URL query string.
if (isset($_GET["source"]) && !isset($_GET["online"])) {
    $subpage = $conn->real_escape_string($_GET["source"]);
} elseif (isset($_GET["status"])) {
    $subpage = $conn->real_escape_string($_GET["status"]);
} elseif (isset($_GET["catid"])) {
    $subpage = $conn->real_escape_string($_GET["catid"]);
    if (is_numeric($subpage)) {
        $sql = $conn->query("SELECT category FROM categories WHERE cat_id = {$subpage}");
        $cat = $sql->fetch_row();
    }  
} elseif (isset($_GET["role"])) {
    $subpage = $conn->real_escape_string($_GET["role"]);
} elseif (isset($_GET["user"])) {
    $subpage = $conn->real_escape_string($_GET["user"]);
}

if (isset($_GET["source"]) && isset($_GET["online"])) {
    $subpage = $conn->real_escape_string($_GET["source"]);
    $online  = $_GET["online"];
}

// Get the name of the page (file).
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
            <a href="profile.php#profile" alt="Profile Image">
            <?php if (isset($filename) && !empty($filename)) { ?>            
            <div class="user-thumb md"
                 style="background-image: url('images/user_images/<?php echo $filename; ?>')">
            </div>
            <?php } else { ?>
            <div class="user-thumb md"
                 style="background-image: url('images/user_images/defaultuser.png')">
            </div>
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
    if (isset($_GET["update_cat"])) {
        echo "<h2 class='page-header'>Update Category</h2>";
        echo "<p class='page-descript'>Manage all categories: view, add, edit, and delete categories.</p>";
    } else {
        echo "<h2 class='page-header'>Categories</h2>";
        echo "<p class='page-descript'>Manage all categories: view, add, edit, and delete categories.</p>"; 
    }
} elseif ($pageName == "comments" && empty($subpage) || $pageName == "comments" && isset($_GET["search"])) {
    if ($currRole == "admin") {
        echo "<h2 class='page-header'>All Comments</h2>";
        echo "<p class='page-descript'>Manage all comments: view, approve, deny, and delete comments.</p>";
    } else {
        echo "<h2 class='page-header'>Comments on {$currFname}'s Posts</h2>";
        echo "<p class='page-descript'>Manage all your posts' comments: view, approve, deny, and delete your posts' comments.</p>";
    }
} elseif ($pageName == "posts" && empty($subpage) || $pageName == "posts" && isset($_GET["search"])) {
    if (isset($_GET["user"])) {
        echo "<h2 class='page-header'>{$currFname}'s Posts</h2>";
        echo "<p class='page-descript'>Manage your own posts: view, approve, deny, edit, and delete posts.</p>"; 
    } else {
        echo "<h2 class='page-header'>All Posts</h2>";
        echo "<p class='page-descript'>Manage all published and unpublished posts: view, approve, deny, edit, and delete posts. 
             Set the featured post.
             </p>";
    }
} elseif ($pageName == "authuser_posts" && empty($subpage) || $pageName == "authuser_posts" && isset($_GET["search"])) {
    echo "<h2 class='page-header'>{$currFname}'s Posts</h2>";
    echo "<p class='page-descript'>Manage your own posts: view, approve, deny, edit, and delete posts.</p>"; 
} elseif ($pageName == "users" && empty($subpage) 
          || $pageName == "users" && isset($_GET["search"]) && $currRole == "admin" && !isset($_GET["lmt"])) {
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
        if (isset($_GET["user"]) || $pageName == "authuser_posts") {
            echo "<h2 class='page-header'>{$currFname}'s Published Posts</h2>";
        echo "<p class='page-descript'>View and manage your published posts.</p>";
        } else {
            echo "<h2 class='page-header'>Published Posts</h2>";
            echo "<p class='page-descript'>View and manage published posts.</p>";
        }
    }
    if ($subpage == "draft") {
        if (isset($_GET["user"]) || $pageName == "authuser_posts") {
            echo "<h2 class='page-header'>{$currFname}'s Drafts</h2>";
            echo "<p class='page-descript'>View and manage your post drafts.</p>";
        } else {
            echo "<h2 class='page-header'>Drafts</h2>";
            echo "<p class='page-descript'>View and manage post drafts.</p>";
        }
    }
    if (is_numeric($subpage) && isset($cat)) {
        if (isset($_GET["user"]) || $pageName == "authuser_posts") {
            echo "<h2 class='page-header'>{$currFname}'s Posts by Category: " . $cat[0] . "</h2>";
            echo "<p class='page-descript'>View and manage your posts sorted by category.</p>";
        } else {
            echo "<h2 class='page-header'>Posts by Category: " . $cat[0] . "</h2>";
            echo "<p class='page-descript'>View and manage posts sorted by category.</p>";
        }
    }
    if ($subpage == "approved") {
        if ($_SESSION["role"] == "author") {
            echo "<h2 class='page-header'>Comments on {$currFname}'s Posts: <br>Approved Comments</h2>";
        } else {
            echo "<h2 class='page-header'>Approved Comments</h2>";
        }        
        echo "<p class='page-descript'>View and manage approved comments.</p>";
    }
    if ($subpage == "unapproved") {
        if ($_SESSION["role"] == "author") {
            echo "<h2 class='page-header'>Comments on {$currFname}'s Posts: <br>Pending Comments</h2>";
        } else {
            echo "<h2 class='page-header'>Pending Comments</h2>";
        }
        echo "<p class='page-descript'>View and manage comments that have not been approved.</p>";
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
    if ($subpage == "view_members" && $currRole == "author" && !isset($online)) {
        echo "<h2 class='page-header'>Members</h2>";
        echo "<p class='page-descript'>View all member users.</p>";
    }
    if ($subpage == "view_members" && isset($online)) {
        echo "<h2 class='page-header'>Online Members</h2>";
        echo "<p class='page-descript'>View online member users.</p>";
    }
    if ($subpage == "admin" && $currRole != "author") {
        echo "<h2 class='page-header'>Admin</h2>";
        echo "<p class='page-descript'>Manage admin users.</p>";
    }
    if ($subpage == "author" && $currRole != "author") {
        echo "<h2 class='page-header'>Authors</h2>";
        echo "<p class='page-descript'>Manage author users.</p>";
    }
    if ($subpage == "member" && $currRole != "author") {
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
                    <?php if ($currRole == "admin") { ?>
                    <li><a href="users.php?source=insert_user">Add Admin User</a></li>
                    <li><a href="users.php">View Users</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="col-xs-6 col-sm-7 col-md-6">
                <ul class="list-unstyled">
                    <li><a href="admin_categories.php">Add New Category</a></li>
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
        if (isset($_GET["search"])) {
            if ($pageName == "posts") {
                if (isset($_GET["srch1"])) {
                    $srch1 = trim($conn->real_escape_string($_GET["srch1"]));                   
                }
                if (isset($_GET["srch2"])) {
                    $srch2 = trim($conn->real_escape_string($_GET["srch2"]));                    
                } 
                if (isset($_GET["user"])) {
                    echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Search: <span>";
                } else {
                    echo "<i class='fa fa-file'></i> All Posts: Search: <span>";
                }                
                if (isset($srch1) && !empty($srch1)) { 
                    echo "{$srch1} </span>";
                }
                if (!empty($srch1) && !empty($srch2)) { 
                    echo " | <span>";
                }
                if (isset($srch2) && !empty($srch2)) {
                    echo "{$srch2} </span>";
                }
            } elseif ($pageName == "comments") {
                if (isset($_GET["srch1"]) && !empty($_GET["srch1"])) {
                    $srch1 = trim($conn->real_escape_string($_GET["srch1"]));
                }               
                if (isset($_GET["srch2"]) && !empty($_GET["srch2"])) {
                    $srch2 = trim($conn->real_escape_string($_GET["srch2"]));
                }
                if ($_SESSION["role"] == "author") {
                    echo "<i class='fa fa-file'></i> Comments on {$currFname}'s Posts: Search: <span>";
                } else {
                    echo "<i class='fa fa-file'></i> All Comments: Search: <span>";
                }
                if (isset($srch1) && !empty($srch1)) { 
                    echo "{$srch1} </span>";
                }
                if (!empty($srch1) && !empty($srch2)) { 
                    echo " | <span>";
                }
                if (isset($srch2) && !empty($srch2)) {
                    echo "{$srch2} </span>";
                }
            } elseif ($pageName == "users") {
                if (isset($_GET["srch1"]) && !empty($_GET["srch1"])) {
                     $srch1 = trim($conn->real_escape_string($_GET["srch1"]));
                }                
                if (isset($_GET["srch2"]) && !empty($_GET["srch2"])) {
                    $srch2 = trim($conn->real_escape_string($_GET["srch2"]));
                }
                if (isset($_GET["lmt"]) && isset($_GET["search"])) {
                    echo "<i class='fa fa-file'></i> Members: Search: <span>";
                } else {
                    echo "<i class='fa fa-file'></i> All Users: Search: <span>";
                }
                if (isset($srch1) && !empty($srch1)) { 
                    echo "{$srch1} </span>";
                }
                if (!empty($srch1) && !empty($srch2)) { 
                    echo " | <span>";
                }
                if (isset($srch2) && !empty($srch2)) {
                    echo "{$srch2} </span>";
                }
            } elseif ($pageName == "authuser_posts") {
                if (isset($_GET["srch1"])) {
                    $srch1 = trim($conn->real_escape_string($_GET["srch1"]));
                }
                echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Search: <span>";
                if (isset($srch1) && !empty($srch1)) { 
                    echo "{$srch1} </span>";
                }
            }
        } else {
            if (isset($pageName) && isset($subpage)) {
                if ($pageName == "posts" && $subpage == "insert_post") {
                    echo "<i class='fa fa-file'></i> Add Post";
                } elseif ($pageName == "posts" && $subpage == "update_post") {
                    echo "<i class='fa fa-file'></i> Update Post";
                } elseif ($pageName == "posts" && $subpage == "published") {
                    if (isset($_GET["user"])) {
                        echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Status: " . ucwords($subpage);
                    } else {
                        echo "<i class='fa fa-file'></i> All Posts: Status: " . ucwords($subpage);
                    }
                } elseif ($pageName == "posts" && $subpage == "draft") {
                    if (isset($_GET["user"])) {
                        echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Status: " . ucwords($subpage);
                    } else {
                        echo "<i class='fa fa-file'></i> All Posts: Status: " . ucwords($subpage);
                    }
                } elseif ($pageName == "posts" && is_numeric($subpage)) {
                    if (isset($_GET["user"])) {
                        echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Category: {$cat[0]}";
                    } else {
                        echo "<i class='fa fa-file'></i> All Posts: Category: {$cat[0]}";
                    }
                } elseif ($pageName == "posts" && $subpage == $currUname) {
                    echo "<i class='fa fa-file'></i> {$currFname}'s Posts";                    
                } elseif ($pageName == "authuser_posts" && $subpage == "published") {
                    echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Status: " . ucwords($subpage);
                } elseif ($pageName == "authuser_posts" && $subpage == "draft") {
                    echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Status: " . ucwords($subpage);
                } elseif ($pageName == "authuser_posts" && is_numeric($subpage)) {
                    echo "<i class='fa fa-file'></i> {$currFname}'s Posts: Category: {$cat[0]}";
                } elseif ($pageName == "users" && $subpage == "insert_user") {
                    echo "<i class='fa fa-file'></i> Add Admin or Author";  
                } elseif ($pageName == "users" && $subpage == "update_user") {
                    echo "<i class='fa fa-file'></i> Update User";
                } elseif ($pageName == "users" && $subpage == "admin") {
                    echo "<i class='fa fa-file'></i> Admin";
                } elseif ($pageName == "users" && $subpage == "author") {
                    echo "<i class='fa fa-file'></i> Authors";
                } elseif ($pageName == "users" && $subpage == "member" || $subpage == "view_members" && !isset($online)) {
                    echo "<i class='fa fa-file'></i> Members";
                } elseif ($pageName == "users" && $subpage == "view_members" && isset($online)) {
                    echo "<i class='fa fa-file'></i> Online Members";
                } elseif ($pageName == "comments" && $subpage == "approved") {                    
                    echo "<i class='fa fa-file'></i> ";
                    if ($_SESSION["role"] == "author") { 
                        echo "Comments on {$currFname}'s Posts: "; 
                    } else {
                        echo "All Comments: ";
                    }
                    echo "Status: Approved";
                } elseif ($pageName == "comments" && $subpage == "unapproved") {
                    echo "<i class='fa fa-file'></i> ";
                    if ($_SESSION["role"] == "author") { 
                        echo "Comments on {$currFname}'s Posts: "; 
                    } else {
                        echo "All Comments: ";
                    }
                    echo "Status: Unapproved";  
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

                    case "admin_categories";
                    echo "<i class='fa fa-file'></i> Categories";
                    if (isset($_GET["update_cat"])) {
                       $updateCat = $conn->query("SELECT * FROM categories WHERE cat_id =" . $_GET["update_cat"]);
                       confirmQuery($updateCat);
                       $row = $updateCat->fetch_assoc();
                       echo ": Update Category: " . $row["category"]; 
                    }
                    break;

                    case "users";
                    echo "<i class='fa fa-file'></i> All Users";
                    break;

                    case "comments";
                    if ($currRole == "admin") {
                        echo "<i class='fa fa-file'></i> All Comments";
                    } else {
                        echo "<i class='fa fa-file'></i> Comments on {$currFname}'s Posts";
                    }
                    break;

                    case "logo_banner";
                    echo "<i class='fa fa-file'></i> Logo &amp; Banner";
                    break;

                    case "update_profile";
                    echo "<i class='fa fa-file'></i> Update Profile";
                    break;

                    default:
                    $pageName = str_replace("_", " ", $pageName); // replace the underscore with a whitespace            
                    echo "<i class='fa fa-file'></i> " . ucwords($pageName);
                }
            }   
        }     
        ?>
    </li>
</ol>