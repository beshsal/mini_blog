<?php
$currUname = $_SESSION["username"];

if (isset($_GET["user"])) {
    if (!is_numeric($_GET["user"])) {
        header("Location: " . BASE_URL . "users.php");
        exit;
    } else {
        // Extract its value, the current user's id, to a variable
        $userId = (int) $_GET["user"];
    }
    // First the specific user that will be updated by user_id needs to be selected
    $query   = "SELECT * FROM users WHERE user_id = {$userId}";
    $getUser = $conn->query($query); confirmQuery($getUser);
    while ($row = $getUser->fetch_assoc()) {
      $user_id   = $row["user_id"]; // used in update_user_mysqli.inc.php
      $username  = $row["username"];
      $firstname = $row["firstname"];
      $lastname  = $row["lastname"];
      $email     = $row["email"];
      $password  = $row["password"];
      $role      = $row["role"];
    }
    
    if (isset($_POST['update_user'])) {
      $firstname  = trim($_POST['fname']);
      $lastname   = trim($_POST['lname']);
      $role       = trim($_POST["role"]);
      $username   = trim($_POST['uname']);
      $email      = trim($_POST["email"]);
      $password   = trim($_POST['pwd']);
      $confirmPwd = trim($_POST["conf_pwd"]);        
      require_once('includes/update_user_mysqli.inc.php');
    }
} else {  
    // If the user_id is not present in the URL, redirect to the users page
    header("Location: " . BASE_URL . "users.php");
    exit;
}
?>

<h1><?php echo "Update user: " . $firstname . " (" . $username . ")"; ?></h1>
<form class="formwidth" action="" method="post" enctype="multipart/form-data">
<p class="text-right"><a href="users.php">View Users</a></p>
<?php    
// If the user is successfully updated (update_user_mysqli.inc.php)
if (isset($success)) {
  // Display the $success message; otherwise dispay any/each error in $error array
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
        <input type="text" value="<?php echo $firstname; ?>" class="form-control" name="fname">
    </div>
    <div class="form-group">
        <label for="lastname">Lastname:</label>
        <input type="text" value="<?php echo $lastname; ?>" class="form-control" name="lname">
    </div>
    <?php if ($currUname != $username && $_SESSION["role"] == "admin") { ?>
    <div class="form-group">
        <label for="role">Role:</label>
        <select name="role" id="">
        <option value="<?php echo $role; ?>"><?php echo ucfirst($role); ?></option>
        <?php
          if ($role == "member") {
             echo "<option value='author'>Author</option>";
          } else {
            echo "<option value='admin'>Admin</option>";
          }
        ?>
        </select>
    </div>
    <?php } ?>
    <div class="form-group">
        <input type="hidden" value="<?php echo $username; ?>" class="form-control" name="uname">
    </div>
    <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" value="<?php echo $email; ?>" class="form-control" name="email">
    </div>
    <div class="form-group">
        <label for="password">Please enter the user's new password:</label>
        <input type="password" class="form-control" name="pwd" id="pwd">
    </div>    
    <div class="form-group">
      <label for="password">Re-enter the user's new password:</label>
      <input name="conf_pwd" type="password" class="form-control" id="pwd">
    </div>
    <div class="form-group">
        <input class="btn standard-btn right" type="submit" name="update_user" value="Update User">
    </div>
</form>