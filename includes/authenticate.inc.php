<?php
$username = $conn->real_escape_string($username);
$password = $conn->real_escape_string($password);

$query = "SELECT salt, firstname, lastname, username, password, role, email FROM users WHERE username = ?";

// Initialize the prepared statement
$stmt = $conn->stmt_init();

// Prepare the SQL statement
$stmt = $conn->prepare($query);

// Bind the input parameter
$stmt->bind_param("s", $username);

// Bind the results of the query to new variables for each column
$stmt->bind_result($salt, $dbFname, $dbLname, $dbUname, $dbPwd, $dbRole, $dbEmail);

// Execute the statement
$stmt->execute();

// Fetch the resulting values of the executing the statement
$stmt->fetch();

// Added below to fix the "Commands out of sync" error; this frees the db resource
$stmt->free_result();

// Encrypt the password entered by the user by combining it with a salt and passing it to sha1(); then check if it matches 
// the stored version of the password, which was also encrypted; if they match, create a session variable to indicate 
// a successful sign-in and to obtain the time the session began
if (sha1($password . $salt) == $dbPwd) {
  $_SESSION["firstname"]     = $dbFname;    
  $_SESSION["lastname"]      = $dbLname;    
  $_SESSION["username"]      = $dbUname;    
  $_SESSION["role"]          = $dbRole;    
  $_SESSION["email"]         = $dbEmail;   
  $_SESSION["authenticated"] = "Logged in without a hitch";
  
  // Get the time the session started
  $_SESSION["start"] = time();
    
  // Set a flag on the session to determine if the session has not yet timed out
  $_SESSION["timeout"] = false;
    
  // Generate a session id (update the current session id with a newly generated one)
  session_regenerate_id();    
  
  // Location to redirect to upon successful sign-in
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
      header("Location: {$redirect}");
      exit;
  }
  
} else {
  $error = "Invalid username or password";
}
?>