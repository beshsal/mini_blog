<?php
// This is the page the user is redirected to when the sign-in session expires.

include "includes/html_head.inc.php";
include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

if (isset($_SESSION["authenticated"])) {
    header("Location: " . BASE_URL);
    exit;
}

// SIGN-IN SCRIPT
include_once "includes/signin.inc.php";
?>
<!-- PAGE CONTENT -->
<main class="page-content container"> 
  <section id="contact">
    <header class="section-heading">
      <h2 style="margin-bottom: 10px;">Session Expired</h2>
      <p>( Thank you for visiting
      <?php 
      // The message displays the logo text stored in the LOGO_UNSTYLED constant.
      echo "<strong>" . LOGO_UNSTYLED . "</strong>";
      ?>.
      Feel free to sign in again. )
      </p>
    </header>
    <div class="row">
        <h4 class="text-center">Sign in:</h4>
        <form action="" method="post">
            <div class="form-group">
              <label for="username">Username:</label>
              <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username">
            </div>
            <div class="form-group">
              <label for="password">Password:</label>
              <input name="pwd" type="password" class="form-control" id="pwd" placeholder="Enter password">
            </div>
            <button name="sign_in" type="submit" class="btn standard-btn">SIGN IN</button>
        </form>
    </div>
  </section>
</main>
<hr>
<!-- FOOTER -->
<?php include "includes/footer.inc.php"; ?>