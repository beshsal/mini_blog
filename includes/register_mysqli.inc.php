<?php
require_once("db.inc.php");
include("util_funcs.inc.php");
session_start(); 

// If all fields are submitted with the registration form
if (isset($_POST["fname"]) && isset($_POST["lname"]) && isset($_POST["email"]) && isset($_POST["uname"]) && 
    isset($_POST["pwd"]) && isset($_POST["conf_pwd"]) && isset($_POST["role"])) {
    
    // Require the class for validating the password.
    require_once("classes/ValidatePwd.php");
    
    // Retrieve the data entered by the user, and escape it to prevent SQL injections.
    $firstname   = trim($conn->real_escape_string($_POST["fname"]));
    $lastname    = trim($conn->real_escape_string($_POST["lname"]));
    $email       = trim($conn->real_escape_string($_POST["email"]));
    $username    = trim($conn->real_escape_string($_POST["uname"]));
    $password    = trim($conn->real_escape_string($_POST["pwd"]));
    $confirm_pwd = trim($conn->real_escape_string($_POST["conf_pwd"]));
    $role        = $_POST["role"];

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
        
    // Validate the username.
    
    if (!empty($username)) {      
        // Check if the username already exists in the database.
         $query = "SELECT username FROM users WHERE username = '{$username}'";

         $result = $conn->query($query);    
         confirmQuery($result);

        // If the query result already has a row/record that matches the entered username value,
        // the username is already taken by someone else. Add an error message to the $errors array.
        if($result->num_rows > 0) {
             $errors[] = "{$username} is already in use. Please choose another username.";
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
        } else {
            $errors[] = "Your email is invalid. Check that it is correctly formatted.";
        }
    } else {
      $errors[] = "Please enter an email address.";
    }
    
    // Validate the password.
    
    if (!empty($password)) {     
        // Create an instance (object) of the ValidatePwd class, and change the minimum number of password characters
        // to 10 (default is 8).
        $validatePwd = new ValidatePwd($password, 10);

        // Call the object's methods for setting password requirements:
        $validatePwd->requireMixedCase(); // check that uppercase and lowercase characters are used
        $validatePwd->requireNumbers(2); // check that at least 2 numbers are used
        // $validatePwd->requireSymbols(); //check that at least one symbol is used 

        // Call the method that checks that the validation conditions of the current instance are met.
        // The method will return either true or false to $passwordOK.
        $passwordOK = $validatePwd->check();

        // If the password fails to validate
        if (!$passwordOK) {    
          // The getErrors() method retrieves the array of errors from the $validatePwd object (the object's $_errors property) and 
          // merges it into the $errors array.
          $errors = array_merge($errors, $validatePwd->getErrors());    
        }

        // Make sure the values from the password and the re-entered password fields are not equal.
        if ($password != $confirm_pwd) {
          // If they are, add an error message to the $errors array.
          $errors[] = "Your passwords do not match.";
        }
    } else {
        $errors[] = "Please enter a password.";
    }

    // If there are no validation errors, insert the new user data.
    if (!$errors) {
        // Get the current timestamp and assign it to $salt.
        $salt = time();

        // Encrypt the password and salt with SHA1.    
        // The salt is concatenated to the submitted password and saved to $pwd.
        $pwd = sha1($password . $salt);

        // Define the query for inserting the user data.
        $query= "INSERT INTO users (firstname, lastname, email, username, password, salt, role) VALUES(?, ?, ?, ?, ?, ?, ?)";

        // Initialize the statement.
        $stmt = $conn->stmt_init();

        // Prepare the SQL statement.
        $stmt = $conn->prepare($query);

        // Bind the values from the form to parameters.
        $stmt->bind_param("sssssis", $firstname, $lastname, $email, $username, $pwd, $salt, $role);

        // Execute the statement.
        $stmt->execute();

        // If the registration is successful, return the specified user data in an array as a JSON representation;
        // "successPath" contains URL parameters for the location to redirect to upon successful registration.
        if ($stmt->affected_rows == 1) {
            echo json_encode(array("userSuccess" => $username, "successPath" => uniqid(true) . "/{$username}"));
        } else {
            // Otherwise, if the registration fails (affected_rows is 0), add a message informing the user to $errors.
            $errors[] = "Sorry, there was a problem with the database.";     
        }
    // If there are errors
    } else {
        // If there is more than one error, create a ul of errors. Otherwise, wrap the single error in in p tags.
        if (count($errors) > 1) {
            $userFail = "<ul class='error'>";
            foreach ($errors as $err) {
               $userFail .= "<li>$err</li>";
            }
            $userFail .= "</ul>";
        } else if (count($errors) == 1) {
            $userFail = "<p class='text-center error'>$errors[0]</p>";
        }
        
        // Return the errors in an array as a JSON representation.
        echo json_encode(array("userFail" => $userFail, "failFirstname" => $firstname, "failLastname" => $lastname, "failUsername" => $username, "failEmail" => $email));    
        
        $_SESSION["errors"] = $errors;
    }
}
?>