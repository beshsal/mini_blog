<?php
// namespace MinBg;
class UploadImg {	
  // Define properties.
  protected $_uploaded = array();
  protected $_destination;
  protected $_max = 10485760; // raw binary (base 2) for 10MB (decimal/base 10)
  protected $_messages = array();
  protected $_permitted = array("image/gif",
								"image/jpeg",
								"image/pjpeg",
								"image/png");

  protected $_renamed   = false; // $_renamed is set to true if whitespace in a filename is replaced by underscores
  protected $_filenames = array();

  public function __construct($path) {
    // If the specified file is not a directory or is not writeable
	if (!is_dir($path) || !is_writable($path)) {
	  throw new Exception("{$path} must be a valid, writable directory."); 
	}
    // If the directory is valid or writeable, the path that is passed is assigned to the $_destination property, and the actual file
    // is assigned to the $_uploaded property.
	$this->_destination = $path;
	$this->_uploaded = $_FILES;
  }
  
  // Get the assigned max (default) size.  
  public function getMaxSize() {
	return number_format($this->_max/1048576, 1) . "MB"; // number_format() formats a number with grouped thousands
  }

  // If the user wants to set a new max size
  public function setMaxSize($num) {
	if (!is_numeric($num)) {
	  throw new Exception("Maximum size must be a number.");
	}
    $this->_max = (int) $num;
  }

  // $overwrite is added as argument; false sets the default value and makes the argument optional; if you want to overwrite files, 
  // use true as an argument when move() is called, i.e. move(true).
  public function move($overwrite = false) {
    // With current(), $field["name"] can be used instead of $FILES["image"]["name"].
	$field = current($this->_uploaded); // $_uploaded contains the $FILES array (the actual file array)
    // If $field["name"] is an array
	if (is_array($field["name"])) {
      // For each element, the key is assigned to $number, and its value (actual filename) is assigned to $filename.
	  foreach ($field["name"] as $number => $filename) {
		// Process the upload.
        // Each time the loop runs, $_renamed is reset.
		$this->_renamed = false;
        // At each iteration, values extracted from the current element of $_FILES are passed to the processFiles() method, 
        // e.g. via the current element's key ($number), its error, size, type, and location are accessed.
		$this->processFile($filename, $field["error"][$number], $field["size"][$number], $field["type"][$number], $field["tmp_name"][$number], $overwrite);	
	  }
	} else {
	  $this->processFile($field["name"], $field["error"], $field["size"], $field["type"], $field["tmp_name"], $overwrite);
	}
  }

  // When called, this returns any message passed to the &_messages property.
  public function getMessages() {
	return $this->_messages;
  }
  
  // Check for errors, and pass any to the $_messages property.
  // The function is passed the filename (name from $field/$_FILE) and the error level from $field["error"].
  protected function checkError($filename, $error) {
	switch ($error) {
	  case 0: // check that $_FILES["image"]["error"] == 0 (no errors)
		return true;
	  case 1:
	  case 2:
	    $this->_messages[] = "{$filename} exceeds maximum size: " . $this->getMaxSize();
		return true;
	  case 3:
		$this->_messages[] = "Error uploading {$filename}. Please try again.";
		return false;
	  case 4:
		$this->_messages[] = "No file selected.";
		return false;
	  default:
		$this->_messages[] = "System error uploading {$filename}. Contact webmaster.";
		return false;
	}
  }
  
  // Check the size of the file as reported by $_FILES ($field["size"]).
  protected function checkSize($filename, $size) {
    // If no file is selected
	if ($size == 0) {
	  return false; // false because there is no file to save, and the error message will have been created by checkError()
    // If the size of the file is too big
	} elseif ($size > $this->_max) {
	  $this->_messages[] = "{$filename} exceeds maximum size: " . $this->getMaxSize();
	  return false;
	} else {
      // If no issues, returns true
	  return true;
	}
  }
  
  // Check the type of file reported by $_FILES ($field["type"]).
  protected function checkType($filename, $type) {
	if (empty($type)) {
	  return false;
    //If not a permitted type (the $type value is not in the $_permitted array), false is returned.
	} elseif (!in_array($type, $this->_permitted)) {
	  $this->_messages[] = "$filename is not a permitted type of file.";
	  return false;
	} else {
	  return true;
	}
  }

  public function addPermittedTypes($types) {
    // Casting operator forces $types to be an array because $types must be an array for the array_merge() function.
	$types = (array) $types;
    // Call isValidMime to check if the type is valid; if not valid, it throws an error, which halts the script.
    $this->isValidMime($types);
    // If valid, merge $types with the _permitted array property (types already defined in the property).
	$this->_permitted = array_merge($this->_permitted, $types);
  }
  
  // Get the filename(s). 
  // This simply returns the value of the $_filenames property.
  public function getFilenames() {
	return $this->_filenames;
  }
  
  // Check if a valid type is used; if not, throw an exception.
  protected function isValidMime($types) {
    $alsoValid = array("image/tiff",
				       "application/pdf",
				       "text/plain",
				       "text/rtf");
  	$valid = array_merge($this->_permitted, $alsoValid);
	foreach ($types as $type) {
	  if (!in_array($type, $valid)) {
		throw new Exception("{$type} is not a permitted MIME type");
	  }
	}
  }
    
  // The function is passed a filename and a boolean variable that determines whether to overwrite the existing file.
  protected function checkName($name, $overwrite) {
    // Remove any whitespace from a filename and replace it with an underscore; add the revised filename to $nospaces.
	$nospaces = str_replace(" ", "_", $name); // character you want to replace, the replacement character, string you want to update
    // If $nospace is not the same as the original filename, then the $_renamed property is set to true.
	if ($nospaces != $name) {
	  $this->_renamed = true;
	}
    // Set whether to rename the file if one with the same name already exists or to overwrite it.
    // If overwrite is false (argument passed from move() to processFile() to checkNames), then rename the file
    // using an underscore; otherwise overwrite it.
	if (!$overwrite) {
      // scandir() returns an array of all files and folders in the directory; the array is stored in $existing.
	  $existing = scandir($this->_destination);
      // If the revised filename is in the $existing array (already in the directory)
	  if (in_array($nospaces, $existing)) {
        // If the same name is in the array, find the position of the "." in $nospaces.
		$dot = strrpos($nospaces, ".");
		if ($dot) {
          // Count the characters in $nospaces starting from position 0 up to $dot ($base is the name without the extension).
		  $base = substr($nospaces, 0, $dot);
            
          // Count the characters starting from $dot to the end of the string (the file extension name).
		  $extension = substr($nospaces, $dot);
		} else {
          // If $dot is false (e.g. name doesn't have .jpg), the full name is stored in $base and $extension is set to empty.
		  $base = $nospaces;
		  $extension = "";
		}
        // if $overwrite is false, the new filename will be built here.
		$i = 1;
		do {
		  $nospaces = $base . "_" . $i++ . $extension; // value stored in $base_$i.$extension (e.g. filename_1.jpg)
          // Keep checking for the same filename, incrementing $i if necessary, until it can't find the filename.
		} while (in_array($nospaces, $existing));
          
		$this->_renamed = true; // and since it is renamed, the $_renamed property is set to true.
	  }
	}
	return $nospaces; // the final filename result is returned
  }

  // Method that processes the file
  // The method is called with parameter values inside the move() method.
  protected function processFile($filename, $error, $size, $type, $tmp_name, $overwrite) {
	$OK = $this->checkError($filename, $error); // check the filename for error level, the resulting true or false is saved to $OK
	// if $OK is true as defined by checkError()
    if ($OK) {
      // Check the size reported by $_FILES; $sizeOK will be assigned true or false.  
	  $sizeOK = $this->checkSize($filename, $size);
      // Check the type reported by $_FILES; $typeOK will be assigned true or false.
	  $typeOK = $this->checkType($filename, $type);
      // Only if both are okay
	  if ($sizeOK && $typeOK) {
        // Create $name, which stores the result of calling the checkName method with the filename transmitted from $_FILES 
        // and $overwrite as arguments.
		$name = $this->checkName($filename, $overwrite);
        // $name is used in the move_uploaded_file's second argument to ensure the new name is used when saving the file.
		$success = move_uploaded_file($tmp_name, $this->_destination . $name); // tmp_name is replaced with a new destination ($path)
		// If successfully uploaded, save the filename to the $_filenames array and set a success message.
        if ($success) {
	      // Add the amended filename to the array of filenames.
	      $this->_filenames[] = $name;
			$message = "{$filename} uploaded successfully";
            // If the file has been renamed, a string is added to the success message string.
			if ($this->_renamed) {
			  $message .= " and renamed {$name}";
			}
			$this->_messages[] = $message; // the complete message is then assigned to the $_messages array property
		// Otherwise, if the file was not successfully uploaded, add a message informing the user to the $_messages array.  
		} else {            
		  $this->_messages[] = "Could not upload {$filename}";
		}
	  }
	}
  }
}