<?php
// Get the welcome message and heading (if there is one).
$query  = "SELECT * FROM welcome";
$result = $conn->query($query);
confirmQuery($result);
while ($row = $result->fetch_assoc()) {
    $heading  = $row["heading"];
    $greeting = $row["greeting"];
}
?>
<section id="welcome" data-type="background" data-speed="5">    
	<div class="intro">
      <p class="heading">
      <!-- If there is a heading, display it. -->
      <?php if(isset($heading) && !empty($heading)){echo $heading;} ?>
      </p>
      <!-- If there is a welcome message, display it; otherwise, display a default one. -->
      <?php if (isset($greeting) && !empty($greeting)) {
        echo $greeting;
      } else { ?>
        <p>Simplicity is nature's first step, and the last of art.
        <br><span>&#8212; Philip James Bailey</span></p>
      <?php } ?>
        
      <!-- Below is used for the page-scroll button in the header. -->  
      <!-- The target may be either #pageTop or #featured. -->
        
      <!-- If there are posts, add the page-scroll button. -->
      <?php if (mysqli_num_rows($conn->query("SELECT * FROM posts")) > 0) {        
      ?>
      <!-- If there is a featured post, scroll to #featured section (or #pageTop instead); otherwise, scroll to the #bloglist section. -->
      <a class="page-scroll scroll-btn" href="<?php if(isset($featured) && $featured == 'Yes'){echo '#featured';}else{echo '#bloglist';} ?>">EXPLORE</a>
      <?php } ?>
	</div>
</section>