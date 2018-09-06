    <footer>        
    <?php
    if (THIS_PAGE == "post.php") {
      if (isset($auth_uid) && $auth_uid != '') {  
    ?>
      <section id="about">           
        <div class="container">
          <div class="row">
            <div class="col-sm-2 col-md-3"></div> <!-- outer div -->
            <div class="col-sm-8 col-md-6">
              <div class="author">
              <?php   
              $getAuthProfile = "SELECT fullname, bio, filename FROM auth_profile
                                LEFT JOIN user_images USING (user_id)             
                                WHERE auth_profile.user_id = {$auth_uid}"; // $auth_uid is set in post.php
                                                        
              $authProfile = $conn->query($getAuthProfile);                                          
              confirmQuery($authProfile);
              $row = $authProfile->fetch_array();
              if (isset($row) && !empty($row)) {
              ?>
                <a href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>">
                <img src="admin/images/user_images/<?php echo $row["filename"]; ?>" class="img-responsive img-circle" alt="User Image" height="100" width="100"></a>
                <a  class="authname" href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>"><h3><?php echo $row["fullname"]; ?></h3></a>
                <p class="bio"><?php echo $row["bio"]; ?></p>
              <?php } else { ?>
                <h3 class="authname">By Anonymous</h3>
              <?php } ?>
              </div>
            </div>
            <div class="col-sm-2 col-md-3"></div> <!-- outer div -->
          </div>
        </div>
      </section>        
    <?php }} ?>        
      <nav class="footer-nav">
      <?php if (THIS_PAGE == "post.php" || THIS_PAGE == "category.php" || THIS_PAGE == "author_posts.php") : ?>
        <ul class="list-inline">
            <li class="goback"><button onclick="goBack()">Go Back</button></li>
        </ul>
        <?php else : ?>
        <ul class="list-inline">
          <li><a href="<?php echo BASE_URL; ?>" <?php if(THIS_PAGE == "index.php") {echo "id='active'";} ?>>Home</a></li>
          <li><a href="categories" <?php if(THIS_PAGE == "categories.php") {echo "id='active'";} ?>>Categories</a></li>
          <li><a href="contact" <?php if(THIS_PAGE == "contact.php") {echo "id='active'";} ?>>Contact</a></li>        
        </ul>
        <?php endif; ?>     
      </nav>
      <div class="copyright col-xs-12 text-center">
        <div class="container">
          &copy; 
            <?php
              $startYear = 2017;
              $thisYear = date('Y');
              if ($startYear == $thisYear) {
                echo $startYear;
              } else {
                echo "{$startYear} &#8211; {$thisYear}";
              }
            ?> 
            Beshara Saleh
        </div>
      </div>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="bower_components/jquery/dist/jquery.js"></script>
    <!-- Include all compiled plugins (below) or include individual files as needed -->
    <script src="bower_components/jquery-ui/jquery-ui.min.js"></script>    
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script> <!-- scroll easing -->
    <script src='http://npmcdn.com/isotope-layout@3/dist/isotope.pkgd.js'></script>
    <script src="bower_components/isotope/dist/isotope.pkgd.min.js"></script>
    <script src="http://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/js/bootstrapValidator.min.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/bootstrap-filestyle-1.2.3/src/bootstrap-filestyle.min.js"></script>
    <script>
    // LOAD MORE COMMENTS
    $(document).on("click", ".loadmore", function() {
        // Add text indicating the records are loading to the button's text
        $(this).text("Loading...");
        // Get the parent element (.load-more div) of the .loadmore button and save it in a variable
        var ele = $(this).parent('div');
        $.ajax({
            url: "includes/loadmore.inc.php", // access loadmore.php
            type: "POST", // specify sending a POST request
            data: { // data we are sending (page data of the .loadmore button)
              postID:$(this).data("postid"),
              page:$(this).data("page")
              // data() stores arbitrary data associated with the specified element and/or returns the value that was set
              // This stores page=2 (from data-page attribute)                  
            },
            // On success of the POST request, the loaded records should be returned in the response
            success: function(response) {
              // If the response is returned
              if(response) {                      
                setTimeout(function() {                    
                // Hide the .load-more div; if this isn't here, the .loadmore button stays where it is, and the loaded article 
                // elements containing the comment data display directly under it; this makes it disappear but only when the 
                // response comes in; that's why it shows again immediately after the response was successful
                    ele.hide();
                    // Append the data to the #comment-list section, which hold articles
                    $("#comment-list").append(response);
                    $("#comment-list-mobile").append(response);
                }, 250); // 0.25 sec

              }
            }
        });
    });
	</script>

    <?php
    // ERROR MODAL SCRIPT
    // The script for invoking the error modal is added only if there are errors
    if (isset($error) && !empty($error) || isset($errors) && !empty($errors)) {
        include "error_modal.inc.php";        
        echo "<script type='text/javascript'>
                $(document).ready(function() {
                    $('#errorModal').modal('show');
                });
             </script>";
    }
    ?>

   <script>
   // SLIDE DOWN "TRY AGAIN" LOGIN FORM IN THE ERROR MODAL
   $(".slide-form").hide();
   $(".try-again").click(function() {
       $(".try-again").hide();
       $(".slide-form").slideToggle().show();
   });
   </script>

    <script>
    // RETURN TO PREVIOUS PAGE
    function goBack() {
        window.history.back();
    }
    </script>

    <?php
    // TRACK SIGNED IN MEMBERS
    if (isset($_SESSION["authenticated"])) { ?>
    <script>
    <?php
      $sessionID   = session_id(); // get the current session id
      $sessionTime = time(); // capture the time the user is online starting at sign-in
    
      echo "var sessionID = '{$sessionID}';";
      echo "var sessionTime = '{$sessionTime}';";
    ?>    
    $(function() {
        $.get( 
              "includes/track_members.inc.php",
              {
              sessionID: sessionID, 
              sessionTime: sessionTime
              },
              function(data) {
                // alert("Data loaded: " + data); // works
              }
        );
    });        
    </script>
    <?php } ?>
  </body>
</html>
<?php ob_end_flush(); ?>