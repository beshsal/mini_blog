<?php
// Check if an "authuid" parameter is received from the URL query string AND its value is numeric.
// If it is, store its value (auth_uid) in $authUid; otherwise, set $authUid to 0.
if (isset($_GET["authuid"]) && is_numeric($_GET["authuid"])) {
    $authUid = $_GET["authuid"];
} else {   
   $authUid = 0;
}

include "includes/html_head.inc.php";
include "includes/header.inc.php";

// (Note: used in breadcrumb.inc.php) If the user alters the query string and the auth_uid doesn't exist, 
// $author will be an empty string; otherwise, it will be the author's name.
$author = "";

// Get the specified author's/admin's profile details.
$result = $conn->query("SELECT firstname, lastname FROM auth_profile WHERE user_id = {$authUid}");
confirmQuery($result);

$row       = $result->fetch_assoc();
$firstname = $row["firstname"];
$lastname  = $row["lastname"];
$author    = $row["firstname"] . " " . $row["lastname"];

// Get all posts associated with the author or admin by auth_uid. 
$getAuthPosts = "SELECT * FROM posts
                LEFT JOIN images USING (image_id)
                WHERE post_status = 'published'
                AND posts.auth_uid = {$authUid}";

$authPosts = $conn->query($getAuthPosts);
confirmQuery($authPosts);

// Select the author's/admin's image. 
// Return the identified record/row to a variable as an object, and check if a filename property is set on it.
// If so, store its value (the image's filename) in the variable. Otherwise, set the variable an empty value.
$thisAuthImg = $conn->query("SELECT filename FROM user_images WHERE user_id = {$authUid}")->fetch_object();
$thisAuthImg = isset($thisAuthImg->filename) ? $thisAuthImg->filename : "";

// BREADCRUMB
include "includes/breadcrumb.inc.php";
?>
<!-- PAGE CONTENT --> 
<main class="page-content container">
  <section id="bloglist" class="authpostlist">
  <?php 
  if ($result->num_rows == 0):
    echo "<h1 class='text-center'>Sorry</h1> 
         <h3 class='text-center'>The author you are searching for does not exist.</h3>";
  else:
    // If the variable for storing the image's filename is not empty, display the image.
    if (!empty($thisAuthImg)) { 
  ?>
    <div class="user-thumb md"
         style="background-image: url('admin/images/user_images/<?php echo $thisAuthImg; ?>')"
         alt="User Image">
    </div>
  <?php } ?>
    <header class="section-heading">
        <h3>Posts By <?php echo $firstname; ?></h3>        
    </header>
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6"></div>
      <?php
      // Display all posts by the specified author or admin.
      while($row = $authPosts->fetch_assoc()) {
        $post_id       = $row["post_id"];
        $auth_uid      = $row["auth_uid"];
        $post_auth     = $row["post_auth"];        
        $title         = $row["title"];
        $post_date     = $row["post_date"];
        $post_image    = "admin/images/post_images/{$row['filename']}";
        $post_content  = $row["post_content"];
        $post_views    = $row["post_views"];
          
        // Count all approved comment records associated with the posts.
        $getApprvdComments = "SELECT * FROM comments
                             LEFT JOIN postxcomment USING (comment_id)
                             WHERE comment_status = 'approved'
                             AND postxcomment.post_id = {$post_id}";

        $apprvdComments = $conn->query($getApprvdComments);
        confirmQuery($apprvdComments);
        $approvedComments = $apprvdComments->num_rows;
      ?>
      <article id="<?php echo $post_id; ?>" class="grid-item col-xs-12 col-sm-6">
        <div class="grid-item-content">
          <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>">
            <img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          </a>
          <span class="post-date"><?php echo date("M d", strtotime($post_date)) ?></span>
          <header class="post-header">
            <div class="post-title-wrapper">
            <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <div class="post-details post-auth">By <?php echo "<span style='color: #000;'>{$post_auth}</span>"; ?></div>
            <ul class="post-details list-inline">
              <li class="post-details-item">
                <i class="fa fa-eye"></i> <?php echo $post_views; ?>              
                <i class="fa fa-comments-o"></i> <?php echo $approvedComments; ?>
              </li>
              <li class="post-details-item">
              <?php
              // Retrieve and display associated categories.
              $getCategories = "SELECT * FROM categories
                               LEFT JOIN postxcat USING (cat_id)
                               WHERE postxcat.post_id = {$post_id}
                               ORDER BY category ASC";
              
              $categories = $conn->query($getCategories);
              confirmQuery($categories);
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
            <hr>
          </header>                  
          <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>">
              <p><?php 
                // Create excerpts of each post's content - defaults to 2 for the number of sentences to extract.
                $extract = getFirst($post_content);
                echo $extract[0]; ?>
              </p>
          </a>
          <footer class="post-footer">
            <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>" class="btn standard-btn">Read</a>
          </footer>
        </div>
      </article>        
      <?php }
      endif;  
      ?> <!-- /while -->        
    </div>
  </section>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>