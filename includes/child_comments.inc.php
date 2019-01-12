<article class="child comment" id="<?php echo 'comment_id' . $commentId; ?>">
  <header class="comment-header" style="position: relative;">
  <?php
  // Get the user's image (the name of the image file).
  $getFilename = $conn->query("SELECT filename FROM user_images WHERE user_id = {$userId}");
  confirmQuery($getFilename);
  $row      = $getFilename->fetch_array();
  $filename = $row["filename"];

  // Get the user's role to display it in the user's details.
  $getRole = $conn->query("SELECT role FROM users WHERE user_id = {$userId}");
  confirmQuery($getRole);
  $row  = $getRole->fetch_array();
  $role = $row["role"];
  
  // Get the name of the parent comment_auth (commenter). This will be used to display the comment or reply the
  // current reply is a response to.
  $getParentName = $conn->query("SELECT comment_auth FROM comments WHERE comment_id = {$parentId}");
  confirmQuery($getParentName);
  $row    = $getParentName->fetch_array();
  $parent = $row["comment_auth"];

  // If the user has uploaded an image, display it. Otherwise, display the default user image.
  if (isset($filename) && !empty($filename)) { ?>
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
    <h4 class="comment-details"><?php echo $commenter . "<span style='font-size: 12px; font-family: Sans-Serif;'> (" . ucfirst($role) . ")</span>"; ?>
    </h4>
    <!-- The date the comment was posted -->
    <p><small><i>Posted on <?php echo $date; ?></i></small></p>                    
  </header>
  <section class="comment-content">
    <small style="font-family: sans-serif; color: #777;">@ <?php echo "{$parent}:"; ?></small>      
    <p>
      <!-- The comment -->
      <?php echo $content; ?>
    </p>
  </section>
  <footer class="comment-footer">
    <ul class="list-inline">
      <?php 
      $responses = $conn->query("SELECT * FROM comments 
                                WHERE comment_status = 'approved'
                                AND parent_id = {$commentId}"); // may also use $parentId
      confirmQuery($responses);
      ?>
      <li><a>Responses (<?php echo $responses->num_rows; ?>)</a></li>
      <li><a id="<?php echo $commentId; ?>" class="reply closed">Reply</a></li>
    </ul>
  </footer>

  <!-- CHILD COMMENT FORM -->
  <div id="form-id<?php echo $commentId; ?>" class="child-comment-form-wrapper" style="display: none;">
  <?php
  if (isset($_SESSION["authenticated"])) { ?>              
  <form action="" method="post" class="child-comment-form form-horizontal" role="form">
      <p class="text-center error childCommentErr"></p>
      <input name="comment_auth" type="hidden" value="<?php echo $_SESSION['firstname'] . ' ' . $_SESSION['lastname']; ?>"> 
      <input name="comment_email" type="hidden" value="<?php echo $_SESSION['email']; ?>">
      <input name="parent_id" type="hidden" value="<?php echo $commentId; ?>"> <!-- $parent_id/$parentId -->
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
  <?php
    displayReplies($commentId, $postId);
  ?>
</article>