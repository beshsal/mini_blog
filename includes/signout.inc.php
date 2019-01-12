<?php
// Run this script only if the #signoutForm has been submitted.
if (isset($_POST["sign_out"])) {
    
  // If the session has not timed out yet, redirect to the current page when the user signs out.
  if ($_SESSION["timeout"] != true) {
      // $redirect = $_SERVER["PHP_SELF"];
      $redirect = basename(THIS_PAGE, ".php");
  }
  
  // Get the session ID, which will be used to remove the user from the online_users table.
  $currSessID = session_id();
    
  // Remove this delete query if you want to store the number of online users.
  $conn->query("DELETE FROM online_users WHERE session = '{$currSessID}'");
    
  // Empty the $_SESSION array:
  // Unset all session variables by setting the $_SESSION array to an empty array.
  $_SESSION = array();
  
  // Invalidate the session cookie:
  // Use session_name() to get the name of the session dynamically, so it can be used to reset the session cookie.
  if(isset($_COOKIE[session_name()])) {
    // This invalidates the session cookie by resetting it to an empty string and to expire 24 hours ago.
	setcookie(session_name(), "", time()-86400, "/");
  }
  // End the session and redirect the user:
  // This destroys the session to prevent the risk of unauthorized users gaining access.
  session_destroy();
  
  // If the page is either the categories or contact page, set the redirect to the respective page.
  if (THIS_PAGE == "categories.php" || THIS_PAGE == "contact.php") {      
      $redirect = BASE_URL . basename(THIS_PAGE, ".php");
  // If the page is the post, category, or author_posts page, set the redirect to the respective page, including the query string.
  } elseif (THIS_PAGE == "post.php" || THIS_PAGE == "category.php" || THIS_PAGE == "author_posts.php") {
      redirectToParams();
  // For any other page, set the redirect to the home page.
  } else {
      $redirect = BASE_URL;
  }
    
  // Run the specific redirect.
  if (isset($redirect)) {
    header("Location: {$redirect}");
    exit;
  }
}