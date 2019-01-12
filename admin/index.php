<?php 
// HEADER
include "includes/admin_header.inc.php";

// Get the current admin/author's details.
$currAuthname = $_SESSION["firstname"] . " " . $_SESSION["lastname"];
$currRole     = $_SESSION["role"];
$currUname    = $_SESSION["username"];

// Get the current admin/author's user_id.
// Using the auth_profile table instead of users in case I decide to create a separate members table.
$authuidResult = $conn->query("SELECT user_id FROM auth_profile WHERE username = '{$currUname}'"); confirmQuery($authuidResult);
$row           = $authuidResult->fetch_array();
$auth_uid      = $row["user_id"];
?>
<body>
<div id="wrapper">
    <?php 
    // NAVIGATION
    include "includes/admin_nav.inc.php";    
    // PAGE HEADER
    include "includes/page_header.inc.php";
    ?>
    <div id="page-wrapper">
        <div class="container-fluid">
            <!-- Display the panels containing the number of posts, users, comments, etc. -->
            <div class="row <?php if($currRole == 'author'){ echo 'panel-container';}else{echo 'panel-container-admin';} ?>">
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php } ?>
                    <a href="posts.php">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-file-text fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <!-- Display the total count of all posts. -->
                                    <div class="huge"><?php echo $totalPosts = countRecords("posts"); ?></div>
                                </div>
                                <div class="col-xs-12">All Posts</div>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>                
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                    <a href="posts.php?status=published">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-file-text fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right"> 
                                    <div class="huge">
                                    <!-- Display the total count of published posts. -->
                                    <?php echo $publishedPosts = countRecords("posts", "post_status", "", "published", ""); ?>
                                </div>
                                </div>
                                <div class="col-xs-12">Published Posts</div>
                            </div>
                        </div>                      
                    </div>
                    </a>
                </div>                
                <div class="col-lg-3 col-sm-4 col-xs-6">
                    <a href="posts.php?status=draft">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-file-text-o fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right"> 
                                    <div class="huge">
                                    <!-- Display the total count of post drafts. -->
                                    <?php echo $pendingPosts = countRecords("posts", "post_status", "", "draft", ""); ?>
                                    </div>
                                </div>
                                <div class="col-xs-12">Pending Posts</div>
                            </div>
                        </div>                     
                    </div>
                    </a>
                </div>
                <?php } ?>                
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php } ?>
                    <!-- Specific admin/author owned posts - if an admin, access the posts through a GET request to the
                     posts page; if an author, go to the authuser_posts page. -->
                    <?php if ($currRole == "author") { ?>
                    <a href="authuser_posts.php">
                    <?php } else { ?>
                    <a href="posts.php?user=<?php echo $currUname; ?>">
                    <?php } ?>                 
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-file-text fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                    <!-- Display the count of the admin's/author's post. -->
                                    <?php echo $authorPosts = countRecords("posts", "", "", "", $auth_uid); ?>
                                 </div>
                                </div>
                                <!-- Specify the admin's/author's name. -->
                                <div class="col-xs-12"><?php echo $_SESSION["firstname"]; ?>'s Posts</div>
                            </div>
                        </div>                       
                    </div>
                    </a>   
                </div>                 
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php } ?>
                    <a href="admin_categories.php">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-th fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <!-- Display the total count of categories. -->
                                    <div class="huge"><?php echo $totalCats = countRecords("categories"); ?></div>
                                </div>
                                <div class="col-xs-12">Categories</div>
                            </div>
                        </div>                     
                    </div>
                    </a>
                </div>                    
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php } ?>
                    <a href="comments.php">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-comments fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                    <!-- Display the count of comments. -->
                                    <?php
                                    // If an admin, show the total count of comments. If an author, only show the count of comments
                                    // assigned to the author's posts.
                                    if ($currRole == "author") {
                                        echo $totalComments = countRecords("comments", "", "", "", $auth_uid);
                                    } else {
                                        echo $totalComments = countRecords("comments");
                                    }
                                    ?>
                                    </div>                                  
                                </div>
                                <div class="col-xs-12">Comments</div>
                            </div>
                        </div>                       
                    </div>
                    </a>
                </div>                    
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php } ?>
                    <a href="comments.php?status=unapproved">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-comments-o fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">
                                    <?php 
                                    // If an admin, show the total count of unapproved comments. If an author, only show the count of
                                    // unapproved comments assigned to the author's posts.
                                    if ($currRole == "author") {
                                        echo $pendingComments = countRecords("comments", "comment_status", "", "unapproved", $auth_uid);
                                    } else {
                                        echo $pendingComments = countRecords("comments", "comment_status", "", "unapproved", "");
                                    }
                                    ?>
                                    </div>                                  
                                </div>
                                <div class="col-xs-12">Pending Comments</div>
                            </div>
                        </div>                     
                    </div>
                    </a>
                </div>                    
                <?php if ($currRole == "admin") { ?>
                <div class="col-lg-3 col-sm-4 col-xs-6">
                <?php } else { ?>
                <div class="col-lg-4 col-xs-6">
                <?php }
                // Admin are given access to deleting and editing users directly on the view_users template. Authors only
                // have access to viewing members via the view_members template.
                if ($currRole == "author") { ?>
                    <a href="users.php?source=view_members">
                <?php } else { ?>
                    <a href="users.php?role=member&lmt=1">
                <?php } ?>
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-users fa-4x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <!-- Display the total count of members. -->
                                    <div class="huge"><?php echo $totalMembers = countRecords("users", "", "member", "", ""); ?></div>
                                </div>
                                <div class="col-xs-12">Members</div>
                            </div>
                        </div>                      
                    </div>
                    </a>  
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->
<!-- FOOTER -->
<?php include "includes/admin_footer.inc.php"; ?>