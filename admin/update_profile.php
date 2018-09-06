<?php
include "includes/admin_header.inc.php";

$currRole  = $_SESSION["role"];
$currUname = $_SESSION["username"];

// Initialize prepared statement
$stmt = $conn->stmt_init();
if (isset($_GET["uid"]) && is_numeric($_GET["uid"])) {
        $uid = (int) $_GET["uid"];
} else {
    header("Location: " . BASE_URL . "profile.php");
    exit;
}

if (isset($_GET["img"]) && is_numeric($_GET["img"])) {
        $imgId = (int) $_GET["img"];
} else {
    header("Location: " . BASE_URL . "profile.php");
    exit;
}

if (isset($_GET["aid"]) && is_numeric($_GET["aid"])) {
        $aid = (int) $_GET["aid"];
} else {
    header("Location: " . BASE_URL . "profile.php");
    exit;
}

if (isset($aid) && !empty($aid) && $aid != 0) {
    // Get the current profile data to persist it to the fields
    // It might be better to use $uid since both auth_profile and admin_profile have this field
    $query = "SELECT * FROM auth_profile WHERE auth_id = {$aid}";
    $result = $conn->query($query);
    confirmQuery($result);    
    while ($row = $result->fetch_assoc()) {
        $auth_id   = $row["auth_id"];
        $user_id   = $row["user_id"];
        $bio       = $row["bio"]; // this is empty the very first time (condition made for that below)
        $firstname = $row["firstname"];   
    }
} else {
    $result = $conn->query("SELECT * FROM users WHERE user_id = {$uid}");
    confirmQuery($result);    
    while ($row = $result->fetch_assoc()) {
        $user_id   = $row["user_id"];
        $firstname = $row["firstname"];
        $lastname  = $row["lastname"];
        $fullname  = $firstname . " " . $lastname;
        $username  = $row["username"];
        $role      = $row["role"];
        $email     = $row["email"];
    } 
}

// If the form is submitted
if (isset($_POST["update_profile"])) {    
    if (!isset($aid) || empty($aid) || $aid == 0) {
        // Consider inserting 1 into auth_id so that the user_id and auth_id are both 1 when the first admin's profile is
        // automatically added
        $query = "INSERT INTO auth_profile (user_id, username, firstname, lastname, fullname, email, role) VALUES (?, ?, ?, ?, ?,?, ?)";
        
        if (!($stmt->prepare($query))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("issssss", $user_id, $username, $firstname, $lastname, $fullname, $email, $role)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
    }    
    if (isset($imgId) && !empty($imgId) && $imgId != 0) {
        $deleteQuery = "DELETE FROM user_images WHERE image_id = {$imgId}"; 
    } else {
        $deleteQuery = "DELETE FROM user_images WHERE user_id = {$user_id}";
    }
    $conn->query($deleteQuery);    
    $newBio = trim($_POST["bio"]);    
    $imageOK = false;
    // Require the class that verifies that the image is the correct type, size, etc.
    require_once("includes/classes/UploadImg.php");
    // Create an instance of the class, and pass it the path ($path) to the destination directory for uploaded images
     $upload = new UploadImg("images/user_images/");
    // Move the image to the destination directory
     $upload->move(true); // added true to set the optional parameter $overwrite to true; the filename will be overwritten instead of renamed
    // The contents of $_filenames property (an array) of the upload is stored in $filenames
    // $filenames will be an empty array if the upload fails
    $filenames = $upload->getFilenames();
    
    // print_r($filenames);

    // If $filenames array has a value
    if ($filenames) {
       $query = "INSERT INTO user_images (user_id, filename, role) VALUES (?, ?, ?)";        
       // Prepare the statement    
       $stmt->prepare($query) or die($conn->error);        
       // Use the filename retrieved from $_filenames property and the role of the current user submitted from the form
       $stmt->bind_param("iss", $user_id, $filenames[0], $currRole);        
       // Execute the statement
       $stmt->execute();
       $imageOK = $stmt->affected_rows; // $imageOK is reset to true (1 treated as true)
    }
    // Get the image's primary key
    // If $imageOk has a value (the image details were stored in the images table)
    if ($imageOK) {
       // The insert_id property stores the primary key of the uploaded image in $image_id (there is now an image_id for the new file)
       // ("Get the ID generated from the previous INSERT operation")
       $image_id = $stmt->insert_id;
    } else {
       $imageError = implode(" ", $upload->getMessages()); // its elements separated by spaces
    }    
    if (!isset($imageError)) {        
        $stmt->prepare("UPDATE users SET image_id = ? WHERE user_id = ?") or die($conn->error);        
        // Bind the values from the form to the parameters
        $stmt->bind_param("ii", $image_id, $user_id);
        // Execute the statement
        $stmt->execute();        
        $stmt->prepare("UPDATE auth_profile SET image_id = ?, bio = ? WHERE user_id = ?") or die($conn->error);        
        // Bind the values from the form to the parameters
        $stmt->bind_param("isi", $image_id, $newBio, $user_id);
        // Execute the statement
        $stmt->execute();
        header("Location: " . BASE_URL . "profile.php");
        exit;
    } else {
        $imgError = "<p class='error'>Please upload an image.</p>";
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
                    <form class="formwidth" action="" method="post" enctype="multipart/form-data">
                        <?php if(isset($imgError)){echo $imgError;} ?>
                        <div class="form-group">
                            <label for="user-image">Select image:</label>
                            <input type="file" name="image" class="filestyle" data-buttonName="btn gray-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio:</label>
                            <textarea name="bio" class="form-control" id="" cols="30" rows="10"><?php if(isset($bio)){echo $bio;}else{ echo "";} ?></textarea>
                        </div>
                        <div class="form-group">
                            <input class="btn standard-btn right" type="submit" name="update_profile" value="Update Profile">
                        </div>
                    </form>                    
                 </div>
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
</div><!-- /#wrapper -->
<?php include "includes/admin_footer.inc.php"; ?>