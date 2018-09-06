<?php
// Run this script only if the sign-out button has been clicked
if (isset($_POST["sign_out"])) {
    
  if ($_SESSION["timeout"] != true) {
      // $redirect = $_SERVER["PHP_SELF"];
      $redirect = basename(THIS_PAGE, ".php");
  }
    
  $currSessID = session_id();
    
  // Remove this delete query if you want to store the number of online users
  $conn->query("DELETE FROM online_users WHERE session = '{$currSessID}'");
    
  // Empty the $_SESSION array
  // Unset all session variables by setting $_SESSION array to empty array
  $_SESSION = array();
  
  // Invalidate the session cookie
  // Use the session_name() function to get name of session dynamically, so it can be used to reset the session cookie
  if(isset($_COOKIE[session_name()])) {
    // This invalidates the session cookie by resetting it to an empty string and to expire 24 hours ago
	setcookie(session_name(), "", time()-86400, "/");
  }
  // End the session and redirect to home
  // This destroys the session to prevent the risk of unauthorized users gaining access
  session_destroy();
    
  if (THIS_PAGE == "categories.php" || THIS_PAGE == "contact.php") {      
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
}