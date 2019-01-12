<?php
// Posts can be sorted by status.
// If the user selects a status from the Sort by dropdown menu, it's value is sent through the URL query string. 
// If "status" is successfully retrieved from the URL query string, save its value to $status.
if (isset($_GET["status"])) {
    if (!is_numeric($_GET["status"])) {
        $status = $conn->real_escape_string($_GET["status"]);
    } else {
        header("Location: " . BASE_URL . THIS_PAGE); // posts.php
        exit;
    }
}

// Posts can be sorted by category.
// if the user selects a category from the Sort by dropdown menu, "catid" containing the selected category's cat_id 
// is sent through the URL query string. 
// If "catid" is successfully retrieved from the URL query string and has a numeric value, save it in $catId.
if (isset($_GET["catid"])) {
    if (!is_numeric($_GET["catid"])) {
        header("Location: " . BASE_URL . THIS_PAGE); // posts.php
        exit;
    } else {
        $catId = (int) $_GET["catid"]; // casting to ensure the value really is numeric (an integer) and not a string
    }
}

// Get the name of the current page and the current user's user_id.
$pageName  = basename(THIS_PAGE, ".php"); // posts
$currRole  = $_SESSION["role"]; // either admin or author
$currUname = $_SESSION["username"];
$currUid   = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row       = $currUid->fetch_array();
$uid       = $row["user_id"];

// Initialize a variable to hold search errors.
$searchError = "";

// Get all posts to display by default.
$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)
         ORDER BY post_id ASC"; // originally ORDER BY post_date DESC

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

// Under the Sort by dropdown menu, the user can select either a Published or Drafts status.
if (isset($status)) {
    switch($status) {            
        case "published":
        if (isset($_GET["user"])) {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'published'
                        AND posts.auth_uid = {$uid}
                        ORDER BY post_id ASC";  
        } else {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'published'
                        ORDER BY post_id ASC";
        }
            
        $result = $conn->query($getPosts);
        confirmQuery($result);
        break;

        case "draft":
        if (isset($_GET["user"])) {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'draft'
                        AND posts.auth_uid = {$uid}
                        ORDER BY post_id ASC";
        } else {
            $getPosts = "SELECT * FROM posts
                        LEFT JOIN images USING (image_id)
                        WHERE post_status = 'draft'
                        ORDER BY post_id ASC";    
        }
            
        $result = $conn->query($getPosts);
        confirmQuery($result);
        break;

        default: 
        header("Location: " . BASE_URL . THIS_PAGE);
    }   
}

// If an admin clicks the link on index.php to view his or her own posts, "user" is sent through the URL, triggering
// a query to run that selects only posts by the admin.
if (isset($_GET["user"]) && !isset($status)) {
    $getPosts = "SELECT * FROM posts
                LEFT JOIN images USING (image_id)
                WHERE posts.auth_uid = {$uid}
                ORDER BY post_id ASC";
    
    $result = $conn->query($getPosts);
    confirmQuery($result);
}

// Under the Sort by dropdown menu, the user can select a category. This will display all posts with the selected category
// if there are any.
if (isset($catId)) {
    if (isset($_GET["user"])) {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    LEFT JOIN postxcat USING (post_id)
                    WHERE postxcat.cat_id = {$catId}
                    AND posts.auth_uid = {$uid}
                    ORDER BY post_id ASC";
    } else {
        $getPosts = "SELECT * FROM posts
                    LEFT JOIN images USING (image_id)
                    LEFT JOIN postxcat USING (post_id)
                    WHERE postxcat.cat_id = {$catId}
                    ORDER BY post_id ASC";
    }
    
    $result = $conn->query($getPosts);
    confirmQuery($result);
}

// Search posts by author and/or title.
if (isset($_POST["search_posts"])) {
    // Get the field values.
    $byAuthname = trim($_POST["by_authname"]);
    $byTitle    = trim($_POST["by_title"]);
    $byAuthname = $conn->real_escape_string($byAuthname);
    $byTitle    = $conn->real_escape_string($byTitle);    
    $srchParams = "?search=posts";
    
    if (isset($byAuthname) && !empty($byAuthname)) {
        $srchParams .= "&srch1={$byAuthname}";
    }
    
    if (isset($byTitle) && !empty($byTitle)) {
        $srchParams .= "&srch2={$byTitle}";
    }
    
    if (isset($_GET["user"])) {
        $srchParams .= "&user=" . $_GET["user"];
    }
    
    // Refresh the page and add the search parameters to the URL query string.
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If the user submits a search, a "search" parameter is added to the URL query string.
if (isset($_GET["search"])) {
    // Search terms are retrieved from from the URL query string and used in an SQL query to
    // retrieve the results.
    
    // If a title is submitted, set its value to $search1; otherwise, set $search1 to an empty string.
    // If an author name is submitted, set its value to $search2; otherwise, set $search2 to an empty string.
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";
    
    if (!empty($search1) || !empty($search2)) {
        if (isset($_GET["user"])) {
            $findPosts = "SELECT * FROM posts
                         LEFT JOIN images USING (image_id)
                         WHERE posts.title LIKE '%" . $search2 . "%'
                         AND posts.auth_uid = {$uid}
                         ORDER BY post_id ASC";
        } else {
            $findPosts = "SELECT * FROM posts
                         LEFT JOIN images USING (image_id)
                         WHERE posts.post_auth LIKE '%" . $search1 . "%'
                         AND posts.title LIKE '%" . $search2 . "%'
                         ORDER BY post_id ASC";
        }
    }

    // If empty fields are submitted (search terms are empty), add an error message to $searchError.
    if (empty($findPosts)) {
        if (isset($_GET["user"])) {
            $searchError = "The search field cannot be empty! Please enter a post title.";
        } else {
            $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
        }
    // Otherwise, run the query.       
    } else {        
        $result = $conn->query($findPosts);
        confirmQuery($result);
        $search = $result;
        // If no results are found, add an error message to $searchError.
        if ($search->num_rows == 0) {
            if (isset($_GET["user"])) {
                $searchError = $_SESSION["firstname"] . " is not an author of a post that matches the title you entered. 
                               <br>The title may be misspelled or the record may not exist.";    
            } else {
                $searchError = "No results found! The search term(s) may be misspelled or the record may not exist.";
            }
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
            $setStatus = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                
            $postStatus = $conn->query($setStatus);
            confirmQuery($postStatus);                        
            break;     
            // If the "draft" option is selected, set the status of the checked post(s) to "draft".    
            case "draft":                
            $setStatus = "UPDATE posts SET post_status = '{$bulk_options}' WHERE post_id = {$postValueId}";                
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
    // Redirect the user back to the posts page (view_posts).
    if (isset($_GET["user"])) {
        header("Location: " . BASE_URL . THIS_PAGE . "?user=" . $currUname);
        exit;
    } else {
        header("Location: " . BASE_URL . THIS_PAGE);
        exit;
    }
}
?>
<nav class="navbar navbar-default search-sort-nav">
  <div class="container-fluid">
    <div class="navbar-header">
      <ul class="nav nav-pills">          
        <li class="pull-left"><a href="<?php if (isset($_GET['user'])) echo 'posts.php?user=' . $_GET['user']; else echo 'posts.php'; ?>">View all <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="posts.php?status=published<?php if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>">Published</a></li>
            <li><a href="posts.php?status=draft<?php if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>">Drafts</a></li>
            <!-- Display all categories in the dropdown. -->
            <li class="dropdown-submenu">
                <a class="sub-dropdown" tabindex="-1" href="#">Category <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <?php if ($catData) : foreach ($catData as $row) { ?>
                  <li><a tabindex="-1" href="posts.php?catid=<?php echo $row['cat_id']; if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>"><?php echo $row["category"]; ?></a></li>
                  <?php } else : echo "<li class='text-center'>No categories</li>"; endif; ?>               
                </ul>
            </li>
            <li role="separator" class="divider"></li>
            <li><a href="<?php if (isset($_GET['user'])) echo 'posts.php?user=' . $_GET['user']; else echo 'posts.php'; ?>">View all</a></li>
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
        <li class="pull-left"><a href="<?php if (isset($_GET['user'])) echo 'posts.php?user=' . $_GET['user']; else echo 'posts.php'; ?>">View all <span class="sr-only">(current)</span></a></li>
        <li class="dropdown pull-left">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="posts.php?status=published<?php if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>">Published</a></li>
            <li><a href="posts.php?status=draft<?php if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>">Drafts</a></li>
            <!-- Display all categories in the dropdown. -->
            <li class="dropdown-submenu">
                <a class="sub-dropdown" tabindex="-1" href="#">Category <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <?php if ($catData) : foreach ($catData as $row) { ?>
                  <li><a tabindex="-1" href="posts.php?catid=<?php echo $row['cat_id']; if(isset($_GET["user"])) {echo "&user=" . $_GET["user"];} ?>"><?php echo $row["category"]; ?></a></li>
                  <?php } else : echo "<li class='text-center'>No categories</li>"; endif; ?>               
                </ul>
            </li>
            <li role="separator" class="divider"></li>
            <li><a href="<?php if (isset($_GET['user'])) echo 'posts.php?user=' . $_GET['user']; else echo 'posts.php'; ?>">View all</a></li>
          </ul>
        </li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <form action="" method="post" class="navbar-form navbar-left" role="search" style="">
          <label class="search-label">Search for posts:</label>
          <?php if (!isset($_GET["user"])) { ?>
          <div class="form-group">
            <input type="search" class="form-control" id="auth-name" placeholder="Search by author" name="by_authname" value="<?php if (isset($byAuthname) && $searchError != "") {
                echo $byAuthname; 
              } ?>">
          </div>
          <?php } ?>
          <div class="form-group">
            <input type="search" class="form-control" id="post-title" placeholder="Search by title" name="by_title" value="<?php if (isset($byTitle) && $searchError != "") {
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
<?php
// If there is a search error, display it.
if (isset($searchError) && $searchError != "") {
    echo "<p class='error'>{$searchError}</p>";
} 
    
// If the sort result has no value, a respective message is placed under the sort navigation.
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
<label class="inline-label">Choose an action:</label>
<form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
    <div id="bulkOptionContainer" class="col-xs-12">
    <!-- bulk_options gets its value from an option element when it is selected -->
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
    <!-- 
    When the chevron by the ID column is clicked, the value of this hidden field is accessed and used to specify the order in which to
    display the posts table's data.
    -->
    <input type="hidden" id="order" value="DESC">
    <table class="table" id="posts-table"> <!-- Add "table-bordered" to add borders -->
        <?php if (isset($search) && $search->num_rows > 0) { 
            if (isset($byAuthname)) {
                $srchTerm1 = $byAuthname;
            }
            if (isset($byTitle)) {
                $srchTerm2 = $byTitle;
            }                                
        ?>
        <div class="query-total">
            <p>
                Search: 
                <span><?php
                if (isset($byAuthname) && !empty($srchTerm1)) echo 'Author: '.'"'.$srchTerm1.'"';
                ?></span> 
                <?php if (!empty($srchTerm1) && !empty($srchTerm2)) echo " | ";  ?>
                <span><?php
                if (isset($byTitle) && !empty($srchTerm2)) echo 'Title: '.'"'.$srchTerm2.'"';
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
                <!-- <th>Id <i class="fa fa-chevron-down" onclick="sortTable('post_id');"></i></th> -->
                <th>Id 
                <i class="fa fa-chevron-down"
                   id="sort-table"
                   data-column="post_id"
                   data-status="<?php if (isset($status)) echo $status; ?>" 
                   data-catid="<?php if (isset($catId)) echo $catId; ?>" 
                   data-user="<?php if (isset($_GET["user"])) echo $_GET["user"]; ?>"
                   data-srch1="<?php if (isset($_GET["srch1"])) echo $_GET["srch1"]; ?>" 
                   data-srch2="<?php if (isset($_GET["srch2"])) echo $_GET["srch2"]; ?>">
                </i>
                </th>
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
        // Display posts in the table.
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
            $post_views    = $row["post_views"]; // the value is set in post.php
            $caption       = $row["caption"];
            $post_comments = $row["post_comments"];
            $post_status   = ucwords($row["post_status"]);            
            $disabled      = "";
            
            // This is used to ensure that only an admin has access to setting any post to a featured post. 
            // Authors can only set one of their own posts to a featured post (may disable this feature for authors).

            // If the currently logged-in author's user_id is not the same as a post record's user_id,
            // then "disabled" is added to the field.
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
            
            if (isset($status) && $status == "draft" || $row["post_status"] == "draft") {
                echo "<input type=radio class='disabled'>";
            } else {
            ?> 
            <!-- Radio button for setting the featured post
            Note the disabled attribute ($disabled). -->
            
            <input type="radio" name="set_featpost" value="<?php echo $post_id; ?>" data-postid="<?php echo $post_id; ?>" class="btn set-featpost<?php echo ' ' . $disabled; ?>"                  
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
            <?php }    
            echo "</td>";           
            // echo "<td class='td-left'><a href='../post.php?postid={$post_id}'>{$title}</a></td>";
            echo "<td class='td-left'><a href='../post/{$post_id}/" . formatUrlStr($title) . "'>{$title}</a></td>";
            echo "<td class='td-left'>{$post_auth}</td>"; 
            echo "<td class='td-bold td-date'>{$post_date}</td>";
            echo "<td class='td-bold td-date'>{$updated}</td>";

            // Get the categories associated with each post.
            $getCategories = "SELECT * FROM categories
                             LEFT JOIN postxcat USING (cat_id)
                             WHERE postxcat.post_id = {$post_id}                              
                             ORDER BY category ASC";
            
            $categories = $conn->query($getCategories);            
            $cats = array();
            
            while ($row = $categories->fetch_assoc()) {
                $catId  = $row["cat_id"];
                $cats[] = $row["category"];
            }
            ?>                
            <td class="td-left">              
                <?php  
                // Categories are separated by commas.          
                $catlist = rtrim(implode(', ', $cats));
                echo $catlist;
                ?>             
            </td>            
            <?php
            // If there is an image, display it. Otherwise inform the user there is no image.
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
            
            // An author may edit only his or her own post(s). A disabled attribute is added to other posts, which prevents an
            // author from editing them. However, authors may still view other posts.
            if ($currRole == "author") {
                if ($auth_uid == $uid) {
                    echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";  
                } else {
                    echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-redtext-btn disabled'>Disabled</a></td>";
                }                
            } else {
                echo "<td><a href='posts.php?source=update_post&postid={$post_id}' class='btn gray-btn'>Edit</a></td>";
            }   

            // Only admin may delete posts. This displays a delete link on each post for admin.         
            if ($currRole == "admin") {
                // The post_id of the post the user wants to delete is needed when the Delete link is clicked.
                // When the link (a.delete) is clicked, JS attaches a click event to it that invokes the modal and passes
                // it the post_id through the rel attribute (see the respective JS in custom.js, "SINGLE ITEM DELETION").
                if ($featured == "Yes") {
                    echo "<td><a class='btn delete-btn delete disabled'>Delete</a></td>";
                } else {
                    echo "<td><a rel='{$post_id}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                }
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

// If "delete_item", the name of the submit input element in the delete modal (delete_modal.inc.php), is set.
if (isset($_POST["delete_item"])) {   
    // Get the post_id value stored in the hidden input element. 
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
?>