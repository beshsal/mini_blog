<?php
if(isset($_SESSION["authenticated"])) :
?>
    <div class="userImageMobile">
        <?php
        $usrImgMob = $conn->query("SELECT filename FROM users
                                  LEFT JOIN user_images USING (user_id)             
                                  WHERE users.username = '" . $_SESSION["username"] . "'");             
        confirmQuery($usrImgMob);
        $row = $usrImgMob->fetch_array();
        if (isset($row["filename"]) && !empty($row["filename"])) {
            if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") { ?>
            <a href="admin/profile.php">
            <img src="admin/images/user_images/<?php echo $row["filename"]; ?>" class="img-responsive img-circle" alt="User Image" height="60" width="60">
            </a>
            <?php } else { ?>
            <a id="addImage" onClick="$('#userImageModal').modal()">
            <img src="admin/images/user_images/<?php echo $row["filename"]; ?>" class="img-responsive img-circle" alt="User Image" height="60" width="60">
            </a>
            <?php } ?>
        <?php } ?>
        <h5>
        <?php echo $_SESSION['firstname'] . " (" . $_SESSION['username'] . ") "; ?>            
        </h5>
    </div>
<?php endif; ?>

<ul class="side-menu-content list-unstyled <?php if(isset($_SESSION["authenticated"]) && isset($row["filename"])){echo 'userimage-adjust';} ?>">
    <li>
      <a href="<?php echo BASE_URL; ?>" <?php if(THIS_PAGE == "index.php") {echo "id='active'";} ?>>Home</a>
    </li>
    <li>
      <a href="categories" <?php if(THIS_PAGE == "categories.php") {echo "id='active'";} ?>>Categories</a>
    </li>
    <li>
      <a href="contact" <?php if(THIS_PAGE == "contact.php") {echo "id='active'";} ?>>Contact</a>
    </li>    
    <?php    
      // If logged in as an admin or an author, the Admin link will display
      if(isset($_SESSION["authenticated"]) && $_SESSION["role"] == "admin"
      || isset($_SESSION["authenticated"]) && $_SESSION["role"] == "author") {
          echo "<li><a class='admin-link' href='admin/'>Admin</a></li>";
      }
      ?>    
</ul>
<ul class="social text-center">
    <span><a><i class="fa fa-facebook-square" aria-hidden="true"></i></a></span>
    <span><a><i class="fa fa-twitter-square" aria-hidden="true"></i></a></span>
    <span><a><i class="fa fa-google-plus-square" aria-hidden="true"></i></a></span>
</ul>