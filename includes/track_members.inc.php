<?php
require_once("db.inc.php");
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
    
    // Check the database for the current online user's session info
    $dbSessUname = $conn->query("SELECT sess_username FROM online_users WHERE sess_username = '{$sessUname}'");
    $dbSessID    = $conn->query("SELECT session FROM online_users WHERE session = '{$sessionID}'");    
    $memberCount = $dbSessID->num_rows;
    
        
    if ($memberCount == NULL) {
        if ($dbSessUname->num_rows > 0) {
            $conn->query("DELETE FROM online_users WHERE sess_username = '{$sessUname}'");
        }
        
        $conn->query("INSERT INTO online_users (session, sess_username, time, sess_role) VALUES('{$sessionID}', '{$sessUname}', '{$sessionTime}', '{$sessRole}')");
    } else {
        $conn->query("UPDATE online_users SET time = '{$sessionTime}' WHERE session = '{$sessionID}'");
    }
}
?>