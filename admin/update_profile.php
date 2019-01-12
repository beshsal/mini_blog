<?php
include "includes/admin_header.inc.php";

// Get the user's current role and username.
$currRole  = $_SESSION["role"];
$currUname = $_SESSION["username"];

// Set an array for storing errors.
$imgErrors = array();

// Parameters for user_id, image_id, auth_id, and filename are sent from the profile page when the user clicks on 
// his or her profile image or the link to this page.
if (isset($_GET["uid"]) && is_numeric($_GET["uid"])) {
        $userId = (int) $_GET["uid"];
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
        $authId = (int) $_GET["aid"];
} else {
    header("Location: " . BASE_URL . "profile.php");
    exit;
}

if (isset($_GET["fname"])) {
    $filename = $_GET["fname"];
}

// If $authId is set, that means there is a record already in the auth_profile table for the user, so select the record in the
// auth_profile table.
if (isset($authId) && !empty($authId) && $authId != 0) {
    // Get the current profile data to persist it to the fields.
    $query = "SELECT * FROM auth_profile WHERE auth_id = {$authId}";
    $result = $conn->query($query);
    confirmQuery($result);    
    while ($row = $result->fetch_assoc()) {
        $auth_id   = $row["auth_id"];
        $user_id   = $row["user_id"];
        $bio       = $row["bio"]; // this is empty the very first time; however, below it is assigned whitespace
        $firstname = $row["firstname"];   
    }
// Otherwise, if the user has not been added to the auth_profile table yet, select the record in the users table.
} else {
    $result = $conn->query("SELECT * FROM users WHERE user_id = {$userId}");
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
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["update_profile"])) {

    // Initialize the prepared statement.
    $stmt = $conn->stmt_init();
    
    // Get the user's bio. This will either contain text or be empty.
    $newBio = trim($_POST["bio"]);
    
    // If there is no record for the user yet in the auth_profile table, insert one with the data selected from the users table.
    if (!isset($authId) || empty($authId) || $authId == 0) {
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
    
    // If the user submits an image file (the form is not submitted with an empty field)
    if (!empty($_FILES['image']['tmp_name'])) { 
        // Set a flag to determine if the image file passed validation and uploaded successfully.
        $imageOK = false;
        // Require the class for validating and uploading the image file.
        require_once("includes/classes/UploadImg.php");
        // Create an instance of the class, and pass it the path ($path) to the destination directory for uploaded images.
        $upload = new UploadImg("images/user_images/");
        // Move the image file to the destination directory.
        $upload->move(true); // true sets the optional parameter $overwrite to overwrite the filename instead of renaming it
        // The contents of the $_filenames property (array) of the upload is stored in $filenames.
        // $filenames will be an empty array if the upload fails.
        $filenames = $upload->getFilenames();

        // If the $filenames array has a value
        if ($filenames) {
            // Delete the user's current image record, so it can be replaced by a new one.
            if (isset($imgId) && !empty($imgId) && $imgId != 0) {
                $deleteUserImg = "DELETE FROM user_images WHERE image_id = {$imgId}"; 
            } else {
                $deleteUserImg = "DELETE FROM user_images WHERE user_id = {$user_id}";
            }
            $conn->query($deleteUserImg);

            // Insert a new record for the user in the user_images table.
            $query = "INSERT INTO user_images (user_id, filename, role) VALUES (?, ?, ?)";        
            // Prepare the statement. 
            $stmt->prepare($query) or die($conn->error);        
            // Use the filename retrieved from $_filenames property and the role of the current user submitted from the form.
            $stmt->bind_param("iss", $user_id, $filenames[0], $currRole);        
            // Execute the statement.
            $stmt->execute();
            $imageOK = $stmt->affected_rows; // $imageOK is reset to true (1 treated as true)
        }

        // If $imageOK is true (the upload was successful), get the primary key of the inserted image. Otherwise, get the error messages.
        if ($imageOK) {
            // The insert_id property stores the primary key of the uploaded image in $image_id (there is now an image_id for the new file).
            // ("Get the ID generated from the previous INSERT operation.")
            $image_id = $stmt->insert_id;
        // Otherwise store any error caught by the $upload object.
        } else {
            // The getMessages method returns the $_messages array, which stores error messages. Its elements are separated by spaces.
            $uploadError = implode(" ", $upload->getMessages());
        }
    // If the form is submitted without an image file
    } else {
        // Otherwise, if the user doesn't upload a new image, use the current image_id in the update operation.
        $image_id = $imgId;
        // $emptyField = "Please select an image file to upload.";
    }
    
    // If there is a prepared statement error or an image upload error, add it to the $imgErrors array.
    if ($stmt->error) {
        $imgErrors[] = $stmt->error;
    } elseif (isset($uploadError)) {
        $imgErrors[] = $uploadError;
    // } elseif (isset($emptyField)) {
    //     $imgErrors[] = $emptyField;
    // Otherwise update the image_id fields in the users and auth_profile tables.
    } else {
        $stmt->prepare("UPDATE users SET image_id = ? WHERE user_id = ?") or die($conn->error);        
        // Bind the values from the form to the parameters.
        $stmt->bind_param("ii", $image_id, $user_id);
        // Execute the statement.
        $stmt->execute();        
        $stmt->prepare("UPDATE auth_profile SET image_id = ?, bio = ? WHERE user_id = ?") or die($conn->error);        
        // Bind the values from the form to the parameters.
        $stmt->bind_param("isi", $image_id, $newBio, $user_id);
        // Execute the statement.
        $stmt->execute();
        header("Location: " . BASE_URL . "profile.php");
        exit;
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
                        <div class="row">
                            <div class="col-sm-6 form-group">
                                <?php 
                                // If there is an error, display it.
                                if (isset($imgErrors) && !empty($imgErrors)) {
                                    $countErr = count($imgErrors);        
                                    if ($countErr == 1) {
                                        echo "<p class='error'>ERROR: " . $imgErrors[0] . "</p>";
                                    } else {
                                        echo "<p class='error' style='margin-bottom: 0;'>ERROR:</p>";
                                        echo "<ul class='list-unstyled'>";        
                                        foreach ($imgErrors as $error) {
                                           echo "<li class='error'>&#8226; " . $error . "</li>";
                                        }
                                        echo "</ul>";
                                    }
                                } ?>
                                <label for="user-image">Upload image:</label>
                                <input type="file" name="image" class="filestyle" data-buttonName="btn gray-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
                            </div>
                            <div class="col-sm-6 form-group" id="user-profile-image">
                                <?php 
                                // If there is an image file (a filename value came in through the query string), display it.
                                if (isset($filename)) { 
                                ?>
                                <!-- 
                                <img src="images/user_images/<?php // echo $filename; ?>" alt='Profile Image' height='180' width='180'>
                                -->
                                <div class="user-thumb lg"
                                     style="background-image: url('images/user_images/<?php echo $filename; ?>')">
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="bio">Bio:</label>
                            <!-- The bio is optional. It will either contain text or be empty. -->
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