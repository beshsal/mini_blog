<?php
// Create arrays for holding missing and required fields.
$missing   = array();
$required  = array("title", "post_content");
// Get the user_id of the current user.
// This will be assigned to $auth_uid, which will be inserted into the respective column in posts.
$currUname = $_SESSION["username"];
$currUid   = $conn->query("SELECT user_id from users WHERE username = '{$currUname}'"); confirmQuery($currUid);
$row       = $currUid->fetch_array();
$uid       = $row["user_id"];
// Create a full name for the current admin or author.
$name      = $conn->query("SELECT firstname, lastname from users WHERE user_id = '{$uid}'"); confirmQuery($name);
$row       = $name->fetch_array();
$fullName  = $row["firstname"] . " " . $row["lastname"];

if (isset($_POST["insert_post"])) { 
  $title        = trim($_POST["title"]);
  $auth_uid     = $uid;
  $post_auth    = $fullName; // $_POST["author"]
  $lead         = trim($_POST["lead"]);
  $post_content = trim($_POST["post_content"]);
  $post_status  = $_POST["post_status"];
    
  // Check if there are more than 2 categories (the limit). 
  // If there are, an error message is assigned that will be used to display the appropriate message.
  if (isset($_POST["category"])) {
    $totalCats = count($_POST["category"]);        
    if ($totalCats > 2) {
      $moreThan2 = "Categories error";
    }
  }
    
  // Initialize a flag for checking if the post was successfully inserted.
  $OK = false;    
  // Initialize the prepared statement.
  $stmt = $conn->stmt_init();
    
  // If either the title or the content are missing, add them to the $missing array so the appropriate error messages
  // will be displayed.
  if (empty($title) || empty($post_content)) {
      // For each element in the $_POST array, assign the key/name attribute to the variable $key and the value to the
      // variable $value.
      foreach ($_POST as $key => $value) {          
          // Assign the value to $temp.
          $temp = $value;
          // If $temp is empty and the key/name attribute is in the $required array, add $key to the $missing array.
          if (empty($temp) && in_array($key, $required)) {
            $missing[] = $key;
            // Create a variable with the name of the key/name attribute, and set its value to an empty string.
            ${$key} = "";              
          }
      }
  }
    
  // Insert the image into the images table and posts table.   
  // If the checkbox to upload a new image file is checked and there are no file errors (the image is successfully uploaded),
  // $_FILES holds the actual image (input type='file').
  if (isset($_POST["upload_new"]) && $_FILES["image"]["error"] == 0) {	    
    // Initialize a flag for checking if the image file passed validation and uploaded successfully.
    $imageOK = false;
    // Require the class for validating and uploading the image file. The class verifies that the image is the correct type, size, etc.
    require_once("includes/classes/UploadImg.php");        
    // Create an instance of the class, and pass it the path ($path) to the destination directory for uploaded images.
    $upload = new UploadImg("images/post_images/");        
    // Move the image to the destination directory.
    $upload->move();        
    // The contents of the $_filenames property (array) of the upload is stored in $filenames; $filenames will be an 
    // empty array if the upload fails.
    $filenames = $upload->getFilenames();        
    // If $filenames is not empty
    if ($filenames) {
      if (!($stmt->prepare("INSERT INTO images (filename, caption, artist, url) VALUES (?, ?, ?, ?)"))) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
      }            
      // Use the filename retrieved from the $_filenames property and image details submitted from the form.
      if (!$stmt->bind_param("ssss", $filenames[0], $_POST["caption"], $_POST["artist"], $_POST["url"])) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      }           
      // Execute the statement.
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      }	  
      $imageOK = $stmt->affected_rows; // $imageOK is reset to true
    }        
    // If $imageOk is true (the upload was successful), get the primary key of the inserted image. Otherwise, get the error messages.
    if ($imageOK) {            
      // Get the primary key of the uploaded image and save it to a $image_id (insert_id returns the ID of the last inserted record).
      // ($image_id is used to update the image_id field of the record in the users table with the primary key of the record that 
      // was just inserted in user_images.)
      $image_id = $stmt->insert_id; 
    // Otherwise store any error caught by the $upload object.           
    } else {
      // The getMessages method returns the $_messages array, which stores error messages. Its elements are separated 
      // by spaces and stored in $imageError.
      $imageError = implode(' ', $upload->getMessages());
    }
  // If an already uploaded image is selected from the select element     
  } elseif (isset($_POST["image_id"]) && !empty($_POST["image_id"])) {
    // Get the image_id of selected image stored in the option tag and assign it to $image_id.
    $image_id = $_POST["image_id"];
  // Otherwise set $image_id to empty.
  } else {
    $image_id = "";
  }

  // Note: $image_id will hold either the image_id of a new uploaded image OR the image_id
  // of an image that already exists.

  // If the image did not fail to upload
  if (!isset($imageError)) {        
    // If $image_id (image_id of a new or existing image file) has been set, insert it as a foreign key into the
    // posts table (posts.image_id column) along with the other post details submitted from the form.
    if (isset($image_id) && isset($title) && isset($post_content) && !empty($image_id) && !empty($title) && !empty($post_content) && !isset($moreThan2)) {
      if (!($stmt->prepare("INSERT INTO posts (image_id, title, auth_uid, post_auth, lead,
                           post_content, post_status, post_date)
                           VALUES(?, ?, ?, ?, ?, ?, ?, NOW())"))) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
       }          
      if (!$stmt->bind_param("isissss", $image_id, $title, $auth_uid, $post_auth, $lead, $post_content, $post_status)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
      }
      if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
      }
      // Reset $OK to true to indicate that the post was successfully inserted.
      $OK = $stmt->affected_rows;            
    }

    // If an image file was not uploaded, assign a variable that will be used to display the appropriate message.
    if (!isset($image_id) || empty($image_id)) {
      $missingImage = "No image uploaded";        
    }
    // If the $missing array has values, assign a variable that will be used to display the appropriate message.
    if ($missing) {
      $missingField = "Missing required field(s)";
    }
  }    
              
  // Insert categories.  
  // Now, ID values must be inserted into the cross reference table.
  // If $OK is true (the post entry was inserted successfully), check for categories.
  if ($OK && isset($_POST["category"])) {        
    // Get the new post entry's primary key.
    $post_id = $stmt->insert_id;            
    // Loop through the selected category value(s).
    foreach ($_POST["category"] as $cat_id) {          
      // If numeric as expected
      if (is_numeric($cat_id)) {              
        // Create an array containing values of the two primary keys, post_id and cat_id.
        $values[] = "({$post_id}, " . (int) $cat_id . ")";              
      }
    }        
    // If there are values for post_id and cat_id in the array, insert them in the cross-reference table.
    if ($values) {            
      $query = "INSERT INTO postxcat (post_id, cat_id)
               VALUES " . implode(',', $values); // its elements are separated into a comma separated string

      // Execute the query and get an error message if it fails.
      if (!$conn->query($query)) {
        $catError = $conn->error;
      }
    }
  }
    
  // Redirect to the view_posts page if successful, or create the appropriate error message.

  // If there are no errors, redirect to the view_posts page.
  if ($OK && !isset($imageError) && !isset($catError) && !isset($missingImage) && !isset($missingField) && !isset($moreThan2)) {
    header("Location: " . BASE_URL . "posts.php");
    exit;
  // if there are errors
  } else {
    // Assign any errors associated with executing the statement.
    $error = $stmt->error;
    
    // Assign any errors associated with uploading an image.
    if (isset($imageError)) {
      if (isset($missingImage) || isset($catError) || isset($missingField) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $imageError;
      } else {
        $error .= " " . $imageError;
      }            
    }        
    // Assign any errors associated with categories.
	  if (isset($catError)) {           
      if (isset($missingField) || isset($missingImage) || isset($imageError) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $catError;
      } else {
        $error .= " " . $catError;
      }
	  }      
    // Assign an error for a missing image.  
    if (isset($missingImage)) {
      if (isset($missingField) || isset($catError) || isset($imageError) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $missingImage;
      } else {
        $error .= " " . $missingImage;
      }
    }
    // Assign an error for a missing required field.
    if (isset($missingField)) {
      if (isset($missingImage) || isset($catError) || isset($imageError) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $missingField;
      } else {
        $error .= " " . $missingField;
      }
    }        
    // Assign an error for more than 2 categories.
    if (isset($moreThan2)) {
      if (isset($missingField) || isset($missingImage) || isset($catError) || isset($imageError)) {
        $error .= "<br>&#8226; " . $moreThan2;
      } else {
        $error .= " " . $moreThan2;
      }
    }
  }
}
?>
<form class="formwidth" action="" method="post" enctype="multipart/form-data">
  <p class="text-right"><a href="posts.php">View Posts</a></p>
  <?php 
  // If there are errors when the form is submitted, display them.
  if (isset($error)) {
    echo "<p class='error'>ERROR: {$error}</p>";
  }
  ?>
  <!-- If there are errors, the fields retain their data. -->
  <div class="form-group">
    <label for="title">Title:
    <!-- If the title is missing, inform the user. -->
    <?php if ($missing && in_array('title', $missing)) { ?>
      <span class="error">A title must be entered</span>
    <?php } ?>
    </label>
    <input name="title" type="text" class="form-control" value="<?php if (isset($error)) { 
      echo htmlentities($title, ENT_COMPAT, "utf-8");
	 } ?>">
  </div>
  <div class="form-group">
    <label for="categories">Select 1 to 2 categories:
    <!-- If there are more than 2 categories selected, inform the user; note each option tag has a cat_id value. -->
    <?php if (isset($moreThan2)) { ?>
      <span class="error">Select no more than 2 categories</span>
    <?php } ?>
    </label>
    <!-- The select element for categories -->
      <select name="category[]" size="" multiple class="">
        <?php
        // Get all categories to display them in the select element
        $getCategories = "SELECT * FROM categories ORDER BY category";
        $categories = $conn->query($getCategories);
        confirmQuery($categories);
        // Iterated through the result_set, and create option tags containing the cat_id and category name
        // of each record
        while ($row = $categories->fetch_assoc()) {
        ?>          
          <option value="<?php echo $row["cat_id"]; ?>" <?php
          // If there is an error or missing data, the category should remain selected. Check if a cat_id
          // resulting from query is in the $_POST["category"] array. If so, mark it as selected.
          if (isset($_POST["category"]) && in_array($row["cat_id"], $_POST["category"])) {
              echo "selected";
          } ?>><?php echo $row["category"]; ?></option>
        <?php } ?>          
      </select>
  </div>    
  <div class="row">
    <div class="form-group col-sm-6">
      <label for="image_id">Select image:
      <!-- If an image was not uploaded, inform the user. -->
      <?php if (isset($missingImage)) { ?>
        <span class="error">An image must be selected or uploaded</span>
      <?php } ?>
      </label>
      <!-- The select element that holds all the current post images; note each option tag has an image_id value. -->
      <select name="image_id" id="imageId" class="">
        <option value="" style="display: none;">Select image:</option>
      <?php
      // Get the list of images
      $get_images = "SELECT image_id, filename FROM images ORDER BY filename";
      $images = $conn->query($get_images);
      while ($row = $images->fetch_assoc()) {
      ?>
      <option value="<?php echo $row["image_id"]; ?>"
      <?php
      if (isset($_POST["image_id"]) && $row["image_id"] == $_POST["image_id"]) {
        echo "selected";
      }
      ?>><?php echo $row["filename"]; ?></option>
      <?php } ?>
      </select>
    </div>
    <div class="col-sm-6" style="postion: relative;">
      <!-- Show the currently selected image. -->
      <img id="post-img-holder">
    </div>
  </div>
  <!-- Checkbox for uploading a new image if the user doesn't want to select one that already exists -->
  <div class="form-group">
    <label for="upload new" class="custom-control custom-checkbox">
      <input name="upload_new" type="checkbox" class="custom-control-input" id="uploadNew">
      <span class="custom-control-indicator"></span>
      <span class="custom-control-description">Upload new image</span>
    </label>
  </div>
  <!-- If the checkbox is selected, display the fields for uploading the image data. -->
  <!-- Upload an image file. -->
  <div class="form-group uploadOption">
    <label for="image">Select image:</label>
    <input type="file" name="image" class="filestyle" data-buttonName="btn gray-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md" id="image">
  </div>
  <!-- Add an optional caption. -->
  <div class="form-group uploadOption">
    <label for="caption">Caption:</label>
    <input name="caption" type="text" class="form-control" id="caption">
  </div>  
  <!-- Add an option artist name. -->
  <div class="form-group uploadOption">
    <label for="artist">Artist:</label>
    <input name="artist" type="text" class="form-control" id="artist">
  </div>
  <!-- Add an optional link to the image or artist's page. -->
  <div class="form-group uploadOption">
    <label for="url">URL:</label>
    <input name="url" type="text" class="form-control" id="url">
  </div>
  <!-- The select element for the post status 
  If Published is selected, the post will be immediately displayed on the home site. -->
  <div class="form-group">
    <label for="status">Status:</label>
    <select name="post_status" id="" class="">
      <option value="draft">Post Status</option>
      <option value="published" <?php 
      if (isset($post_status) && $post_status == "published") { echo "selected"; } 
      ?>>Published</option>
      <option value="draft" <?php 
      if (isset($post_status) && $post_status == "draft") { echo "selected"; } 
      ?>>Draft</option>
    </select>
  </div>    
 <div class="form-group">
    <label for="lead">Lead:</label>
    <textarea name="lead" class="form-control" id="lead" cols="30" rows="3"><?php if (isset($error)) { echo htmlentities($lead, ENT_COMPAT, "utf-8"); } ?></textarea>
 </div>
  <div class="form-group">
    <label for="post_content">Content:
    <!-- If the content is missing, inform the user. -->
    <?php if ($missing && in_array("post_content", $missing)) { ?>
      <span class="error">Post content must be entered</span>
    <?php } ?>
    </label>
    <textarea name="post_content" class="form-control" id="content" cols="30" rows="10"><?php if (isset($error)) {
	  echo htmlentities($post_content, ENT_COMPAT, 'utf-8'); } ?></textarea>
  </div>
  <div class="form-group">
    <input name="insert_post" class="btn standard-btn right" type="submit" value="Publish">
  </div>
</form>