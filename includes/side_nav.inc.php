<aside class="side-menu-basic">
  <div class="row">
    <div class="col-sm-5">    
      <a class="logo" href="<?php echo BASE_URL; ?>">
          <?php
          echo LOGO;
          ?>                  
      </a>
      <?php 
      // MENU
      include("menu.inc.php");
    
      // If the user is logged in, show the sign-out button.
      if(isset($_SESSION["authenticated"])):
      ?>
      <form id="signoutForm" method="post" action="">
        <button name="sign_out" type="submit" id="sign-out" class="btn standard-btn">SIGN OUT</button>
      </form>        
      <?php else: ?>        
      <!-- Otherwise, display the sign-in button. 
      The button to call the sign-in modal form on mobile sizes -->
      <button id="sign-in" class="btn standard-btn mobile">SIGN IN</button>        
      <?php endif; ?>
      <a class="project-details" href="https://beshsaleh.com/project_details">Project Details</a>
    </div>      
    <div class="col-sm-7">        
      <div class="side-menu-content">          
      <?php    
      // If the session has started/the user is logged in, show the user's image and the sign-out button.
      if(isset($_SESSION["authenticated"])):
          $userImage = $conn->query("SELECT filename FROM users
                                    LEFT JOIN user_images USING (user_id)             
                                    WHERE users.username = '" . $_SESSION["username"] . "'");             
          confirmQuery($userImage);
          $row = $userImage->fetch_array();
          
          // If there is an image filename for the user stored in the user_images table, use it to display the image. Otherwise
          // display the default user image.
          
          // For member users, a button for either adding or updating their user image will be displayed.
          
          if (isset($row["filename"]) && !empty($row["filename"])) {
      ?> 
<!--
          <img src="admin/images/user_images/<?php // echo $row["filename"]; ?>" class="img-responsive img-circle" alt="User Image" height="200" width="200" style="cursor: pointer;" onclick="$('#userImageModal').modal()">
-->          
          <div class="user-thumb lg"
               style="cursor: pointer; background-image: url('admin/images/user_images/<?php echo $row["filename"]; ?>')"
               onclick="$('#userImageModal').modal()"
               alt="User Image">
          </div>
      <?php
          // If the member has already uploaded an image, the member can update his or her image. Set the variable that gives the member
          // access to updating the current user image.
          $imgAction = "Update";
          } else { ?>
<!--
         <img src="admin/images/user_images/defaultuser.png" class="img-responsive img-circle" alt="Default User Image" height="200" width="200" style="cursor: pointer;" onclick="$('#userImageModal').modal()">
-->
          <div class="user-thumb lg"
               style="cursor: pointer; background-image: url('admin/images/user_images/defaultuser.png')"
               onclick="$('#userImageModal').modal()"
               alt="User Image">
          </div>
      <?php 
          // If the member has not uploaded an image, the member can add his or her image. Set the variable that gives the member 
          // access to inserting a user image.
          $imgAction = "Add";
          } ?>
          <h3>
          <!-- Display the logged-in user's first name and username. -->
          <?php echo $_SESSION['firstname'] . " (" . $_SESSION['username'] . ") "; ?>            
          </h3>      
          <br>
          <h4 style="color: gray; padding-bottom: 10%;">is currently signed in</h4>
          <form id="signoutForm" method="post" action="">
            <button name="sign_out" type="submit" id="sign-out" class="btn standard-btn">SIGN OUT</button>
          </form>
          <?php 
          // For admin or author users, a link to their profile page where they can add/update
          // their user image and bio will be displayed.
          if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") { ?>
          <div class="view-profile">
          <span>Go to your <a href="admin/profile.php">Profile</a></span>
          </div>
        <?php } else { ?>
          <!-- For members, display a button for either adding or updating their user image. -->
          <div class="add-image">
              <!-- Invoke userImageModal (userimage_modal.inc.php). -->
              <button id="addImage" onclick="$('#userImageModal').modal()"><?php echo $imgAction . " Image"; ?></button>
          </div>
        <?php } ?>
      <?php else: ?>          
        <div id="signinbox">
            <p>Member Sign-in</p>            
            <div class="pwd-check">
              <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
            </div>            
            <!-- SIGN-IN FORM -->
            <form action="" method="post" id="signinForm">
                <div class="form-group">
                  <label for="username">Username:</label>                   
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username" required>
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                  <div class="checkbox">
                    <label>
                    <input class="unavail" 
                           type="checkbox" 
                           id="remember" 
                           data-placement="bottom" 
                           data-title="" 
                           data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO."> Remember me
                    </label>
                  </div>
                </div>
                <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
            </form>            
            <div class="sign-up">
                <span>Not a member?
                <!-- Show the register form if clicked. -->
                <a onclick="$('#signinbox').hide(); $('#registerbox').show()">
                    Register here
                </a>
                </span>
            </div>          
        </div>
          
        <div id="registerbox" style="display:none;"> <!-- be sure not to remove display:none! -->
            <p>Register</p>
            <div class="sign-in">
            <!-- Show the sign-in form if clicked. -->
            <a onclick="$('#registerbox').hide(); $('#signinbox').show()">Sign in</a>
            </div>            
            <!-- REGISTER FORM -->
            <form action="" method="post" class="reg-form">
                <div class="form-group">
                  <label for="firstname">Enter your firstname:</label>
                  <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname">
                </div>

                <div class="form-group">
                  <label for="lastname">Enter your lastname:</label>
                  <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname">
                </div>

                <div class="form-group">
                  <label for="email">Enter your email:</label>
                  <!-- Change the input field's type to "email" to use the built-in HTML validation instead. -->
                  <input name="email" type="text" class="form-control" id="email" placeholder="Email">
                </div>
                <div class="form-group">
                  <label for="username">Enter your username:</label> 
                  <a id="info-uname">
                      <i class="fa fa-info" aria-hidden="true" style="color: white; float: right;"></i>
                  </a>
                  <div id="info-key1">                    
                    <small>Username must be at least 6 characters</small>                 
                  </div>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Username">
                </div>            
                <div class="form-group">
                  <label for="password">Enter your password:</label>
                  <a id="info-pwd">
                      <i class="fa fa-info" aria-hidden="true" style="color: white; float: right;"></i>
                  </a>
                  <div id="info-key2">
                    <ul>
                        <li><small>Password must be at least 10 characters</small></li>
                        <li><small>Password cannot contain spaces</small></li>
                        <li><small>Password should include uppercase and lowercase characters</small></li>
                        <li><small>Password should include at least 2 numbers</small></li>
                    </ul>
                  </div>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password">
                </div>
                <div class="form-group">
                  <label for="password">Re-enter your password:</label>
                  <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password">
                </div>
                <input name="role" type="hidden" value="member">
                <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
            </form>
        </div>                
      </div>              
      <?php endif; ?>        
    </div>     
  </div>    
  <span class="close-btn">×</span>
</aside>