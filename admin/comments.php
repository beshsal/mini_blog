<?php
include "includes/admin_header.inc.php";

// Comments can be sorted by status.
// If the user selects an status from the Sort by dropdown menu, it's value is sent through the URL query string. 
// If "status" is successfully retrieved from the URL query string, save it in $status.
if (isset($_GET["status"])) {
    if (!is_numeric($_GET["status"]) 
        && $_GET["status"] == "approved" 
        || $_GET["status"] == "unapproved") 
    {
        $status = $conn->real_escape_string($_GET["status"]);
    } else {
        header("Location: " . BASE_URL . THIS_PAGE);
        exit;
    }
}

// Get the name of the current page and the current user's user_id.
$pageName    = basename(THIS_PAGE, ".php"); // comments
$currRole    = $_SESSION["role"]; // either admin or author
$currUname   = $_SESSION['username'];
$currAuthUid = $conn->query("SELECT user_id FROM auth_profile WHERE username = '{$currUname}'"); confirmQuery($currAuthUid);
$row         = $currAuthUid->fetch_array();
$authUid     = $row["user_id"];

// Initialize a variable to hold search errors.
$searchError = "";

// Get comments to display them.
// If the currently logged-in user is an admin, get all comments.
if ($currRole == "admin") {
    $query = "SELECT * FROM comments
             LEFT JOIN postxcomment USING (comment_id)
             LEFT JOIN posts USING (post_id)
             WHERE comments.comment_id = postxcomment.comment_id
             ORDER BY comment_date DESC";
// Otherwise, if the currently logged-in user is an author, get only the author's comments.
} else {
    $query = "SELECT * FROM comments
             LEFT JOIN postxcomment USING (comment_id)
             LEFT JOIN posts USING (post_id)
             WHERE comments.comment_id = postxcomment.comment_id
             AND posts.auth_uid = {$authUid}
             ORDER BY comment_date DESC";
}

$result = $conn->query($query);
confirmQuery($result);

// Under the Sort by dropdown menu, the user can select either an Approved or Unapproved status.
if ($currRole != "author") {            
    if (isset($status)) {
        if ($status == "approved" || $status == "unapproved") {
            $getComments = "SELECT * FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.comment_status = '{$status}'
                           ORDER BY comment_date DESC";
            
            $result = $conn->query($getComments);
            confirmQuery($result);
        } 
    }
} else {
    if (isset($status)) {
        if ($status == "approved" || $status == "unapproved") {
            $getComments = "SELECT * FROM comments
                           LEFT JOIN postxcomment USING (comment_id)
                           LEFT JOIN posts USING (post_id)
                           WHERE comments.comment_id = postxcomment.comment_id
                           AND comments.comment_status = '{$status}'
                           AND posts.auth_uid = {$authUid}
                           ORDER BY comment_date DESC";
        }
        $result = $conn->query($getComments);
        confirmQuery($result);
    }
}

// Search comments by an author's name and/or by the title of a post.
// Authors can only search by title.
if (isset($_POST["search_comments1"])) {
    if ($currRole == "admin") {
        $byAuthname = trim($_POST["by_authname"]);
        $byAuthname = $conn->real_escape_string($byAuthname);
    }
    $byTitle = trim($_POST["by_title"]);
    $byTitle = $conn->real_escape_string($byTitle);
    $srchParams = "?search=comments";
    
    if (isset($byAuthname) && !empty($byAuthname)) {
        $srchParams .= "&srch1={$byAuthname}";
    }
    
    if (isset($byTitle) && !empty($byTitle)) {
        $srchParams .= "&srch2={$byTitle}";
    }
    
    // Refresh the page and add the search parameters to the URL query string.
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If a search is submitted from the first search option, get the search term(s) from the URL query string.
if (isset($_GET["search"]) && $_GET["search"] != "comments2") {
    // If a title is submitted, set its value to $search1; otherwise, set $search1 to an empty string.
    // If an author name is submitted, set its value to $search2; otherwise, set $search2 to an empty string.
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";

    // If at least one of the fields is filled (not empty)
    if (!empty($search1) || !empty($search2)) { 
        if ($currRole == "author") {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.title LIKE '%" . $search2 . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.post_auth LIKE '%" . $search1 . "%'
                            AND posts.title LIKE '%" . $search2 . "%' 
                            ORDER BY comment_date DESC";  
        }
    }    
    // If empty fields are submitted (search terms are empty), add an error message to $searchError.
    if (empty($findComments)) {
        if ($currRole == "admin") {
            $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
        } else {
            $searchError = "The field cannot be empty! Please enter a search term.";
        }
    // Otherwise, run the query.
    } else {        
        $result = $conn->query($findComments);
        confirmQuery($result);
        $search = $result;
        // If no results are found, add an error message to $searchError.
        if ($search->num_rows == 0) {
            confirmQuery($search);
            if ($currRole == "admin") {
                $searchError = "No results found! The search term(s) may be misspelled or a matching comment record may not exist.";
            } else {
                $searchError = "No result found! The search term may be misspelled or a matching comment record may not exist.";
            }
        }
    }
}

// Search for a comment by the commenter's name or a username.
if (isset($_POST["search_comments2"])) {
    $byUser     = trim($_POST["by_user"]);
    $byUsername = trim($_POST["by_username"]);   
    $byUser     = $conn->real_escape_string($byUser);
    $byUsername = $conn->real_escape_string($byUsername);    
    $srchParams = "?search=comments2";
    
    if (isset($byUser) && !empty($byUser)) {
        $srchParams .= "&srch1={$byUser}";
    }
    
    if (isset($byUsername) && !empty($byUsername)) {
        $srchParams .= "&srch2={$byUsername}";
    }
    
    // Refresh the page and add the search parameters to the URL query string.
    header("Location: " . BASE_URL . THIS_PAGE . $srchParams);
}

// If a search is submitted from the second search option, get the search term(s) from the URL query string.
if (isset($_GET["search"]) && $_GET["search"] == "comments2") {
    // If a commenter's name is submitted, set its value to $search1; otherwise, set $search1 to an empty string.
    // If a username is submitted, set its value to $search2; otherwise, set $search2 to an empty string.
    $search1 = isset($_GET["srch1"]) ? trim($_GET["srch1"]) : "";
    $search2 = isset($_GET["srch2"]) ? trim($_GET["srch2"]) : "";

    // If at least one of the fields is filled (not empty)
    if (!empty($search1) || !empty($search2)) {
        if ($currRole == "author") {        
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $search1 . "%'
                            AND comments.username LIKE '%" . $search2 . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $search1 . "%'
                            AND comments.username LIKE '%" . $search2 . "%'
                            ORDER BY comment_date DESC";  
        }
    }
    // If empty fields are submitted, add an error message to $searchError.
    if (empty($findComments)) {
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
    // Otherwise, run the query.      
    } else {        
        $result = $conn->query($findComments);
        confirmQuery($result);
        $search = $result;
        // If no results are found, add an error message to $searchError.       
        if ($search->num_rows == 0) {
            $searchError = "No results found! The search term(s) may be misspelled or a matching comment record may not exist.";
        }
    }
}

// "checkBoxArray" is the name of the checkbox input element created for each comment; the value(s) of the checkBoxArray array must
// be captured to know which comments to apply the action to.

if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST['checkBoxArray'] as $commentValueId) {
        // "bulk_options" is the name of the select element; this takes the value from the select element (the value of an 
        // option element when selected) and assigns it to a variable, which will be used in the switch statement.
        $bulk_options = $_POST['bulk_options'];

        switch($bulk_options) {
            case "approved":                
            $setStatus = "UPDATE comments SET comment_status = '{$bulk_options}' WHERE comment_id = {$commentValueId}";                    
            $commentStatus = $conn->query($setStatus); 
            confirmQuery($commentStatus);                        
            break;
            
            case "unapproved":                
            $setStatus = "UPDATE comments SET comment_status = '{$bulk_options}' WHERE comment_id = {$commentValueId}";                    
            $commentStatus = $conn->query($setStatus);                        
            confirmQuery($commentStatus);            
            break;
            
            case "delete_selected":                    
            $deleteComments = $conn->query("DELETE FROM comments WHERE comment_id = {$commentValueId}");                        
            confirmQuery($deleteComments);            
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
                            <li class="pull-left"><a href="comments.php">View all <span class="sr-only">(current)</span></a></li>
                            <li class="dropdown pull-left">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
                              <ul class="dropdown-menu">
                                <li><a href="comments.php?status=approved">Approved</a></li>
                                <li><a href="comments.php?status=unapproved">Unapproved</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="comments.php">View all</a></li>
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
                            <li class="pull-left"><a href="comments.php">View all <span class="sr-only">(current)</span></a></li>
                            <li class="dropdown pull-left">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Sort by: <span class="caret"></span></a>
                              <ul class="dropdown-menu">
                                <li><a href="comments.php?status=approved">Approved</a></li>
                                <li><a href="comments.php?status=unapproved">Unapproved</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="comments.php">View all</a></li>
                              </ul>
                            </li>
                          </ul> 
                          <ul id="search-wrapper" class="nav navbar-nav navbar-right">                                                       
                            <form id="searchCommForm" action="" method="post" class="navbar-form navbar-left show" role="search">
                                <div id="fieldset1">
                                    <?php if ($currRole == "admin") { ?>
                                        <label class="search-label">Search for comments by post author and/or title:</label>
                                    <?php } else { ?>
                                        <label class="search-label text-right">Search for comments by post title:</label>
                                    <?php }
                                    if ($currRole == "admin") { ?>
                                    <div class="form-group">        
                                    <input type="search" class="form-control" id="post_auth" placeholder="Search by author" name="by_authname" value="<?php if(isset($byAuthname) && $searchError != ""){echo $byAuthname;} ?>">
                                    </div>
                                    <?php } ?>
                                    <div class="form-group">
                                    <input type="search" class="form-control" id="post-title" placeholder="Search by post title" name="by_title" value="<?php if(isset($byTitle) && $searchError != ""){echo $byTitle;} ?>">
                                    </div>    
                                    <button type="submit" name="search_comments1" class="btn gray-btn">Search</button>
                                </div>
                                <div id="fieldset2" style="display:none">
                                    <label class="search-label">Search for comments by user:</label>
                                    <div class="form-group">
                                    <input type="search" class="form-control" id="user" placeholder="Search by name" name="by_user" value="<?php if(isset($byUser) && $searchError != ""){echo $byUser;} ?>">
                                    </div>
                                    <div class="form-group">
                                    <input type="search" class="form-control" id="uname" placeholder="Search by username" name="by_username" value="<?php if(isset($byUsername) && $searchError != "") { echo $byUsername; } ?>">
                                    </div>
                                    <button type="submit" name="search_comments2" class="btn gray-btn">Search</button>
                                </div>
                                <div class="form-group switchFields">
                                    <label class="switchFields-label">
                                        Search by user<input name="switchfieldset" class="switchfieldset" type="checkbox" style="margin-left: 4px;" <?php
                                        if(isset($_GET["search"]) && $_GET["search"] == "comments2") echo "checked='checked'";                   
                                        ?>>
                                    </label>
                                </div>    
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
                        
                    if (!isset($search) && $result->num_rows == 0 && !isset($status)) {
                        echo "<h1 class='text-center'>There are no comments.</h1>";
                    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "approved") {
                        echo "<h1 class='text-center'>There are no approved comments.</h1>";
                    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "unapproved") {
                        echo "<h1 class='text-center'>There are no pending comments.</h1>";   
                    } else {
                    ?>
                    <label class="inline-label">Choose an action:</label>
                    <form id="bulkform" action="" method="post" onsubmit="return confirmDelete()">
                        <div id="bulkOptionContainer" class="col-xs-12">
                        <select class="form-control" name="bulk_options" id="bulkoptions">
                            <option value="">Select Option</option>
                            <option value="approved">Approve</option>
                            <option value="unapproved">Deny</option>
                            <option value="delete_selected">Delete</option>
                        </select>
                        </div>
                        <div class="apply-div col-xs-12">
                            <button type="submit" name="submit" class="btn standard-btn" style="" id="apply">
                            Apply
                            </button>
                        </div>                        
                        <table class="table"> 
                            <?php if (isset($search) && $search->num_rows > 0) { 
                                if (isset($byAuthname) || isset($byUser)) {
                                    $srchTerm1 = isset($byAuthname) ? $byAuthname : $byUser;
                                }
                                if (isset($byTitle) || isset($byUsername)) {
                                    $srchTerm2 = isset($byTitle) ? $byTitle : $byUsername;
                                }                                
                            ?>
                            <div class="query-total" id="qt-comments">
                                <p>
                                    Search: 
                                    <span><?php
                                    if (isset($byAuthname) && !empty($srchTerm1)) echo 'Author: '.'"'.$srchTerm1.'"';
                                    elseif (isset($byUser) && !empty($srchTerm1)) echo 'Name: '.'"'.$srchTerm1.'"';
                                    ?></span> 
                                    <?php if (!empty($srchTerm1) && !empty($srchTerm2)) echo " | ";  ?>
                                    <span><?php
                                    if (isset($byTitle) && !empty($srchTerm2)) echo 'Title: '.'"'.$srchTerm2.'"';
                                    elseif (isset($byUsername) && !empty($srchTerm2)) echo 'Username: '.'"'.$srchTerm2.'"';
                                    ?></span> 
                                    Results: 
                                    <span><?php echo $search->num_rows; ?></span>
                                </p>
                            </div>
                            <?php } else { ?>
                            <div class="query-total" id="qt-comments">
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
                                    <th>User</th>
                                    <th>Userid</th>
                                    <th>Username</th>
                                    <th>Comment</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Reply To</th>
                                    <th>Post</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Approve</th>
                                    <th>Unapprove</th>                                    
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
                            // Display comments in the table. 
                            while ($row = $result->fetch_assoc()) {
                                $post_id         = $row["post_id"];
                                $post_auth       = $row["post_auth"];
                                $title           = $row["title"];
                                $comment_id      = $row["comment_id"];
                                $parent_id       = $row["parent_id"];
                                $comment_auth    = $row["comment_auth"];
                                $user_id         = $row["user_id"];
                                $username        = $row["username"];
                                $comment_content = $row["comment_content"];
                                $comment_email   = $row["comment_email"];
                                $comment_status  = ucfirst($row["comment_status"]);
                                $comment_date    = $row["comment_date"];
                                echo "<tr>";
                                ?>
                                <td>
                                <input class='checkBoxes' type='checkbox' name='checkBoxArray[]' value='<?php echo $comment_id; ?>'>
                                </td>
                                <?php
                                echo "<td class='td-bold'>{$comment_id}</td>";
                                echo "<td class='td-left'>{$comment_auth}</td>";
                                echo "<td>{$user_id}</td>";
                                echo "<td>{$username}</td>";
                                echo "<td class='td-left'>{$comment_content}</td>";
                                echo "<td>{$comment_email}</td>"; ?>
                                
                                <td class="td-status td-bold <?php echo ($comment_status == 'Approved') ? 'td-apprvd' : 'td-denied'; ?>">
                                <?php echo $comment_status; ?>
                                </td>
                                <td style="min-width: 100px;">                                
                                <?php if (!empty($parent_id) && $parent_id != 0) { 
                                    echo "ID : <a href='../post/1/hello-world#comment_id{$parent_id}'>{$parent_id}</a>";
                                } ?>
                                </td> 
                                <?php
                                echo "<td class='td-left'><a href='../post/{$post_id}/" . formatUrlStr($title) . "'>{$title}</a></td>";
                                echo "<td class='td-left'>{$post_auth}</td>";
                                echo "<td class='td-bold td-date'>{$comment_date}</td>";                                
                                // Button links for approving or denying single comments
                                // The comment_id is passed through the URL query string and used in a SQL query.
                                echo "<td><a href='comments.php?approve={$comment_id}' class='btn gray-btn'>Approve</a></td>";
                                echo "<td><a href='comments.php?deny={$comment_id}' class='btn gray-redtext-btn'>Deny</a></td>";
                                echo "<td><a rel='{$comment_id}' data-page='{$pageName}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                                echo "</tr>";
                            }
                            ?>
                            </tbody> 
                            <?php } ?>
                        </table>                        
                    </form>
                    </div>
                    <?php  
                    }
                    // Conditions for the button links for approving or denying a comment
                    if (isset($_GET["approve"])) {
                        $commentId      = $_GET["approve"];                        
                        $approveComment = $conn->query("UPDATE comments SET comment_status = 'approved' WHERE comment_id = {$commentId}");
                        confirmQuery($approveComment);
                        header("Location: " . BASE_URL . THIS_PAGE);
                    }
                    if (isset($_GET["deny"])){
                        $commentId   = $_GET["deny"];                        
                        $denyComment = $conn->query("UPDATE comments SET comment_status = 'unapproved' WHERE comment_id = {$commentId}");
                        confirmQuery($denyComment);
                        header("Location: " . BASE_URL . THIS_PAGE);
                    }
                    if (isset($_POST["delete_item"])) {
                        $del_id        = $_POST["id"];
                        $deleteComment = $conn->query("DELETE from comments WHERE comment_id = {$del_id}");
                        $deleteChild   = $conn->query("DELETE from comments WHERE parent_id = {$del_id}");
                        header("Location: " . BASE_URL . THIS_PAGE); // refresh the page
                    } 
                    ?>                    
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->

<?php include "includes/admin_footer.inc.php"; ?>