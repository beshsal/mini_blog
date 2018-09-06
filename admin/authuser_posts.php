<?php
include "includes/admin_header.inc.php";

if (isset($_GET["status"])) {
    if (!is_numeric($_GET["status"])) {
        $status = $conn->real_escape_string($_GET["status"]);
    } else {
        header("Location: " . BASE_URL);
        exit;
    }
}

$pageName    = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$currRole    = $_SESSION["role"]; // holds admin or author
$currUname   = $_SESSION["username"];
$currUid     = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row         = $currUid->fetch_array();
$uid         = $row["user_id"];
$searchError = "";

$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)
         WHERE posts.auth_uid = {$uid}
         ORDER BY post_date DESC";

$result = $conn->query($query);
confirmQuery($result);

if (isset($status)) {
    if ($status == "published") {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    WHERE post_status = 'published' AND posts.auth_uid = {$uid}
                     ORDER BY post_date DESC";
    } elseif ($status == "draft") {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    WHERE post_status = 'draft' AND posts.auth_uid = {$uid}                     
                    ORDER BY post_date DESC";
    }
    
    $result = $conn->query($getPosts);
    confirmQuery($result);
}

// SEARCH POSTS
if (isset($_POST["search_posts"])) {
    $byTitle = $conn->real_escape_string($_POST["by_title"]);    
    // If both fields are filled (not empty)
    if (!empty($byTitle)) {        
        $findPosts = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.title LIKE '%" . $byTitle . "%'
                     AND posts.auth_uid = {$uid}
                     ORDER BY post_date DESC";
    }    
    if (empty($findPosts)) {        
        $searchError = "The search field cannot be empty! Please enter a post title.";        
    } else {        
        $result = $conn->query($findPosts);
        confirmQuery($result);
        $search = $result;        
        if ($search->num_rows == 0) {
            $searchError = "No results found! The search term may be mispelled, or the post may not exist.";
        }
    }
}

if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST["checkBoxArray"] as $postValueId) {
        $bulk_options = $_POST["bulk_options"];

        switch($bulk_options) {
            case "published":                
            $setStatus  = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                    
            $postStatus = $conn->query($setStatus);
            confirmQuery($postStatus);                        
            break;     
                        
            case "draft":                
            $setStatus  = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                    
            $postStatus = $conn->query($setStatus);                        
            confirmQuery($postStatus);            
            break;
            
            case "delete_selected":                    
            $deletePosts = $conn->query("DELETE FROM posts WHERE post_id = {$postValueId}");                        
            confirmQuery($deletePosts);            
            break; 
        }
    }    
        header("Location: " . BASE_URL . "authuser_posts.php");
        exit;
}
?>
<div id="wrapper">
<?php 
include "includes/admin_nav.inc.php";
include "includes/page_header.inc.php"; 
?>
<div id="page-wrapper">
<div class="container-fluid">
<div class="row">
<div class="col-lg-12">    
    <nav class="navbar navbar-default search-sort-nav">
      <div class="container-fluid">
        <div class="navbar-header">
          <ul class="nav nav-pills">          
            <li class="pull-left"><a href="authuser_posts.php">View All <span class="sr-only">(current)</span></a></li>
            <li class="dropdown pull-left">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="authuser_posts.php?status=published">Published</a></li>
                <li><a href="authuser_posts.php?status=draft">Drafts</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="authuser_posts.php">View all</a></li>
              </ul>
            </li>
          </ul>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <i class="glyphicon glyphicon-search"></i>
          </button>
        </div>
        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
          <ul class="nav nav-pills">          
            <li class="pull-left"><a href="authuser_posts.php">View All <span class="sr-only">(current)</span></a></li>
            <li class="dropdown pull-left">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="authuser_posts.php?status=published">Published</a></li>
                <li><a href="authuser_posts.php?status=draft">Drafts</a></li>
                <li role="separator" class="divider"></li>
                <li><a href="authuser_posts.php">View all</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <?php if (isset($searchError) && $searchError != "") {
            echo "<p class='error'>{$searchError}</p>";
            } ?>
            <form action="" method="post" class="navbar-form navbar-left" role="search" style="">
              <div class="form-group">
                <input type="text" class="form-control" id="post-title" placeholder="Search by title" name="by_title" value="<?php if (isset($byTitle)) {
                    echo $byTitle; 
                  } ?>">
              </div>
              <button type="submit" name="search_posts" class="btn gray-btn">Search</button>
            </form>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>
    <div class="col-xs-12" style="margin-top: 30px;">
    <?php 
    if (!isset($search) && $result->num_rows == 0 && !isset($status)) {
        echo "<h1 class='text-center'>There are no posts by the {$currFname}.</h1>"; // $currFname borrowed from page_header.inc.php
    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "draft") { 
        echo "<h1 class='text-center'>There are no pending posts by the {$currFname}.</h1>
             <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";    
    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "published") {
        echo "<h1 class='text-center'>There are no published posts by the {$currFname}.</h1>";
    } else { ?>
    <form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
        <div id="bulkOptionContainer" class="col-xs-12">
        <select class="form-control" name="bulk_options" id="bulkoptions">
            <option value="">Select Option</option>
            <option value="published">Publish</option>
            <option value="draft">Make Draft</option>
            <option value="delete_selected">Delete</option>
        </select>
        </div>
        <div class="apply-div col-xs-12">
            <button type="submit" name="submit" class="btn standard-btn" id="apply">Apply</button>
        </div>

        <div class="col-xs-12" style="margin-left: 0; padding-left: 0;">
            <a href="posts.php?source=insert_post" style="display: inline-block; margin-top: 15px; margin-bottom: 15px;">Add New Post</a>
        </div>
        <table class="table">
            <thead>
                <tr>
                <th><input id="selectAllBoxes" type="checkbox"></th>
                <th>Id</th>
                <th>Featured</th>
                <th>Title</th>
                <th>Author</th>
                <th>Date</th>
                <th>Category</th>
                <th>Image</th>
                <th>Views</th>
                <th>Comments</th>
                <th>Status</th>
                <?php // if ($currRole == "admin") { ?>
                <th>Edit</th>
                <th>Delete</th>
                <?php // } ?>
                </tr>
            </thead>
            <tbody>
            <?php
            // Display posts in the table
            while ($row = $result->fetch_assoc()) {
                $post_id       = $row["post_id"];
                $featured      = $row["featured"];
                $title         = $row["title"];
                $post_auth     = $row["post_auth"];
                $post_date     = $row["post_date"];
                $post_image    = "images/post_images/{$row['filename']}";
                $post_views    = $row["post_views"]; // value is set in post.php
                $post_comments = $row["post_comments"];
                $post_status   = ucwords($row["post_status"]);
                
                echo "<tr>";
            ?>
                <td><input class="checkBoxes" type="checkbox" name="checkBoxArray[]" value="<?php echo $post_id; ?>"></td>                
            <?php
                echo "<td class='td-bold'>{$post_id}</td>";
                echo "<td>";
            ?>
                <input type="radio" name="set_featpost" value="" data-postid="<?php echo $post_id; ?>" class="set-featpost"               
                <?php            
                if ($featured == "Yes") {                
                    echo "checked";
                } elseif ($featured == "No") {
                    echo "unchecked";
                }                   
                ?>>
                <?php    
                echo "</td>";
                echo "<td class='td-left'><a href='../post.php?post_id={$post_id}'>{$title}</a></td>";
                echo "<td>{$post_auth}</td>"; 
                echo "<td class='td-bold td-date'>{$post_date}</td>";
                
                $getCategories = "SELECT * FROM categories
                                  LEFT JOIN postxcat USING (cat_id)
                                  WHERE postxcat.post_id = {$post_id}";

                $categories = $conn->query($getCategories);
                confirmQuery($categories);
                $cats = array();
                while ($row = $categories->fetch_assoc()) {
                    $catId  = $row["cat_id"];
                    $cats[] = $row["category"];
                }
                ?>

                <td>              
                <?php
                $catlist = rtrim(implode(", ", $cats));
                echo $catlist;
                ?>             
                </td>
                <?php
                echo "<td><img width='100' src='{$post_image}'></td>";
                echo "<td>{$post_views}</td>";
                echo "<td>{$post_comments}</td>"; 
                echo "<td class='td-bold td-status'>{$post_status}</td>";
                echo "<td><a href='posts.php?source=update_post&postid={$post_id}&uid={$uid}' class='btn gray-btn'>Edit</a></td>";
                echo "<td><a rel='{$post_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </form>
    </div>        
    <?php } ?>
</div>
</div><!-- /.row -->
</div><!-- /.container-fluid -->
</div>
</div><!-- /#wrapper -->
<?php
if (isset($_POST["delete_item"])) {
    $del_id = $_POST["id"]; // get the post_id from the hidden input element
    $deletePost = $conn->query("DELETE from posts WHERE post_id = {$del_id}");
    header("Location: authuser_posts.php");
    exit;
}

include "includes/admin_footer.inc.php";
?>