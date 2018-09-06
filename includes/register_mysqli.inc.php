<?php
// This file is the processing script required in register.inc.php
$firstname   = $conn->real_escape_string($firstname);
$lastname    = $conn->real_escape_string($lastname);
$email       = $conn->real_escape_string($email);
$username    = $conn->real_escape_string($username);
$password    = $conn->real_escape_string($password);
$confirm_pwd = $conn->real_escape_string($confirm_pwd);

// Require the class for verifying the password
// require_once("classes/ValidatePwd.php");
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set the minimum number of characters the username must be to 6
$usernameMinChars = 6;

// Initialize an array to hold registration errors
$errors = array();

// Check if the username submitted by the user already exists in the database (via a query)
 $query = "SELECT username FROM users WHERE username = '{$username}'";

 $result = $conn->query($query);    
 confirmQuery($result);

// If the query result already has a row/record that matches a matching username value,
// the username is already taken by someone else
if($result->num_rows > 0) {
     $errors[] = "{$username} is already in use. Please choose another username.";
 }

// Check that the username does not have less than the required minimum number of characters
if (strlen($username) < $usernameMinChars) {
  // if it does, an error message is added to the $errors array
  $errors[] = "Username must be at least {$usernameMinChars} characters.";
}

// Check that the username does not have whitespaces
if (preg_match("/\s/", $username)) {
  // If it does, an error message is added to the $errors array
  $errors[] = "Username should not contain spaces.";
}

// Create an instance (object) of the ValidatePwd class, and change the minimum number of password characters
// to 10 (default is 8)
$validatePwd = new ValidatePwd($password, 10);

// Call the $validatePwd instance's methods for setting password requirements:
$validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
$validatePwd->requireNumbers(2); // check that at least 2 numbers are used
// $validatePwd->requireSymbols(); //check that at least one symbol is used 

// Call the method that checks that the validation conditions of the current instance are met
// The method will return either true or false to $passwordOK
$passwordOK = $validatePwd->check();

// If the password fails to validate
if (!$passwordOK) {    
  // the getErrors() method retrieves the array of errors ($_errors) from the $validatePwd object and saves it to the 
  // $errors array; the result of $validatePwd->getErrors() is merged with any existing errors added from 
  // register_mysqli.inc.php
  $errors = array_merge($errors, $validatePwd->getErrors());
    
}

// Check if the result of the password and the re-entered password submitted from the form are not equal
if ($password != $confirm_pwd) {
  $errors[] = "Your passwords do not match.";
}

// If there are no validation errors
if (!$errors) {
  // Get the current timestamp and assigns it to $salt
  $salt = time();
    
  // Encrypt the password and salt with SHA1    
  // The salt is concatenated to the submitted password and saved to $pwd
  $pwd = sha1($password . $salt);
    
  // Insert the user data
  $query= "INSERT INTO users (firstname, lastname, email, username, password, salt, role) VALUES(?, ?, ?, ?, ?, ?, ?)";
  
  // Initialize the statement
  $stmt = $conn->stmt_init();
  
  // Prepare the SQL statement
  $stmt = $conn->prepare($query);
    
 // Bind the values from the form to parameters
 $stmt->bind_param("sssssis", $firstname, $lastname, $email, $username, $pwd, $salt, $role);
  
 // Execute the statement
 $stmt->execute();
  
 // If registration is successful    
 if ($stmt->affected_rows == 1) {
     
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

    // Recepients
    $mail->setFrom('beshsaleh@gmail.com', 'MiniBlog');
    $mail->addAddress($email); // add a recipient
    $mail->addReplyTo('no-reply@' . $_SERVER['SERVER_NAME'], 'Please do not reply to this email.');
    $mail->Subject = 'Thank You for Joining MiniBlog';
    $mail->Body = 
    '<h1>Welcome to MiniBlog</h1>
    <p>Sign in any time with your new username and password:        
    </p><a href="' . BASE_URL . '">Go to site now</a>';        
        
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

        // Recepients
        $mail2->setFrom('beshsaleh@gmail.com', 'MiniBlog');
        $mail2->addAddress('beshsaleh@gmail.com'); // add a recipient
        $mail2->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
        $mail2->Subject = 'NOTICE: A New Member Joined MiniBlog';
        $mail2->Body = 
        '<h1>NOTICE:</h1>
        <p>' . ucfirst($firstname) . ' ' . ucfirst($lastname) . ' (' . $username . '/' . $email . ') has joined MiniBlog</p>';       

        $mail2->send();
    }
     
    // header("Location: new_member.php?tempid=" . uniqid(true) . "&uname=" . $username);
    header("Location: " . BASE_URL . "welcome_new_member/" . uniqid(true) . "/" . $username);
    exit;
  } else {
       // Otherwise if registration fails
	   $errors[] = "Sorry, there was a problem with the database.";
  }
}
?>