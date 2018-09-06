<?php
// Get the number of posts under the specified category to determine how to structure the grid system
$getCount = "SELECT COUNT(*) FROM posts
            LEFT JOIN postxcat USING (post_id)
            WHERE postxcat.cat_id = {$cat_id}";

$result = $conn->query($getCount);        
confirmQuery($result);
$row = $result->fetch_row();
$count = $row[0] - 1; // -1 because the current row/record should not be included in the count

$query = "SELECT posts.post_id, posts.post_date, posts.title, images.filename FROM posts         
         LEFT JOIN images USING (image_id)
         LEFT JOIN postxcat USING (post_id)
         WHERE postxcat.cat_id = {$cat_id}
         AND posts.post_id != {$postId}";        

$related = $conn->query($query);
confirmQuery($related);

if ($count == 1) {
    $itemNum = "one-item";
} elseif ($count == 2) {
    $itemNum = "two-items";
} elseif ($count == 3) {
    $itemNum = "three-items";
} elseif ($count == 4) {
    $itemNum = "four-items";
}
?>
<section id="related">
  <header class="section-heading">
      <h3>Related Posts</h3>
  </header>
  <!-- Main div for carousel (Note: only adding carousel if there are more than 4 related posts) -->
  <?php if ($count > 4) { ?>    
  <div class="carousel slide" data-ride="carousel" data-type="multi" data-interval="false" id="related-carousel">
      <div class="carousel-inner">          
        <?php
        // DISPLAY RELATED POSTS
        $i = 0;
    
        foreach ($related as $row) {
        // If at the first record ($i == 0), add "active" class
        ?>
          <div class="item<?php if($i == 0){echo ' active';} ?>"> <!-- do not add space before php tags! -->
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
    // Increment the variable for counting
     $i++; } ?>          
        </div>
        <a class="left carousel-control" href="#related-carousel" data-slide="prev"><i class="glyphicon glyphicon-chevron-left"></i></a>
        <a class="right carousel-control" href="#related-carousel" data-slide="next"><i class="glyphicon glyphicon-chevron-right"></i></a>
    </div>    
    <?php } else { ?>
    <div class="row <?php echo $itemNum; ?>">
    <?php    
    if ($count == 1) {
    while($row = $related->fetch_assoc()) { ?>    
    <div class="item">
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
    elseif ($count == 2) {
    while ($row = $related->fetch_assoc()) { ?>      
    <div class="item">
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
    elseif ($count == 3) { 
    while($row = $related->fetch_assoc()) { ?>      
      <div class="item">
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
    elseif ($count == 4) {
    while($row = $related->fetch_assoc()) { ?>
    <div class="item">
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