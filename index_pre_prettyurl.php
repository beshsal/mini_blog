<?php
// HTML HEAD & HEADER
include "includes/html_head.inc.php";
include "includes/header.inc.php";

// Check if there are published posts
$checkPosts = $conn->query("SELECT * FROM posts WHERE post_status = 'published'");

// Get the featured post
$getFeatpost = "SELECT * FROM posts
               LEFT JOIN images USING (image_id)                 
               WHERE post_status = 'published'
               AND featured = 'Yes'
               ORDER BY post_date ASC
               LIMIT 1";

$featuredPost = $conn->query($getFeatpost);
confirmQuery($featuredPost);    
while ($row = $featuredPost->fetch_assoc()) {
    $auth_uid     = $row["auth_uid"];
    $post_id      = $row["post_id"];
    $title        = $row["title"];
    $featured     = $row["featured"];
    $post_auth    = $row["post_auth"];
    $post_date    = $row["post_date"];
    $post_image   = "admin/images/post_images/{$row['filename']}";
    $lead         = $row["lead"];
    $post_content = $row["post_content"];
    $post_views   = $row["post_views"];
    $post_status  = $row["post_status"];
}

// WELCOME BANNER
include "includes/welcome_banner.inc.php";

// If there are posts, display them
if (isset($checkPosts) && $checkPosts->num_rows == 0) {
    echo "<h1 class='text-center' style='margin-top: 60px; margin-bottom: 15px;'>Uh oh. Looks like there aren't any posts yet.</h1>";
    if (isset($_SESSION["role"])) {
      if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") {
         echo "<p class='admin-priv text-center'>
                Please <a href='admin/posts.php?source=insert_post'>add</a> or <a href='admin/posts.php'>publish</a> posts.
              </p>";
      }
    }
} else { ?>
<!-- PAGE CONTENT -->	
<main class="page-content container" id="pageTop">    
    <?php
    if(isset($featured) && $featured == "Yes") {
        // Count all approved comment records associated with the current post
        $getApprvdComments = "SELECT * FROM comments
                             LEFT JOIN postxcomment USING (comment_id)
                             WHERE comment_status = 'approved'
                             AND postxcomment.post_id = {$post_id}";

        $apprvdComments = $conn->query($getApprvdComments);
        confirmQuery($apprvdComments);
        $approvedComments = $apprvdComments->num_rows;
    ?>    
  <!-- FEATURED POST --> 
  <section id="featured" class="post">
    <header class="section-heading">
      <h3>Featured Post</h3> <!-- add style="display: none;" if the fade-in is enabled -->
    </header>      
    <article>
      <div class="row">
        <div class="featured" class="col-xs-12">
          <header class="post-header">
            <div class="post-title-wrapper">
                <a href="post.php?feat=true&postid=<?php echo $post_id; ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <hr>
            <ul class="post-details list-inline">
              <li class="post-details-item post-auth">
                  By <a href="author_posts.php?author=<?php echo $auth_uid; ?>"><?php echo $post_auth; ?></a> 
              </li>
              <li class="post-details-item">
                <span class="post-date"><?php echo date("j F Y", strtotime($post_date)) ?></span>
              </li>
              <?php
              $getCategories = "SELECT * FROM categories
                               LEFT JOIN postxcat USING (cat_id)
                               WHERE postxcat.post_id = {$post_id}
                               ORDER BY category ASC";
              
              $categories = $conn->query($getCategories);
              confirmQuery($categories); 
              ?>
              <li class="post-details-item <?php if($categories->num_rows > 1){echo 'post-details-adjust1';} ?>">
                <i class="fa fa-eye" aria-hidden="true"></i> <?php echo $post_views; ?>              
                <i class="fa fa-comments-o"></i> <?php echo $approvedComments; ?>
              </li>              
              <li class="post-details-item <?php if($categories->num_rows > 1){echo 'post-details-adjust2';} ?>">
              <?php
              while ($row = $categories->fetch_assoc()) {                  
                $cat_id  = $row["cat_id"];
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
          <a href="post.php?feat=true&postid=<?php echo $post_id; ?>"><img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image"></a>
          <?php if (isset($lead) && !empty($lead)) { ?>
          <div class="lead">
            <blockquote>
            <a href="post.php?feat=true&postid=<?php echo $post_id; ?>"><p><?php echo $lead; ?></p></a>
            </blockquote>
          </div>
          <?php } ?>
          <div class="trim">
              <!-- Truncate the content to excerpts starting at character 0 to 600 character -->
              <a href="post.php?feat=true&postid=<?php echo $post_id; ?>">
                <div class="post-body"><?php echo convertToParas($post_content); ?></div>
              </a>
              <div id="fadeout">
              <a href="post.php?feat=true&postid=<?php echo $post_id; ?>" class="btn standard-btn">Continue Reading</a>
              </div>
          </div> <!-- /.trim -->            
        </div>
      </div>                    
    </article>        
  </section>      
  <hr>
  <?php } ?> 
  <!-- BLOGLIST -->
  <section id="bloglist">
    <header class="section-heading">
      <h3>Recent Posts</h3>
    </header>
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6"></div>      
      <?php    
      // Define a constant to hold a set number of published posts
      define('SHOWMAX', 5);

      // Get the number of all published posts, not including the featured post
      $getTotal = "SELECT COUNT(*) FROM posts WHERE post_status = 'published' AND featured = 'No'";        
      $total = $conn->query($getTotal);
        
      // Get the result row on its own as an enumerated array
      $row = $total->fetch_row();
      
      // The total count of records in the table
      $total_posts = $row[0];
        
      // Set the current page
      $currPage = isset($_GET["curPage"]) ? $_GET["curPage"] : 0;
    
      // Calculate the starting row/record of the subset (MySQL is zero-based so begins counting at 0)
      $startRow = $currPage * SHOWMAX;
    
      $query = "SELECT * FROM posts
               LEFT JOIN images USING (image_id)         
               WHERE post_status = 'published'
               AND featured = 'No'
               ORDER BY post_date DESC
               LIMIT {$startRow}," . SHOWMAX;      

      // This will contain the details of just the first record as $row['post_id'], $row['title'], etc.
      $result = $conn->query($query);
      confirmQuery($result);      
      while ($row = $result->fetch_assoc()) {
        $auth_uid      = $row["auth_uid"];
        $post_id       = $row["post_id"];
        $title         = $row["title"];
        $post_auth     = $row["post_auth"];
        $post_date     = $row["post_date"];
        $post_image    = "admin/images/post_images/{$row['filename']}";
        $lead          = $row["lead"];
        $post_content  = $row["post_content"];
        $post_views    = $row["post_views"];
        $post_status   = $row["post_status"];
        $post_comments = $row["post_comments"]; // this can be used to show an admin/author the number of all comments
          
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
          <a href="post.php?refpage=<?php echo $currPage; ?>&postid=<?php echo $post_id; ?>">
            <img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          </a>
          <span class="post-date"><?php echo date("M d", strtotime($post_date)) ?></span>
          <header class="post-header">
            <div class="post-title-wrapper">
            <a href="post.php?refpage=<?php echo $currPage; ?>&postid=<?php echo $post_id; ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <div class="post-details post-auth">By <a href="author_posts.php?author=<?php echo $auth_uid; ?>"><?php echo $post_auth; ?></a></div>
            <ul class="post-details list-inline">
              <li class="post-details-item">
                <i class="fa fa-eye" aria-hidden="true"></i> <?php echo $post_views; ?>              
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
          <a href="post.php?refpage=<?php echo $currPage; ?>&postid=<?php echo $post_id; ?>">
              <p><?php 
                // Defaults to 2 for the number of sentences to extract
                $extract = getFirst($post_content);
                echo $extract[0]; ?>
              </p>
          </a>
          <footer class="post-footer">
            <a href="post.php?refpage=<?php echo $currPage; ?>&postid=<?php echo $post_id; ?>" class="btn standard-btn">Read</a>
          </footer>
        </div>
      </article>        
      <?php } ?> <!-- /while -->        
    </div>
    <!-- PAGINATION -->
    <ul class="pager prev-next">
        <?php
        // If the current page is higher than the first page(0), create a back link
        if ($currPage > 0) {          
          echo "<li class='previous'><a href='" . $_SERVER['PHP_SELF'] . "?curPage=" . ($currPage-1) . "#bloglist'><span>&laquo;</span> Prev</a></li>";
        }
        
        // Create a forward link if the max of posts per page is exceeded
        if ($startRow + SHOWMAX < $total_posts) {
          // ?curPage+1 to move up a page
          echo "<li class='next'><a href='" . $_SERVER['PHP_SELF'] . "?curPage=" . ($currPage+1) . "#bloglist'>Next <span>&raquo;</span></a></li>";
        }        
        ?>
    </ul>
  </section>
</main>
<hr>
<?php }
// FOOTER
include "includes/footer.inc.php";
?>