<?php
$currUname = $_SESSION["username"];
// Check if a "userid" parameter (the user_id of the user to be updated) is received from the URL query string AND its 
// value is numeric. If it is, store its value in $userId; otherwise, redirect the user back to the users page.
if (isset($_GET["userid"])) {
    if (!is_numeric($_GET["userid"])) {
        header("Location: " . BASE_URL . "users.php");
        exit;
    // Otherwise store the value in a variable.
    } else {
        $userId = (int) $_GET["userid"];
    }
    // First the specific user that will be updated must be selected.
    $query = "SELECT * FROM users WHERE user_id = {$userId}";
    $row = $conn->query($query)->fetch_object();
    confirmQuery($row);
    
    // If the update form is submitted
    if (isset($_POST['update_user'])) {
      // Get the data from the form.
      $firstname  = trim($_POST['fname']);
      $lastname   = trim($_POST['lname']);
      $role       = trim($_POST["role"]);
      $username   = trim($_POST['uname']);
      $email      = trim($_POST["email"]);
      $password   = trim($_POST['pwd']);
      $confirmPwd = trim($_POST["conf_pwd"]);  

      // Require the script for updating the record in the database.      
      require_once('includes/update_user_mysqli.inc.php');
    }
} else {  
    // If the user_id is not present in the URL, redirect to the users page.
    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>

<h1><?php echo "Update user: " . $row->firstname . " (" . $row->username . ")"; ?></h1>
<form class="formwidth" action="" method="post" enctype="multipart/form-data">
<p class="text-right"><a href="users.php">View Users</a></p>
<?php    
// If the user is successfully updated (update_user_mysqli.inc.php)
if (isset($success)) {
  // Display the $success message; otherwise display any error in $error array.
  echo "<p class='success'>$success</p>";
} elseif (isset($errors) && !empty($errors)) {
    // Get the number of errors.
    $countErr = count($errors);
    // If there is only one error, display it in a paragraph.      
    if ($countErr == 1) {
        echo "<p class='error'>ERROR: " . $errors[0] . "</p>";
    // Otherwise, display the errors in an unordered list.
    } else {
        echo "<p class='error' style='margin-bottom: 0;'>ERROR:</p>";
        echo "<ul class='list-unstyled'>";        
        foreach ($errors as $error) {
           echo "<li class='error'>&#8226; " . $error . "</li>";
        }
        echo "</ul>";
    }
}
?>     
    <div class="form-group">
        <label for="firstname">Firstname:</label>
        <input type="text" value="<?php if(isset($firstname)){echo htmlentities($firstname, ENT_COMPAT, "utf-8");}else{echo $row->firstname;} ?>" class="form-control" name="fname">
    </div>
    <div class="form-group">
        <label for="lastname">Lastname:</label>
        <input type="text" value="<?php if(isset($lastname)){echo htmlentities($lastname, ENT_COMPAT, "utf-8");}else{echo $row->lastname;} ?>" class="form-control" name="lname">
    </div>
    <?php 
    // Prevent the current admin from changing his own role (the select element will not show).
    if ($currUname != $row->username && $_SESSION["role"] == "admin") { ?>
    <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="">
        <?php if ($row->role == "admin" || $row->role == "author") { ?>
        <option value="admin" <?php
        if (!isset($role) && $row->role == "admin" || isset($role) && $role == "admin") {
            echo "selected"; 
        }?>>Admin</option>
        <?php } ?>
        <option value="author" <?php
        if (!isset($role) && $row->role == "author" || isset($role) && $role == "author") {
            echo "selected"; 
        }?>>Author</option>
        <?php if ($row->role == "member") { ?>
        <option value="member" <?php
        if (!isset($role) && $row->role == "member" || isset($role) && $role == "member") {
            echo "selected"; 
        }?>>Member</option>
        <?php } ?>
        </select>
    </div>
    <?php } ?>
    <div class="form-group">
        <input type="hidden" value="<?php echo $row->username; ?>" class="form-control" name="uname">
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="text" value="<?php if(isset($email)){echo htmlentities($email, ENT_COMPAT, "utf-8");}else{echo $row->email;} ?>" class="form-control" name="email">
    </div>
    <div class="form-group">
        <label for="password">Enter the user's new password:</label>
        <input type="password" class="form-control" name="pwd" id="pwd">
    </div>    
    <div class="form-group">
      <label for="password">Re-enter the user's new password:</label>
      <input name="conf_pwd" type="password" class="form-control" id="pwd">
    </div>
    <?php 
    // The current admin user cannot change his role through the select element. The role is passed through
    // a hidden input field.
    if ($currUname == $row->username && $_SESSION["role"] == "admin") { ?>
      <input name="role" type="hidden" value="<?php echo $_SESSION["role"]; ?>">
    <?php } ?>
    <div class="form-group">
        <input class="btn standard-btn right" type="submit" name="update_user" value="Update User">
    </div>
</form>