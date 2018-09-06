<?php
$currRole  = $_SESSION["role"];

if ($currRole != "admin") {
    header("Location: " . BASE_URL);
    exit;
}

if (isset($_POST['insert_user'])) {
    $firstname  = trim($_POST["fname"]);
    $lastname   = trim($_POST["lname"]);
    $role       = trim($_POST["role"]);
    $username   = trim($_POST["uname"]);
    $email      = trim($_POST["email"]);
    $password   = trim($_POST["pwd"]);
    $confirmPwd = trim($_POST["conf_pwd"]);

    require_once("includes/insert_user_mysqli.inc.php");
}
?>
<form class="formwidth" action="" method="post" enctype="multipart/form-data">
<p class="text-right"><a href="users.php">View Users</a></p>
<?php
// If the user is successfully inserted (insert_user_mysqli.inc.php)
if (isset($success)) {
  // Display the $success message; otherwise display each error value in the $error array
  echo "<p>$success</p>";
} elseif (isset($errors) && !empty($errors)) {
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
        <option value="admin">Select Options</option>
          <option value="admin">Admin</option>
          <option value="author">Author</option>        
        </select>
    </div>
    <div class="form-group">
     <label for="uname">Username:</label>
      <input type="text" class="form-control" name="uname" value="<?php if(isset($errors)){
      echo htmlentities($username, ENT_COMPAT, 'utf-8');} ?>">
    </div>
    <div class="form-group">
     <label for="email">Email:</label>
     <input type="email" class="form-control" name="email" value="<?php if(isset($errors)){
      echo htmlentities($email, ENT_COMPAT, 'utf-8');} ?>">
    </div>    
    <div class="form-group">
     <label for="pwd">Password:</label>
      <input type="password" class="form-control" name="pwd" id="pwd" required value="<?php if(isset($errors)){
      echo htmlentities($password, ENT_COMPAT, 'utf-8');} ?>">
    </div>    
    <div class="form-group">
      <label for="password">Re-enter your password:</label>
      <input name="conf_pwd" type="password" class="form-control" id="pwd" required value="<?php if(isset($errors)){
      echo htmlentities($password, ENT_COMPAT, 'utf-8');} ?>">
    </div>
    <div class="form-group">
      <input class="btn standard-btn right" type="submit" name="insert_user" value="Add User">
    </div>
</form>