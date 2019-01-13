<?php
// Clean the entered credentials to make sure they are safe for SQL queries.
$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);

// Get the user's data.
$query = "SELECT salt, user_id, firstname, lastname, username, password, role, email FROM users WHERE username = ?";

// Initialize the prepared statement.
$stmt = $conn->stmt_init();

// Prepare the SQL statement.
$stmt = $conn->prepare($query);

// Bind the input parameter.
$stmt->bind_param("s", $username);

// Bind the results of the query to new variables for each field.
$stmt->bind_result($salt, $dbUid, $dbFname, $dbLname, $dbUname, $dbPwd, $dbRole, $dbEmail);

// Execute the statement.
$stmt->execute();

// Fetch the resulting values of the executed the statement.
$stmt->fetch();

// Added below to fix the "Commands out of sync" error; this frees the db resource.
$stmt->free_result();

// Encrypt the password entered by the user by combining it with a salt and passing it to sha1(); then check if it matches 
// the stored version of the password, which was also encrypted; if they match, create a session variable to indicate 
// a successful sign-in and to obtain the time the session began.
if (sha1($password . $salt) == $dbPwd) {
  $_SESSION["user_id"]       = $dbUid; // user_id needed for creating an author profile for the admin/author if one doesn't already exist
  $_SESSION["firstname"]     = $dbFname;    
  $_SESSION["lastname"]      = $dbLname;    
  $_SESSION["username"]      = $dbUname;    
  $_SESSION["role"]          = $dbRole;    
  $_SESSION["email"]         = $dbEmail;   
  $_SESSION["authenticated"] = "Logged in without a hitch.";
  
  // If the admin or author doesn't have an author profile, create one.
  if (isset($_SESSION["authenticated"])) { 
      if ($_SESSION["role"] == "admin" || $_SESSION["role"] == "author") {
        $checkProf = $conn->query("SELECT * FROM auth_profile WHERE username = '" . $_SESSION["username"] . "' ");
        confirmQuery($checkProf);

      if ($checkProf->num_rows == 0) {
        // Create the full name for the author's profile (auth_profile).
        $full = $_SESSION["firstname"] . " " . $_SESSION["lastname"];
        $insertProf = "INSERT INTO auth_profile (user_id, username, firstname, lastname, fullname, email, role) VALUES (?, ?, ?, ?, ?,?, ?)";
        if (!($stmt->prepare($insertProf))) {
            echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        }
        if (!$stmt->bind_param("issssss", 
                               $_SESSION["user_id"], 
                               $_SESSION["username"], 
                               $_SESSION["firstname"], 
                               $_SESSION["lastname"], 
                               $full, 
                               $_SESSION["email"], 
                               $_SESSION["role"])) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        }  
      }
    }
  }
  
  // Get the time the session started.
  $_SESSION["start"] = time();
    
  // Set a flag on the session to determine if the session has not timed out yet.
  $_SESSION["timeout"] = false;
    
  // Generate a session ID (update the current session ID with a newly generated one)
  session_regenerate_id();    
  
  // The location to redirect to upon a successful sign-in
  // If the user is an admin or author and is on the home page, the user will be redirected to the admin page. Otherwise, if
  // the user is a member, the user will be directed back to the home page (handled in the admin code). For other pages, users
  // will be redirected to the same page.
  if (THIS_PAGE == "index.php") {
    $redirect = BASE_URL . "admin/";
  } elseif (THIS_PAGE == "categories.php" || THIS_PAGE == "contact.php") {
    $redirect = BASE_URL . basename(THIS_PAGE, ".php");
  } elseif (THIS_PAGE == "post.php" || THIS_PAGE == "category.php" || THIS_PAGE == "author_posts.php") {
    redirectToParams();
  } else {
    $redirect = BASE_URL;
  } 
    
  if (isset($redirect)) {
      // If the variable that is set on the session if the user's
      // password has been successfully changed is set, unset it (reset_password.php).
      if (isset($_SESSION["pwdreset"])) {
        unset($_SESSION["pwdreset"]);   
      }
      header("Location: {$redirect}");
      exit;
  }
  
} else {
  // If set, $error triggers the error modal, which will display its content.
  $error = "Invalid username or password";
}
?>