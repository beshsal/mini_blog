<?php
$error = "";
// If a request for signing in is made, authenticate the user.
if (isset($_POST["sign_in"])) {
    // Retrieve the data/credentials entered by the user.
    $username = trim($_POST["uname"]);
    $password = trim($_POST["pwd"]);
    
    // Include the authentication script.
    require_once("authenticate.inc.php");    
}
?>