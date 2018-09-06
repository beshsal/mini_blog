<?php
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
      <?php if(isset($heading) && !empty($heading)){echo $heading;} ?>
      </p>
      <?php if (isset($greeting) && !empty($greeting)) {
        echo $greeting;
      } else { ?>
        <p>Simplicity is nature's first step, and the last of art.
        <br><span>&#8212; Philip James Bailey</span></p>
      <?php } ?>
        
      <!-- The target may be either #pageTop or #featured -->
      <?php if (mysqli_num_rows($conn->query("SELECT * FROM posts")) > 0) {        
      ?>
      <a class="page-scroll scroll-btn" href="<?php if(isset($featured) && $featured == 'Yes'){echo '#featured';}else{echo '#bloglist';} ?>">EXPLORE</a>
      <?php } ?>
	</div>
</section>