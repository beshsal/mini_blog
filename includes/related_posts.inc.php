<?php
// Get the required data for creating posts. Note that the current post is excluded so that it is not displayed
// in the grid.
$related = $conn->query("SELECT posts.post_id, posts.post_date, posts.title, images.filename FROM posts
                        LEFT JOIN images USING (image_id)
                        LEFT JOIN postxcat USING (post_id)
                        WHERE post_status = 'published'
                        AND postxcat.cat_id IN(" . implode(',', $catIdsArray) . ")
                        AND posts.post_id != {$postId}");
confirmQuery($related);

// Determine and set the number of post items. If there are 4 or fewer related posts, a div holding the grid 
// of post templates will be implemented; $itemNum's value is used as a class on the div to set the width of 
// the grid depending on the number of posts. If the are more than 4 related posts, a carousel will be implemented.
if ($relatedCount == 1) {
    $itemNum = "one-item";
} elseif ($relatedCount == 2) {
    $itemNum = "two-items";
} elseif ($relatedCount == 3) {
    $itemNum = "three-items";
} elseif ($relatedCount == 4) {
    $itemNum = "four-items";
}
?>
<section id="related">
  <header class="section-heading">
      <h3>Related Posts</h3>
  </header>
  <!-- If there are more than 4 posts, implement a carousel. -->
  <?php if ($relatedCount > 4) { ?>
  <!-- Main div for the carousel -->
  <div class="carousel slide" data-ride="carousel" data-type="multi" data-interval="false" id="related-carousel">
      <div class="carousel-inner">          
        <?php
        // DISPLAY RELATED POSTS
        $i = 0;    
        foreach ($related as $row) {
        // If at the first record ($i == 0), add the "active" class.
        ?>
          <div class="item<?php if($i == 0){echo ' active';} ?>"> <!-- do not add a space before php tags! -->
            <div class="col-xs-12 col-sm-6 col-md-3" style="min-height: 350px;">
              <div class="image-wrapper">
                <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>">
                <img src="admin/images/post_images/<?php echo $row['filename']; ?>" class="img-responsive">
                </a>
              </div>
              <div>
                <small><?php echo date("j F Y", strtotime($row["post_date"])); ?></small>
                  <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>"><h4><?php echo $row["title"]; ?></h4></a>
              </div>
            </div>                      
          </div>
    <?php 
    // Increment the variable for counting.
     $i++; } ?>          
        </div>
        <a class="left carousel-control" href="#related-carousel" data-slide="prev"><i class="glyphicon glyphicon-chevron-left"></i></a>
        <a class="right carousel-control" href="#related-carousel" data-slide="next"><i class="glyphicon glyphicon-chevron-right"></i></a>
    </div>    
    <?php 
    // If there are 4 or fewer posts, implement a grid system.
    } else { ?>
    <div class="row <?php echo $itemNum; ?>">
    <?php
    // If there is 1 related post, apply the respective formatting.
    if ($relatedCount == 1) {
    while ($row = $related->fetch_assoc()) { ?>    
    <div class="item adjust">
        <div class="col-xs-12">
            <div class="related-1">
                <div class="image-wrapper">
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>">
                    <img src="admin/images/post_images/<?php echo $row['filename']?>" class="img-responsive">
                    </a>
                </div>
                <div>
                    <small><?php echo date("j F Y", strtotime($row["post_date"])); ?></small>
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>"><h4><?php echo $row["title"]; ?></h4></a>
                </div>
            </div>
        </div>
    </div>      
  <?php }} 
    // If there are 2 related posts, apply the respective formatting.
    elseif ($relatedCount == 2) {
    while ($row = $related->fetch_assoc()) { ?>      
    <div class="item adjust">
        <div class="col-xs-12 col-sm-6">
            <div class="related-2">
                <div class="image-wrapper">
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>">
                    <img src="admin/images/post_images/<?php echo $row['filename']?>" class="img-responsive">
                    </a>
                </div>
                <div>
                    <small><?php echo date("j F Y", strtotime($row["post_date"])); ?></small>
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>"><h4><?php echo $row["title"]; ?></h4></a>
                </div>
            </div>
        </div>
    </div>      
  <?php }} 
    // If there are 3 related posts, apply the respective formatting.
    elseif ($relatedCount == 3) { 
    while($row = $related->fetch_assoc()) { ?>      
      <div class="item adjust">
        <div class="col-xs-12 col-sm-6 col-md-4">
            <div class="related-3">
                <div class="image-wrapper">
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>">
                    <img src='admin/images/post_images/<?php echo $row['filename']?>' class="img-responsive">
                    </a>
                </div>
                <div>
                    <small><?php echo date("j F Y", strtotime($row["post_date"])); ?></small>
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>"><h4><?php echo $row["title"]; ?></h4></a>
                </div>
            </div>
        </div>
      </div>  
  <?php }}
    // If there are 4 related posts, apply the respective formatting.
    elseif ($relatedCount == 4) {
    while($row = $related->fetch_assoc()) { ?>
    <div class="item adjust">
        <div class="col-xs-12 col-sm-6 col-md-3">
            <div class="related-4">
                <div class="image-wrapper">
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>">
                    <img src='admin/images/post_images/<?php echo $row['filename']?>' class="img-responsive">
                    </a>
                </div>
                <div>
                    <small><?php echo date("j F Y", strtotime($row["post_date"])); ?></small>
                    <a href="post/<?php echo $row['post_id']; ?>/<?php echo formatUrlStr($row['title']); ?>"><h4><?php echo $row["title"]; ?></h4></a>
                </div>
            </div>
        </div>
    </div> 
  <?php }} else { echo ""; } ?>
    </div>
    <?php } ?>
</section>