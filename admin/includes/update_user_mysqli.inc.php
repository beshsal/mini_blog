<?php
// Require the class for verifying the password
require_once("../includes/classes/ValidatePwd.php");
// Set the minimum number of characters the username must be to 6
$usernameMinChars = 6;
// Initialize an array to hold any registration error
$errors = array();

if(empty($firstname)) {
  $errors[] = "Please enter your first name.";
}
// Comment out to make optional
if(empty($lastname)) {
  $errors[] = "Please enter your last name.";
}
if(empty($email)) {
  $errors[] = "Please enter an email address.";
}
// Check that the username submitted from the form is not less than the set minimum
if (strlen($username) < $usernameMinChars) {
  // If it is, an error message is added to the $errors array
  $errors[] = "Username must be at least {$usernameMinChars} characters.";
}
// Check that the username has no whitespaces
if (preg_match("/\s/", $username)) {
  // If it does, an error message is added to the $errors array
  $errors[] = "Username should not contain spaces.";
}
// Access the ValidatePwd class from ValidatePwd.php, and create an instance of it; change the minimum number of password
// characters to 10 (default is 8)
$validatePwd = new ValidatePwd($password, 10);
// Call the $validatePwd instance's methods to set password requirements:
$validatePwd->requireMixedCase(); // checks that uppercase and lowercase characters are used
$validatePwd->requireNumbers(2); // checks that at least 2 numbers are used
// Check that the validation conditions of the current instance are met
// The method will return true or false to $passwordOK
$passwordOK = $validatePwd->check();
// If the password fails to validate
if (!$passwordOK) { 
  $errors = array_merge($errors, $validatePwd->getErrors());    
}
// Check if the password and the re-entered password submitted from the form are not equal
if ($password != $confirmPwd) {
  // If not, the appropriate error message is added to the $errors array
  $errors[] = "Your passwords don't match.";
}
// If there are no validation errors
if (!$errors) {    
  // Get the current timestamp and assign it to $salt
  $salt = time();    
  // Encrypt the password and salt with SHA1
  $pwd = sha1($password . $salt);
    
  $query = "UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, password = ?, salt = ?, role = ? 
           WHERE user_id = {$user_id}";
  
  // Prepare the SQL statement
  if (!($stmt = $conn->prepare($query))) {
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
  // If the update is successful    
  if ($stmt->affected_rows == 1) {      
    // A confirmation message is assigned to $success
	$success = "<p class='success'>The user {$username} has been successfully updated.</p>";      
  } else {
       // If the user cannot be updated (affected rows is 0)
	   $errors[] = "Sorry, there was a problem with the database.";
  }
}