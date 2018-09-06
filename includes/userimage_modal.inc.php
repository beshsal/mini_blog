<?php
if (isset($_SESSION["username"])) {
   $currUname   = $_SESSION["username"];
   $userDetails = $conn->query("SELECT * FROM users WHERE username = '{$currUname}'");
   confirmQuery($userDetails);
   while ($row = $userDetails->fetch_assoc()) {
       $user_id = $row["user_id"];
       $img_id  = $row["image_id"];
       $role    = $row["role"];
   }
}

if (isset($_POST["upload_img"])) {
    // Initialize prepared statement
    $stmt = $conn->stmt_init();
    // If an image is already uploaded for the member, the current image must be deleted
    if (isset($img_id) && !empty($img_id) && $img_id != 0) {
        $deleteQuery = "DELETE FROM user_images WHERE image_id = {$img_id}"; 
    } else {
        $deleteQuery = "DELETE FROM user_images WHERE user_id = {$user_id}";
    }

    $conn->query($deleteQuery);
    confirmQuery($deleteQuery);
    
    require_once("admin/includes/classes/UploadImg.php");
    
    $imageOK = false;
    $upload  = new UploadImg("admin/images/user_images/");
    // Move the image to the destination directory
    // true sets the optional parameter $overwrite to true, so the filename will be overwritten instead of renamed
    $upload->move(true);
    $filenames = $upload->getFilenames();
    
    if ($filenames) {
       if (!($stmt->prepare("INSERT INTO user_images (user_id, filename, role) VALUES (?, ?, ?)"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
       }            
       // Use the filename retrieved from $_filenames property and image details submitted from the form
       if (!$stmt->bind_param("iss", $user_id, $filenames[0], $role)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
       }           
       // Execute the statement
       if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
       }        
       $imageOK = $stmt->affected_rows; // $imageOK is reset to true (1 treated as true)
    }
    // If $imageOk is true
    if ($imageOK) {
       // The insert_id property stores the primary key of the uploaded image in $image_id
       $image_id = $stmt->insert_id;
    } else {
       $imageError = implode(" ", $upload->getMessages()); // its elements are separated by spaces
    }
    // If there are no errors
    if (!isset($imageError)) {        
        $stmt->prepare("UPDATE users SET image_id = ? WHERE user_id = ?") or die($conn->error);        
        // Bind the values from the form to the parameters
        $stmt->bind_param("ii", $image_id, $user_id);
        // Execute the statement
        $stmt->execute();
        
        if (THIS_PAGE == "index.php") {
            header("Location: " . BASE_URL); 
        } elseif (THIS_PAGE == "post.php" || THIS_PAGE == "category.php" || THIS_PAGE == "author_posts.php") {
            // header("Location: " . $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"]);
            redirectToParams();
        } else {
            header("Location: " . BASE_URL . basename(THIS_PAGE, ".php"));
        }
        die;
    } else {
        $imgError = "<p class='error text-center userImgErr'>Please choose an image to upload.</p>";
    }
}
?>

<div id="overlay"></div> <!-- #overlay is a single div placed above all elements -->
<div id="userImageModal" class="modal fade" role="dialog">
  <div id="modal-userImage" class="modal-dialog">
    <!-- Modal content -->
    <div class="modal-content">
      <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">User Image</h4>
      </div>
      <div class="modal-body">
        <form action="" method="post" enctype="multipart/form-data" id="userImageForm">
            <?php if(isset($imgError)){echo $imgError;} ?>
            <div class="form-group">
              <input type="file" name="image" class="filestyle" data-buttonName="btn neutral-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
            </div>
            <input name="upload_img" type="submit" class="btn standard-btn" value="Upload Image">
        </form>
      </div>
    </div>
  </div>
</div>