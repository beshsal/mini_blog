<div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->
<!-- MODAL -->
<div id="errorModal" class="modal fade" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title error" id="myModalLabel">
            <?php 
            // If there is a sign-in error
            if($error) {
                echo $error . "!";
            // if there are registration error(s)
            } elseif($errors) {
                echo "Registration Errors!";
            }
            ?>
        </h4>
      </div>
      <div class="modal-body">
        <?php 
        if($error) {
            echo "<p>Check that you are entering your correct username and/or password</p>";
        } elseif ($errors) {
            echo "<ul>";
            foreach ($errors as $err) {
                echo "<li>$err</li>";
            }
            echo "</ul>";
        } 
        ?>
          <div class="text-center">
            <?php if(!isset($_GET["token"])) { ?>
            <button class="try-again btn standard-btn">Try again</button>
            <?php } ?>
          </div>
          <div class="slide-form">
              <?php if($error) { ?>              
              <form action="" method="post">
                <div class="form-group">
                  <label for="username">Username:</label>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username" 
                  value="<?php if(isset($username)){echo $username;} ?>">
                </div>
                <div class="form-group">
                  <label for="password">Password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
                </div>
                <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
              </form>
              <div style="text-align: right;">
                <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
              </div>
              <?php } elseif ($errors) { ?>
              <form action="" method="post">
                <div class="form-group">
                  <label for="firstname">Enter your firstname:</label>
                  <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname" value="<?php if(isset($firstname)){echo $firstname;} ?>" required>
                </div>
                <div class="form-group">
                  <label for="lastname">Enter your lastname:</label>
                  <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname" value="<?php if(isset($lastname)){echo $lastname;} ?>" required>
                </div>
                <div class="form-group">
                  <label for="email">Enter your email:</label>
                  <input name="email" type="email" class="form-control" id="email" placeholder="Email" value="<?php if(isset($email)){echo $email;} ?>" required>
                </div>
                <div class="form-group">
                  <label for="username">Enter your username:</label>
                  <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
                  <input name="uname" type="text" class="form-control" id="uname" placeholder="Username" value="<?php if(isset($username)){echo $username;} ?>" required>
                </div>            
                <div class="form-group">
                  <label for="password">Enter your password:</label>
                  <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
                </div>
                <div class="form-group">
                  <label for="password">Re-enter your password:</label>
                  <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
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