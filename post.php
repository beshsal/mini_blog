<?php
// Check if the postid parameter was received from the URL query string AND if it is numeric
if (isset($_GET["postid"]) && is_numeric($_GET["postid"])) {
    $postId = (int) $_GET["postid"];
} else {    
   // Otherwise
  $postId = 0;
}

include "includes/html_head.inc.php";
include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

$_SESSION["post_id"] = $postId; // used in signin.inc.php for redirecting

// Count the post views
$postViews = $conn->query("SELECT post_views FROM posts WHERE post_id = {$postId}"); confirmQuery($postViews);
if ($postViews->num_rows == 0) {
   $firstView = $conn->query("INSERT INTO posts (post_views) VALUES(1)"); confirmQuery($firstView);
} else {
// Increment views for views count
$updateViews = "UPDATE posts SET post_views = post_views + 1 WHERE post_id = {$postId}";
$views = $conn->query($updateViews); confirmQuery($views);
}
    
$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)             
         WHERE posts.post_id = {$postId}";

$result = $conn->query($query);
confirmQuery($result);

// Count all approved comment records associated with the current post
$getApprvdComments = "SELECT * FROM comments
                     LEFT JOIN postxcomment USING (comment_id)
                     WHERE comment_status = 'approved'
                     AND postxcomment.post_id = {$postId}";

$apprvdComments = $conn->query($getApprvdComments);
confirmQuery($apprvdComments);

// Get the comment record(s) for the post - just the result-set with the records
$row = $apprvdComments->fetch_array();

// Count the rows in the result-set
$approvedComments = $apprvdComments->num_rows;

$getCategories = "SELECT * FROM categories
                 LEFT JOIN postxcat USING (cat_id)
                 WHERE postxcat.post_id = {$postId}
                 ORDER BY category ASC"; 

$categories = $conn->query($getCategories);
confirmQuery($categories);
?>
<!-- PAGE CONTENT -->
<main class="page-content container">
  <!-- POST -->
  <section id="post" class="post">
  <?php 
  // ADMIN VIEW
  if (isset($_SESSION["role"])) {
    $permittedRole = ($_SESSION["role"] == "admin") ? "admin" : "author";
  }
      
  if (isset($_SESSION["authenticated"]) && isset($permittedRole)) {
      if ($permittedRole == "author") {
          // Get the user_id of the currently logged-in user
          $currUname = $_SESSION["username"];
          $getUid    = $conn->query("SELECT user_id FROM users WHERE username = '{$currUname}'"); confirmQuery ($getUid);
          $row       = $getUid->fetch_array();
          $uid       = $row["user_id"];
          
          // Get the user_id of the post author
          $getAuthUid = $conn->query("SELECT auth_uid FROM posts WHERE post_id = {$postId}"); confirmQuery ($getAuthUid);
          $row        = $getAuthUid->fetch_array();
          $authUid    = $row["auth_uid"];
          
          // If the user_id of the logged-in user and the post author match, display the admin-view div
          if ($uid == $authUid) {
              echo "<div class='admin-view' style='position: relative;'>
                   <a href='admin/posts.php?source=update_post&postid={$postId}&editpost={$postId}' class='editPost'>EDIT POST</a>
                   </div>";
          }          
      } else {
          // If an admin, the admin-view div is available for all posts
          echo "<div class='admin-view' style='position: relative;'>
                <a href='admin/posts.php?source=update_post&postid={$postId}&editpost={$postId}' class='editPost'>EDIT POST</a>
                </div>";
      }
  }
      
  if ($result->num_rows == 0):
    echo "<h1 class='text-center'>Sorry</h1> 
         <h3 class='text-center'>The post you are searching for does not exist.</h3>";
    echo "</section>"; // close the section tag if there is an error
  else:
  ?>
    <article>
      <div class="row">
        <div class="col-xs-12">
          <header class="post-header">
          <?php           
          while($row = $result->fetch_assoc()) {              
            $auth_uid     = $row["auth_uid"]; 
            $title        = $row["title"];
            $post_auth    = $row["post_auth"];
            $post_date    = date("j F Y", strtotime($row["post_date"]));
            $post_image   = "admin/images/post_images/{$row['filename']}";
            $lead         = $row["lead"];
            $post_content = $row["post_content"];
            $caption      = $row["caption"];
            $artist       = $row["artist"];
            $url          = $row["url"];
            $post_views   = $row["post_views"];
            $post_status  = $row["post_status"];
          ?>
            <div class="post-title-wrapper">
                <span class="post-title"><?php echo $title; ?></span>
            </div>
            <hr>
            <ul class="post-details list-inline">
                <li class="post-details-item post-auth">
                    By <a href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>"><?php echo $post_auth; ?></a>
                </li>
              <li class="post-details-item">
                <span class="post-date"><?php echo $post_date; ?></span>
              </li>
              <li class="post-details-item <?php if($categories->num_rows > 1){echo 'post-details-adjust1';} ?>">
                    <i class="fa fa-eye" aria-hidden="true"></i> <?php echo $post_views; ?>            
                    <i class="fa fa-comments-o"></i> <a href="#comments"><?php echo $approvedComments; ?></a>
              </li>
              <li class="post-details-item <?php if($categories->num_rows > 1){echo 'post-details-adjust2';} ?>">
              <?php         
              while ($row = $categories->fetch_assoc()) {
                 $cat_id   = $row["cat_id"];
                 $category = $row["category"];
                 echo "<span class='tag'>
                      <a href='category/{$cat_id}/" . formatUrlStr($category) . "'>{$category}</a>
                      </span>";
              }
              ?>
              </li>
            </ul><!-- /.post details -->
          </header>
          <img class="post-image img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          <?php if (isset($lead) && !empty($lead)) { ?>
          <div class="lead">
            <blockquote>
            <p><?php echo $lead; ?></p>
            </blockquote>
          </div>
          <?php } ?>
          <div class="post-body">
            <?php echo convertToParas($post_content); ?>
          </div>
          <?php if (isset($caption) && !empty($caption) || isset($artist) && !empty($artist)) { ?>            
          <p class="image-details"><?php if(isset($caption) && !empty($caption)){echo "Image: <span>$caption</span>";} ?></p> 
          <p class="image-details"><?php if(!isset($caption) || empty($caption)){echo "Image ";} ?>by <span><?php if(isset($artist) && isset($url) && !empty($url)){echo "<a href='{$url}'>{$artist}</a>";}else{echo $artist;} ?></span>
          </p>
          <?php }} ?> <!-- /while --> 
            
          <footer class="post-footer">
            <ul class="share list-inline">
              <li>
                <a class="btn btn-block btn-social btn-facebook" title="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                  <span class="fa fa-facebook"></span> SHARE
                </a>
              </li>
              <li>
                <a class="btn btn-block btn-social btn-twitter" title="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                  <span class="fa fa-twitter"></span> SHARE
                </a>
              </li>
              <li>
                <a class="btn btn-block btn-social btn-google" title="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                  <span class="fa fa-google"></span> SHARE
                </a>
              </li>       
            </ul>
          </footer>           
        </div>
      </div>
    </article>
    <?php
    // COMMENT FORM AND LIST      
    include "includes/comments.inc.php";  
    ?>     
  </section>
  <hr>

  <!-- RELATED POSTS -->  
  <?php    
  // Get the number of posts to determine how to structure the grid system
  if (isset($cat_id)) {      
      $getRelCount = "SELECT COUNT(*) FROM posts
                     LEFT JOIN postxcat USING (post_id)
                     WHERE postxcat.cat_id = {$cat_id}";

      $relCount = $conn->query($getRelCount);
      confirmQuery($relCount);
      $row = $relCount->fetch_row(); 
      $relatedCount = $row[0] - 1; // - 1 because the current row should not be included in the count  
  }
  
    if (isset($relatedCount) && $relatedCount >= 1) {
    include "includes/related_posts.inc.php";  
  }
  endif;
  ?>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>
