<?php
// REPORT ERRORS
ini_set('display_errors', 1); error_reporting(E_ALL);
ob_start();
session_start();

// Constant for current PHP page
define("THIS_PAGE", basename($_SERVER["SCRIPT_FILENAME"]));
// echo THIS_PAGE;

// Constant for base directory
define("BASE_DIR", realpath(dirname(__DIR__)));

// Constant for base URL
define("BASE_URL", "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/");

// ACCESS CONTROL
$permittedRole = ($_SESSION["role"] == "admin") ? "admin" : "author";

if(!isset($_SESSION["authenticated"]) || $_SESSION["role"] != $permittedRole) {
    header("Location: " . BASE_URL . "../");
    exit;
}

require_once("../includes/db.inc.php");
include("../includes/util_funcs.inc.php");
include("../includes/signout.inc.php");
include("delete_modal.inc.php");

// Session timeout
require_once(BASE_DIR . "/includes/session_timeout.inc.php");
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <title>Admin</title>
        
        <!-- Bootstrap -->
        <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">    

        <!-- Custom styles -->
        <link href="css/sb-admin.css" rel="stylesheet">
        <link href="css/style.css" rel="stylesheet"> 

        <!-- Fonts -->
        <link href="bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Ledger|Playfair+Display|Poiret+One|Raleway|Roboto|Roboto+Condensed" rel="stylesheet">
        
         <script src="bower_components/jquery/dist/jquery.min.js"></script> 
        
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        
        <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
        
        <!-- See the difference between referencing the CDN and local copy -->
        <script src='https://cloud.tinymce.com/stable/tinymce.min.js'></script>
        
<!--
        <script>
          // Target all textarea elements
          tinymce.init({selector: 'textarea'});
        </script>
-->
    </head>
    <body>