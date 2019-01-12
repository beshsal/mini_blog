    <footer>        
    <?php
    // If on the post page, add the about section (author's bio).
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
              // Get the author's full name, bio, and user image filename.
              $getAuthProfile = "SELECT fullname, bio, filename FROM auth_profile
                                LEFT JOIN user_images USING (user_id)             
                                WHERE auth_profile.user_id = {$auth_uid}"; // $auth_uid is set in post.php
                                                        
              $authProfile = $conn->query($getAuthProfile);                                          
              confirmQuery($authProfile);
              $row = $authProfile->fetch_array();
              
              // If there is a result
              if (isset($row) && !empty($row)) {
                if (isset($row["filename"]) && !empty($row["filename"])) {
              ?>
                <a href="author_posts/<?php echo $auth_uid; ?>/<?php echo formatUrlStr($post_auth); ?>"
                   class="user-thumb md"
                   style="cursor: pointer; background-image: url('admin/images/user_images/<?php echo $row["filename"]; ?>')"
                   alt="User Image">
                </a>
              <?php } ?>
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
      <!-- Default footer components -->
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
            <p>Design by <a href="https://beshsaleh.com/">Beshara Saleh</a></p>
        </div>
      </div>
    </footer>
    
    <!-- JS -->
    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="bower_components/jquery/dist/jquery.js"></script>
    <!-- Include all compiled plugins (below) or include individual files as needed -->
    <script src="bower_components/jquery-ui/jquery-ui.min.js"></script>    
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.3/jquery.easing.min.js"></script> <!-- scroll easing -->
    <script src="https://unpkg.com/imagesloaded@4/imagesloaded.pkgd.min.js"></script> <!-- required to prevent isotope overlapping -->
    <!-- <script src='https://npmcdn.com/isotope-layout@3/dist/isotope.pkgd.js'></script> -->
    <script src="bower_components/isotope/dist/isotope.pkgd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.bootstrapvalidator/0.5.2/js/bootstrapValidator.min.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/bootstrap-filestyle-1.2.3/src/bootstrap-filestyle.min.js"></script>
    <script>
    // LOAD MORE COMMENTS
    $(document).on("click", ".loadmore", function() {
        // Add text to the button indicating that the records are loading.
        $(this).text("Loading...");
        // Get the parent element (.load-more div) of the .loadmore button, and save it in a variable.
        var ele = $(this).parent('div');
        $.ajax({
            url: "includes/loadmore.inc.php", // access loadmore.inc.php
            type: "POST", // specify sending a POST request
            data: { // data we are sending (page data of the .loadmore button)
              postID:$(this).data("postid"), // the post_id in the data-postid attribute assigned to the .loadmore button
              page:$(this).data("page") // the value from the data-page attribute assigned to the .loadmore button (initially page=2)
              // data() stores arbitrary data associated with the specified element and/or returns the value that was set.              
            },
            // On success of the POST request, the loaded records should be returned in the response.
            success: function(response) {
              // If the response is returned
              if(response) {                      
                setTimeout(function() {                    
                    // Hide the .load-more div; if this isn't here, the .loadmore button stays where it is, and the loaded article 
                    // elements containing the comment data display directly under it; this makes it disappear but only when the 
                    // response comes in; that's why it shows again immediately after the response was successful.
                    ele.hide();
                    // Append the comment data to the #comment-list section, which hold articles.
                    $("#comment-list").append(response);
                    $("#comment-list-mobile").append(response);
                }, 250); // 0.25 sec

              }
            }
        });
    });
    </script>

    <script>
    // SUBMIT MEMBER USER IMAGE
    function submitImage() {
        // Get the image file data from the form.
        var formData = new FormData();
        var file = $("#imgFile")[0].files[0];
        formData.append('file', file);
        // Check if a file is uploaded. If there is a file, submit the form through AJAX.
        if(file) { // returns true if the string is not empty
            $.ajax({
                type: "POST",
                url: "includes/submit_userimage.inc.php",
                // data: $("#userImageForm").serialize(),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () { 
                    $('.header-nav').css('opacity', '.1');
                    $('#welcome').css('opacity', '.1');
                    $('.side-menu-basic').css('opacity', '.1');
                    $('.modal-header').css('opacity', '.1');
                    $('.modal-body').css('opacity', '.1');
                    $('#uploadImg').attr("disabled","disabled");
                    $('#imgFile').attr("disabled","disabled");
                },
                success: function(response) {
                    if (response) {                            
                        $('#userImageForm')[0].reset();
                        $('.success-alert').html('<span>' + response + '</span>');
                        $(".success-alert").fadeIn(300).delay(3300).fadeOut(400);
                    } else {
                        $('.header-nav').css('opacity', '');
                        $('#welcome').css('opacity', '');
                        $('.side-menu-basic').css('opacity', '');
                        $('.modal-header').css('opacity', '');
                        $('.modal-body').css('opacity', '');
                        $('#uploadImg').removeAttr("disabled");
                        $('#imgFile').removeAttr("disabled");
                        // alert("No response returned.");
                    }
                }
            }).done(function() {
                setTimeout(function() { 
                    location.reload();
                }, 3800);
            });
        } else { // no file was selected
            $('.uploadMsg').html('<span class="imgErr error">No file selected.</span>');
            $("#imgFile").val('');
        }
    }
        
    // Check the file type. Warn the user before the form is submitted.
    $("#imgFile").change(function() {
        var file = this.files[0];
        var type = file.type;
        var filename = this.files[0].name;
        var match= ["image/jpeg","image/png","image/jpg", "image/gif"];
        if(!((type==match[0]) || (type==match[1]) || (type==match[2]) || (type==match[3]))) {
            $('.uploadMsg').html('<span class="imgErr error">Please select a valid image file (JPEG/JPG/PNG/GIF).</span>');
            $("#imgFile").val('');
            return false;
        }
    });
    </script>

    <script>
    // SEARCH FORM (AJAX)
    $(function() {
        $(".search-form").on('submit', function(event) {
            event.preventDefault();
            $.ajax({
                type: 'POST',
                url: 'includes/get_search_results.inc.php',
                data: new FormData(this),
                contentType: false,
                cache: false,
                processData:false,                
                success: function(response) {
                    if (response) { 
                        // window.location.href = "search_results.php?searchterm=" + response;
                        window.location.href = "search_results/" + response;
                    } 
                }
            });
        });
    });
    </script>

    <script>
    // DISPLAY LOADER AND SUBMIT USER REGISTRATION FORM
    $(function() {
        var loader = $('#loader');
        $(".reg-form").on('submit', function(event) { // be sure to add the "reg-form" class to each registration form
            event.preventDefault();
            // loader.show();
            $.ajax({
              type: 'POST',
              url: 'includes/register_mysqli.inc.php',
              data: $(this).serialize(),
              dataType: 'JSON',
              success: function(response) {                
                if (response.hasOwnProperty("userSuccess")) {
                    $('#errorModal').modal('hide');
                    loader.show();
                    var res = response;
                    var userSuccess = res.userSuccess;
                    var successPath = res.successPath;
                    // alert(res.userSuccess + " " + res.successPath);
                    $("#signinModal").modal('hide');
                    setTimeout(function() {                        
                        window.location.href = "welcome_new_member/" + successPath;
                    }, 3000);
                // If there are registration errors, retrieve the errors from the response object,
                // and apply them to the error modal.
                } else if (response.hasOwnProperty("userFail")) {
                    var res = response;
                    var errors = res.userFail;
                    var firstname = res.failFirstname;
                    var lastname = res.failLastname;
                    var username = res.failUsername;
                    var email = res.failEmail

                    $(".reg-errors").html(errors); // ul of errors                 
                    $("#reg-fname").attr("value", firstname);
                    $("#reg-lname").attr("value", lastname);
                    $("#reg-uname").attr("value", username);
                    $("#reg-email").attr("value", email);                    
                    $('#errorModal').modal('show');
                }
              }
            }).done(function(response) {
                if (response) {
                    // alert(res.userSuccess);
                    var res = response;
                    var userSuccess = res.userSuccess;
                    // Send mail.
                    $.post('includes/process_welcome_mail.inc.php',
                    {
                        username: userSuccess
                    },
                    function(response) {
                    if (response) {
                        // alert(response);
                    } else {
                        // alert('No response!');
                    }
                    });
                }
            });
        });
    });
    </script>

    <?php
    // DISPLAY ERROR MODAL
    // ERROR MODAL FORM RESET AND PAGE REFRESH WHEN CLOSED
    if (isset($error) && !empty($error)) { 
        // Call the script for displaying the error modal.
        echo "<script>
             $(function() {
                $('#errorModal').modal('show');
             });
             </script>";
        
        echo "<script>
             $(function() {
                $('#errorModal').on('hidden.bs.modal', function() {                   
                   $('.errSigninForm')[0].reset();
                   if($('#comments').hasClass('comntSignin')) {
                      window.location.href = window.location.href + '#comments';
                      $('#tab1').removeClass('active');
                      $('#tab2').addClass('active');
                      $('#comment-list').removeClass('active');
                      $('#comment-form').addClass('active');
                      $('#comments').removeClass('comntSignin');
                   } else {                      
                      window.location.href = window.location.href;    
                   }   
                  });
                });
             </script>";
    }
    ?>

    <script>
    // "TRY AGAIN" SLIDE DOWN LOGIN FORM IN THE ERROR MODAL
    $(".slide-form").hide();
    $(".try-again").click(function() {
       $(".try-again").hide();
       $(".slide-form").slideToggle().show();
    });
    </script>

    <script>
//    // INSERT AND LOAD CHILD COMMENTS
    $(function() {
        // Must use $(document).on for jquery to work in AJAX loaded content. 
        $(document).on('submit','.child-comment-form', function(event) {
            event.preventDefault();
            var thisForm = $(this);
            var thisErr = $(this).find(".childCommentErr");
            var formData = $(this).serialize();
            $.ajax({
                method: "POST",
                url: "includes/insert_child_comment.inc.php",       
                data: formData,
                dataType: "JSON",
                success: function(response) {
                    if(response) {
                        // If the reply was successfully inserted, reset the form and error message. Otherwise,
                        // retrieve the error from the response, and display it.
                        if (response.commentSuccess) {
                            thisForm[0].reset();
                            thisErr.html('');
                            document.location.reload(true);
                        } else if (response.commentError || response.insertFail) {
                             var commErr = response.commentError ? response.commentError : response.insertFail;
                             thisErr.html(commErr);    
                        } else {
                             alert("No response");
                        }
                    }
                }
            });
        });
    });
    </script>

    <?php
    // TRACK SIGNED-IN MEMBERS
    if (isset($_SESSION["authenticated"])) { ?>
    <script>
    <?php
      $sessionID   = session_id(); // get the current session ID
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