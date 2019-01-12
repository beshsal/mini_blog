<div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->
<!-- MODAL -->
<div id="errorModal" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="errHeading">
            <?php 
            // If there is a sign-in error
            if($error) {
                echo $error . "!";
            // If there a registration error
            } else {
                echo "Registration Errors!";
            }
            ?>
        </h4>
      </div>
      <div class="modal-body">
        <div class="reg-errors">
        <?php 
        // If there is a sign-in error, inform the user.
        if($error) {
            echo "<p class='error'>Check that you are entering your correct username and/or password.</p>";
        }
        ?>
          </div>
          <div class="text-center">
            <!-- Give the user an opportunity to try again. -->            
            <button class="try-again btn standard-btn">Try again</button>            
          </div>
          <div class="slide-form">
              <!-- If the error occured when attempting to log in, show the sign-in form. -->
              <!-- Note the fields will retain their data. -->
              <?php if ($error) { ?>              
              <form action="" method="post" class="errSigninForm">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username" 
                  value="<?php if(isset($username)) echo $username; elseif(isset($_GET['username'])) echo $_GET['username']; ?>" required>
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password" required>
                </div>
                <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
              </form>
              <div style="text-align: right;">
                <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
              </div>
              <!-- If the error occured during registration, show the registration form. -->
              <!-- Note the fields will retain their data. -->
              <?php } else { ?>
              <form action="" method="post" class="reg-form">
                <div class="form-group">
                  <label for="firstname">Enter your firstname:</label>
                  <input name="fname" type="text" class="form-control" autocomplete="off" id="reg-fname" placeholder="Firstname" value="">
                </div>
                <div class="form-group">
                  <label for="lastname">Enter your lastname:</label>
                  <input type="text" class="form-control" name="lname" autocomplete="off" id="reg-lname" placeholder="Lastname" value="">
                </div>
                <div class="form-group">
                  <label for="email">Enter your email:</label>
                  <input name="email" type="text" class="form-control" id="reg-email" placeholder="Email" value="">
                </div>
                <div class="form-group">
                  <label for="username">Enter your username:</label>
                  <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
                  <input name="uname" type="text" class="form-control" id="reg-uname" placeholder="Username" value="">
                </div>            
                <div class="form-group">
                  <label for="password">Enter your password:</label>
                  <input name="pwd" type="password" class="form-control" id="reg-pwd" placeholder="Password">
                </div>
                <div class="form-group">
                  <label for="password">Re-enter your password:</label>
                  <input name="conf_pwd" type="password" class="form-control" id="reg-pwd" placeholder="Password">
                </div>
                <input name="role" type="hidden" value="member">
                <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
              </form>
             <?php } ?>  
          </div>
      </div>      
      <div class="modal-footer">
          <button type="button" class="btn cancel-btn" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>