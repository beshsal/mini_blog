<?php
$currSessID = session_id();

// Set a time limit in seconds
// $timelimit = 3600; // 1 hour
$timelimit = 180;

// Get the current time
$now = time();
// If the sum of $_SESSION["start"] plus $timelimit is less than the current time ($now), the session is ended and 
// the user is redirected to session_expired.php
if (isset($_SESSION["authenticated"]) && $now > $_SESSION["start"] + $timelimit) {
   $_SESSION["timeout"] = true;
  
  // ********** The script for resetting/reseeding the database upon session expiration **********
  // require_once("db_table_resets.php");
    
  // Remove this delete query if you want to store the number of online users
  $conn->query("DELETE FROM online_users WHERE session = '{$currSessID}'");
    
  // If the time limit has expired, destroy the session and redirect
  $_SESSION = array();
    
  // Invalidate the session cookie
  if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-86400, '/');
  }
    
  // End the session and redirect with a query string
  session_destroy();
    
  header("Location: http://" . $_SERVER['SERVER_NAME'] . "/mini_blog/session_expired");
  exit;
} else {
  // If it's gotten this far, update the start time
  $_SESSION["start"] = time();
}
?>