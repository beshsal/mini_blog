<?php
if (isset($_POST["register"])) {    
  $firstname   = trim($_POST["fname"]);
  $lastname    = trim($_POST["lname"]);
  $email       = trim($_POST["email"]);
  $username    = trim($_POST["uname"]);
  $password    = trim($_POST["pwd"]);
  $confirm_pwd = trim($_POST["conf_pwd"]);
  $role        = $_POST["role"];
  
  // Include the script for validating and inserting the new user
  require_once("register_mysqli.inc.php");
}
?>