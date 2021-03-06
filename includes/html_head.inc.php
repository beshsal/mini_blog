<?php
// REPORT ERRORS
ini_set('display_errors', 1); error_reporting(E_ALL);
date_default_timezone_set("UTC");
ob_start();
session_start();

// Constant for the current page
define("THIS_PAGE", basename($_SERVER["SCRIPT_FILENAME"]));

// Constant for the base directory
define("BASE_DIR", realpath(dirname(__DIR__)));

// Constant for the base URL
define("BASE_URL", "http://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . "/");

require_once("includes/db.inc.php");
include("includes/util_funcs.inc.php");
include("includes/register.inc.php");
include("includes/signin.inc.php");
include("includes/signout.inc.php");
include("title.inc.php");
include("includes/track_members.inc.php");

// Optional timeout script for the session
if (isset($_SESSION["authenticated"])) {
    require_once(BASE_DIR . "/admin/includes/session_timeout.inc.php");
}

// Get the background image's filename (used in the in-line styles below).
$welcomeQuery = "SELECT * FROM welcome";
$result = $conn->query($welcomeQuery);
confirmQuery($result);
while ($row = $result->fetch_assoc()) {
    $filename = $row["filename"];
}

// Constants for logo (styled and unstyled)
define("LOGO", outputLogo());
define("LOGO_UNSTYLED", outputLogo(false));
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>
    <?php
    if(isset($title)) {echo $title;}
    echo " &#8212; " . LOGO_UNSTYLED;
    ?>      
    </title>

    <!-- Bootstrap -->
    <base href="/mini_blog/">
    <link href="bower_components/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="bower_components/bootstrap/dist/css/bootstrap-theme.min.css" rel="stylesheet">    
    
    <!-- Custom styles -->
    <link href="css/bootstrap-social.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="bower_components/font-awesome/css/font-awesome.min.css" rel="stylesheet">
      
    <link href="https://fonts.googleapis.com/css?family=Ledger|Playfair+Display|Poiret+One|Raleway|Roboto|Roboto+Condensed" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Oxygen" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Abril+Fatface|Chonburi|Leckerli+One|Lobster|Yesteryear" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <style>
    /* Styles for the header's background image */
    #welcome {
        background-color: #000;
        background-image: url(admin/images/welcome_images/<?php echo $filename; ?>);
        background-repeat: no-repeat;
        background-attachment: fixed;
        background-position: top center;
        background-size: cover;
        justify-content: center;
        align-items: center;
        resize: both;
        display: flex;
    }

    @media screen and (max-width: 290px) {
        #welcome {
            background-image: url(admin/images/welcome_images/<?php echo $filename; ?>);
            background-size: 50%;
        }
    }

    @media screen and (max-width: 460px) {
        #welcome {
            background-image: url(admin/images/welcome_images/<?php echo $filename; ?>);
        }
    }

    @media screen and (max-width: 600px) {
        #welcome {
            background-image: url(admin/images/welcome_images/<?php echo $filename; ?>);        
        }
    }

    @media only screen and (max-width: 768px) {
        #welcome {  
            background-image: url(admin/images/welcome_images/<?php echo $filename; ?>);        
        }
    }
        
    /* Styles for the spinning loader */
    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(255, 255, 255, 0.95) url(https://media.giphy.com/media/3o84TSvGGfaIor8VzO/giphy.gif) no-repeat center center;
        z-index: 100000;
    }
    
    #loader span {
        display: inline-block;
        margin-top: 50%;
        font-family: "Roboto", sans-serif;
        font-size: 1.2em;
    }
    
    @media only screen and (min-width: 768px) {
        #loader span {
            margin-top: 20%;
        }
    }
    </style>
  </head>