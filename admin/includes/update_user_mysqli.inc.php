<?php
// Require the class for verifying the password.
require_once("../includes/classes/ValidatePwd.php");
// Set the minimum number of characters the username must be.
$usernameMinChars = 6;
// Initialize an array for holding registration errors.
$errors = array();

// Make sure values are submitted for required fields.
if(empty($firstname)) {
  $errors[] = "Please enter your first name.";
}
if(empty($lastname)) {
  $errors[] = "Please enter your last name.";
}
if(empty($email)) {
  $errors[] = "Please enter an email address.";
}
// Check that the username submitted from the form does not have fewer characters than the set minimum.
if (strlen($username) < $usernameMinChars) {
  // If it does, add an error message to the $errors array.
  $errors[] = "Username must be at least {$usernameMinChars} characters.";
}
// Check that the username has no whitespaces.
if (preg_match("/\s/", $username)) {
  // If it does, add an error message to the $errors array.
  $errors[] = "Username should not contain spaces.";
}
// Access the ValidatePwd class from ValidatePwd.php, and create an instance (object) of it; change the 
// minimum number of password characters to 10 (default is 8).
$validatePwd = new ValidatePwd($password, 10);
// Call the object's methods to set password requirements:
$validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
$validatePwd->requireNumbers(2); // check that at least 2 numbers are used
// Call the method that checks that the validation conditions of the current instance are met.
// The method will return true or false to $passwordOK.
$passwordOK = $validatePwd->check();
// If the password fails to validate
if (!$passwordOK) {
  // The getErrors() method retrieves the array of errors from the $validatePwd object (the object's $_errors property) and 
  // merges it into the $errors array. 
  $errors = array_merge($errors, $validatePwd->getErrors());    
}
// Make sure the values from the password and the re-entered password fields are not equal.
if ($password != $confirmPwd) {
  // If they are, add an error message to the $errors array.
  $errors[] = "Your passwords don't match.";
}

// Validate the email. (Change the input field's type to "email" if you want to use the built-in HTML validation instead.)

// filter_input() is used to validate the email. INPUT_POST specifies that the value must be in the $_POST array; "email" is the name of 
// element you want to test, and FILTER_VALIDATE_EMAIL specifies to check that the element conforms to a valid format for email; 
// filter_input returns the email address (if valid) or false.
$validemail = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

// Check if the email is valid. If not, add an error message to the $errors array. 
if (!$validemail) {
    $errors[] = "Your email is invalid. Check that it is correctly formatted.";
}

// If there are no validation errors, the user's data can be updated.
if (!$errors) {    
  // Get the current timestamp, and assign it to $salt.
  $salt = time();    
  // Encrypt the password and salt with SHA1.
  $pwd = sha1($password . $salt);
  
  // Define the query for updating the user data.  
  $query = "UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, password = ?, salt = ?, role = ? 
           WHERE user_id = {$userId}";
  
  // Prepare the SQL statement.
  if (!($stmt = $conn->prepare($query))) {
     echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
  }    
  // Bind the values from the form to the parameters.
  if (!$stmt->bind_param("sssssis", $firstname, $lastname, $email, $username, $pwd, $salt, $role)) {
    echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
  }  
  // Execute the statement.
  if (!$stmt->execute()) {
    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
  }    
  // If the update is successful    
  if ($stmt->affected_rows == 1) {      
    // A confirmation message is assigned to $success.
	  $success = "The user {$username} has been successfully updated.";      
  } else {
    // Otherwise, if the user cannot be updated (affected_rows is 0), add a message informing the user to $errors.
	  $errors[] = "Sorry, there was a problem with the database.";
  }
}