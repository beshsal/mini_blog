<?php
// This file is the processing script required in insert_user.php.

// Require the class for validating the password.
require_once("../includes/classes/ValidatePwd.php");

// Set the minimum number of characters the username must be.
$usernameMinChars = 6;

// Initialize an array for holding registration errors.
$errors = array();

// Make sure values are submitted for required fields.
if (empty($firstname)) {
  $errors[] = "Please enter your first name.";
}
if (empty($lastname)) {
  $errors[] = "Please enter your last name.";
}
if (empty($role)) {
  $errors[] = "Please select a role for the user.";
}

// Validate the username.

if (!empty($username)) {
    // Check if the username already exists in the database.
    $query = "SELECT username FROM users WHERE username = '{$username}'";
    $result = $conn->query($query);    
    confirmQuery($result);

    // If the query result already has a row/record that matches the entered username value,
    // the username is already taken by someone else. Add an error message to the $errors array.
    if($result->num_rows > 0) {
        $errors[] = "$username is already in use. Please choose another username.";
    }

    // Check that the username does not have fewer characters than the required minimum.
    if (strlen($username) < $usernameMinChars) {
      // if it does, add an error message to the $errors array.
      $errors[] = "Username must be at least {$usernameMinChars} characters.";
    }

    // Check that the username does not have whitespaces.
    if (preg_match("/\s/", $username)) {
      // If it does, add an error message to the $errors array.
      $errors[] = "Username should not contain spaces.";
    }
} else {
  $errors[] = "Please enter a username.";
}

// Validate the email. 
// (Change the input field's type to "email" to use the built-in HTML validation instead.)

if (!empty($email)) {
    // filter_input() is used to validate the email. INPUT_POST specifies that the value must be in the $_POST array; "email" is the name of 
    // element you want to test, and FILTER_VALIDATE_EMAIL specifies to check that the element conforms to a valid format for email; 
    // filter_input returns the email address (if valid) or false.
    $validemail = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);

    // If the entered email is valid, check if it is already in use.
    if ($validemail) {
        // Check if the email submitted by the user already exists in the database.
        $query = "SELECT email FROM users WHERE email = '{$email}'";

        $result = $conn->query($query);    
        confirmQuery($result);

        // If the query result already has a row/record that matches the entered email value,
        // the user has already registered with the email.
        if($result->num_rows > 0) {
            $errors[] = "{$email} is already registered. You cannot use the same email.";
        }
    // Otherwise, if the email is not valid, add an error message to $errors.
    } else {
        $errors[] = "Your email is invalid. Check that it is correctly formatted.";
    }
} else {
  $errors[] = "Please enter an email address.";
}

// Validate the password.

if (!empty($password)) {
    // Access the ValidatePwd class from ValidatePwd.php, and create an instance (object) of it; change the 
    // minimum number of password characters to 10 (default is 8).
    $validatePwd = new ValidatePwd($password, 10);

    // Call the object's methods to set password requirements:
    $validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
    $validatePwd->requireNumbers(2); // check that at least 2 numbers are used
    // $validatePwd->requireSymbols(); // check that at least one symbol is used 

    // Call the method that checks that the validation conditions of the current instance are met.
    // The method will return true or false to $passwordOK.
    $passwordOK = $validatePwd->check();

    // If the password fails to validate ($passwordOk is false)
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
} else {
  $errors[] = "Please enter a password.";
}

// If there are no validation errors, the new user can be inserted.
if (!$errors) {
  // Get the current timestamp, and assign it to $salt.
  $salt = time();
    
  // Encrypt the password and salt with SHA1.   
  // The salt is concatenated to the submitted password and saved to $pwd.
  $pwd = sha1($password . $salt);
    
  // Define the query for inserting a record for the user in the users table, and set parameters to receive values from the form.
  $query = "INSERT INTO users (firstname, lastname, email, username, password, salt, role) 
           VALUES(?, ?, ?, ?, ?, ?, ?)";

  // Initialize the statement.
  // ($stmt = $conn->prepare() may be used directly to prepare the statement below instead of initializing the statement here).
  $stmt = $conn->stmt_init();
    
  // Prepare the SQL statement.
  if (!($stmt->prepare($query))) {
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
  
  // Get the current user's user_id to insert it in auth_profile table if an author record is created;
  // insert_id returns the auto-generated id (primary key) used in the latest query.
  $user_id = $stmt->insert_id;

  // Create a full name for the user.
  $fullname = $firstname . " " . $lastname; 

  // Insert a record containing the user's data in the auth_profile table.
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
    // A confirmation message is assigned to $success.
    $success = "The user {$username} has been registered.";
  } else {
    // Otherwise, if the insert fails (affected_rows is 0), add a message informing the user to $errors.
    $errors[] = "Sorry, there was a problem with the database."; // generic error message
  }
}