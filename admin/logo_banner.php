<?php
include "includes/admin_header.inc.php";
?>
<div id="wrapper">
<?php
include "includes/admin_nav.inc.php";
include "includes/page_header.inc.php";
    
// If the welcome table is empty, insert a record with a default greeting.
$customHeading   = "";
$customGreeting  = "";
$defaultGreeting = $conn->real_escape_string("<p>Simplicity is nature's first step, and the last of art.<br><small>&#8212; Philip James Bailey</small></p>");
$defaultHeading  = "";
    
$checkWelcome = $conn->query("SELECT * FROM welcome");
confirmQuery($checkWelcome);

if ($checkWelcome->num_rows == 0) {    
    $insertDefaults = $conn->query("INSERT INTO welcome (id, heading, greeting, filename) VALUES(1, '{$defaultHeading}', '{$defaultGreeting}', '')");
    confirmQuery($insertDefaults);
} else {
    while ($row = $checkWelcome->fetch_assoc()) {
        $id       = $row["id"];
        $heading  = $row["heading"];
        $greeting = $row["greeting"];
        $filename = $row["filename"];
    }
}   

// Logo queries
$defaultLogo = $conn->query("SELECT * FROM logo");
confirmQuery($defaultLogo);   
// If the logo table is empty, insert a default logo record; else select the current record.
if ($defaultLogo->num_rows == 0) {
    $logo        = "Logo";
    $logo_multi1 = "";
    $logo_multi2 = ""; 
    $result = $conn->query("INSERT INTO logo (id, logo, logo_multi1, logo_multi2) VALUES(1,'{$logo}', '{$logo_multi1}', '{$logo_multi2}')");    
    confirmQuery($result);
} else {
    //  Get the logo.
    $logoQuery = "SELECT * FROM logo";
    $result    = $conn->query($logoQuery);
    confirmQuery($result);
    while ($row = $result->fetch_assoc()) {
        $logo_id     = $row["id"];
        $logo        = $row["logo"];
        $logo_multi1 = $row["logo_multi1"];
        $logo_multi2 = $row["logo_multi2"];
    }
}

// Change the background image.
// If the form for uploading an image is submitted
if (isset($_POST["update_image"])) {
    // If the input field is sent without an uploaded image file, set an error message.
    if (empty($_FILES['image']['tmp_name']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
        $error = "<p class='error'>Please select an image file to upload.</p>";
    // Otherwise, validate the file, and attempt to upload it.
    } else {
        // Set a flag to determine if the file is valid.
        $imageOK = true;

        // Get the filename.
        // $fname = $conn->real_escape_string($_FILES['image']['name']);
        $fname = basename($_FILES['image']['name']);
        
        // Get the file data.
        $isImage = getimagesize($_FILES["image"]["tmp_name"]);

        // Get the file extension. 
        $type = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

        // Set the permitted extensions.
        $permitted = array('jpg','jpeg','png','gif');
        
        // Check if the file is permitted. If not, set an error message.

        // if($type != "jpg" || $type != "jpeg" || $type != "png" || $type != "gif" ) {
            // $error = "<p class='error'> Only JPG, JPEG, PNG and GIF files may be upload.</p>";
        // }

        if (!in_array($type, $permitted)) {
            $imageOK = false;
            $error = "<p class='error'> Only JPG, JPEG, PNG and GIF files may be upload.</p>";
        }

        // Check if the file is an image. If not, set an error message.
        if ($isImage == false) {
            $imageOK = false;
            $error = "<p class='error'>Only image files may be uploaded.</p>";
        } 

        // If the file is valid, attempt to upload it.
        if ($imageOK) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], "images/welcome_images/{$fname}")) {
                $success = "<p class='success'>The file has been successfully uploaded.</p>";

                // If the file has not been uploaded yet, insert a record for it in the welcome table. Otherwise, update
                // the current record's filename.
                if (!isset($id)) {
                    // Insert the filename into the filename column in the welcome table
                    $query = "INSERT INTO welcome (filename)      
                             VALUES('{$fname}')";
                } else {
                    $query = "UPDATE welcome SET filename = '{$fname}'
                             WHERE id = {$id}";
                }
                // Run the query.
                $result = $conn->query($query);
                // Confirm that is was successful. 
                confirmQuery($result);
                header("Location: logo_banner.php");
                exit;
            } else {
                $error = "<p class='error'>There is a problem uploading the file.</p>";
            }
        }
    }
}
 
// If a greeting and heading already exist, update them.
if (isset($_POST["update_welcome"])) {    
    if (isset($_POST["greeting"])) {
        $customGreeting = $_POST["greeting"]; 
        
        if (!($stmt = $conn->prepare("UPDATE welcome SET greeting = ? WHERE id = ?"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("si", $customGreeting, $id)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();
    }    
    if (isset($_POST["heading"])) {
        $customHeading = $_POST["heading"]; 
        
        if (!($stmt = $conn->prepare("UPDATE welcome SET heading = ? WHERE id = ?"))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("si", $customHeading, $id)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: logo_banner.php");
    exit;
}

// If a single color logo already exists, update it.
if (isset($_POST["update_logo1"])) {
    // The logo_multi1 and logo_multi2 fields will be empty.   
    $logo_multi1 = "";
    $logo_multi2 = "";

    if (isset($_POST["logo"]) || !empty($_POST["logo"])) {
        $logo = $_POST["logo"];
    } else {
        $logo = "Logo";
    }

    // Update the logo field.
    if (!($stmt = $conn->prepare("UPDATE logo SET logo = ?, logo_multi1 = ?, logo_multi2 = ? WHERE id = ?"))) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    if (!$stmt->bind_param("sssi", $logo, $logo_multi1, $logo_multi2, $logo_id)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
    header("Location: logo_banner.php");
    exit;
// Else if a multi-colored logo exists, update it.
} elseif (isset($_POST["update_logo2"])) {
    // The single logo field will be empty, and the logo_multi1 and logo_multi2 fields will have values.
    $logo = "";

    if (isset($_POST["logo_multi1"]) || isset($_POST["logo_multi2"]) && !empty($_POST["logo_multi1"]) || !empty($_POST["logo_multi2"])) {
        $logo_multi1 = $_POST["logo_multi1"];
        $logo_multi2 = $_POST["logo_multi2"];
    } else {
        $logo_multi1 = "Part1";
        $logo_multi2 = "Part2";
    }    
    if (!($stmt = $conn->prepare("UPDATE logo SET logo = ?, logo_multi1 = ?, logo_multi2 = ? WHERE id = ?"))) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
    }
    if (!$stmt->bind_param("sssi", $logo, $logo_multi1, $logo_multi2, $logo_id)) {
        echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
    }
    $stmt->close();
    header("Location: logo_banner.php");
    exit;
}
?>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <div id="pagetop" class="col-lg-12">
                <div class="formwidth">
                <form class="" action="" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <?php if(isset($error)) echo $error; ?>
                            <label for="image">Upload background image:</label>
                            <input type="file" name="image" class="filestyle" data-buttonName="btn gray-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
                        </div>
                        <div class="form-group col-sm-6" style="postion: relative;">
                        <?php if(!empty($filename)){echo "<img id='post-img-holder' src='images/welcome_images/{$filename}'>";}else{echo "";} ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input class="btn standard-btn" type="submit" name="update_image" value="Update Image">
                    </div>
                </form>                
                 <div style="margin-bottom: 5px;"><span style="font-weight:bold;">Logo: </span><span><a id="switchFields">Add multi-colored logo</a></span></div>                
                <form id="form1" class="" action="" method="post" enctype="multipart/form-data">
                    <div id="logo1" class="form-group show">
                        <input id="logo-edit" class="set-logo-color" type="text" name="logo" value="<?php if(isset($logo)){echo $logo;}else{echo '';} ?>";>                    
                    </div>
                    <div class="form-group">
                        <input class="btn standard-btn" type="submit" name="update_logo1" value="Update Logo">
                    </div>
                </form>                
                <form id="form2" class="" action="" method="post" enctype="multipart/form-data" style="display:none;">
                    <div id="logo2" class="form-group">
                        <label for='logo-multi'>Portion 1:</label>
                        <input class="set-logo-color" type="text" name="logo_multi1" value="<?php if(isset($logo_multi1)){echo $logo_multi1;}else{echo '';} ?>" >
                        <label for='logo-multi'>Portion 2:</label>
                        <input class="set-logo-color" type="text" name="logo_multi2" value="<?php if(isset($logo_multi2)){echo $logo_multi2;}else{echo '';} ?>">
                    </div>
                    <div class="form-group">
                        <input class="btn standard-btn" type="submit" name="update_logo2" value="Update Logo">
                    </div>
                </form>
                <form class="" action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                         <label for="title">Heading:</label>
                          <input name="heading" type="text" class="form-control" value="<?php if(isset($heading)){echo htmlentities($heading, ENT_COMPAT, "utf-8");} ?>">
                    </div>
                    <div class="form-group">
                        <label for='welcome'>Welcome message:</label>
                        <textarea name="greeting" class="form-control" id="wel-msg" cols="30" rows="10"><?php if(isset($greeting)){echo $greeting;}else{ echo "";} ?></textarea>
                    </div>
                    <div class="form-group">
                        <input class="btn standard-btn right" type="submit" name="update_welcome" value="Update Greeting">
                    </div>
                </form>
                </div>
             </div>
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
</div><!-- /#wrapper -->
<?php include "includes/admin_footer.inc.php"; ?>