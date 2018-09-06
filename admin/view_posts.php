<?php
if (isset($_GET["status"])) {
    if (!is_numeric($_GET["status"])) {
        $status = $conn->real_escape_string($_GET["status"]);
    } else {
        header("Location: " . BASE_URL . "posts.php");
        exit;
    }
}

if (isset($_GET["catid"])) {
    if (!is_numeric($_GET["catid"])) {
        header("Location: " . BASE_URL . "posts.php");
        exit;
    } else {
        $catId = (int) $_GET["catid"];
    }
}
// Get the name of the page
$pageName    = basename($_SERVER["SCRIPT_FILENAME"], ".php");
$currRole    = $_SESSION["role"]; // holds admin or author
$currUname   = $_SESSION["username"];
$currUid     = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row         = $currUid->fetch_array();
$uid         = $row["user_id"];
$searchError = "";

// All posts will be displayed by default
$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)
         ORDER BY post_id DESC"; // originally ORDER BY post_date DESC

$result = $conn->query($query);
confirmQuery($result);

$allCat = $conn->query("SELECT * FROM categories");
confirmQuery($allCat);

    if (isset($status)) {
        if ($status == "published") {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'published'
                        ORDER BY post_id DESC";

           $result = $conn->query($getPosts);
            confirmQuery($result);

        } elseif ($status == "draft") {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'draft'                     
                        ORDER BY post_id DESC";
            
            $result = $conn->query($getPosts);
            confirmQuery($result);
        }
    }

    if (isset($_GET["user"])) {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    WHERE posts.auth_uid = {$uid}
                    ORDER BY post_id DESC";
        
        $result = $conn->query($getPosts);
        confirmQuery($result);
    }

    if (isset($catId)) {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    LEFT JOIN postxcat USING (post_id)
                    WHERE postxcat.cat_id = {$catId}
                    ORDER BY post_id DESC";
        
        $result = $conn->query($getPosts);
        confirmQuery($result);
    }

if (isset($_POST["search_posts"])) {
    $byAuthname = $conn->real_escape_string($_POST["by_authname"]);
    $byTitle    = $conn->real_escape_string($_POST["by_title"]);
    
    // If both fields are filled (not empty)
    If (!empty($byAuthname) && !empty($byTitle)) {        
        $findPosts = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.post_auth LIKE '%" . $byAuthname . "%'
                     AND posts.title LIKE '%" . $byTitle . "%'
                     ORDER BY post_id DESC";        
    // If the author name is not empty but the title is empty
    } elseif (!empty($byAuthname) && empty($byTitle)) {        
        $findPosts = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.post_auth LIKE '%" . $byAuthname . "%'
                     ORDER BY post_id DESC";    
    // if author name is empty but title is not empty
    } elseif (empty($byAuthname) && !empty($byTitle)) {        
        $findPosts = "SELECT * FROM posts
                     LEFT JOIN images USING (image_id)
                     WHERE posts.title LIKE '%" . $byTitle . "%'
                     ORDER BY post_id DESC";
    }
    
    if (empty($findPosts)) {        
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";        
    } else {        
        $result = $conn->query($findPosts);
        confirmQuery($result);
        $search = $result;
        
        if ($search->num_rows == 0) {
            $searchError = "No results found! The search term(s) may be mispelled or the record may not exist.";
        }
    }   
}

// "checkBoxArray" is the name of the checkbox input element created for each post; the value(s) of the checkBoxArray array must
// be captured to know which posts to apply the action to
if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST["checkBoxArray"] as $postValueId) {
        // "bulk_options" is the name of the select element; this takes the value from the select element and assigns it to a variable, 
        // which will be used in the switch statement; it holds the value of an option element when an option is selected
        $bulk_options = $_POST["bulk_options"];

        switch($bulk_options) {                
            case "published":                
            $setStatus = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                
            $postStatus = $conn->query($setStatus);
            confirmQuery($postStatus);                        
            break;     
                        
            case "draft":                
            $setStatus = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                
            $postStatus = $conn->query($setStatus);                        
            confirmQuery($postStatus);            
            break;
            
            case "delete_selected":                
            $getCommId = $conn->query("SELECT * FROM postxcomment WHERE post_id = {$postValueId}");
            confirmQuery($getCommId);                
            while ($row = $getCommId->fetch_assoc()) {
                $commentId = $row["comment_id"];
                $deleteComments = $conn->query("DELETE FROM comments WHERE comment_id = {$commentId}");                
                confirmQuery($deleteComments); 
            }
                
            $deletePosts = $conn->query("DELETE FROM posts WHERE post_id = {$postValueId}");                
            confirmQuery($deletePosts);
            break;
        }
    }
    
    header("Location: " . BASE_URL . "posts.php");
    exit;
}
?>
<nav class="navbar navbar-default search-sort-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="posts.php">View All <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="posts.php?status=published">Published</a></li>
            <li><a href="posts.php?status=draft">Drafts</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="posts.php">View all</a></li>
          </ul>
        </li>
      </ul>
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <i class="fa fa-search"></i>
      </button>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="posts.php">View All <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="posts.php?status=published">Published</a></li>
            <li><a href="posts.php?status=draft">Drafts</a></li>
              
            <li class="dropdown-submenu">
                <a class="test" tabindex="-1" href="#">Category <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <?php while ($row = $allCat->fetch_assoc()) { ?>
                  <li><a tabindex="-1" href="posts.php?catid=<?php echo $row['cat_id'] ?>"><?php echo $row["category"]; ?></a></li>
                  <?php } ?>               
                </ul>
            </li>
              
            <li role="separator" class="divider"></li>
            <li><a href="posts.php">View all</a></li>
          </ul>
        </li>
      </ul>
<?php
// If the sort result has no value, a respective message is placed under the sort navigation
if (!isset($search) && $result->num_rows == 0 && !isset($status) && !isset($catId)) {
    echo "<h1 class='text-center'>There are no posts.</h1>
         <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
} elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "draft") {    
    echo "<h1 class='text-center'>There are no pending posts.</h1>
         <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";    
} elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "published") {
    echo "<h1 class='text-center'>There are no published posts.</h1>
         <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
} elseif (!isset($search) && $result->num_rows == 0 && isset($catId)) {
    echo "<h1 class='text-center'>There are no posts for this category.</h1>
         <p class='text-center'><a href='posts.php?source=insert_post'>Add New Post</a></p>";
} else {
?>
      <ul class="nav navbar-nav navbar-right">
        <?php if (isset($searchError) && $searchError != "") {
        echo "<p class='error'>{$searchError}</p>";
        } ?>
        <form action="" method="post" class="navbar-form navbar-left" role="search" style="">
          <div class="form-group">
            <input type="text" class="form-control" id="auth-name" placeholder="Search by author" name="by_authname" value="<?php if (isset($byAuthname)) {
                echo $byAuthname; 
              } ?>">
          </div>
          <div class="form-group">
            <input type="text" class="form-control" id="post-title" placeholder="Search by title" name="by_title" value="<?php if (isset($byTitle)) {
                echo $byTitle; 
              } ?>">
          </div>
          <button type="submit" name="search_posts" class="btn gray-btn">Search</button> <!-- btn btn-default -->
        </form>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>
<div class="view-content col-xs-12">
<label class="inline-label">Choose an action:</label>
<form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
    <div id="bulkOptionContainer" class="col-xs-12">
    <!-- bulk_options gets its value from an option element when it is selected -->
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
    <table class="table"> <!-- Add "table-bordered" to add borders -->
        <thead>
            <tr>
                <th><input id="selectAllBoxes" type="checkbox"></th>
                <th>Id</th>
                <th>Featured</th>
                <th>Title</th>
                <th>Author</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Category</th>
                <th>Image</th>
                <th>Caption</th>
                <th>Views</th>
                <th>Comments</th>
                <th>Status</th>                
                <th>Edit</th>
                <?php if ($currRole == "admin") { ?>
                <th>Delete</th>
                <?php } ?>
            </tr>
        </thead>
        <?php if (empty($searchError)) { ?>
        <tbody>        
        <?php 
        // Display posts in the table
        while ($row = $result->fetch_assoc()) {
            $post_id       = $row["post_id"];
            $image_id      = $row["image_id"];
            $auth_uid      = $row["auth_uid"];
            $featured      = $row["featured"];
            $title         = $row["title"];
            $post_auth     = $row["post_auth"];
            $post_date     = $row["post_date"];
            $updated       = (isset($row["updated"]) && $row["updated"] != "0000-00-00" ? $row["updated"] : "&mdash;");
            $post_image    = "images/post_images/{$row['filename']}";
            $post_views    = $row["post_views"]; // value is set in post.php
            $caption       = $row["caption"];
            $post_comments = $row["post_comments"];
            $post_status   = ucwords($row["post_status"]);            
            $disabled      = "";
            
            if ($currRole == "author") {
                if ($auth_uid != $uid) {
                    $disabled = "disabled";
                }
            }
            echo "<tr>";            
            if ($currRole == "author") {
                if ($auth_uid == $uid) {
                    echo "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
                } else {
                    echo "<td><input class='checkBoxes disabled' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
                }
            } else {
                echo "<td><input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value={$post_id}></td>";
            }            
            echo "<td class='td-bold'>{$post_id}</td>";            
            echo "<td>";
            ?>            
            <input type="radio" name="set_featpost" value="" data-postid="<?php echo $post_id; ?>" class="btn set-featpost<?php
            echo ' ' . $disabled; ?>"                  
            <?php            
            if ($featured == "Yes") {                
                echo "checked";
            } elseif ($featured == "No") {
                echo "unchecked";
            }                   
            ?>>            
            <?php    
            echo "</td>";           
            echo "<td class='td-left'><a href='../post.php?postid={$post_id}'>{$title}</a></td>";
            echo "<td class='td-left'>{$post_auth}</td>"; 
            echo "<td class='td-bold td-date'>{$post_date}</td>";
            echo "<td class='td-bold td-date'>{$updated}</td>";

            $getCategories = "SELECT * FROM categories
                             LEFT JOIN postxcat USING (cat_id)
                             WHERE postxcat.post_id = {$post_id}                              
                             ORDER BY category ASC";
            
            $categories = $conn->query($getCategories);            
            $cats = array();
            
            while ($row = $categories->fetch_assoc()) {                
                $catId  = $row['cat_id'];
                $cats[] = $row['category'];
            }
            ?>                
            <td class="td-left">              
                <?php            
                $catlist = rtrim(implode(', ', $cats));
                echo $catlist;
                ?>             
            </td>            
            <?php
            if ($image_id != NULL) { 
                echo "<td><img width='100' src='{$post_image}'></td>";
                echo "<td class='td-left'>{$caption}</td>";
            } else { 
                echo "<td>No Image</td>";
                echo "<td></td>";
            }
            echo "<td>{$post_views}</td>"; 
            echo "<td>{$post_comments}</td>"; 
            echo "<td class='td-bold td-status'>{$post_status}</td>";
            
            if ($currRole == "author") {
                if ($auth_uid == $uid) {
                    echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";  
                } else {
                    echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-redtext-btn disabled'>Disabled</a></td>";
                }                
            } else {
                echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";
            }            
            if ($currRole == "admin") {
            // The post_id of the post the user wants to delete is needed when the Delete link is clicked;
            // When this link (a.delete) is clicked, JS attaches a click event to it that invokes the modal and passes
            // it the post_id through the rel attribute (see the JS)
            echo "<td><a rel='{$post_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
            }            
            echo "</tr>"; // close the table row
        }
        ?>
        </tbody>
        <?php } ?>
    </table>    
</form>
</div>

<?php
}
// If delete_item (name of submit input element in modal) is set
if (isset($_POST["delete_item"])) {    
    $del_id = $_POST['id'];    
    $getCommId = $conn->query("SELECT * FROM postxcomment WHERE post_id = {$post_id}");
    confirmQuery($getCommId);
    while ($row = $getCommId->fetch_assoc()) {
        $commentId = $row["comment_id"];
        $deleteComments = $conn->query("DELETE FROM comments WHERE comment_id = {$commentId}");                
        confirmQuery($deleteComments); 
    }
    
    $deletePost = $conn->query("DELETE from posts WHERE post_id = {$del_id}");    
    header("Location: posts.php"); // refresh the page
    exit;
}
?>