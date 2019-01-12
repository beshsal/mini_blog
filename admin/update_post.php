<?php
// Check if a "postid" parameter (the post_id of the post to be updated) is received from the URL query string AND its 
// value is numeric. If it is, store its value in $postId; otherwise, redirect the user back to the posts page.
if (isset($_GET["postid"]) && !$_POST) {
  if (!is_numeric($_GET["postid"])) {
    header("Location: " . BASE_URL . "posts.php");
    exit;
  } else {
    $postId = (int) $_GET["postid"];
  }
}

// if (!isset($_GET["postid"])) {
//   header("Location: " . BASE_URL . "posts.php");
//   exit;
// }

// If the user is an author, the user_id will be sent in the $_GET request in addition to the post_id. This will be 
// used to ensure the author can edit only his or her posts.
// Check if a "uid" parameter (the author's user_id) is received from the URL query string AND its value is numeric.
// If it is, store its value in $userId; otherwise, redirect the user back authuser_posts page.
if (isset($_GET["uid"])) {
  if (!is_numeric($_GET["uid"])) {
    header("Location: " . BASE_URL . "authuser_posts.php");
    exit;
  } else {
    $userId = (int) $_GET["uid"];
  }
}

// Create arrays for holding missing and required fields.
$missing  = array();
$required = array("title", "post_content");

// Initialize the prepared statement.
$stmt = $conn->stmt_init();

// Get the current post's details. The form's fields will be automatically populated with this data.
$query = "SELECT posts.post_id, posts.image_id, posts.title, posts.lead, posts.post_content,
         posts.post_status, images.caption, images.artist, images.url FROM posts
         JOIN images ON (posts.image_id = images.image_id)
         WHERE posts.post_id = ?";

$stmt->prepare($query);
// Bind the post_id to the query parameter.
$stmt->bind_param("i", $postId);
// Bind the results to variables (these are used to persist data to the form).
$stmt->bind_result($post_id, $image_id, $title, $lead, $post_content, $post_status, $caption, $artist, $url);
// Execute the query, and fetch the result.
$OK = $stmt->execute(); // $OK will be true if a record is retrieved after executing the statement.
// Fetch the result(s) of the executed statement.
$stmt->fetch();
// Free the database resource for the next query.
$stmt->free_result();
// Get the categories associated with the post.
$getCategories = "SELECT cat_id FROM postxcat WHERE post_id = ?";
$stmt->prepare($getCategories);
$stmt->bind_param("i", $postId);
$stmt->bind_result($cat_id); // bind the selected result to $cat_id
$OK = $stmt->execute();

// Create an array for storing the cat_ids of the identified post's categories.
$selectedCats = array();

while ($stmt->fetch()) {
  $selectedCats[] = $cat_id;
}

// Check if the user tries to alter the url query string.
if ($post_id != $postId) {
  header("Location: " . BASE_URL . "posts.php");
  exit;
}

// If the form is submitted
if (isset($_POST["update_post"])) {    
  // Initialize flags.
  $OK   = false; // boolean used to check the success of retrieving the record
  $done = false; // boolean used to check the success of the update
  
  // Check if there are more than 2 categories (the limit). 
  // If there are, an error message is assigned that will be used to display the appropriate message.
  if (isset($_POST["category"])) {
    $totalCats = count($_POST["category"]);        
    if ($totalCats > 2) {
      $moreThan2 = "Categories error";
    }
  }    
  // If either the title or the content are missing, add them to the $missing array so the appropriate error messages
  // will be displayed.
  if (empty($_POST["title"]) || empty($_POST["post_content"])) {
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
        
  // If a new image is uploaded, it must be inserted into the images table, and its primary key must be retrieved,
  // so it can be used to update the respective record in the posts table.
  if (isset($_POST["upload_new"]) && $_FILES["image"]["error"] == 0) {	    
    // Initialize a flag for checking if the image was uploaded successfully.
    $imageOK = false;        
    // Require the class that verifies that the image is the correct type, size, etc.
    require("includes/classes/UploadImg.php");        
    // Create an instance of the class, and pass it the path ($path) to the destination directory for uploaded images.
    $upload = new UploadImg("images/post_images/");        
    // Move the image to the destination directory.
    $upload->move();        
    // The contents of the $_filenames property (an array) of the upload is stored in $filenames; $filenames will be an
    // empty array if the upload fails.
    $filenames = $upload->getFilenames();
      
    // If $filenames has a value
    if ($filenames) {
      $query = "INSERT INTO images (filename, caption, artist, url) VALUES (?, ?, ?, ?)";            
      // Prepare the statement.   
      $stmt->prepare($query);            
      // Use the filename retrieved from the $_filenames property and image details submitted from the form.   
      $stmt->bind_param("ssss", $filenames[0], $_POST["caption"], $_POST["artist"], $_POST["url"]);           
      // Execute the statement.
      $stmt->execute(); // execute the statement to insert the record into images	  
      $imageOK = $stmt->affected_rows; // $imageOK is reset to true
    }        
    // If $imageOk has a value (the image details were stored in the images table)
    if ($imageOK) {            
      // The insert_id property stores the primary key of the uploaded image in $image_id.
      $image_id = $stmt->insert_id;
    // Otherwise, get error messages.          
    } else {
      // The getMessages method returns the $_messages array, which stores error messages. Its elements are separated 
      // by spaces and stored in $imageError.
      $imageError = implode(" ", $upload->getMessages());          
    }
  // If an already uploaded image is selected from the select element
  } elseif (isset($_POST["image_id"]) && !empty($_POST["image_id"])) {        
    // Get the image_id of selected image stored in the option tag and assign it to $image_id.
    $image_id = $_POST["image_id"];
  } else {
    $image_id = "";
  }    
  
  // Note: $image_id will hold either the image_id of a new uploaded image OR the image_id
  // of an image that already exists.

  // If the image did not fail to upload
  if (!isset($imageError)) {        
    // If $image_id is set, update the record in the posts table.
    if (isset($image_id) && isset($_POST["title"]) && isset($_POST["post_content"]) && !empty($image_id) && !empty($_POST["title"]) && !empty($_POST["post_content"]) && !isset($moreThan2)) {
        $updatePost = "UPDATE posts SET image_id = ?, title = ?, updated = NOW(),
                      lead = ?, post_content = ?, post_status = ?
                      WHERE post_id = ?"; 
       
      $stmt->prepare($updatePost);
      // Bind $_POST values to parameters
      $stmt->bind_param("issssi", $image_id, $_POST["title"], $_POST["lead"],
      $_POST["post_content"], $_POST["post_status"], $_POST["post_id"]); // a hidden field holds the value of $post_id            
      $done = $stmt->execute(); // $done will be true or false depending on whether the statement is successfully executed            
    }    

    // If an image was not uploaded, assign a variable that will be used to display the appropriate message.    
    if (!isset($image_id) || empty($image_id)) {
      $missingImage = "No image uploaded";            
    }
    // If the $missing array has values, assign a variable that will be used to display the appropriate message.
    if ($missing) {
      $missingField = "Missing required field(s)";
    }
  }
    
  if (!isset($moreThan2)) {
    // The category records are not updated. They are deleted in the cross-reference table, and new ones are inserted that 
    // correspond to the categories table.
    // Delete existing records in the postxcat cross-reference table.
    $delete_pxc_recs = "DELETE FROM postxcat WHERE post_id = ?";
    $stmt->prepare($delete_pxc_recs);
    // Bind the post_id value submitted in $_POST to the query parameter.
    $stmt->bind_param("i", $_POST["post_id"]);
    $stmt->execute(); // effectively delete the post_id from the table.
    // Insert the new records into the postxcat table.
    if (isset($_POST["category"]) && is_numeric($_POST["post_id"])) {
      $post_id = (int) $_POST["post_id"];
      foreach ($_POST["category"] as $cat_id) {
        $values[] = "({$post_id}, " . (int) $cat_id . ")";
      }
      if ($values) {
        $insert_pxc_recs = "INSERT INTO postxcat (post_id, cat_id) VALUES " . implode(",", $values);
        if (!$conn->query($insert_pxc_recs)) {
            $catError = $conn->error;
        }   
      }
    }
  }

  // Redirect if the updated is completed with no errors OR the $_GET["postid"] is not defined.
  if ($done && !isset($imageError) && !isset($catError) && !isset($missingImage) && !isset($missingField) && !isset($moreThan2)) {
    if (isset($userId)) {
      if (isset($_GET["editpost"])) {
        $editpostId = $_GET["editpost"];
        header("Location: " . BASE_URL . "../post.php?postid={$editpostId}");
      } else {
        header("Location: " . BASE_URL . "authuser_posts.php");
      }
    } else {
      if (isset($_GET["editpost"])) {
        $editpostId = $_GET["editpost"];
        header("Location: " . BASE_URL . "../post.php?postid={$editpostId}");
      } else {
        header("Location: " . BASE_URL . "posts.php");
      }
    }
    exit;
  // Else store the appropriate error message if the query fails.
  } else {
    // Assign any errors associated with executing the statement.
    $error = $stmt->error;
    
    // Assign any errors associated with uploading an image.
    if (isset($imageError)) {
      if (isset($missingImage) || isset($catError) || isset($missingField) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $imageError;
      } else {
        $error .= ' ' . $imageError;
      }            
    }
    // Assign any errors associated with categories.
    if (isset($catError)) {           
      if (isset($missingField) || isset($missingImage) || isset($imageError) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $catError;
      } else {
        $error .= ' ' . $catError;
      }
    }
    // Assign an error for a missing image.  
    if (isset($missingImage)) {
      if (isset($missingField) || isset($catError) || isset($imageError) || isset($moreThan2)) {
        $error .= "<br>&#8226; " . $missingImage;
      } else {
        $error .= ' ' . $missingImage;
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
  if (isset($error)) { echo "<p class='error'>ERROR: {$error}</p>"; } ?>
  <div class="form-group">
    <label for="title">Title:
    <?php if ($missing && in_array("title", $missing)) { ?>
      <span class="error">A title must be entered</span>
    <?php } ?>
    </label>
    <input name="title" value="<?php if (isset($error)){echo htmlentities($_POST['title'], ENT_COMPAT, 'utf-8');}else{echo htmlentities($title, ENT_COMPAT, 'utf-8');} ?>" type="text" class="form-control">
  </div>
  <div class="form-group">
    <label for="categories">Select 1 to 2 categories:
    <?php if (isset($moreThan2)) { ?>
        <span class="error">Select no more than 2 categories</span>
    <?php } ?>
    </label>
    <select name="category[]" size="5" multiple id="category">
    <?php
    // Display all categories in the select element.
    $displayCats = "SELECT cat_id, category FROM categories ORDER BY category";
    $categories = $conn->query($displayCats);
    // If there are no missing fields or errors, check if a cat_id resulting from the query
    // is in the array of cat_ids selected for the post. If so, mark the option tag as "selected".
    if (empty($missing) && !isset($error)) {
        // echo "<option value=''>Select Image:</option>";                
        while ($row = $categories->fetch_assoc()) { ?>
          <option value="<?php echo $row['cat_id']; ?>" 
          <?php if (in_array($row["cat_id"], $selectedCats)) {
            echo "selected"; } ?>><?php echo $row["category"]; ?></option>
        <?php } 
    // Otherwise, if there are missing fields or errors, check if a cat_id resulting from the query
    // is in the $_POST["category"] array. If so, mark the option tag as "selected".
    } else {
        while ($row = $categories->fetch_assoc()) { ?>
        <option value="<?php echo $row['cat_id']; ?>" <?php if (in_array($row["cat_id"], $_POST["category"])) 
        {echo "selected";} ?>><?php echo $row["category"]; ?>
        </option>
        <?php }
    } ?>
    </select>
  </div>
  <div class="row">
    <div class="form-group col-sm-6">
      <label for="image_id">Select image:
      <?php if (isset($missingImage)) { ?>
        <span class="error">An image must be selected or uploaded</span>
      <?php } ?>
      </label>
      <select name="image_id" id="image_id" class="">
        <option value="" style="display: none;">Select image</option>
        <?php
        // Get the list of images
        $getImages = "SELECT image_id, filename FROM images ORDER BY filename";
        $images = $conn->query($getImages);
        while ($row = $images->fetch_assoc()) {
        ?>
        <option value="<?php echo $row['image_id']; ?>"
        <?php
        if ($row["image_id"] == $image_id) {
          echo "selected";
        }
        ?>><?php echo $row["filename"]; ?></option>
        <?php } ?>
      </select>
    </div>      
    <div class="col-sm-6" style="postion: relative;">
      <!-- Show the currently selected image -->
      <img id="post-img-holder">
    </div>
  </div>    
  <div class="form-group">
    <label for="upload new" class="custom-control custom-checkbox">
      <input name="upload_new" type="checkbox" class="custom-control-input" id="uploadNew">
      <span class="custom-control-indicator"></span>
      <span class="custom-control-description">Upload new image</span>
    </label>
  </div>
  <div class="form-group uploadOption">
    <label for="image">Select image:</label>
    <input name="image" type="file" id="image">
  </div>    
  <div class="form-group uploadOption">
    <label for="caption">Caption:</label>
    <input name="caption" type="text" class="form-control" id="caption" value="<?php // echo htmlentities($caption, ENT_COMPAT, 'utf-8'); ?>">
  </div>    
  <div class="form-group uploadOption">
    <label for="artist">Artist:</label>
    <input name="artist" type="text" class="form-control" id="artist" value="<?php // echo htmlentities($artist, ENT_COMPAT, 'utf-8'); ?>">
  </div>    
  <div class="form-group uploadOption">
    <label for="url">URL:</label>
    <input name="url" type="text" class="form-control" id="url" value="<?php // echo htmlentities($url, ENT_COMPAT, 'utf-8'); ?>">
  </div>    
  <div class="form-group">
    <label for="status">Status:</label>
    <select name="post_status" id="">
      <option value="draft">Post Status</option>
      <option value="published" <?php
      // If the status retrieved from either the query or $_POST["post_status"] is "published", mark the option tag as "selected".
      if (isset($post_status) && $post_status == "published" || 
          isset($_POST["post_status"]) && $_POST["post_status"] == "published") 
      { echo "selected"; } 
      ?>>Published</option>
      <option value="draft" <?php
      // If the status retrieved from either the query or $_POST["post_status"] is "draft", mark the option tag as "selected". 
      if (isset($post_status) && $post_status == "draft" ||
          isset($_POST["post_status"]) && $_POST["post_status"] == "draft") 
      { echo "selected"; } 
      ?>>Draft</option>
    </select>
  </div>
  <div class="form-group">
    <label for="lead">Lead:</label>
    <textarea name="lead" class="form-control" id="lead" cols="30" rows="3"><?php if(isset($error)){echo htmlentities($_POST["lead"], ENT_COMPAT, "utf-8");}else{echo htmlentities($lead, ENT_COMPAT, "utf-8");} ?></textarea>
  </div>
  <div class="form-group">
    <label for="post_content">Content:<?php if ($missing && in_array("post_content", $missing)) { ?> 
      <span class="error">Post content must be entered</span><?php } ?>
    </label>
    <textarea name="post_content" class="form-control" id="content" cols="30" rows="10"><?php if(isset($error)){echo htmlentities($_POST["post_content"], ENT_COMPAT, "utf-8");}else{echo htmlentities($post_content, ENT_COMPAT, "utf-8");} ?></textarea>
  </div>
  <div class="form-group">
    <input name="update_post" class="btn standard-btn right" type="submit" value="Update Post">
  </div>
  <input name="post_id" type="hidden" value="<?php if(isset($post_id) && $post_id == $postId){echo $post_id;}else{echo $_GET['postid'];} ?>"> 
</form>