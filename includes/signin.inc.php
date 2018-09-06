<?php
$error = "";

if (isset($_POST["sign_in"])) {
    $username = trim($_POST["uname"]);
    $password = trim($_POST["pwd"]);
    
    // Location to redirect to upon successful sign-in
    // if (isset($_POST["cmnt_signin"])) {
    //   $redirect = $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"] . "#comments";
    // } elseif (THIS_PAGE == "post.php") {
    //   $redirect = $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"];
    // } elseif (THIS_PAGE == "index.php") {
    //   $redirect = BASE_URL . "admin/";
    // } elseif (THIS_PAGE == "new_member.php") {
    //     $redirect = BASE_URL;
    // } elseif (THIS_PAGE == "category.php") {
    //     $redirect = $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"];
    // } elseif (THIS_PAGE == "author_posts.php") {
    //     $redirect = $_SERVER["PHP_SELF"] . "?" . $_SERVER["QUERY_STRING"];
    // } else {
    //     $redirect = BASE_URL . basename(THIS_PAGE, ".php");
    // }    
    
    require_once("authenticate.inc.php");    
}
?>