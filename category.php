<?php
if (isset($_GET["catid"]) && is_numeric($_GET["catid"])) {
    $catId = $_GET["catid"];
} else {    
   // Otherwise
  $catId = 0;
}

include "includes/html_head.inc.php";
include "includes/header.inc.php";

// If the user alters the query string and the category doesn't exist, $category will be an empty string 
// (Note: used in breadcrumb.inc.php)
$category    = "";
$getCategory = $conn->query("SELECT category FROM categories WHERE cat_id = {$catId}");
confirmQuery($getCategory);
while($row = $getCategory->fetch_assoc()) {
    $category = $row["category"];
}

include "includes/breadcrumb.inc.php";
?>
<!-- PAGE CONTENT -->	
<main class="page-content container">
  <section id="bloglist" class="catlist">
    <?php 
    if (!isset($category) || empty($category)):
        echo "<h1 class='text-center'>Sorry</h1> 
             <h3 class='text-center'>The category you are searching for does not exist.</h3>";
    else:
    ?>
    <header class="section-heading">
    <h3><?php echo $category; ?></h3>
    </header>
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6"></div>
      <?php        
      // Find all posts related to the current category and display them
      $query = "SELECT * FROM posts
               LEFT JOIN images USING (image_id)
               LEFT JOIN postxcat USING (post_id)
               WHERE post_status = 'published'
               AND postxcat.cat_id = {$catId}";
        
      $result = $conn->query($query);        
      confirmQuery($result);
        
      while($row = $result->fetch_assoc()) {           
        $post_id       = $row["post_id"];
        $auth_uid      = $row["auth_uid"];
        $post_auth     = $row["post_auth"];
        $title         = $row["title"];
        $post_auth     = $row["post_auth"];
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
          <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>">
            <img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          </a>
          <span class="post-date"><?php echo date("M d", strtotime($post_date)) ?></span>
          <header class="post-header">
            <div class="post-title-wrapper">
            <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <div class="post-details post-auth">By <a href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>"><?php echo $post_auth; ?></a></div>
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
                echo "<span class='tag'>";
                if ($cat_id == $catId) {
                    echo "<span>{$category}</span>";
                } else {
                    echo "<a href='category/{$cat_id}/" . formatUrlStr($category) . "'>{$category}</a>";
                }
                echo "</span>";
              }
              ?>
              </li>     
            </ul><!-- /.post details -->
            <hr>
          </header>                  
          <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>">
              <p><?php 
                // Defaults to 2 for number of sentences to extract
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