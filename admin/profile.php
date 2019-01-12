<?php
include "includes/admin_header.inc.php";

// If there is a record in the auth_profile table for the user, get the user's data from the auth_profile.
$currUname      = $_SESSION["username"];
$currRole       = $_SESSION["role"];
$getProfileData = "SELECT * FROM auth_profile WHERE username = '{$currUname}'";
$profileData    = $conn->query($getProfileData);
confirmQuery($profileData);

if ($profileData->num_rows != 0) {
    while ($row = $profileData->fetch_assoc()) {
        $auth_id     = $row["auth_id"];
        $user_id     = $row["user_id"];
        $username    = $row["username"];
        $firstname   = $row["firstname"];
        $lastname    = $row["lastname"];
        $full_name   = $firstname . " " . $lastname;
        $email       = $row["email"];
        $bio         = $row["bio"];
        $total_posts = countRecords('posts', '', '', '', $user_id);       
        $uimg_id     = $row["image_id"];
    }
// Otherwise, set default values for fields in the auth_profile, and get the user's details from the users table. The default values
// will be used to determine if a record must be inserted or updated in auth_profile table (see update_profile.php).
} else {
    $auth_id     = 0;   
    $bio         = "";
    $total_posts = 0;    
    $userData    = $conn->query("SELECT * FROM users WHERE username = '{$currUname}'");
    
    while ($row = $userData->fetch_assoc()) {
        $user_id     = $row["user_id"];
        $username    = $row["username"];
        $firstname   = $row["firstname"];
        $lastname    = $row["lastname"];
        $full_name   = $firstname . " " . $lastname;
        $email       = $row["email"];
        $bio         = "";
        $total_posts = countRecords('posts', '', '', '', $user_id);       
        $uimg_id     = $row["image_id"];
    }
}
?>
<div id="wrapper">    
    <?php
    include "includes/admin_nav.inc.php";
    include "includes/page_header.inc.php";
    ?> 
    <div id="page-wrapper">
        <div class="container-fluid">            
            <div class="row">
                <div class="col-lg-12">
                    <div id="profile">
                        <div class="profile-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <?php
                                    // Get the filename of the user's image, and use it to display the image. If there is no image,
                                    // display the default user image.
                                    $getFilename = "SELECT filename FROM user_images WHERE image_id = {$uimg_id}";
                                    $result      = $conn->query($getFilename); confirmQuery($result);
                                    $row         = $result->fetch_array();
                                    $filename    = $row["filename"];
                                    
                                    echo "<a href='update_profile.php?aid={$auth_id}&uid={$user_id}&img={$uimg_id}' alt='Profile Image'>";
                                    if (isset($filename) && !empty($filename)) { ?>
<!--                               
                                        <img src='images/user_images/{$filename}' class='img-responsive img-circle' alt='Profile Image' height='180' width='180'>
-->
                                        <div class="user-thumb lg"
                                             style="background-image: url('images/user_images/<?php echo $filename; ?>')">
                                        </div>
                                    <?php 
                                    } else { ?>
<!--                           
                                        <img src='images/user_images/defaultuser.png' class='img-responsive img-circle' alt='Profile Image' height='180' width='180'>
-->
                                        <div class="user-thumb lg"
                                             style="background-image: url('images/user_images/defaultuser.png')">
                                        </div>
                                    <?php
                                    }
                                    echo "</a>";
                                    ?>                                     
                                </div>
                                <div class="col-md-6">
                                  <!-- Display the user's profile info in a table - role, name, email, username, and number of posts -->
                                  <table class="">
                                    <tbody>
                                      <tr>
                                        <th>Role:</th>
                                        <td>
                                        <?php 
                                        if (isset($role)) {
                                            echo ucwords($role);
                                        } else {
                                            echo ucwords($_SESSION["role"]); 
                                        }
                                        ?> 
                                        </td>
                                      </tr>
                                      <tr>
                                        <th>Firstname:</th>
                                        <td>
                                        <?php 
                                        if (isset($firstname)) {
                                            echo $firstname;
                                        } else {
                                            echo $_SESSION["firstname"]; 
                                        }
                                        ?>
                                        </td>
                                      </tr>
                                      <tr>
                                        <th>Lastname:</th>
                                        <td>
                                        <?php 
                                        if (isset($lastname)) {
                                            echo $lastname;
                                        } else {
                                            echo $_SESSION["lastname"]; 
                                        }
                                        ?>
                                        </td>
                                      </tr>
                                      <tr>
                                       <th>Email:</th>
                                        <td>
                                        <?php 
                                        if (isset($email)) {
                                            echo $email;
                                        } else {
                                            echo $_SESSION["email"]; 
                                        }
                                        ?>
                                        </td>
                                      </tr>
                                      <tr>
                                        <th>
                                        Username:
                                        </th>
                                        <td>
                                         <?php
                                         if (isset($username)) {
                                            echo $username;
                                         } else {
                                            echo $_SESSION["username"];
                                         }
                                         ?>
                                        </td>
                                      </tr>    

                                      <tr>
                                       <th>Total Posts:</th>
                                        <td><?php echo $total_posts; ?></td>
                                      </tr>                                                
                                    </tbody>
                                  </table> 
                                </div>
                                <div class="col-xs-12">
                                    <h4><?php echo $firstname; ?>'s Privileges</h4>
                                    <p>Add posts. View and edit your own posts. View and edit your profile bio and image.</p>
                                    
                                    <h4><?php echo $firstname; ?>'s Bio</h4>
                                    <p><small><strong>(This will be displayed on your post pages.)</strong></small></p>
                                <?php
                                // If the user has added a bio, display it; otherwise request that the user adds a bio before
                                // publishing posts.
                                if (isset($bio) && !empty($bio)) {
                                    echo "<p class='bio'>" . $bio . "</p>";
                                } else {
                                    echo "<p class='text-center' style='padding: 20px 0 30px 0;'>
                                         Please add a bio before publishing a post. <br>
                                         Click the <strong>UPDATE PROFILE</strong> button below.
                                         </p>";
                                }
                                ?>
                                </div>
                                <div class="col-xs-12 text-center">
                                    <ul class="list-inline">
                                    <?php
                                    // Link to the update_profile page
                                    echo "<li>
                                         <a href='update_profile.php?aid={$auth_id}&uid={$user_id}&img={$uimg_id}&fname={$filename}' class='btn standard-btn'>
                                         UPDATE PROFILE</a>
                                         </li>";
                                    ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div> 
                </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->

<?php include "includes/admin_footer.inc.php"; ?>