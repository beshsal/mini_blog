<?php
// Import the required PHPMailer scripts.
require './vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Create a pattern to locate suspect phrases.
// The string is used to do a case insensitive (i) search for suspect phrases.
$pattern = "/Content-Type:|Bcc:|Cc:/i";

// Create a function to check for suspect phrases.
// Pass in the $_POST array, the pattern, and $suspect boolean (in contact.php) as arguments when called.
// This overrides limited scope, so any change to $suspect here effects the value of $suspect elsewhere in the script.
function isSuspect($val, $pattern, &$suspect) { 
  // If the variable is an array (i.e. $_POST array), iterate through each element in the array and pass it recursively back
  // to the same function.
  if (is_array($val)) {
    // Loop through each element in the $_POST array and assign that element to a temporary variable called $item.
  foreach ($val as $item) {
      // isSuspect() calls itself - $item, the pattern, and $suspect boolean are passed to isSuspect inside the isSuspect function, 
      // so it keeps calling itself until it finds a value that it can compare against the regex ($suspect remains false until it does).
    isSuspect($item, $pattern, $suspect); 
  }
  } else { 
    // Otherwise, if $val is not an array, it is a single element ($item) extracted from the array and passed back to the function.
  // If one of the suspect phrases is found, set the boolean to true.
  if (preg_match($pattern, $val)) { 
      // If there is a match (e.g. input tag with name attribute 'email' contains a value/phrase that matches $pattern),
      // then $suspect is reset to true.
    $suspect = true;
  }
  }
}

// Call isSuspect to check the $_POST array and any subarray for suspect content.
isSuspect($_POST, $pattern, $suspect);

// If $suspect remains false
// (if $suspect was true, there would be no point in processing $_POST any further, so variables would not be processed.)
if (!$suspect) {
  foreach ($_POST as $key => $value) {
  // For each element in the $_POST array, assign the key/name attribute to a variable $key and the value to a variable $value.
    // If $value is an array, assign it to $temp; if not, strip whitespace from it and assign it to $temp.
  $temp = is_array($value) ? $value : trim($value);
  // If $temp is empty and the name attribute is in the $required array, add $key to the $missing array.
  if (empty($temp) && in_array($key, $required)) {
    $missing[] = $key;
      // Create a variable with the name of the name attribute and set its value to an empty string.
    ${$key} = ""; 
  } elseif (in_array($key, $expected)) {
    // Otherwise, if the field is in the $expected array, assign it to a variable of the same name as $key
      // and set $temp as its value.
    ${$key} = $temp; // this should have $first_name, $last_name, $email, and $comment, etc, containing respective values
  }
  }
}

// Validate the user's email.

// Check that no suspect phrase exists and the email field ($_POST['email']) isn't empty.
if (!$suspect && !empty($email)) {
  // filter_input() is used to validate the email. INPUT_POST specifies that the value must be in the $_POST array; 'email' is the name of 
  // element you want to test, and FILTER_VALIDATE_EMAIL specifies to check that the element conforms to a valid format for email; 
  // filter_input returns the email address if valid or false.
  $validemail = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
  // If $validemail returns false, $issues['email'] is added to the $issues array, and a warning message is displayed (contact.php).
  if (!$validemail) {    
  $issues["email"] = true;
  }
}

// Validate the optional phone number.

if (!$suspect && !empty($phone)) {
  trim($phone);
  // Extract all numbers/digits from the entry.
  $digits = preg_match_all( "/[0-9]/", $phone);
  // If there are 10 digits (standard American phone number), insert hyphens.
  if ($digits == 10) {
    $validphone = preg_replace("/^1?(\d{3})(\d{3})(\d{4})$/", "$1-$2-$3", $phone);
  }    
  // If $validphone is not set (the entry does not include at least 10 digits)
  if (!isset($validphone)) {
    // "phone" is added to the $issues array, and a warning message is displayed (contact.php).
    $issues["phone"] = true;
  } else {
    $phone = $validphone;
  }
}

// $mailSent is a variable that is used to redirect the user to the thank you page after the mail has been sent.
// It's set to false until $mail->send() has succeeded; below is the code that builds the message.
$mailSent = false;
// Go ahead only if $suspect is false, there are no issues, and all required fields have values.
if (!$suspect && !$missing && !$issues) {
   // Initialize the $message variable.
   // Build the message body by looping through the $expected array and storing results in $message as a series of 
   // key/value pairs (key derived from each input field's name attribute).
   $message = "";
    
   // Loop through the $expected array
   // For each element in the $expected array, assign it to a temporary variable called $item.
   foreach($expected as $item) {     
		 // if set and not empty, assign the value of the current item to $val.
		 if (isset(${$item}) && !empty(${$item})) {
			 $val = ${$item}; // values of $name, $email, and so on assigned to $val in each iteration
		 } else { 
				 // Otherwise, if a field has an empty value but is not specified as required, assign "Not provided" as its value.
			 $val = "Not provided";
		 }
		 // If an array, expand as a comma-separated string (e.g. values from multiple choice elements like checkbox groups, <select> lists, 
			 // submitted as sub-arrays of a $_POST array). (not included in this demo)
		 if (is_array($val)) {
			 $val = implode(", ", $val); // subarrays are converted into comma-separated strings (adds ", " between each)
		 }
		 // Replace underscores and hyphens in the key with spaces.
		 $item = str_replace(array('_', '-'), ' ', $item); 
		 // Add a label and the value for each item to the message body (e.g. Email: beshsaleh@gmail.com).
		 // Note: This is formatted differently for the remote version.
		 $message .= "<label style='font-family:sans-serif;'><strong>" . ucfirst($item) . "</strong></label>:<br><span style='font-family:sans-serif;'>$val</span>\r\n\r\n";
   }

  // Limit the line length to 200 characters.
  $message = wordwrap($message, 200);
    
    // Finally, send the mail.
    
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

        // Recipients
        $mail->setFrom('beshsaleh@gmail.com', 'MiniBlog');
        $mail->addAddress($to); // add a recipient ($to is set in contact.php)
        $mail->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
        $mail->Subject = $subject;
        $mail->Body = "<p style='font-family:sans-serif;'><strong>{$first_name} {$last_name} sent the following message to MiniBlog:</strong></p><br>" . nl2br($message);
        
        // If the message was successfully processed and sent, send the user a confirmation email.
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

            // Recipients
            $mail2->setFrom('beshsaleh@gmail.com', 'MiniBlog');
            $mail2->addAddress($email); // add a recipient
            $mail2->addReplyTo('no-reply@beshsaleh.com', 'Please do not reply to this email.');
            $mail2->Subject = 'Thank You for Contacting MiniBlog';
            $mail2->Body = '<h1 style="font-family:sans-serif;">Thank You for Contacting MiniBlog</h1>
            <p style="font-family:sans-serif;"><strong>Hi, ' . $first_name . '. You sent the following message to MiniBlog:</strong><p/><br>' . nl2br($message);
            $mail2->send();
        }
        
    // If the mail could not be sent, 'mailfail' is added to $issues array.
    } catch (Exception $e) {
        $issues["mailfail"] = true; 
        // echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
    }
}