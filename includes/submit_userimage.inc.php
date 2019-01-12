<?php
require_once("db.inc.php");
include("util_funcs.inc.php");
session_start();

$imgErrors = array();
$success = "";

// Get the user's current image data.
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

// Check if the file was received from the AJAX POST request, and get the filename.
if (!empty($_FILES['file']['name'])) {
    $filename = $_FILES['file']['name'];

    // Initialize the prepared statement.
    $stmt = $conn->stmt_init();
    // Set a flag to determine if a record for the new image is inserted.
    $imageOK = false;
    
    if ($filename) {
        // If an image is already uploaded and saved for the member, the current record in the user_images table  must be deleted.
        if (isset($img_id) && !empty($img_id) && $img_id != 0) {
            $deleteUserImg = "DELETE FROM user_images WHERE image_id = {$img_id}"; 
        } else {
            $deleteUserImg = "DELETE FROM user_images WHERE user_id = {$user_id}";
        }
        $conn->query($deleteUserImg);
        confirmQuery($deleteUserImg);

        // Prepare the statement for inserting a new record in the user_images table.
        if (!($stmt->prepare("INSERT INTO user_images (user_id, filename, role) VALUES (?, ?, ?)"))) {
             echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }            
        // Bind the image data to parameters.
        if (!$stmt->bind_param("iss", $user_id, $filename, $role)) {
             echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }           
        // Execute the statement.
        if (!$stmt->execute()) {
             echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }        
        $imageOK = $stmt->affected_rows; // $imageOK is reset to true (1 treated as true)
    }
    // If $imageOk is true, store the ID of the uploaded image in $image_id.
    if ($imageOK) {       
      $image_id = $stmt->insert_id;
      
      // Move the file to the specified directory, and set a response indicating the upload was successful.
      if (move_uploaded_file($_FILES['file']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/mini_blog/admin/images/user_images/{$filename}")) {
          $success = "The image {$filename} has been successfully uploaded.";
      } else {
          echo "The file could not be uploaded to the specified directory.";
      }
    } else {
       echo "There was a problem uploading the file.";
    }
} else {
    echo "Something went wrong with uploading the image. Please try again later.";
}   

// Check if there are errors inserting/uploading the new image. If not, update the users table.
if ($stmt->error) {
    echo $stmt->error;
} else {
    $stmt->prepare("UPDATE users SET image_id = ? WHERE user_id = ?") or die($conn->error);
    $stmt->bind_param("ii", $image_id, $user_id);
    $stmt->execute();
    
    if ($role == "admin" || $role == "author") {
        $stmt->prepare("UPDATE auth_profile SET image_id = ? WHERE user_id = ?") or die($conn->error);
        $stmt->bind_param("ii", $image_id, $user_id);
        $stmt->execute();
    }
    
    // If the record for the member in the users table is successfully updated, send the response 
    // indicating the upload was successful.
    echo $success;
}
?>