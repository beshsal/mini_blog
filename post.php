<?php
include "includes/html_head.inc.php";

// Check if a "postid" parameter is received from the URL query string AND its value is numeric.
// If it is, store its value (post_id) in $postId; otherwise, set $postId to 0.
if (isset($_GET["postid"]) && is_numeric($_GET["postid"])) {
    $postId = (int) $_GET["postid"];
    if ($postStatus = $conn->query("SELECT post_status FROM posts WHERE post_id = {$postId}")) {
        confirmQuery($postStatus);
        $row = $postStatus->fetch_object();    
    }
} else {
  $postId = 0;
}

// Create a variable to signify access to editing a post for an admin or author.
if (isset($_SESSION["role"])) {
    // Set the permitted role (either an admin or author).
    if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") {
        $permittedRole = ($_SESSION["role"] == "admin") ? "admin" : "author";   
    }
}

// Only an admin or author user can view a post draft.
if (isset($row->post_status) && $row->post_status == "draft" && !isset($permittedRole)) {
    header("Location: " . BASE_URL);
    exit;
}  

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

$_SESSION["post_id"] = $postId; // used in signin.inc.php for redirecting

// Create an array for holding cat_ids. This is used in the query for selecting related posts.
$catIdsArray = array();

// Count the post views.
$postViews = $conn->query("SELECT post_views FROM posts WHERE post_id = {$postId}"); confirmQuery($postViews);
// If the post is viewed for the first time (there are no views yet), insert 1 into the post_views field. Otherwise
// increment the current views value by 1.
if ($postViews->num_rows == 0) {
    // $firstView = $conn->query("INSERT INTO posts (post_views) VALUES(1)"); confirmQuery($firstView);
    $firstView = $conn->query("UPDATE posts SET post_views = 1 WHERE post_id = {$postId}"); confirmQuery($firstView);
} else {
$updateViews = "UPDATE posts SET post_views = post_views + 1 WHERE post_id = {$postId}";
$views = $conn->query($updateViews); confirmQuery($views);
}
    
$query = "SELECT * FROM posts
         LEFT JOIN images USING (image_id)             
         WHERE posts.post_id = {$postId}";

$result = $conn->query($query);
confirmQuery($result);

// Count all approved comment records associated with the current post.
$approved = $conn->query("SELECT * FROM comments
                         LEFT JOIN postxcomment USING (comment_id)
                         WHERE comment_status = 'approved'
                         AND postxcomment.post_id = {$postId}");

confirmQuery($approved);

// Get the comment record(s) for the post - just the result-set with the records.
$row = $approved->fetch_array();

// Count the rows in the result-set.
$approvedComments = $approved->num_rows;

// Get all associated categories for the post identified by the post_id.
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
  // If the user is logged in and is either an admin or author, add the admin-view div.
  // For an author, the admin-view div is added to only the posts owned by the author; for
  // an admin, the admin-view div is added to all posts.
  if (isset($_SESSION["authenticated"]) && isset($permittedRole) && $result->num_rows != 0) {
      // If the currently logged-in user is an author, get the username and user_id of the user.
      if ($permittedRole == "author") {          
          $currUname = $_SESSION["username"];
          $getUid    = $conn->query("SELECT user_id FROM users WHERE username = '{$currUname}'"); confirmQuery ($getUid);
          $row       = $getUid->fetch_array();
          $uid       = $row["user_id"];
          
          // Get the auth_uid (user_id) of the author of the specific post stored in the posts table.
          $getAuthUid = $conn->query("SELECT auth_uid FROM posts WHERE post_id = {$postId}"); confirmQuery ($getAuthUid);
          $row        = $getAuthUid->fetch_array();
          $authUid    = $row["auth_uid"];
          
          // If the user_id of the signed-in user and the auth_uid of the record in the posts table match, display the admin-view div.
          // This gives the author (if the owner of the post) or admin access to editing the post by sending the post_id and the 
          // appropriate parameters for editing to admin/posts.php.
          if ($uid == $authUid) {
              echo "<div class='admin-view' style='position: relative;'>
                   <a href='admin/posts.php?source=update_post&postid={$postId}&editpost={$postId}' class='editPost'>EDIT POST</a>
                   </div>";
          }          
      } else {
          // If the currently logged-in user is an admin, the admin-view div is available for all posts.
          echo "<div class='admin-view' style='position: relative;'>
                <a href='admin/posts.php?source=update_post&postid={$postId}&editpost={$postId}' class='editPost'>EDIT POST</a>
                </div>";
      }
  }
  // If there is no matching post, inform the viewer; otherwise, display the post.
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
                    <!-- The author's name contains the URL to the author_posts page for the author identified by the auth_uid. -->
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
              // For each category, display a link to the category page identified by the cat_id.
              while ($row = $categories->fetch_assoc()) {                 
                 $cat_id   = $row["cat_id"];
                 $category = $row["category"];
                 
                 // Add each cat_id to $catIdsArray.
                 array_push($catIdsArray, $cat_id);
                  
                 echo "<span class='tag'>
                      <a href='category/{$cat_id}/" . formatUrlStr($category) . "'>{$category}</a>
                      </span>";
              }
              ?>
              </li>
            </ul><!-- /.post details -->
          </header>
          <!-- The post image -->
          <img class="post-image img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          <!-- If there is a lead, include it. -->
          <?php if (isset($lead) && !empty($lead)) { ?>
          <div class="lead">
            <blockquote>
            <p><?php echo $lead; ?></p>
            </blockquote>
          </div>
          <?php } ?>
          <!-- The post's body content -->
          <div class="post-body">
            <?php echo makeParagraphs($post_content); ?>
          </div>
          <!-- If an image caption and/or artist exist, display them. -->
          <?php if (isset($caption) && !empty($caption) || isset($artist) && !empty($artist)) { ?>            
          <p class="image-details"><?php if(isset($caption) && !empty($caption)){echo "Image: <span>$caption</span>";} ?></p> 
          <p class="image-details"><?php if(!isset($caption) || empty($caption)){echo "Image ";} ?>by <span><?php if(isset($artist) && isset($url) && !empty($url)){echo "<a href='" . addHttp($url) . "'>{$artist}</a>";}else{echo $artist;} ?></span>
          </p>
          <?php }} ?> <!-- /while --> 
            
          <footer class="post-footer">
            <ul class="share list-inline">
              <li>
                <a class="btn btn-block btn-social btn-facebook unavail" 
                   data-placement="bottom" 
                   data-title="" 
                   data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                  <span class="fa fa-facebook"></span> SHARE
                </a>
              </li>
              <li>
                <a class="btn btn-block btn-social btn-twitter unavail"
                   data-placement="bottom" 
                   data-title="" 
                   data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                  <span class="fa fa-twitter"></span> SHARE
                </a>
              </li>
              <li>
                <a class="btn btn-block btn-social btn-google unavail" 
                   data-placement="bottom" 
                   data-title="" 
                   data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
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
  // If there are categories, get the count of all posts that match each category, excluding the current post.
  // This will be used to determine how to structure the related posts grid system (see related_posts.inc.php).
  if ($catIdsArray) {
      $getRelCount = $conn->query("SELECT COUNT(*) FROM posts
                                  LEFT JOIN postxcat USING (post_id)
                                  WHERE post_status = 'published'
                                  AND postxcat.cat_id IN(" . implode(',', $catIdsArray) . ")
                                  AND posts.post_id != {$postId}");
                                  
      confirmQuery($getRelCount);
      $row = $getRelCount->fetch_row();
      $relatedCount = $row[0];
  }
    
    
  // If there is 1 or more related posts, include the related posts grid.
  if (isset($relatedCount) && $relatedCount >= 1) {
    include "includes/related_posts.inc.php";
    // echo $relatedCount;
  }
  endif;
  ?>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>
