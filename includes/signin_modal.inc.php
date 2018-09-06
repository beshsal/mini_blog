<div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->
<!-- SIGN-IN MODAL -->
<div id="signinModal" class="modal fade" role="dialog">
  <div id="modal-signin" class="modal-dialog">
    <!-- Modal content -->
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Member Sign-in</h4>
      </div>
      <div class="modal-body">
        <div class="pwd-check">
          <a href="forgot_password/<?php echo uniqid(true); ?>">Forgot password?</a>
        </div>
          
        <!-- SIGN-IN FORM -->         
        <form action="" method="post">
            <div class="form-group">
              <label for="username">Username:</label>                   
              <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username">
            </div>
            <div class="form-group">
              <label for="password">Password:</label>
              <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
            </div>
            <div class="form-group">
              <div class="checkbox">
                <label class="unavail" data-placement="bottom" data-title="" data-content="THIS FEATURE IS NOT AVAILABLE FOR THIS DEMO.">
                    <input class="unAvail" type="checkbox"> Remember me
                </label>
              </div>
            </div>
            <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
          </form>
          
        <div class="sign-up">
          <span>Not a member?
            <a onClick="$('#modal-signin').hide(); $('#modal-register').show()">Register here</a>
          </span>
        </div>                                  
      </div>
    </div>
  </div>
  <div id="modal-register" class="modal-dialog" style="display:none;"> <!-- be sure not to remove display: none! -->
    <!-- Modal content -->
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Register</h4>
      </div>              
      <div class="modal-body">
        <div class="sign-in">
        <a onclick="$('#modal-register').hide(); $('#modal-signin').show()">Sign in</a>
        </div>
          
        <!-- REGISTER FORM -->
        <form action="" method="post">
            <div class="form-group">
              <label for="firstname">Enter your firstname:</label>
              <input name="fname" type="text" class="form-control" autocomplete="off" id="fname" placeholder="Firstname" required>
            </div>
            <div class="form-group">
              <label for="lastname">Enter your lastname:</label>
              <input type="text" class="form-control" name="lname" autocomplete="off" id="lname" placeholder="Lastname" required>
            </div>
            <div class="form-group">
              <label for="email">Enter your email:</label>
              <input name="email" type="email" class="form-control" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
              <label for="username">Enter your username:</label>
              <a id="info-uname-mobile">
                  <i class="fa fa-info" aria-hidden="true" style="color: white; float: right;"></i>
              </a>
              <div id="info-key1-mobile">                    
                <small>Username must be at least 6 characters</small>                 
              </div>
              <div class="tooltip reg-guide" style="color: white;">? <span class="tooltiptext">This is a tip</span></div>
              <input name="uname" type="text" class="form-control" id="uname" placeholder="Username" required>
            </div>            
            <div class="form-group">
              <label for="password">Enter your password:</label>
              <a id="info-pwd-mobile">
                  <i class="fa fa-info" aria-hidden="true" style="color: white; float: right;"></i>
              </a>
              <div id="info-key2-mobile">
                <ul>
                    <li><small>Password must be at least 10 characters</small></li>
                    <li><small>Password cannot contain spaces</small></li>
                    <li><small>Password should include uppercase and lowercase characters</small></li>
                    <li><small>Password should include at least 2 numbers</small></li>
                </ul>
              </div>
              <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
            </div>
            <div class="form-group">
              <label for="password">Re-enter your password:</label>
              <input name="conf_pwd" type="password" class="form-control" id="pwd" placeholder="Password" required>
            </div>
            <input name="role" type="hidden" value="member">
            <button name="register" type="submit" class="btn standard-btn">REGISTER</button>
        </form> 
      </div>
    </div>
  </div>
</div>