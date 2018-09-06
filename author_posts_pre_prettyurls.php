<?php
// if $_GET["author"] (auth_id) is set and numeric
if (isset($_GET["author"]) && is_numeric($_GET["author"])) {
    $authUid = $_GET["author"];
} else {    
   // Otherwise
   $authUid = 0;
}

include "includes/html_head.inc.php";
include "includes/header.inc.php";

$authname  = "";
$result    = $conn->query("SELECT firstname, lastname FROM auth_profile WHERE user_id = {$authUid}");
$row       = $result->fetch_assoc();
$firstname = $row["firstname"];
$lastname  = $row["lastname"];
$authname  = $row["firstname"] . " " . $row["lastname"];

$getAuthPosts = "SELECT * FROM posts
                LEFT JOIN images USING (image_id)
                WHERE post_status = 'published'
                AND posts.auth_uid = {$authUid}";

$authPosts = $conn->query($getAuthPosts);
confirmQuery($authPosts);

$thisAuthImg = $conn->query("SELECT filename FROM user_images WHERE user_id = {$authUid}")->fetch_object()->filename;

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
  ?>
    <div class="text-center">
    <img src="admin/images/user_images/<?php echo $thisAuthImg; ?>" class="img-responsive img-circle" alt="User Image" height="100" width="100" style="display:inline-block; margin-bottom:15px;">
    </div>
    <header class="section-heading">
        <h3>Posts By <?php echo $firstname; ?></h3>        
    </header>
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6"></div>
      <?php
      while($row = $authPosts->fetch_assoc()) {
        $post_id       = $row["post_id"];
        $auth_uid      = $row["auth_uid"];
        $post_auth     = $row["post_auth"];        
        $title         = $row["title"];
        $post_date     = $row["post_date"];
        $post_image    = "admin/images/post_images/{$row['filename']}";
        $post_content  = $row["post_content"];
        $post_views    = $row["post_views"];
          
        // Count all approved comment records associated with the current post
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
          <a href="post.php?authref=<?php echo $auth_uid; ?>&postid=<?php echo $post_id; ?>">
            <img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          </a>
          <span class="post-date"><?php echo date("M d", strtotime($post_date)) ?></span>
          <header class="post-header">
            <div class="post-title-wrapper">
            <a href="post.php?authref=<?php echo $auth_uid; ?>&postid=<?php echo $post_id; ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <div class="post-details post-auth">By <?php echo "<span style='color: #000;'>{$post_auth}</span>"; ?></div>
            <ul class="post-details list-inline">
              <li class="post-details-item">
                <i class="fa fa-heart-o"></i> <?php echo $post_views; ?>              
                <i class="fa fa-comments-o"></i> <?php echo $approvedComments; ?>
              </li>
              <li class="post-details-item">
              <?php
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
                     <a href='category.php?category={$cat_id}'>{$category}</a>
                     </span>";
                }
              ?>
              </li>            
            </ul><!-- /.post details -->
            <hr>
          </header>                  
          <a href="post.php?authref=<?php echo $auth_uid; ?>&postid=<?php echo $post_id; ?>">
              <p><?php 
                // Defaults to 2 for number of sentences to extract
                $extract = getFirst($post_content);
                echo $extract[0]; ?>
              </p>
          </a>
          <footer class="post-footer">
            <a href="post.php?authref=<?php echo $auth_uid; ?>&postid=<?php echo $post_id; ?>" class="btn standard-btn">Read</a>
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