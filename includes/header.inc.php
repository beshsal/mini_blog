  <body>
    <div id="loader" class="text-center"><span>Please wait...</span></div>
    <div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->    
    <?php 
    // SIGN-IN MODAL 
    include "signin_modal.inc.php";
      
    // USER IMAGE MODAL 
    include "userimage_modal.inc.php";
      
    // ERROR MODAL
    include "error_modal.inc.php";

    // SIDE MENU 
    include "side_nav.inc.php";

    // SEARCH 
    include "search.inc.php";
    ?>
    <!-- HEADER -->
    <header>      
      <nav class="header-nav">
        <!-- If the user is signed in -->
        <?php if(isset($_SESSION["authenticated"])) { ?>
          <nav class="signin-nav">
            <div class="container">
                <ul class="nav-right pull-right list-inline">
                  <?php
                  // If the user is an author or admin, display an admin link along with the sign out link.
                  // If the user is a member, show only the sign out link.
                  if(isset($_SESSION["authenticated"]) && $_SESSION["role"] == "admin"
                  || isset($_SESSION["authenticated"]) && $_SESSION["role"] == "author") {
                      echo "<li><a class='admin-link' href='admin/'>ADMIN</a></li>";
                  ?>
                  <li><span style="color: #d6d6d6";>|</span></li>
                  <?php 
                  }
                  ?>
                  <li>
                    <form id="signoutForm" method="post" action="">
                      <button name="sign_out" type="submit" id="home-signout">SIGN OUT</button>
                    </form>
                  </li>
                </ul>
            </div>
          </nav>
        <?php } ?>
        <div class="container">
          <div class="row">
            <div class="col-xs-2 col-sm-4 col-md-5">
              <ul class="nav-left pull-left list-unstyled">
                <li><button class="menu-btn" data-show-dialog="side-menu-basic"><i class="fa fa-bars" aria-hidden="true"></i><span> MENU</span></button></li>
              </ul>
            </div>
            <div class="col-xs-8 col-sm-4 col-md-2">
              <!-- Header logo -->
              <a class="logo" href="<?php echo BASE_URL; ?>">
                  <?php
                  echo LOGO;
                  ?>                  
              </a>              
            </div>
            <div class="col-xs-2 col-sm-4 col-md-5">
              <ul class="nav-right pull-right list-inline">
                <li><a href="#search"><i class="fa fa-search" aria-hidden="true"></i></a></li>
              </ul>
              <ul class="social pull-right list-inline">
                <li><a><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
                <li><a><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
                <li><a><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>                
              </ul>
            </div>
          </div>
        </div> <!-- /.container -->
      </nav>
      <div class="jumbotron">
        <div class="container">
          <!-- Jumbotron logo -->
          <a class="logo" href="<?php echo BASE_URL; ?>">
          <?php
          echo LOGO;
          ?>                  
          </a>
        </div>
      </div>
    </header>