<?php
// The db script is directly included.
require_once("db.inc.php");
// Make sure there is a live session.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_GET["sessionID"])) {
    $sessionID   = $_GET["sessionID"];
    $sessionTime = $_GET["sessionTime"];
    $sessUname   = $_SESSION["username"];
    $sessRole    = $_SESSION["role"];
    $timeoff     = 05; // set the amount of time for the user to be marked offline
    $timeout     = $sessionTime - $timeoff; // calculate the time the user has been offline
    
    // Query the database for the current online user's session info.
    $dbSessUname = $conn->query("SELECT sess_username FROM online_users WHERE sess_username = '{$sessUname}'");
    $dbSessID    = $conn->query("SELECT session FROM online_users WHERE session = '{$sessionID}'");    
    $memberCount = $dbSessID->num_rows;
    
    // If the user is not in the online_users table, insert a new record for that user. However, if there is already a 
    // record in the table for the user (e.g. the user wasn't automatically deleted because he or she forgot to sign out
    // before closing the browser or the session timed out), then update the user's record with the current details.
    if ($memberCount == NULL) {
        // Just to be sure there is no record.
        if ($dbSessUname->num_rows > 0) {
            $conn->query("DELETE FROM online_users WHERE sess_username = '{$sessUname}'");
        }
        
        $conn->query("INSERT INTO online_users (session, sess_username, time, sess_role) VALUES('{$sessionID}', '{$sessUname}', '{$sessionTime}', '{$sessRole}')");
    } else {
        $conn->query("UPDATE online_users SET time = '{$sessionTime}' WHERE session = '{$sessionID}'");
    }
}
?>