<?php
// If Logged in
if (isset($_SESSION["authenticated"])) {
    $currUname = $_SESSION["username"];    
    $getUid    = "SELECT user_id FROM users WHERE username = '{$currUname}'";    
    
    $uidResult = $conn->query($getUid);    
    confirmQuery($uidResult);
    
    $row      = $uidResult->fetch_array();
    $uid      = $row["user_id"];    
    $isAdmin  = false;
}

$resultsPerPage = 5;

if (isset($_POST["insert_comment"])) {
    $comment_auth    = $conn->real_escape_string($_POST["comment_auth"]);
    $comment_email   = $conn->real_escape_string($_POST["comment_email"]);
    $comment_content = stripslashes($conn->real_escape_string($_POST["comment_content"]));
    
    if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") {
        $insertComment = 
        "INSERT INTO comments (comment_auth, user_id, username, comment_email, comment_content, comment_status, comment_date)
        VALUES(?, ?, ?, ?, ?, 'approved', now())";        
        $isAdmin = true;
    } elseif ($_SESSION["role"] == "member") {
        $insertComment = 
        "INSERT INTO comments (comment_auth, user_id, username, comment_email, comment_content, comment_status, comment_date)
        VALUES(?, ?, ?, ?, ?, 'unapproved', now())";
        $_SESSION["isMember"] = true;
    }
    
    if(isset($comment_auth) && isset($comment_email) && isset($comment_content) && !empty($comment_content)) {
        // The prepared statement does not need to be initialized if $conn->prepare() is used
        if (!($stmt = $conn->prepare($insertComment))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }

        if (!$stmt->bind_param("sisss", $comment_auth, $uid, $currUname, $comment_email, $comment_content)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }

        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        
        // Get the new comment entry's primary key
        $comment_id = $stmt->insert_id;

        $query = "INSERT INTO postxcomment (post_id, comment_id) VALUES({$postId}, {$comment_id})";
        
        if (!$conn->query($query)) {
            echo $conn->error;
        }
    } else {
        $commentError = "<h3 class='error text-center'>Please enter a comment</h3><br>";
    }
    
    header("Location:" . BASE_URL . "post/" . $postId . "/" . formatUrlStr($title));
    die;
}

// Update the comment count (post_comments) in the posts table
$getComments = "SELECT comment_id FROM comments
               LEFT JOIN postxcomment USING (comment_id)
               WHERE postxcomment.post_id = {$postId}";

$comments = $conn->query($getComments);
confirmQuery($comments);

$row = $comments->fetch_assoc();

// Get the number of the rows/records in the result-set
$commentCount = $comments->num_rows;

if (isset($row["comment_id"])) {
    if ($commentCount == 0) {
        $firstComment = $conn->query("INSERT INTO posts (post_comments) VALUES(1)");
        confirmQuery($firstComment);
    } else {
        $updateComments = $conn->query("UPDATE posts SET post_comments = {$commentCount} WHERE post_id = {$postId}");
        confirmQuery($updateComments);
    }
}
?>
<section id="comments"> <!-- inside #post section -->      
  <!-- TABS (TABLET & DESKTOP) -->  
  <div class="comments-box-tabs">
  <?php if(isset($commentError)){echo $commentError;} ?>
    <ul class="nav nav-tabs">
      <li class="<?php if(!isset($commentError)){echo 'active';} ?>"><a data-toggle="tab" href="#comment-list">Comments (<?php echo $approvedComments;  ?>)</a></li>
      <li class="<?php if(isset($commentError)){echo 'active';} ?>"><a data-toggle="tab" href="#comment-form">Leave a new comment</a></li>
    </ul>
    <div class="tab-content">
      <!-- COMMENT LIST -->
      <section id="comment-list" class="tab-pane fade in <?php if(!isset($commentError)){echo 'active';} ?>">          
        <?php
        $query = "SELECT * FROM comments
                 LEFT JOIN postxcomment USING (comment_id)
                 WHERE postxcomment.post_id = {$postId}         
                 AND comment_status = 'approved'
                 ORDER BY comment_id DESC
                 LIMIT 0, {$resultsPerPage}";

        $commentsPerPage = $conn->query($query);
        confirmQuery($commentsPerPage);
        
        if (isset($_SESSION["isMember"]) && $_SESSION["isMember"] == true) {
            echo "<h3>Thank you for submitting your comment. It will be displayed upon approval.</h3>";
            unset($_SESSION["isMember"]);
        }
        elseif (isset($isAdmin) && $isAdmin == true || $approvedComments > 0) {
            while ($row = $commentsPerPage->fetch_array()) {
            $comment_auth    = $row["comment_auth"];
            $user_id         = $row["user_id"]; // user_id is the id of the commenter; the posts table uses auth_uid
            $comment_date    = date("F j, Y", strtotime($row["comment_date"]));
            $comment_content = $row["comment_content"];
            ?>
            <!-- COMMENT -->
            <article class="comment">
              <header class="comment-header" style="position: relative;">
              <?php
              // Get the user image
              $getFilename = $conn->query("SELECT filename FROM user_images WHERE user_id = {$user_id}");
              confirmQuery($getFilename);
              $row = $getFilename->fetch_array();
              $filename = $row["filename"];

              $getRole = $conn->query("SELECT role FROM users WHERE user_id = {$user_id}");
              confirmQuery($getRole);
              $row  = $getRole->fetch_array();
              $role = $row["role"];

              if (isset($filename) && !empty($filename)) {
              ?>       
                <img class="img-circle enlarge" src="admin/images/user_images/<?php echo $filename; ?>" alt="User Image" width="40">
                <!-- POPUP IMAGE -->
                <div class="largeImg" style="display: none;">
                    <div class="user-desc">
                    <img src="admin/images/user_images/<?php echo $filename; ?>" alt="User Image" width="200"/>
                    <p>User description</p>
                    </div>
                </div>
              <?php } else { ?>
                <img class="img-circle" src="admin/images/user_images/defaultuser.png" alt="User Image" width="40">
              <?php } ?>
                <h4 class="comment-details"><?php echo $comment_auth . "<span style='font-size: 12px; font-family: Sans-Serif;'> (" . ucfirst($role) . ")</span>"; ?>
                </h4>
                <p><small><i>Posted on <?php echo $comment_date; ?></i></small></p>                    
              </header>
              <section class="comment-content">
                <p>
                  <?php echo trim($comment_content); ?>
                </p>
              </section>
              <footer class="comment-footer">
                <ul class="list-inline">                      
                  <li><a>Responses (0)</a></li>
                  <!-- If reply is clicked, add child comment -->
                  <li><a class="reply unavail" data-placement="bottom" data-title="" data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">Reply</a></li>
                </ul>
              </footer>
            </article>            
            <?php }
            // if there are more records than the set amount per load, load more
            if ($approvedComments > $resultsPerPage) { ?>
                <!-- Send key/value page=2 when clicked -->
                <div class="load-more"><button class="loadmore" data-postid="<?php echo $postId; ?>" data-page="2">Load More</button></div>
            <?php }        
        } else { 
            echo "<h3>There are no comments yet. Be the first to leave one.</h3>"; 
        }
        ?>          
         <?php
          ?>
      </section><!-- /.comment-list -->        
      
      <div id="comment-form" class="tab-pane fade in <?php if(isset($commentError)){echo 'active';} ?>" style="position: relative;">

        <!-- COMMENT FORM -->        
        <?php        
        // If the user is not already logged in
        if(!isset($_SESSION["authenticated"])):      
        ?>
            <!-- Display the sign-in form -->
            <div id="cmnt-signin">
                <h4>Please sign in or sign up to leave a comment:</h4>
                <form action="" method="post">
                    <div class="form-group">
                      <label for="username">Username:</label>
                      <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username">
                    </div>
                    <div class="form-group">
                      <label for="password">Password:</label>
                      <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
                    </div>
                    <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
                    <input name="cmnt_signin" type="hidden" value="true">
                </form>
                <span style="position: absolute; bottom: 20px; left: 130px;">Not a member? 
                    <a onClick="$('#cmnt-signin').hide(); $('#cmnt-reg').show()">Sign up</a>
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
                <form action="" method="post">
                    <div class="form-group">
                      <label for="firstname">Enter your firstname:</label>
                      <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname" required>
                    </div>
                    <div class="form-group">
                      <label for="lastname">Enter your lastname:</label>
                      <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname" required>
                    </div>
                    <div class="form-group">
                      <label for="email">Enter your email:</label>
                      <input name="email" type="email" class="form-control" id="email" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                      <label for="username">Enter your username:</label>
                      <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
                      <input name="uname" type="text" class="form-control" id="uname" placeholder="Username" required>
                    </div>            
                    <div class="form-group">
                      <label for="password">Enter your password:</label>
                      <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                      <label for="password">Re-enter your password:</label>
                      <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
                    </div>
                    <input name="role" type="hidden" value="member">
                    <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
                </form>
            </div>          
        <?php else: ?>          
        <!-- if the user is already logged in, display the comment form -->            
            <form action="<?php echo 'post.php?postid=' . $postId . '#comments'; ?>" method="post" class="form-horizontal" id="commentForm" role="form">
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

<!-- 
================================================================================================================================
-->
  <!-- MOBILE -->
    
  <div class="panel-group" id="accordion">
    <div class="panel panel-default">
      <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">
      <div class="panel-heading">
        <h4 class="panel-title">
          Comments (<?php echo $approvedComments; ?>)
        </h4>
      </div>
      </a>
      <div id="collapse1" class="panel-collapse collapse in"> <!-- Remove "in" to close panel by default -->        
        <div class="panel-body">
          <!-- COMMENT LIST -->
          <section id="comment-list-mobile" class="tab-pane fade in">              
            <?php
            $query = "SELECT * FROM comments
                     LEFT JOIN postxcomment USING (comment_id)
                     WHERE postxcomment.post_id = {$postId}         
                     AND comment_status = 'approved'
                     ORDER BY comment_id DESC
                     LIMIT 0, $resultsPerPage";

            $commentsPerPage = $conn->query($query);
            confirmQuery($commentsPerPage);

            if (isset($_SESSION["isMember"]) && $_SESSION["isMember"] == true) {
                echo "<h3>Thank you for submitting your comment. It will be displayed upon approval.</h3>";
            } elseif (isset($isAdmin) && $isAdmin == true || $approvedComments > 0) {
                while ($row = $commentsPerPage->fetch_array()) {
                $comment_auth    = $row["comment_auth"];
                $user_id         = $row["user_id"];
                $comment_date    = date("F j, Y", strtotime($row["comment_date"]));
                $comment_content = $row["comment_content"];
                ?>

                <!-- COMMENT -->
                <article class="comment">
                  <header class="comment-header">                    
                  <?php
                  // Get the user image
                  $getFilename = $conn->query("SELECT filename FROM user_images WHERE user_id = {$user_id}");
                  confirmQuery($getFilename);
                  $row = $getFilename->fetch_array();
                  $filename = $row['filename'];
                    
                  if (isset($filename) && !empty($filename)) { ?>
                      <img class="img-circle enlarge" src="admin/images/user_images/<?php echo $filename; ?>" alt="User Image" width="40">
                      <!-- POPUP IMAGE -->
                      <div class="largeImg" style="display: none;">
                          <div class="user-desc">
                          <img src="admin/images/user_images/<?php echo $filename; ?>" alt="User Image" width="200"/>
                          <p>User description</p>
                          </div>
                      </div>
                  <?php } else { ?>
                      <img class="img-circle" src="admin/images/user_images/defaultuser.png" alt="User Image" width="40">
                  <?php } ?>
                    <h4 class="comment-details"><?php echo $comment_auth; ?></h4>
                    <p><small><i>Posted on <?php echo $comment_date; ?></i></small></p>                    
                  </header>
                  <section class="comment-content">
                    <p>
                      <?php echo trim($comment_content); ?>
                    </p>
                  </section>
                  <footer class="comment-footer">
                    <ul class="list-inline">                      
                      <li><a>Responses (0)</a></li>
                      <!-- If reply is clicked, add child comment with the specific comment_id -->
                      <li><a class="reply unavail" data-placement="bottom" data-title="" data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">Reply</a></li> 
                    </ul>
                  </footer>
                </article>
                <?php }
                //if there are more records than the set amount per load, load more
                if ($approvedComments > $resultsPerPage) { ?>
                <!-- Send key/value page=2 when clicked -->
                <div class="load-more">
                    <button class="loadmore" data-postid="<?php echo $postId; ?>" data-page="2">Load More</button>
                </div>
                <?php }
            } else {
                echo "<h3>There are no comments yet. Be the first to leave one.</h3>"; 
            }
            ?>              
          </section><!-- /.comment-list -->
        </div>
      </div>
    </div>
    <div class="panel panel-default">
      <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">
      <div class="panel-heading">
        <h4 class="panel-title">
          Leave a New Comment
        </h4>
      </div>
      </a>
      <div id="collapse2" class="panel-collapse collapse">
        <div class="panel-body">
          <div id="comment-form-box">

            <!-- COMMENT FORM -->              
            <?php
            // If not logged in
            if(!isset($_SESSION["authenticated"])):
            ?>
                <!-- Display the sign-in form -->
                <div id="cmnt-signin-mobile">
                    <h4>Please sign in or sign up to leave a comment:</h4>
                    <form action="" method="post">
                        <div class="form-group">
                          <label for="username">Username:</label>
                          <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username">
                        </div>
                        <div class="form-group">
                          <label for="password">Password:</label>
                          <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
                        </div>
                        <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
                        <input name="cmnt_signin" type="hidden" value="true">
                    </form>
                    <span>Not a member? <a onClick="$('#cmnt-signin-mobile').hide(); $('#cmnt-reg-mobile').show()">Sign up</a></span>
                    <div style="padding-top: 15px;">
                        <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
                    </div>
                </div>
              
                <div id="cmnt-reg-mobile" style="display:none;"> <!-- Note: be sure not to remove display:none! -->
                    <div class="sign-in">
                    <a onclick="$('#cmnt-reg-mobile').hide(); $('#cmnt-signin-mobile').show()">Sign in</a>
                    </div>
                    <!-- REGISTER FORM -->
                    <form action="" method="post">
                        <div class="form-group">
                          <label for="firstname">Enter your firstname:</label>
                          <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname" required>
                        </div>
                        <div class="form-group">
                          <label for="lastname">Enter your lastname:</label>
                          <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname" required>
                        </div>
                        <div class="form-group">
                          <label for="email">Enter your email:</label>
                          <input name="email" type="email" class="form-control" id="email" placeholder="Email" required>
                        </div>
                        <div class="form-group">
                          <label for="username">Enter your username:</label>
                          <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
                          <input name="uname" type="text" class="form-control" id="uname" placeholder="Username" required>
                        </div>            
                        <div class="form-group">
                          <label for="password">Enter your password:</label>
                          <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
                        </div>
                        <div class="form-group">
                          <label for="password">Re-enter your password:</label>
                          <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
                        </div>
                        <input name="role" type="hidden" value="member">
                        <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- else, if logged in show the comment form -->
                <form action="" method="post" class="form-horizontal" id="commentForm" role="form">
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
        </div>
      </div>
    </div> <!-- /.panel-group -->
  </div>
</section> <!-- /#comments -->