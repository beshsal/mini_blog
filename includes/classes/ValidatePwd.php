<?php
// This is the class for validating the password submitted from the registration form.

class ValidatePwd {  
  // Initialize protected default properties.
  protected $_password;
  protected $_minimumChars;
  protected $_mixedCase = false; 
  protected $_minimumNumbers = 0;
  protected $_minimumSymbols = 0;
  protected $_errors = array();

  // The constructor for creating an instance of the class
  // An object will be instantiated with these arguments ( setting the default password character minimum to 8,
  // but this can be overridden).
  public function __construct($password, $minimumChars = 8) {
    // The password is assigned to the current object's $_password property.
	$this->_password = $password;
    
    // Optionally overwrite the default minimum number of characters by the argument passed in register_mysqli.inc.php.
	$this->_minimumChars = $minimumChars;
  }
  
  // The method for checking that the password has mixed cases (called in register_mysqli.inc.php)
  public function requireMixedCase() {
	$this->_mixedCase = true; // reset the $_mixedCase property of the current instance to true
  }
  
  // The method for checking that at least one number is used (called in register_mysqli.inc.php)
  // The passed $num argument is set to 1 by default (changed to 2 in register_user_mysql.inc.php).
  public function requireNumbers($num = 1) {
    // Makes sure that the passed argument is a number and is greater than 0.
	if (is_numeric($num) && $num > 0) {
      // If so, it is assigned to the respective property of the current instance.
	  $this->_minimumNumbers = (int) $num; 
	}
  }

  // The method for checking all conditions are met and passing the appropriate error messages if not
  public function check() {
    // Use preg_match() with a regular expression to check the password for whitespace.
    if (preg_match('/\s/', $this->_password)) {
      // if there is a whitespace, an error message is stored in the current instance's $_errors array property.
      $this->_errors[] = 'Password cannot contain spaces.';	
    }
      
    // Check that the value in the current instance's $_password property is less than our set minimum.
    if (strlen($this->_password) < $this->_minimumChars) {
	  $this->_errors[] = "Password must be at least $this->_minimumChars characters.";
    } 
    
    // This runs only if the requireMixedCase method is called before check().
    // If $_mixedCase is set to true
	if ($this->_mixedCase) {
      // Create a regex pattern to match the value against (checks for characters that are upper and lowercase).
	  $pattern = '/(?=.*[a-z])(?=.*[A-Z])/';
      // Using preg_match, test characters inside the current instance's $_password property for upper and lowercase.
	  if (!preg_match($pattern, $this->_password)) {
		$this->_errors[] = 'Password should include uppercase and lowercase characters.';
	  }
	}
      
    // This runs only if the requireNumbers method is called before check().
	if ($this->_minimumNumbers) {        
      // Create a regex pattern to check the value in $_password against.
	  $pattern = '/\d/';        
	  $found = preg_match_all($pattern, $this->_password, $matches); // number of matches stored in $found
      
        // If the number of matches is less than the set minimum number property, an error message is passed to the 
        // current instance's $_errors property.
	  if ($found < $this->_minimumNumbers) {
		$this->_errors[] = "Password should include at least $this->_minimumNumbers number(s).";
	  }
	}
      
    // If $_errors has values, check() returns false, indicating the password failed validation; otherwise check() returns true.
	return $this->_errors ? false : true;
  }
  
  // The method that returns the $_errors array property (called in register_mysqli.inc.php)
  public function getErrors() {
	return $this->_errors; 
  }
}