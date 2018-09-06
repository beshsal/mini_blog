<?php
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a pattern to locate suspect phrases
// The string is used to do a case insensitive (i) search for suspect phrases assigned to $pattern
$pattern = "/Content-Type:|Bcc:|Cc:/i";

// Create a function to check for suspect phrases
// Pass in the $_POST array, the pattern, and $suspect boolean (in contact.php) as arguments when called (overrides limited scope, so 
// any change to $suspect here effects the value of $suspect elsewhere in the script)
function isSuspect($val, $pattern, &$suspect) {	
  // If the variable is an array (i.e. $_POST array), loop through each element in the array and pass it recursively back
  // to the same function
  if (is_array($val)) {
    // Loop through each element in the array and assign that element to a temporary variable called $item (passes one at a time)
	foreach ($val as $item) {
      // isSuspect() calls itself - $item, the pattern, and $suspect boolean are passed to isSuspect inside the isSuspect function, 
      // so it keeps calling itself until it finds a value that it can compare against the regex ($suspect remains false until it does)
	  isSuspect($item, $pattern, $suspect); 
	}
  } else { 
    // Otherwise, if $val is not an array, it is a single element ($item) extracted from the array and passed back to the function
	// If one of the suspect phrases is found, set the boolean to true
	if (preg_match($pattern, $val)) { 
      // If there is a match (e.g. input tag with name attribute 'email' contains a value/phrase that matches $pattern),
      //then $suspect is reset to true
	  $suspect = true;
	}
  }
}

// Call isSuspect to check the $_POST array and any subarray for suspect content
isSuspect($_POST, $pattern, $suspect);

// If $suspect remains false
// (if $suspect was true, there would be no point in processing $_POST any further, so variables would not be processed)
if (!$suspect) {
  foreach ($_POST as $key => $value) {
	// Assign key/name attribute to a variable $key and the value to a variable $value
    // If $value is an array assign it to $temp; if not, strip whitespace from it and assign it to $temp
	$temp = is_array($value) ? $value : trim($value);
	// If $temp is empty and the key/name attribute is in the $required array, add $key to the $missing array
	if (empty($temp) && in_array($key, $required)) {
	  $missing[] = $key;
      // Create a variable with the name of the key/name attribute and set its value to an empty string
	  ${$key} = ''; 
	} elseif (in_array($key, $expected)) {
	  // Otherwise, if the field is in the $expected array, assign it to a variable of the same name as $key
      // and set $temp as its value
	  ${$key} = $temp; // this should have $name, $email, and $comment containing respective values
	}
  }
}

// Validate the user's email
// Check that no suspect phrase exists and the email field ($_POST['email']) isn't empty
if (!$suspect && !empty($email)) {
  // filter_input() is used to validate email - INPUT_POST specifies that the value must be in the $_POST array; 'email' is the name of 
  // element you want to test, and FILTER_VALIDATE_EMAIL specifies to check that the element conforms to a valid format for email; 
  // filter_input returns email address if valid, if not valid, it returns false
  $validemail = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
  // If $validemail is true (doesn't return false)
  if (!$validemail) {
    // If $validemail returns false, $issues['email'] is added to the $issues array, and a warning message is displayed (contact.php)
	$issues["email"] = true;
  }
}

if (!$suspect && !empty($phone)) {
  trim($phone);
  // Extract all numbers/digits from the entry
  $digits = preg_match_all( "/[0-9]/", $phone);
  // If there are 10 digits (standard American phone number), insert hyphens
  if ($digits == 10) {
    $validphone = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $phone);
  }    
  // If $validphone is not set (the entry does not include at least 10 digits)
  if (!isset($validphone)) {
    // "phone" is added to the $issues array, and a warning message is displayed (contact.php)
    $issues["phone"] = true;
  } else {
    $phone = $validphone;
  }
}

// $mailSent is a variable that is used to redirect the user to the thank you page after the mail has been sent
// It's set to false until mail() has succeeded, which holds the components of the mail; below is the code that builds the message
$mailSent = false;
// Go ahead only if $suspect is false, there are no issues, and all required fields have values
if (!$suspect && !$missing && !$issues) {
   // Initialize the $message variable
   // Build the message body by looping through $expected array and storing results in $message as a series of 
   // key/value pairs (key derived from each input field's name attribute)
   $message = "";
    
   // Loop through the $expected array
   // For each element in the $expected array, assign it to a temporary variable called $item
   foreach($expected as $item) {     
	 // if set and not empty, assign the value of the current item to $val
	 if (isset(${$item}) && !empty(${$item})) {
	   $val = ${$item}; // values of $name, $email, and so on assigned to $val with each passing
	 } else { 
       // Otherwise, if it has an empty value but is not required, assign "Not provided" as its value
	   $val = "Not provided"; // if a field that is not specified as required is left empty, $val is set to "Not provided"
	 }
	 // If an array, expand as a comma-separated string
     // e.g. values from multiple choice elements, like check box groups, <select> lists, submitted as sub-arrays of $_POST array
     // (not included in this demo)
	 if (is_array($val)) {
	   $val = implode(", ", $val); // subarrays are converted into comma-separated strings (adds ", " between each)
	 }
     // Replace underscores and hyphens in the key with spaces
     $item = str_replace(array('_', '-'), ' ', $item); 
	 // Add label and value to the message body
	 $message .= ucfirst($item).": $val\r\n\r\n"; // e.g Email:beshsaleh@gmail.com
   }

  // Limit the line length to 70 characters
  $message = wordwrap($message, 70);
  // The destination address, subject line, message body, and headers are passed to the mail() function and assigned to $mailSent  
//  $mailSent = mail($to, $subject, $message, $headers);
//  // When called, mail() returns true if it succeeds in handling email to web server's mail transport agent
//  if (!$mailSent) { // if false
//    $issues["mailfail"] = true; //'mailfail' is added to $issues array
//  }
    try {
        // Server settings
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
        $mail->addAddress($to); // add a recipient
        $mail->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
        $mail->Subject = $subject;
        $mail->Body = nl2br($message);
        
        // If the message was successfully processed and sent, send the user a confirmation email
        if ($mail->send()) {
            $mailSent = true;
            
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
            $mail2->addAddress($email); // add a recipient
            $mail2->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
            $mail2->Subject = 'Thank You for Contacting MiniBlog';
            $mail2->Body = '<h1>Thank You for Contacting MiniBlog</h1>
            <p>You sent the following message to MiniBlog:<p/><br>' . nl2br($message);
            $mail2->send();
        }
        
        // echo 'Message has been sent';
    } catch (Exception $e) {
        $issues["mailfail"] = true; //'mailfail' is added to $issues array
        // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}