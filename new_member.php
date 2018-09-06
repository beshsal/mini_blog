<?php
include "includes/html_head.inc.php";

if (isset($_SESSION["authenticated"])) {
    header("Location: " . BASE_URL);
    exit;
}

if (!isset($_GET["tempid"])) {
    header("Location: " . BASE_URL);
    exit;
}

include "includes/header.inc.php";
include "includes/breadcrumb.inc.php";

// SIGN-IN SCRIPT
include_once "includes/signin.inc.php";
?>
<main class="page-content container"> 
  <section id="new-member">
    <header class="section-heading">
      <h2 style="margin-bottom: 10px;">Welcome</h2>
      <p>( Thank you for joining
      <?php
      echo "<strong>" . LOGO_UNSTYLED . "</strong>";
      ?>.
      You may now sign in. )
      </p>
    </header>     
    <div class="row">
        <h4 class="text-center">Sign in:</h4>
        <form action="" method="post">
            <div class="form-group">
              <label for="username">Username:</label>
              <input name="uname" type="text" class="form-control" id="uname" placeholder="Enter Username" 
              value="<?php if(isset($_GET["uname"])){echo$_GET["uname"];} ?>">
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