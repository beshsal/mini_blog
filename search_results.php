<?php
// This is the page that processes the "searchterm" value submitted from the 
// search form (search.inc.php) and returns matching posts.

include "includes/html_head.inc.php";
include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

// Set a default error message.
$searchError = "<p class='warning'>You cannot submit an empty search!</p>";

// If the form is submitted
if (isset($_GET["searchterm"])) {
    // Reset the error message.
    $searchError = "";
    // Extract the search term value from the field to a variable, which will be used
    // to query the database.
    $searchterm = trim($conn->real_escape_string($_GET["searchterm"]));
    
    // If an empty value is not submitted, search for all posts with a category that matches the search term.
    if (!empty($searchterm)) {        
        // Get a matching cat_id if it exists.
        $getCatId = $conn->query("SELECT cat_id FROM categories WHERE category LIKE '%" . $searchterm . "%'");
        $row      = $getCatId->fetch_assoc();
        $catId    = $row["cat_id"];

        if (isset($catId) && !empty($catId)) {
            $result = $conn->query("SELECT * FROM posts
                                   LEFT JOIN images USING (image_id)
                                   LEFT JOIN postxcat USING (post_id)
                                   WHERE post_status = 'published'
                                   AND postxcat.cat_id = {$catId}");
            confirmQuery($result);
        } else {            
            // If there are no posts that match the category, then search through all posts for a title that includes the search term. 
            // If no match is found, then search for a matching post author.
            $result = $conn->query("SELECT * FROM posts
                                   LEFT JOIN images USING (image_id)                 
                                   WHERE posts.title LIKE '%" . $searchterm . "%'
                                   OR posts.post_auth LIKE '%" . $searchterm . "%'
                                   AND post_status = 'published'");
            confirmQuery($result);
        } 
    // If the search term is empty, the user is simply redirected back to this page (the search_results page), and the 
    // default error message is displayed.
    } else {
        header("Location: search_results");
        exit;
    }
    
    // If none of the queries above find matching posts, set an error message informing the viewer.
    if (!isset($result) || $result->num_rows == 0 || $result == null) {        
        $searchError = "<h1 class='text-center'>Sorry</h1> 
                       <h3 class='text-center'>The post you are searching for does not exist.</h3>";
    }
}
?>
<!-- PAGE CONTENT -->
<main class="page-content container">  
  <section id="bloglist" class="text-center">
    <?php
    // If $searchError contains a message, display it.
    if (isset($searchError) && $searchError != "") {
        echo "<h1 class='text-center'>{$searchError}</h1>
             <h4 class='re-search'><a href=#search>Search Again</a></h4>";
    // Otherwise, display the matching posts.
    } else {      
    ?>
    <header class="section-heading">
        <h3>Search Results: <span><?php echo $searchterm; ?></span></h3>
        <h4 class="re-search"><a href=#search>Search Again</a></h4>
    </header>
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6"></div>
      <?php        
      while($row = $result->fetch_assoc()) {           
        $post_id      = $row["post_id"];
        $auth_uid     = $row["auth_uid"];
        $post_auth    = $row["post_auth"];
        $title        = $row["title"];
        $post_auth    = $row["post_auth"];
        $post_date    = $row["post_date"];
        $post_image   = "admin/images/post_images/{$row['filename']}";
        $post_content = $row["post_content"];
        $post_views   = $row["post_views"];
          
        // Count all approved comment records associated with the current post.
        $getApprvdComments = "SELECT * FROM comments
                             LEFT JOIN postxcomment USING (comment_id)
                             WHERE comment_status = 'approved'
                             AND postxcomment.post_id = {$post_id}";

        $apprvdComments = $conn->query($getApprvdComments);
        confirmQuery($apprvdComments);
        $approvedComments = $apprvdComments->num_rows;
      ?>        
      <article class="grid-item col-xs-12 col-sm-6">
        <div class="grid-item-content">
          <a href="post.php?postid=<?php echo $post_id; ?>">
            <img class="img-responsive" src="<?php echo $post_image; ?>" alt="Post Image">
          </a>
          <span class="post-date"><?php echo date("M d", strtotime($post_date)) ?></span>
          <header class="post-header">
            <div class="post-title-wrapper">
            <a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>" class="post-title"><?php echo $title; ?></a>
            </div>
            <div class="post-details post-auth">By <a href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>"><?php echo $post_auth; ?></a>
            </div>
            <ul class="post-details list-inline">
              <li class="post-details-item">
                <i class="fa fa-eye"></i> <?php echo $post_views; ?>              
                <i class="fa fa-comments-o"></i> <?php echo $approvedComments; ?>
              </li>
              <li class="post-details-item">
              <?php        
              $getCategories = "SELECT * FROM categories
                               LEFT JOIN postxcat USING (cat_id)
                               WHERE postxcat.post_id = {$post_id}";
          
              $categories = $conn->query($getCategories);
              confirmQuery($categories); 
          
              while($row = $categories->fetch_assoc()) {                
                  $category = $row["category"];
                  $cat_id   = $row['cat_id'];
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
      <?php } } ?> <!-- /while -->        
    </div>
  </section>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>

