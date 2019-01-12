<?php
include "includes/html_head.inc.php";
include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

$checkCats = $conn->query("SELECT * FROM categories");
?>
<!-- PAGE CONTENT -->
<main class="page-content container">      
  <section id="categories">
  <?php 
  // If there aren't any categories, display a message informing the viewer; otherwise, display the rest of the page.
  if (isset($checkCats) && $checkCats->num_rows == 0):
        echo "<h3 class='text-center'>There are currently no categories assigned to posts.</h3>";
  else: 
  ?>
    <header class="section-heading">
      <h3>Categories</h3>
    </header>
    <!-- Masonry grid for category items -->
    <div class="grid">
      <div class="grid-sizer col-xs-12 col-sm-6 col-md-4"></div>    
      <?php        
      $getCategories = "SELECT * FROM categories";
      $categories = $conn->query($getCategories);
      confirmQuery($categories);        
      while($row = $categories->fetch_assoc()) {          
        $cat_id   = $row["cat_id"];
        $category = $row["category"];
          
        // Get the number of posts for each category based on the cat_id.
        $getPostCount = "SELECT * FROM posts
                        LEFT JOIN postxcat USING (post_id)
                        WHERE post_status = 'published'
                        AND postxcat.cat_id = {$cat_id}";

        $resultCount = $conn->query($getPostCount);
        confirmQuery($resultCount);
        $count = $resultCount->num_rows;
        // If there are posts for a category, display links to the posts.
        if($count > 0) {
      ?>
          <div class="grid-item col-xs-12 col-sm-6 col-md-4">
              <div class="grid-item-content">
                <header class="cat-topic"><?php echo $category; ?></header>
                    <ul class="list-unstyled">
                    <?php
                    // Select the post that is associated with the category.
                    $query = "SELECT posts.post_id, posts.title FROM posts
                             LEFT JOIN postxcat USING (post_id)
                             WHERE post_status = 'published'
                             AND postxcat.cat_id = ?
                             LIMIT 8";

                    if ($stmt = $conn->prepare($query)) {
                        // Bind parameter
                        $stmt->bind_param("i", $cat_id);
                        // Execute statement
                        $stmt->execute();
                        // Bind result variables
                        $stmt->bind_result($post_id, $title);
                        // Fetch values
                        while ($stmt->fetch()) { ?>       
                            <li><a href="post/<?php echo $post_id; ?>/<?php echo formatUrlStr($title); ?>" class="cat-item"><?php echo $title; ?></a></li>            
                        <?php }                
                        // Close statement
                        $stmt->close();
                    } else {
                        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
                    }            
                    ?>   
                        <!-- If there are more than 8 posts associated with the category, add a link to the specific category page. -->
                        <li><?php if ($count > 8){echo "<a href='category.php?category={$cat_id}'>See all...</a>";} ?></li>
                    </ul>
              </div>
          </div>        
      <?php } }
      // Close the connection.
      $conn->close();
      ?>        
    </div>
    <?php endif; ?>
  </section>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>