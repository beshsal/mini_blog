<?php
// If logged in, get the user's user_id.
if (isset($_SESSION["authenticated"])) {    
    $currUname = $_SESSION["username"];    
    $getUid    = "SELECT user_id FROM users WHERE username = '{$currUname}'";
    $uidResult = $conn->query($getUid);
    
    confirmQuery($uidResult);
    
    $row     = $uidResult->fetch_array();
    $uid     = $row["user_id"]; // the current user's user_id
    $isAdmin = false; // flag for checking if the user is an admin or author

    // foreach ($_SESSION as $key=>$value) echo $key. " " . $value . "<br/>";
    
    // If there is a comment error (an empty form was submitted), save the error.
    if (isset($_SESSION["commentErr"])) {
        $commentError = "<h3 class='error text-center'>Please enter a comment.</h3><br>";
        unset($_SESSION["commentErr"]);
    }
}

// The number of comment results to be displayed before loading more
$resultsPerPage = 5;

// The number of approved parent comments (top-level comments directly assigned to the current post) is required to determine
// when to display and load more comments. The result of this query does not include replies like $approvedComments. Note that
// if a parent comment is deleted, so are its child comments (replies).
$approved_parents = $conn->query("SELECT * FROM comments
                                 LEFT JOIN postxcomment USING (comment_id)
                                 WHERE comment_status = 'approved'
                                 AND parent_id = 0
                                 AND postxcomment.post_id = {$postId}");

confirmQuery($approved_parents);

$approvedParents = $approved_parents->num_rows;

// If the user submits a comments, extract the user's details and the comment entry from the form, and insert them into the comments table.
if (isset($_POST["insert_comment"])) {
    $comment_auth    = $conn->real_escape_string($_POST["comment_auth"]);
    $comment_email   = $conn->real_escape_string($_POST["comment_email"]);
    $comment_content = trim($_POST["comment_content"]);
    $comment_content = stripslashes($conn->real_escape_string($comment_content));
    
    // If the user is an admin or author, the comment is automatically approved. For members, the comment will initially be set to
    // unapproved until an admin or author (if the author owns the post) approves the comment.
    if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") {
        $insertComment = 
        "INSERT INTO comments (comment_auth, user_id, username, comment_email, comment_content, comment_status, comment_date)
        VALUES(?, ?, ?, ?, ?, 'approved', now())";        
        $isAdmin = true;
    } elseif ($_SESSION["role"] == "member") {
        $insertComment = 
        "INSERT INTO comments (comment_auth, user_id, username, comment_email, comment_content, comment_status, comment_date)
        VALUES(?, ?, ?, ?, ?, 'unapproved', now())";
        // Set a flag to indicate if the user is a member.
        $_SESSION["isMember"] = true;
    }
    
    // If a comment entry is submitted, perform the insert operation.
    if(isset($comment_auth) && isset($comment_email) && isset($comment_content) && !empty($comment_content)) {
        // The prepared statement does not need to be initialized if $conn->prepare() is used.
        if (!($stmt = $conn->prepare($insertComment))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        // Bind the values for the user's name, user_id, username, email, and the comment's content.
        if (!$stmt->bind_param("sisss", $comment_auth, $uid, $currUname, $comment_email, $comment_content)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        // Execute the statement.
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        
        // Get the new comment entry's ID (comment_id).
        $comment_id = $stmt->insert_id;
 
        // Insert the new comment_id and the associated post_id into the postxcomment cross-reference table.
        if (!$conn->query("INSERT INTO postxcomment (post_id, comment_id) VALUES({$postId}, {$comment_id})")) {
            echo $conn->error;
        }
        
        if (isset($_GET["feat"])) {
            header("Location:" . BASE_URL . "post/featured/" . $postId . "/" . formatUrlStr($title) . "#comments");
        } else {
            header("Location:" . BASE_URL . "post/" . $postId . "/" . formatUrlStr($title) . "#comments");
        }        
        exit;
    } else {
        // If an empty or whitespace value is submitted, warn the user. A boolean indicating there is an error is
        // set on the session so that the error can be displayed after the page is refreshed.
        $_SESSION["commentErr"] = true;
        
        // Reset the session variable indicating the user is a member to false.
        $_SESSION["isMember"] = false;
        
        header("Location:" . BASE_URL . "post/" . $postId . "/" . formatUrlStr($title) . "#comments");
        exit;
    }
}

// The comment count (the post_comments field in the posts table) must be udpated.
// Note that this is retrieving all comments associated with the current post, not
// just the approved comments, because the total number of comments for the post must
// be viewable to an admin user.

// Get all comment records associated with the current post.
$allComments = $conn->query("SELECT * FROM comments
                            LEFT JOIN postxcomment USING (comment_id)
                            WHERE postxcomment.post_id = {$postId}");
confirmQuery($allComments);
$commentCount = $allComments->num_rows;

// Update the post_comments field in the posts table. If the entry is the first comment for the post, insert 1
// in the post_comments field; otherwise, update the field with the new comment count.
if ($commentCount == 0) {
    $firstComment = $conn->query("UPDATE posts SET post_comments = 1 WHERE post_id = {$postId}");
    confirmQuery($firstComment);
} else {
    $updateComments = $conn->query("UPDATE posts SET post_comments = {$commentCount} WHERE post_id = {$postId}");
    confirmQuery($updateComments);
}
?>
<!-- COMMENT SECTION -->
<section id="comments" class="<?php if (isset($_POST['cmnt_signin'])) echo 'comntSignin'; ?>"> <!-- inside #post section -->      
  <!-- TABS -->  
  <div class="comments-box-tabs">
  <?php if(isset($commentError)){echo $commentError;} ?>
    <ul class="nav nav-tabs">
      <!-- The Comments tab shows the number of approved comments. -->
      <li id="tab1" class="<?php if(!isset($commentError)){echo 'active';} ?>"><a data-toggle="tab" href="#comment-list">Comments (<?php echo $approvedComments;  ?>)</a></li> <!-- Change this to $approvedParents to show only the number of parent comments. -->
      <li id="tab2" class="<?php if(isset($commentError)){echo 'active';} ?>"><a data-toggle="tab" href="#comment-form">Leave a new comment</a></li>
    </ul>
    <div class="tab-content">
      <!-- COMMENT LIST -->
      <section id="comment-list" class="tab-pane fade in <?php if(!isset($commentError)){echo 'active';} ?>">          
        <?php
        // Get the subset of comments for the identified post (5 per page load).
        $query = "SELECT * FROM comments
                 LEFT JOIN postxcomment USING (comment_id)
                 WHERE postxcomment.post_id = {$postId} 
                 AND parent_id = 0
                 AND comment_status = 'approved'
                 ORDER BY comment_id DESC
                 LIMIT 0, {$resultsPerPage}";

        $commentsPerPage = $conn->query($query);
        confirmQuery($commentsPerPage);
        
        // If the comment is submitted by a member, display a message informing the member that the comment
        // will be displayed after approval.
        if (isset($_SESSION["isMember"]) && $_SESSION["isMember"] == true) {
            echo "<h3>Thank you for submitting your comment. It will be displayed upon approval.</h3>";
            // Unset the session variable so that the message will be removed upon refreshing the page or submitting the form again.
            unset($_SESSION["isMember"]);
        }
        // If the comment is submitted by an admin or author or is an approved comment by a member, display it in the comment list.
        // Comments by admin or authors are automatically approved and displayed.
        elseif (isset($isAdmin) && $isAdmin == true || $approvedParents > 0) {
            while ($row = $commentsPerPage->fetch_array()) {
            $comment_id      = $row["comment_id"];
            $parent_id       = $row["comment_id"];
            $user_id         = $row["user_id"]; // user_id is used for the ID of the commenter; the posts table uses auth_uid
            $comment_auth    = $row["comment_auth"];            
            $comment_date    = date("F j, Y", strtotime($row["comment_date"]));
            $comment_content = $row["comment_content"];
            
            ?>
            <!-- COMMENT -->
            <article class="comment parent" id="<?php echo 'comment_id' . $comment_id; ?>">
              <header class="comment-header" style="position: relative;">
              <?php
              // Get the user's image (the name of the image file).
              $getFilename = $conn->query("SELECT filename FROM user_images WHERE user_id = {$user_id}");
              confirmQuery($getFilename);                
              $row      = $getFilename->fetch_array();
              $filename = $row["filename"];

              // Get the user's role to display it in the user's details.
              $getRole = $conn->query("SELECT role FROM users WHERE user_id = {$user_id}");
              confirmQuery($getRole);
              $row  = $getRole->fetch_array();
              $role = $row["role"];
              
              // If the user has uploaded an image, display it. Otherwise, display the default user image.
              if (isset($filename) && !empty($filename)) {?>
                <div class="user-thumb xs enlarge"
                     style="cursor: pointer; background-image: url('admin/images/user_images/<?php echo $filename; ?>')"
                     alt="User Image">
                </div>
                  
                <!-- POPUP IMAGE -->
                <div class="largeImg" style="display: none;">
                    <div class="user-desc">
                    <img src="admin/images/user_images/<?php echo $filename; ?>" alt="User Image" width="200"/>
                    </div>
                </div>
              <?php } else { ?>
              <div class="user-thumb xs"
                   style="cursor: pointer; background-image: url('admin/images/user_images/defaultuser.png')"
                   alt="User Image">
              </div>
              <?php } ?>
                <!-- The user's name -->
                <h4 class="comment-details"><?php echo $comment_auth . "<span style='font-size: 12px; font-family: Sans-Serif;'> (" . ucfirst($role) . ")</span>"; ?>
                </h4>
                <!-- The date the comment was posted -->
                <p><small><i>Posted on <?php echo $comment_date; ?></i></small></p>                    
              </header>
              <section class="comment-content">
                <p>
                  <!-- The comment -->
                  <?php echo $comment_content; ?>
                </p>
              </section>
              <footer class="comment-footer">
                <ul class="list-inline">
                  <?php 
                  $responses = $conn->query("SELECT * FROM comments 
                                            WHERE comment_status = 'approved'
                                            AND parent_id = {$parent_id}");
                  confirmQuery($responses);
                  ?>
                  <li><a>Responses (<?php echo $responses->num_rows; ?>)</a></li>
                  <!-- If reply is clicked, show the child comment form. -->
                  <li><a id="<?php echo $comment_id; ?>"class="reply closed">Reply</a></li>
                </ul>
              </footer>
              <!-- TOP-LEVEl REPLY FORM -->
              <div id="form-id<?php echo $comment_id; ?>" class="child-comment-form-wrapper" style="display: none;">
              <?php if (isset($_SESSION["authenticated"])) { ?>              
              <form action="" method="post" class="child-comment-form form-horizontal" role="form">
                  <p class="text-center error childCommentErr"></p>
                  <input name="comment_auth" type="hidden" value="<?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?>"> 
                  <input name="comment_email" type="hidden" value="<?php echo $_SESSION['email']; ?>">
                  <input name="parent_id" type="hidden" value="<?php echo $parent_id; ?>"> <!-- $comment_id -->
                  <input name="post_id" type="hidden" value="<?php echo $postId; ?>">
                  <div class="form-group">
                       <div class="">
                       <textarea name="comment_content" class="form-control" id="addComment" rows="5"></textarea>
                       </div>
                  </div>
                  <div class="form-group">
                       <div class="">                    
                           <button name="insert_child_comment" class="btn standard-btn send-reply" type="submit">Reply</button>
                       </div>
                  </div>            
              </form>
              <?php } else { ?>
              <h4 class="signin-warning text-center" style="margin-top: 8px;">You must sign in to leave a comment.</h4>
              <?php } ?>
              </div>  
                
              <!-- CHILD COMMENT CONTENT & FORM -->
              <?php
                $include = "includes/child_comments.inc.php";
                displayReplies($parent_id, $postId, $include);
              ?>
              <div class="showMore" style="display: none;"><span>Show more replies</span></div>
              <div class="showLess" style="display: none;"><span>Show fewer replies</span></div>
            </article>
            <?php }
            // if there are more records than the set amount per load, display the button to load more.
            if ($approvedParents > $resultsPerPage) { ?>
                <!-- Send the key/value page=2 and the key/value holding the post_id when the .loadmore button is clicked.
                (See loadmore.inc.php and the AJAX request in footer.inc.php). -->
                <div class="load-more"><button class="loadmore" data-postid="<?php echo $postId; ?>" data-page="2">Load More</button></div>
            <?php }        
        } else { 
            echo "<h3>There are no comments yet. Be the first to leave one.</h3>"; 
        }
        ?>
      </section><!-- /#comment-list -->        
      
      <div id="comment-form" class="tab-pane fade in <?php if(isset($commentError)){echo 'active';} ?>" style="position: relative;">

        <!-- COMMENT FORM -->        
        <?php        
        // The user must be logged in to leave a comment. If the user is not already logged in, display the sign-in form.
        if (!isset($_SESSION["authenticated"])):      
        ?>            
        <div id="cmnt-signin">
            <h4>Please sign in or sign up to leave a comment:</h4>
            <form action="" method="post">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username" required>
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password" required>
                </div>
                <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
                <input name="cmnt_signin" type="hidden" value="true">
            </form>
            <!-- Provide the option to register if the user is not already a member. -->
            <span>Not a member? 
                <a onclick="$('#cmnt-signin').hide(); $('#cmnt-reg').show()">Sign up</a>
            </span>
            <div style="padding-top: 15px; float: right;">
                <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
            </div>
        </div>
        <div id="cmnt-reg" style="display:none;"> <!-- Note: be sure not to remove display:none! -->
            <div class="sign-in">
            <a onclick="$('#cmnt-reg').hide(); $('#cmnt-signin').show()">Sign in</a>
            </div>

            <!-- REGISTER FORM -->               
            <form action="" method="post" class="reg-form">
                <div class="form-group">
                  <label for="firstname">Enter your firstname:</label>
                  <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname">
                </div>
                <div class="form-group">
                  <label for="lastname">Enter your lastname:</label>
                  <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname">
                </div>
                <div class="form-group">
                  <label for="email">Enter your email:</label>
                  <input name="email" type="email" class="form-control" id="email" placeholder="Email">
                </div>
                <div class="form-group">
                  <label for="username">Enter your username:</label>
                  <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Username">
                </div>            
                <div class="form-group">
                  <label for="password">Enter your password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password">
                </div>
                <div class="form-group">
                  <label for="password">Re-enter your password:</label>
                  <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password">
                </div>
                <input name="role" type="hidden" value="member">
                <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
            </form>
        </div>          
    <?php else: ?>          
    <!-- If the user is already logged in, display the comment form. -->            
        <form action="" method="post" class="form-horizontal" id="commentForm" role="form" onsubmit="location.href = #comments">
          <!-- "firsname" and "email" added to $_SESSION in the sign-in authentication script -->
          <input name="comment_auth" type="hidden" value="<?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?>"> 
          <input name="comment_email" type="hidden" value="<?php echo $_SESSION['email']; ?>">
          <div class="form-group">
               <label for="comment" class="">Comment</label>
               <div class="">
               <textarea name="comment_content" class="form-control" id="addComment" rows="5"></textarea>
               </div>
          </div>
          <div class="form-group">
               <div class="">                    
               <button name="insert_comment" class="btn standard-btn" type="submit" id="submitComment">Leave a new comment</button>
               </div>
          </div>            
        </form>
        <?php endif; ?>
      </div>
    </div> <!-- /.tab-content -->
  </div>
</section> <!-- /#comments -->