<?php
// This file is the processing script required in insert_user.php

// Escape data from the form to prevent SQL injections
$firstname  = $conn->real_escape_string($firstname);
$lastname   = $conn->real_escape_string($lastname);
$role       = $conn->real_escape_string($role);
$username   = $conn->real_escape_string($username);
$email      = $conn->real_escape_string($email);
$password   = $conn->real_escape_string($password);
$confirmPwd = $conn->real_escape_string($confirmPwd);

// Require the class for verifying the password
require_once("../includes/classes/ValidatePwd.php");

// Set the minimum number of characters the username must be to 6
$usernameMinChars = 6;

// Initialize an array to hold any registration error
$errors = array();

// Check if the username submitted by the user already exists in the database (via a query)
$query = "SELECT username FROM users WHERE username = '{$username}'";
$result = $conn->query($query);    
confirmQuery($result);

// If the result of running the query already has a row/record that matches with a matching username value,
// that means the username is already being used by someone else
if($result->num_rows > 0) {
    $errors[] = "$username is already in use. Please choose another username.";
}

// If the first name field is empty
if(empty($firstname)) {
  $errors[] = "Please enter your first name.";
}

// If the last name field is empty
if(empty($lastname)) {
  $errors[] = "Please enter your last name.";
}

// If the last name field is empty
if(empty($username)) {
  $errors[] = "Please enter a username.";
}

// If the bio is empty
if(empty($email)) {
  $errors[] = "Please enter an email address.";
}

// Check that the username submitted from the form is not less than the set minimum
if (strlen($username) < $usernameMinChars) {
  // if it is, an error message is added to the $errors array
  $errors[] = "Username must be at least {$usernameMinChars} characters.";
}

// Check that the username has no whitespaces
if (preg_match("/\s/", $username)) {
  // If it does, an error message is added to the $errors array
  $errors[] = "Username should not contain spaces.";
}

// Access the ValidatePwd class from ValidatePwd.php and create an instance of it
// Take the password submitted from the form and change the minimum number of password characters to 10 (default is 8)
$validatePwd = new ValidatePwd($password, 10);

// Call the $validatePwd instance's methods to set password requirements:
$validatePwd->requireMixedCase(); // checks that uppercase and lowercase characters are used
$validatePwd->requireNumbers(2); // checks that at least 2 numbers are used
// $validatePwd->requireSymbols(); // checks that at least one symbol is used 

// Call the check method that checks that the validation conditions of the current instance are met
// The method will return true or false to $passwordOK
$passwordOK = $validatePwd->check();

// If the password fails to validate (false was passed to $passwordOk)
if (!$passwordOK) {    
  // the getErrors() method retrieves the array of errors ($_errors) from the $validatePwd object and saves it to the $errors array
  // The result of $validatePwd->getErrors() is merged with any existing errors added from insert_user_mysqli.inc.php
  $errors = array_merge($errors, $validatePwd->getErrors());    
}

// Check if the result of the password and the re-entered password submitted from the form are not equal
if ($password != $confirmPwd) {
  // If not, the appropriate error message is added to the $errors array
  $errors[] = "Your passwords don't match.";
}

// If there are no validation errors, the form can be submitted
if (!$errors) {
  // Get the current timestamp, and assign it to $salt
  $salt = time();
    
  // Encrypt the password and salt with SHA1    
  // The salt is concatenated to the user submitted password and saved to $pwd
  $pwd = sha1($password . $salt);
    
  // Define the query for inserting values from the form into the users table and set parameters to receive arguments from the form
  $query = "INSERT INTO users (firstname, lastname, email, username, password, salt, role) 
           VALUES(?, ?, ?, ?, ?, ?, ?)";

  // Initialize the statement
  // ($stmt = $conn->prepare() may be used directly to prepare the statement below instead of initializing the statement here)
  $stmt = $conn->stmt_init();
    
  // Prepare the SQL statement
  if (!($stmt->prepare($query))) {
     echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
  }
    
  // Bind the values from the form to the parameters
  if (!$stmt->bind_param("sssssis", $firstname, $lastname, $email, $username, $pwd, $salt, $role)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  
  // Execute the statement
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  
  // Get the current user's user_id to insert it in the user_id column of the in auth_profile if an author record is created;
  // insert_id returns the auto-generated id (primary key) used in the latest query
  $user_id = $stmt->insert_id;

  $fullname = $firstname . " " . $lastname; 

  $query = "INSERT INTO auth_profile (user_id, username, firstname, lastname, fullname, email, role) VALUES (?, ?, ?, ?, ?,?, ?)";

  if (!($stmt->prepare($query))) {
     echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
  }
  
  if (!$stmt->bind_param("issssss", $user_id, $username, $firstname, $lastname, $fullname, $email, $role)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }

  if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
  }
  
  // If insert is successful    
  if ($stmt->affected_rows == 1) {      
    // A confirmation message is assigned to $success
	$success = "<p class='success'>The user {$username} has been registered.</p>";
  } else {
    // If affected rows is 0
    $errors[] = "Sorry, there was a problem with the database."; // generic error message
  }
}