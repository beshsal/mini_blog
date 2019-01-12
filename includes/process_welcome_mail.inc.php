<?php
require_once("db.inc.php");
include("util_funcs.inc.php");
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require "../vendor/autoload.php";

// Uncomment these and comment out the autoloader to import PHPMailer manually.
//require 'path/to/PHPMailer/src/Exception.php';
//require 'path/to/PHPMailer/src/PHPMailer.php';
//require 'path/to/PHPMailer/src/SMTP.php';

require "classes/Config.php";

$mail = new PHPMailer();

if (isset($_POST["username"]) && !empty($_POST["username"])) {
    $username = $_POST["username"];
    
    $newUserData = $conn->query("SELECT firstname, lastname, email FROM users WHERE username = '{$username}'");    
    confirmQuery($newUserData);
    
    while ($row = $newUserData->fetch_assoc()) {
        $firstname = $row["firstname"];
        $lastname  = $row["lastname"];
        $email     = $row["email"];
    }
    
    // PHPMailer configuration
     
    $mail = new PHPMailer();                
    // $mail->SMTPDebug = 3; // enable verbose debug output
    $mail->isSMTP(); // set mailer to use SMTP
    $mail->Host = Config::SMTP_HOST;
    $mail->Username = Config::SMTP_USER;
    $mail->Password = Config::SMTP_PASSWORD;
    $mail->Port = Config::SMTP_PORT;
    $mail->SMTPSecure = 'tls'; // enable TLS encryption, `ssl` also accepted
    $mail->SMTPAuth = true; // enable SMTP authentication
    $mail->isHTML(true); // set email format to HTML
    $mail->CharSet = 'UTF-8';

    // Recipients
    $mail->setFrom('beshsaleh@gmail.com', 'MiniBlog');
    $mail->addAddress($email); // add a recipient
    $mail->addReplyTo('no-reply@' . $_SERVER['SERVER_NAME'], 'Please do not reply to this email.');
    $mail->Subject = 'Thank You for Joining MiniBlog';
    $mail->Body = 
    '<h1>Welcome to MiniBlog</h1>
    <p>Sign in any time with your new username and password:        
    </p><a href="http://' . $_SERVER['SERVER_NAME'] . dirname(dirname($_SERVER['PHP_SELF'])) . '"/>Go to site now</a>';        
        
    if ($mail->send()) {     
        $mail2 = new PHPMailer();                
        // $mail->SMTPDebug = 3; // enable verbose debug output
        $mail2->isSMTP(); // set mailer to use SMTP
        $mail2->Host = Config::SMTP_HOST;
        $mail2->Username = Config::SMTP_USER;
        $mail2->Password = Config::SMTP_PASSWORD;
        $mail2->Port = Config::SMTP_PORT;
        $mail2->SMTPSecure = 'tls'; // enable TLS encryption, `ssl` also accepted
        $mail2->SMTPAuth = true; // enable SMTP authentication
        $mail2->isHTML(true); // set email format to HTML
        $mail2->CharSet = 'UTF-8';

        // Recipients
        $mail2->setFrom('beshsaleh@gmail.com', 'MiniBlog');
        $mail2->addAddress('beshsaleh@gmail.com'); // add a recipient
        $mail2->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
        $mail2->Subject = 'NOTICE: A New Member Joined MiniBlog';
        $mail2->Body = 
        '<h1>NOTICE:</h1>
        <p>' . ucfirst($firstname) . ' ' . ucfirst($lastname) . ' (' . $username . '/' . $email . ') has joined MiniBlog.</p>';       

        $mail2->send();
    }
    
    echo "Mail sent!";
}
?>