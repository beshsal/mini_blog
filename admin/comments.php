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
$currUname   = $_SESSION['username'];
$currAuthUid = $conn->query("SELECT user_id FROM auth_profile WHERE username = '{$currUname}'"); confirmQuery($currAuthUid);
$row         = $currAuthUid->fetch_array();
$authUid     = $row["user_id"];
$searchError = "";

if ($currRole == "admin") {
    $query = "SELECT * FROM comments
             LEFT JOIN postxcomment USING (comment_id)
             LEFT JOIN posts USING (post_id)
             WHERE comments.comment_id = postxcomment.comment_id
             ORDER BY comment_date DESC";
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

if (isset($_POST["search_comments1"])) {
    if ($currRole == "admin") {
        $byAuthname = $conn->real_escape_string($_POST["by_authname"]);
    }
    
    $byTitle = $conn->real_escape_string($_POST["by_title"]);
    
    // If both fields are filled (not empty)
    If (!empty($byAuthname) && !empty($byTitle)) {        
        if ($currRole == "author") {        
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $byUser . "%'
                            AND posts.title LIKE '%" . $byTitle . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.post_auth LIKE '%" . $byAuthname . "%'
                            AND posts.title LIKE '%" . $byTitle . "%' 
                            ORDER BY comment_date DESC";  
        }        
    // if the author name is not empty but the title is empty
    } elseif (!empty($byAuthname) && empty($byTitle)) {        
        if ($currRole == "author") {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.post_auth LIKE '%" . $byAuthname . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.post_auth LIKE '%" . $byAuthname . "%'
                            ORDER BY comment_date DESC";
        }    
    // If author name is empty but the title is not empty
    } elseif (empty($byAuthname) && !empty($byTitle)) {
        if ($currRole == "author") {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.title LIKE '%" . $byTitle . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE posts.title LIKE '%" . $byTitle . "%' 
                            ORDER BY comment_date DESC";
        }
    }    
    if (empty($findComments)) {
        if ($currRole == "admin") {
            $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";
        } else {
            $searchError = "The field cannot be empty! Please enter a search term.";
        }
    } else {        
        $result = $conn->query($findComments);
        confirmQuery($result);
        $search = $result;
        
        if ($search->num_rows == 0) {
            confirmQuery($search);
            if ($currRole == "admin") {
                $searchError = "No results found! The search term(s) may be mispelled or a matching comment record may not exist.";
            } else {
                $searchError = "No result found! The search term may be mispelled or a matching comment record may not exist.";
            }
        }
    }
}

if (isset($_POST["search_comments2"])) {    
    $byUser = $conn->real_escape_string($_POST["by_user"]);
    $byUsername = $conn->real_escape_string($_POST["by_username"]);    
    // If both fields are filled (not empty)
    If (!empty($byUser) && !empty($byUsername)) {        
        if ($currRole == "author") {        
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $byUser . "%'
                            AND comments.username LIKE '%" . $byUsername . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $byUser . "%'
                            AND comments.username LIKE '%" . $byUsername . "%'
                            ORDER BY comment_date DESC";  
        }    
    } elseif (!empty($byUser) && empty($byUsername)) {        
        if ($currRole == "author") {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $byUser . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.comment_auth LIKE '%" . $byUser . "%'
                            ORDER BY comment_date DESC";
        }
    } elseif (empty($byUser) && !empty($byUsername)) {
        if ($currRole == "author") {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.username LIKE '%" . $byUsername . "%'
                            AND posts.auth_uid = {$authUid}
                            ORDER BY comment_date DESC";
        } else {
            $findComments = "SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            LEFT JOIN posts USING (post_id)
                            WHERE comments.username LIKE '%" . $byUsername . "%' 
                            ORDER BY comment_date DESC";
        }
    }
    
    if (empty($findComments)) {
        $searchError = "Both fields cannot be empty! Please enter a search term in at least one field.";        
    } else {        
        $result = $conn->query($findComments);
        confirmQuery($result);
        $search = $result;        
        if ($search->num_rows == 0) {
            $searchError = "No results found! The search term(s) may be mispelled or a matching comment record may not exist.";
        }
    }
}

// SORT BY
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

if (isset($_POST["checkBoxArray"])) {
    foreach ($_POST['checkBoxArray'] as $commentValueId) {
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
    header("Location: " . BASE_URL . "comments.php");
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
                            <li class="pull-left"><a href="comments.php">View All <span class="sr-only">(current)</span></a></li>
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
                            <li class="pull-left"><a href="comments.php">View All <span class="sr-only">(current)</span></a></li>
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
                            
                    <?php
                    if (!isset($search) && $result->num_rows == 0 && !isset($status)) {
                        echo "<h1 class='text-center'>There are no comments.</h1>";
                    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "approved") {
                        echo "<h1 class='text-center'>There are no approved comments.</h1>";
                    } elseif (!isset($search) && $result->num_rows == 0 && isset($status) && $status == "unapproved") {
                        echo "<h1 class='text-center'>There are no pending comments.</h1>";   
                    } else {
                    ?>
                          <ul id="search-wrapper" class="nav navbar-nav navbar-right">
                            <?php if (isset($searchError) && $searchError != "") {
                            echo "<p class='error'>{$searchError}</p>";
                            } ?>
                            <form id="searchCommForm1" action="" method="post" class="navbar-form navbar-left show" role="search">
                              <?php if ($currRole == "admin") { ?>
                              <div class="form-group">
                                <input type="text" class="form-control" id="post_auth" placeholder="Search by author" name="by_authname" value="<?php if(isset($byAuthname)){echo $byAuthname;} ?>">
                              </div>
                              <?php } ?>
                              <div class="form-group">
                                <input type="text" class="form-control" id="post-title" placeholder="Search by post title" name="by_title" value="<?php if(isset($byTitle)){echo $byTitle;} ?>">
                              </div>                             
                              <button type="submit" name="search_comments1" class="btn gray-btn">Search</button>
                            </form>
                            <form id="searchCommForm2" action="" method="post" class="navbar-form navbar-left" role="search" style="display:none;">
                              <div class="form-group">
                                <input type="text" class="form-control" id="user" placeholder="Search by name" name="by_user" value="<?php if(isset($byUser)){echo $byUser;} ?>">
                              </div>
                              <div class="form-group">
                                <input type="text" class="form-control" id="uname" placeholder="Search by username" name="by_username" value="<?php if (isset($byUsername) && $searchError != "") { echo $byUsername; } ?>">
                              </div>
                              <button type="submit" name="search_comments2" class="btn gray-btn">Search</button>
                            </form>
                            <div class="switchFields">
                            <?php if ($currRole == "admin") { ?>
                            <a id="switchFields" style="display: inline-block;">Search by user</a>
                            <?php } else { ?>
                            <a id="switchFields" style="display: inline-block;" author="yes">Search by user</a>
                            <?php } ?>
                            </div>
                          </ul>
                        </div><!-- /.navbar-collapse -->
                      </div><!-- /.container-fluid -->
                    </nav>                    
                    <div class="view-content col-xs-12" style="margin-top: 50px;">
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
                            <button type="submit" name="submit" class="btn standard-btn" style="margin-bottom: 30px;" id="apply">
                            Apply
                            </button>
                        </div>                        
                        <table class="table">                            
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
                                    <th>Post</th>
                                    <th>Author</th>
                                    <th>Date</th>
                                    <th>Approve</th>
                                    <th>Unapprove</th>                                    
                                    <th>Delete</th>
                                </tr>
                            </thead>                            
                            <tbody>
                                
                            <?php
                            // Display comments in the table 
                            while ($row = $result->fetch_assoc()) {
                                $post_id         = $row["post_id"];
                                $post_auth       = $row["post_auth"];
                                $title           = $row["title"];
                                $comment_id      = $row["comment_id"];
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
                                <?php
                                echo "<td class='td-left'><a href='../post.php?postid={$post_id}'>{$title}</a></td>";
                                echo "<td class='td-left'>{$post_auth}</td>";
                                echo "<td class='td-bold td-date'>{$comment_date}</td>";
                                echo "<td><a href='comments.php?approve={$comment_id}' class='btn gray-btn'>Approve</a></td>";
                                echo "<td><a href='comments.php?deny={$comment_id}' class='btn gray-redtext-btn'>Deny</a></td>";
                                echo "<td><a rel='{$comment_id}' data-page='{$pageName}' href='javascript:void(0)' class='btn delete-btn delete'>Delete</a></td>";
                                echo "</tr>";
                            }
                            ?>
                            </tbody>                           
                        </table>                        
                    </form>
                    </div>
                    <?php  
                    }
                    
                    if (isset($_GET["approve"])) {
                        $commentId      = $_GET["approve"];                        
                        $approveComment = $conn->query("UPDATE comments SET comment_status = 'approved' WHERE comment_id = {$commentId}");
                        confirmQuery($approveComment);
                        header("Location: comments.php");
                    }
                    if (isset($_GET["deny"])){
                        $commentId   = $_GET["deny"];                        
                        $denyComment = $conn->query("UPDATE comments SET comment_status = 'unapproved' WHERE comment_id = {$commentId}");
                        confirmQuery($denyComment);
                        header("Location: comments.php");
                    }
                    if (isset($_POST["delete_item"])) {
                        $del_id        = $_POST["id"];
                        $deleteComment = $conn->query("DELETE from comments WHERE comment_id = {$del_id}");
                        header("Location: comments.php"); // refresh the page
                    } 
                    ?>                    
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->

<?php include "includes/admin_footer.inc.php"; ?>