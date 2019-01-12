<?php
include "includes/admin_header.inc.php";
// The "status" parameter is sent through the query string when a status is selected from the Sort by dropdown menu. It is used to 
// display either published posts or drafts.
// If "status" is successfully retrieved from the URL query string, save its value to $status.
if (isset($_GET["status"])) {
    if (!is_numeric($_GET["status"]) 
        && $_GET["status"] == "published" 
        || $_GET["status"] == "draft") 
    {
        $status = $conn->real_escape_string($_GET["status"]);
    } else {
        header("Location: " . BASE_URL); 
        exit;
    }
}

if (isset($_GET["catid"])) {
    if (!is_numeric($_GET["catid"])) {
        header("Location: " . BASE_URL . THIS_PAGE);
        exit;
    } else {
        $catId = (int) $_GET["catid"]; // casting to ensure the value really is numeric (an integer) and not a string
    }
}

// Get the name of the current page and the current user's user_id.
$pageName  = basename(THIS_PAGE, ".php"); // authuser_posts
$currRole  = $_SESSION["role"]; // either admin or author
$currUname = $_SESSION["username"];
$currUid   = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row       = $currUid->fetch_array();
$uid       = $row["user_id"];

// Initialize a variable to hold search errors.
$searchError = "";

// Get all posts by the author.
$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)
         WHERE posts.auth_uid = {$uid}
         ORDER BY post_date DESC";

$result = $conn->query($query);
confirmQuery($result);

// Get all categories. These will be displayed in the dropdown menu.
$allCat = $conn->query("SELECT * FROM categories");
confirmQuery($allCat);
// Save all categories data to an array.
$catData = array();
while ($row = $allCat->fetch_assoc()) {
    $catData[] = $row;
}

// Under the Sort by dropdown menu, the author can select his or her own posts with either a Published or Drafts status.
if (isset($status)) {
    if ($status == "published") {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    WHERE post_status = 'published'
                    AND posts.auth_uid = {$uid}
                    ORDER BY post_date DESC";
    } elseif ($status == "draft") {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    WHERE post_status = 'draft'
                    AND posts.auth_uid = {$uid}                     
                    ORDER BY post_date DESC";
    }
    
    $result = $conn->query($getPosts);
    confirmQuery($result);
}

// Under the Sort by dropdown menu, the user can select a category. This will display all posts with the selected category
// if there are any.
if (isset($catId)) {
    $getPosts = "SELECT * FROM posts
                LEFT JOIN images USING (image_id)
                LEFT JOIN postxcat USING (post_id)
                WHERE postxcat.cat_id = {$catId}
                AND posts.auth_uid = {$uid}
                ORDER BY post_id DESC";
    
    $result = $conn->query($getPosts);
    confirmQuery($result);
}

// Search for the author's posts by title.
if (isset($_POST["search_posts"])) {
    // Get the title from the form.
    $byTitle    = trim($_POST["by_title"]);
    $byTitle    = $conn->real_escape_string($byTitle);
    $srchParams = "?search=authposts";
    
    if (isset($byTitle) && !empty($byTitle)) {
        echo $srchParams .= "&srch1={$byTitle}";
    }
    
    // header("Location: " . BASE_URL . THIS_PAGE . "?search=authposts&srch1={$byTitle}");
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If the user searches for a post title, the search term will be retrieved from the URL query string and used
// in a database query.
if (isset($_GET["search"])) { 
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    
    if (!empty($search1)) {
        $findPosts = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.title LIKE '%" . $search1 . "%'
                     AND posts.auth_uid = {$uid}
                     ORDER BY post_date DESC";
    }  

    // If empty fields are submitted, add an error message to $searchError.
    if (empty($findPosts)) {        
        $searchError = "The search field cannot be empty! Please enter a post title.";
    // Otherwise run the query.        
    } else {        
        $result = $conn->query($findPosts);
        confirmQuery($result);
        $search = $result;
        // if no results are found, add an error message to $searchError.   
        if ($search->num_rows == 0) {
            $searchError = $_SESSION["firstname"] . " is not an author of a post that matches the title you entered. 
                           <br>The title may be misspelled or the record may not exist.";
        }
    }
}

// "checkBoxArray" is the name of the checkbox input element created for each post; the value(s) of the checkBoxArray array must
// be captured to know which posts to apply the action to. 

// If posts are checked
if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST["checkBoxArray"] as $postValueId) {
        // "bulk_options" is the name of the select element; this takes the value from the select element (the value of an 
        // option element when selected) and assigns it to a variable, which will be used in the switch statement.
        $bulk_options = $_POST["bulk_options"];
        switch($bulk_options) {
            // If the "published" option is selected, set the status of the checked post(s) to "published".
            case "published":                
            $setStatus  = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                    
            $postStatus = $conn->query($setStatus);
            confirmQuery($postStatus);                        
            break;     
            // If the "draft" option is selected, set the status of the checked post(s) to "draft".      
            case "draft":                
            $setStatus  = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                    
            $postStatus = $conn->query($setStatus);                        
            confirmQuery($postStatus);            
            break;
            // If the "delete_selected" option is selected, delete the post(s).
            case "delete_selected":
            // Delete the comment(s) associated with the post(s).               
            $getCommId = $conn->query("SELECT * FROM postxcomment WHERE post_id = {$postValueId}");
            confirmQuery($getCommId);                
            while ($row = $getCommId->fetch_assoc()) {
                $commentId = $row["comment_id"];
                $deleteComments = $conn->query("DELETE FROM comments WHERE comment_id = {$commentId}");                
                confirmQuery($deleteComments); 
            }
            // Delete the post(s).                    
            $deletePosts = $conn->query("DELETE FROM posts WHERE post_id = {$postValueId}");                        
            confirmQuery($deletePosts);            
            break; 
        }
    }    
        header("Location: " . BASE_URL . THIS_PAGE);
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
            <li class="pull-left"><a href="authuser_posts.php">View all <span class="sr-only">(current)</span></a></li>
            <li class="dropdown pull-left">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="authuser_posts.php?status=published">Published</a></li>
                <li><a href="authuser_posts.php?status=draft">Drafts</a></li>
                <!-- Display all categories in the dropdown. -->
                <li class="dropdown-submenu">
                    <a class="sub-dropdown" tabindex="-1" href="#">Category <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <?php if ($catData) : foreach ($catData as $row) { ?>
                      <li><a tabindex="-1" href="authuser_posts.php?catid=<?php echo $row['cat_id'] ?>"><?php echo $row["category"]; ?></a></li>
                      <?php } else : echo "<li class='text-center'>No categories</li>"; endif; ?>               
                    </ul>
                </li>
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
            <li class="pull-left"><a href="authuser_posts.php">View all <span class="sr-only">(current)</span></a></li>
            <li class="dropdown pull-left">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li><a href="authuser_posts.php?status=published">Published</a></li>
                <li><a href="authuser_posts.php?status=draft">Drafts</a></li>
                <!-- Display all categories in the dropdown. -->
                <li class="dropdown-submenu">
                    <a class="sub-dropdown" tabindex="-1" href="#">Category <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                      <?php if ($catData) : foreach ($catData as $row) { ?>
                      <li><a tabindex="-1" href="authuser_posts.php?catid=<?php echo $row['cat_id'] ?>"><?php echo $row["category"]; ?></a></li>
                      <?php } else : echo "<li class='text-center'>No categories</li>"; endif; ?>               
                    </ul>
                </li>
                <li role="separator" class="divider"></li>
                <li><a href="authuser_posts.php">View all</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <!-- If there is a search error, display it. -->
            <?php // if (isset($searchError) && $searchError != "") {
            // echo "<p class='error'>{$searchError}</p>";
            // } ?>
            <!-- Search form -->
            <form action="" method="post" class="navbar-form navbar-left" role="search" style="">
              <label class="search-label text-right">Search for posts by post title:</label>
              <div class="form-group">
                <input type="search" class="form-control" id="post-title" placeholder="Search by title" name="by_title" value="<?php if (isset($byTitle) && $searchError != "") {
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
    // If there is a search error, display it.
    if (isset($searchError) && $searchError != "") {
        echo "<p class='error'>{$searchError}</p>";
    }
        
    // If there are no posts, inform the user.
    if (!isset($search) && $result->num_rows == 0 && !isset($status) && !isset($catId)) {
        echo "<h1 class='text-center'>There are no posts by the {$currFname}.</h1>"; // $currFname borrowed from page_header.inc.php
    // If the user searches for pending ("draft") posts and there aren't any, inform the user.
    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "draft") { 
        echo "<h1 class='text-center'>There are no pending posts by {$currFname}.</h1>
             <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
    // If the user searches for published posts and there aren't any, inform the user.    
    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "published") {
        echo "<h1 class='text-center'>There are no published posts by {$currFname}.</h1>
             <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
    } elseif (!isset($search) && $result->num_rows == 0 && isset($catId)) {
        echo "<h1 class='text-center'>There are no posts for this category by {$currFname}.</h1>
             <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
    } else { ?>
    <label class="inline-label">Choose an action:</label>
    <form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
        <div id="bulkOptionContainer" class="col-xs-12">
        <select class="form-control" name="bulk_options" id="bulkoptions">
            <option value="">Select Option</option>
            <option value="published">Publish</option>
            <option value="draft">Change Status to Draft</option>
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
            <?php if (isset($search) && $search->num_rows > 0) {
                if (isset($byTitle)) {
                    $srchTerm1 = $byTitle;
                }                                
            ?>
            <div class="query-total">
                <p>
                    Search:                  
                    <span><?php
                    if (isset($byTitle) && !empty($srchTerm1)) echo 'Title: '.'"'.$srchTerm1.'"';
                    ?></span> 
                    Results: 
                    <span><?php echo $search->num_rows; ?></span>
                </p>
            </div>
            <?php } else { ?>
            <div class="query-total">
                <p>
                    Total: 
                    <span><?php 
                    if (empty($searchError)) echo $result->num_rows; else echo "0";
                    ?></span>
                </p>
            </div>
            <?php } ?>
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
                <th>Edit</th>
                <th>Delete</th>
                </tr>
            </thead>
            <?php
            // If there is a search error, hide the table body. 
            // The while loop already prevents the table body from showing if there isn't
            // a matching result, but it doesn't prevent it from showing if an empty
            // form is submitted.
            if (empty($searchError)) {
            ?>
            <tbody>
            <?php
            // Display posts in a table.
            while ($row = $result->fetch_assoc()) {
                $post_id       = $row["post_id"];
                $featured      = $row["featured"];
                $title         = $row["title"];
                $post_auth     = $row["post_auth"];
                $post_date     = $row["post_date"];
                $post_image    = "images/post_images/{$row['filename']}";
                $post_views    = $row["post_views"]; // the value is set in post.php
                $post_comments = $row["post_comments"];
                $post_status   = ucwords($row["post_status"]);
                
                echo "<tr>";
            ?>
                <td><input class="checkBoxes" type="checkbox" name="checkBoxArray[]" value="<?php echo $post_id; ?>"></td>                
            <?php
                echo "<td class='td-bold'>{$post_id}</td>";
                echo "<td>";
            ?>
                <!-- Radio button for setting the featured post
                Authors can set only one of their posts to a featured post. -->
                <input type="radio" name="set_featpost" value="" data-postid="<?php echo $post_id; ?>" class="set-featpost"               
                <?php
                // If selected to be a featured post, add a checked attribute. If not, add unchecked. 
                // This is done through an AJAX request. See the respective JS in admin_footer.inc.php 
                // ("SET FEATURED POST") and the code in set_featpost.inc.php.         
                if ($featured == "Yes") {                
                    echo "checked";
                } elseif ($featured == "No") {
                    echo "unchecked";
                }                   
                ?>>
                <?php    
                echo "</td>";
                echo "<td class='td-left'><a href='../post.php?postid={$post_id}'>{$title}</a></td>";
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
                if ($featured == "Yes") {
                    echo "<td><a class='btn delete-btn delete disabled'>Delete</a></td>";
                } else {
                    echo "<td><a rel='{$post_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                }                
                echo "</tr>";
            }
            ?>
            </tbody>
            <?php } ?>
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
// If "delete_item", the name of the submit input element in the delete modal (delete_modal.inc.php), is set.
if (isset($_POST["delete_item"])) {
    // Get the post_id value stored in the hidden input element, and use it to delete the post.
    $del_id = $_POST["id"];
    // Delete the comment(s) associated with the post.
    // Select all records in the postxcomment cross-reference table by the post_id.
    $getCommId = $conn->query("SELECT * FROM postxcomment WHERE post_id = {$del_id}"); // replaced $post_id with $del_id
    confirmQuery($getCommId);
    // Loop through the records, get the comment_id of each, and use it to delete the respective record(s) in the comments table.
    while ($row = $getCommId->fetch_assoc()) {
        $commentId = $row["comment_id"];
        $deleteComments = $conn->query("DELETE FROM comments WHERE comment_id = {$commentId}");                
        confirmQuery($deleteComments); 
    }
    // Delete the post.
    $deletePost = $conn->query("DELETE from posts WHERE post_id = {$del_id}");
    // header('Location: ' . $_SERVER['PHP_SELF']);
    header("Location: " . BASE_URL . THIS_PAGE); // refresh the page
    exit;

    // See the respective JS in custom.js (SINGLE ITEM DELETION).
}

include "includes/admin_footer.inc.php";
?>