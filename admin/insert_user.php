<?php
// This script is used for adding new admin and authors, not members. 

$currRole  = $_SESSION["role"];

// Only admin are allowed to add new users, so if the user is not an admin, redirect the user away.
if ($currRole != "admin") {
    header("Location: " . BASE_URL);
    exit;
}

if (isset($_POST['insert_user'])) {
    // Remove any whitespaces from the data entered by the user, and escape it to prevent SQL injections.
    $firstname  = trim($conn->real_escape_string($_POST["fname"]));
    $lastname   = trim($conn->real_escape_string($_POST["lname"]));
    $role       = $_POST["role"];    
    $username   = trim($conn->real_escape_string($_POST["uname"]));
    $email      = trim($conn->real_escape_string($_POST["email"]));
    $password   = trim($conn->real_escape_string($_POST["pwd"]));
    $confirmPwd = trim($conn->real_escape_string($_POST["conf_pwd"]));
    
    // Include the script for inserting the user into the database only if the form is submitted.
    require_once("includes/insert_user_mysqli.inc.php");
}
?>
<form class="formwidth" action="" method="post" enctype="multipart/form-data">
<p class="text-right"><a href="users.php">View Users</a></p>
<?php
// If the user is successfully inserted ($success is set in insert_user_mysqli.inc.php)
if (isset($success)) {
  // Display the $success message; otherwise display each error value in the $errors array.
  echo "<p class='success'>$success</p>";
} elseif (isset($errors) && !empty($errors)) {
    // Get the number of errors. If there is only one error, display it in a paragraph; otherwise display the errors
    // in an unordered list.  
    $countErr = count($errors);        
    if ($countErr == 1) {
        echo "<p class='error'>ERROR: " . $errors[0] . "</p>";
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
    <!-- If there are errors, the fields retain their data. -->
    <div class="form-group">
     <label for="firstname">Firstname:</label>
      <input type="text" class="form-control" name="fname" value="<?php if(isset($errors)){
      echo htmlentities($firstname, ENT_COMPAT, 'utf-8');} ?>">
    </div>
    <div class="form-group">
        <label for="lastname">Lastname:</label>
        <input type="text" class="form-control" name="lname" value="<?php if(isset($errors)){
      echo htmlentities($lastname, ENT_COMPAT, 'utf-8');} ?>">
    </div>    
    <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="">
          <option value="">Select Options</option>
          <option value="admin" <?php 
          if (isset($role) && $role == "admin") { echo "selected"; } 
          ?>>Admin</option>
          <option value="author" <?php 
          if (isset($role) && $role == "author") { echo "selected"; } 
          ?>>Author</option>        
        </select>
    </div>
    <div class="form-group">
     <label for="uname">Username:</label>
     <input type="text" class="form-control" name="uname" value="<?php if(isset($errors)){
      echo htmlentities($username, ENT_COMPAT, 'utf-8');} ?>">
    </div>
    <div class="form-group">
     <label for="email">Email:</label>
     <input type="text" class="form-control" name="email" value="<?php if(isset($errors)){
      echo htmlentities($email, ENT_COMPAT, 'utf-8');} ?>">
    </div>    
    <div class="form-group">
     <label for="pwd">Password:</label>
      <input type="password" class="form-control" name="pwd" id="pwd" value="<?php // if(isset($errors)){echo htmlentities($password, ENT_COMPAT, 'utf-8');} ?>">
    </div>    
    <div class="form-group">
      <label for="password">Re-enter your password:</label>
      <input name="conf_pwd" type="password" class="form-control" id="pwd" value="<?php // if(isset($errors)){echo htmlentities($password, ENT_COMPAT, 'utf-8');} ?>">
    </div>
    <div class="form-group">
      <input class="btn standard-btn right" type="submit" name="insert_user" value="Add User">
    </div>
</form>