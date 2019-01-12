<?php
// If the user is logged in
if(isset($_SESSION["authenticated"])) :
?>
    <!-- This div holds the user's image that is display at mobile sizes. For the user's image on 
    larger screen sizes, see side_nav.php. -->
    <div class="userImageMobile">
        <?php
        $usrImgMob = $conn->query("SELECT filename FROM users
                                  LEFT JOIN user_images USING (user_id)             
                                  WHERE users.username = '" . $_SESSION["username"] . "'");             
        confirmQuery($usrImgMob);
        $row = $usrImgMob->fetch_array();
        // If the user has uploaded an image, indicated by an image filename in the user_images table, display 
        // the user's image.
        if (isset($row["filename"]) && !empty($row["filename"])) {
            // If the user is an admin or author, display the user's image with a link to the admin profile page.
            if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") { ?>
            <a href="admin/profile.php">
                <div class="user-thumb sm"
                     style="cursor: pointer; background-image: url('admin/images/user_images/<?php echo $row["filename"]; ?>')"
                     alt="User Image">
                </div>
            </a>
            <?php
            // If the user is a member, display the user's image with an option to update the current image.
            } else { ?>
            <div class="user-thumb sm"
                 style="cursor: pointer; background-image: url('admin/images/user_images/<?php echo $row["filename"]; ?>')"
                 onclick="$('#userImageModal').modal()"
                 alt="User Image">
            </div>
            <?php 
            }
        // If the user has not uploaded an image, display the default user image.
        } else { 
            // If the user is an admin or author, display the default user image with a link to the admin profile page.
            if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") { ?>
            <a href="admin/profile.php">
                <div class="user-thumb sm"
                     style="cursor: pointer; background-image: url('admin/images/user_images/defaultuser.png')"
                     alt="User Image">
                </div>
            </a>
            <?php
            // If the user is a member, display the default user image with an option to add a new image.
            } else { ?>
                <div class="user-thumb sm"
                     style="cursor: pointer; background-image: url('admin/images/user_images/defaultuser.png')"
                     onclick="$('#userImageModal').modal()"
                     alt="User Image">
                </div>            
            <?php 
            } 
        } ?>
        <h5>
        <!-- Display the user's name under the image. -->
        <?php echo $_SESSION['firstname'] . " (" . $_SESSION['username'] . ") "; ?>            
        </h5>
    </div>
<?php endif; ?>

<!-- PAGE LINKS -->
<ul class="side-menu-content list-unstyled <?php if(isset($_SESSION["authenticated"])){echo 'userimage-adjust';} ?>"> 
    <li>
      <a href="<?php echo BASE_URL; ?>" <?php if(THIS_PAGE == "index.php"){echo "id='active'";} ?>>Home</a>
    </li>
    <li>
      <a href="categories" <?php if(THIS_PAGE == "categories.php"){echo "id='active'";} ?>>Categories</a>
    </li>
    <li>
      <a href="contact" <?php if(THIS_PAGE == "contact.php"){echo "id='active'";} ?>>Contact</a>
    </li>    
    <?php    
      // If logged in as an admin or an author, the Admin link is displayed.
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