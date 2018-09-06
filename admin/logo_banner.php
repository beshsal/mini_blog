<?php
include "includes/admin_header.inc.php";
?>
<div id="wrapper">
<?php
include "includes/admin_nav.inc.php";
include "includes/page_header.inc.php";
    
// If the welcome table is empty, insert a record with a default greeting
$customHeading   = "";
$customGreeting  = "";
$defaultGreeting = $conn->real_escape_string("<p>Simplicity is nature's first step, and the last of art.<br><small>&#8212; Philip James Bailey</small></p>");
$defaultHeading  = "";
    
$checkWelcome = $conn->query("SELECT * FROM welcome");
confirmQuery($checkWelcome);

// If the table is empty, insert default data
if ($checkWelcome->num_rows == 0) {
    // echo "There are no records.";    
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
// If the logo table is empty, insert a default logo record; else select the current records
if ($defaultLogo->num_rows == 0) {
   $logo         = "Logo";
    $logo_multi1 = "";
    $logo_multi2 = ""; 
    $result = $conn->query("INSERT INTO logo (id, logo, logo_multi1, logo_multi2) VALUES(1,'{$logo}', '{$logo_multi1}', '{$logo_multi2}')");    
    confirmQuery($result);
} else {
    //  Get the logo
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

// Initialize prepared statement
// $stmt = $conn->stmt_init();
    
if (isset($_POST["update_image"])) {
    if($_FILES['image']['error'] == 0) {
        // $_FILES is the superglobal for uploaded files; it is a multidimensional array (array of arrays)
        $fname          = $conn->real_escape_string($_FILES['image']['name']);            
        $image_temp_loc = $_FILES['image']['tmp_name']; // save the temporary location of the uploaded file

        // move_uploaded_file() tells where to move the temporary file
        // parameters - name of temporary file, where to move it 
        move_uploaded_file($image_temp_loc, "images/welcome_images/{$fname}");

        if (!isset($id)) {
            // Insert the filename into the filename column in the posts table;
            // changed post_author to post_user
            $query = "INSERT INTO welcome (filename)      
                     VALUES('{$fname}')";
        } else {
            $query = "UPDATE welcome SET filename = '{$fname}'
                     WHERE id = {$id}";
        }
        // Run the query
        $result = $conn->query($query);
        // Confirm that is was successful  
        confirmQuery($result);
        header("Location: logo_banner.php");
        exit;
    } else {
        $error = "<p class='error'>There is a problem uploading the file.</p>";
    }
}
 
// If a greeting already exists, update it
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

// If a single logo already exists, update it
if (isset($_POST["update_logo1"])) {    
    $logo_multi1 = "";
    $logo_multi2 = "";

    if (isset($_POST["logo"]) || !empty($_POST["logo"])) {
        $logo = $_POST["logo"];
    } else {
        $logo = "Logo";
    }

    // Update the logo field; logo_multi1 and logo_multi2 will be empty
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
} elseif (isset($_POST["update_logo2"])) {
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
                            <label for="image">Upload background image:</label>
                            <input type="file" name="image" class="filestyle" data-buttonName="btn gray-btn" data-buttonText="Choose Image" data-icon="false" data-badge="false" data-placeholder="No image added" data-size="md">
                        </div>
                        <div class="form-group col-sm-6" style="postion: relative;">
                        <?php if (!empty($filename)) {
                            echo "<img id='post-img-holder' src='images/welcome_images/{$filename}'>";
                        } else { echo ""; }
                        ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <input class="btn standard-btn" type="submit" name="update_image" value="Update Image">
                    </div>
                </form>                
                 <div style="margin-bottom: 5px;"><span style="font-weight:bold;">Logo: </span><span><a id="switchFields" href="#pagetop" style="">Add multicolor logo</a></span></div>                
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
                        <input class="btn standard-btn" type="submit" name="update_logo2" value="Update">
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